<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use DateInterval;
use DateTimeImmutable;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Jobs\RepositorioJobs;

final class WebhookStripeService
{
    public function processar(object $event): void
    {
        $idEvento = (string) ($event->id ?? '');
        $tipo = (string) ($event->type ?? '');

        if ($idEvento === '' || $tipo === '') {
            return;
        }

        $pdo = BancoDeDados::pdo();

        $pdo->beginTransaction();
        try {
            try {
                $ja = $pdo->prepare('SELECT id FROM stripe_events WHERE event_id = :i LIMIT 1');
                $ja->execute([':i' => $idEvento]);
                if ($ja->fetch()) {
                    $pdo->commit();
                    return;
                }

                $ins = $pdo->prepare('INSERT INTO stripe_events (event_id, event_type, created_at) VALUES (:i, :t, :c)');
                $ins->execute([
                    ':i' => $idEvento,
                    ':t' => $tipo,
                    ':c' => date('Y-m-d H:i:s'),
                ]);
            } catch (\PDOException $e) {
                $code = (string) $e->getCode();
                if ($code === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                    $pdo->commit();
                    return;
                }
            } catch (\Throwable $e) {
            }

            if ($tipo === 'invoice.paid') {
                $this->processarInvoicePaid($pdo, $event);
                $pdo->commit();
                return;
            }

            if ($tipo === 'invoice.payment_failed') {
                $this->processarInvoicePaymentFailed($pdo, $event);
                $pdo->commit();
                return;
            }

            if ($tipo === 'customer.subscription.deleted') {
                $this->processarSubscriptionDeleted($pdo, $event);
                $pdo->commit();
                return;
            }

            if ($tipo === 'customer.subscription.created' || $tipo === 'customer.subscription.updated') {
                $this->processarSubscriptionUpsert($pdo, $event);
                $pdo->commit();
                return;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function processarInvoicePaid(\PDO $pdo, object $event): void
    {
        $obj = $event->data->object ?? null;
        if (!is_object($obj)) {
            return;
        }

        $stripeSubId = (string) ($obj->subscription ?? '');
        if ($stripeSubId === '') {
            return;
        }

        $stmt = $pdo->prepare('SELECT id, client_id, vps_id, status FROM subscriptions WHERE stripe_subscription_id = :s LIMIT 1');
        $stmt->execute([':s' => $stripeSubId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return;
        }

        $subId = (int) $sub['id'];
        $vpsId = (int) ($sub['vps_id'] ?? 0);
        $clienteId = (int) ($sub['client_id'] ?? 0);

        $this->atualizarAssinatura($pdo, $subId, 'ACTIVE', null);

        if ($vpsId > 0) {
            $this->concluirSuspensoesPendentes($pdo, $vpsId, $subId, 'Pagamento confirmado (invoice.paid).');

            $upVps = $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id AND status IN ('pending_payment','suspended_payment')");
            $upVps->execute([':id' => $vpsId]);

            $repoJobs = new RepositorioJobs();
            $repoJobs->criar('alerta_billing', [
                'titulo' => 'Pagamento confirmado (Stripe)',
                'mensagem' => "Pagamento confirmado.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nEvento: invoice.paid",
            ]);
            $repoJobs->criar('reativar_vps', ['vps_id' => $vpsId]);
            $repoJobs->criar('provisionar_vps', ['vps_id' => $vpsId]);
        }
    }

    private function processarInvoicePaymentFailed(\PDO $pdo, object $event): void
    {
        $obj = $event->data->object ?? null;
        if (!is_object($obj)) {
            return;
        }

        $stripeSubId = (string) ($obj->subscription ?? '');
        if ($stripeSubId === '') {
            return;
        }

        $stmt = $pdo->prepare('SELECT id, client_id, vps_id FROM subscriptions WHERE stripe_subscription_id = :s LIMIT 1');
        $stmt->execute([':s' => $stripeSubId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return;
        }

        $subId = (int) $sub['id'];
        $vpsId = (int) ($sub['vps_id'] ?? 0);
        $clienteId = (int) ($sub['client_id'] ?? 0);

        $this->atualizarAssinatura($pdo, $subId, 'OVERDUE', null);

        if ($vpsId > 0) {
            $dias = ConfiguracoesSistema::toleranciaPagamentoDias();
            $quando = (new DateTimeImmutable('now'))->add(new DateInterval('P' . $dias . 'D'));

            $repoJobs = new RepositorioJobs();
            $repoJobs->criar('alerta_billing', [
                'titulo' => 'Pagamento falhou (Stripe)',
                'mensagem' => "Pagamento falhou.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nTolerância (dias): {$dias}\nSuspensão agendada para: " . $quando->format('Y-m-d H:i:s'),
            ]);
            $repoJobs->criar('suspender_vps', [
                'vps_id' => $vpsId,
                'assinatura_id' => $subId,
            ], $quando);
        }
    }

    private function processarSubscriptionDeleted(\PDO $pdo, object $event): void
    {
        $obj = $event->data->object ?? null;
        if (!is_object($obj)) {
            return;
        }

        $stripeSubId = (string) ($obj->id ?? '');
        if ($stripeSubId === '') {
            return;
        }

        $stmt = $pdo->prepare('SELECT id, client_id, vps_id FROM subscriptions WHERE stripe_subscription_id = :s LIMIT 1');
        $stmt->execute([':s' => $stripeSubId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return;
        }

        $subId = (int) $sub['id'];
        $vpsId = (int) ($sub['vps_id'] ?? 0);
        $clienteId = (int) ($sub['client_id'] ?? 0);

        $this->atualizarAssinatura($pdo, $subId, 'CANCELED', null);

        if ($vpsId > 0) {
            $repoJobs = new RepositorioJobs();
            $repoJobs->criar('alerta_billing', [
                'titulo' => 'Assinatura cancelada (Stripe)',
                'mensagem' => "Assinatura cancelada.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
            ]);
            $repoJobs->criar('suspender_vps', [
                'vps_id' => $vpsId,
                'assinatura_id' => $subId,
            ]);
        }
    }

    private function processarSubscriptionUpsert(\PDO $pdo, object $event): void
    {
        $obj = $event->data->object ?? null;
        if (!is_object($obj)) {
            return;
        }

        $stripeSubId = (string) ($obj->id ?? '');
        if ($stripeSubId === '') {
            return;
        }

        $metadata = $obj->metadata ?? null;
        $localSubId = 0;
        if (is_object($metadata)) {
            $localSubId = (int) ($metadata->local_subscription_id ?? 0);
        } elseif (is_array($metadata)) {
            $localSubId = (int) ($metadata['local_subscription_id'] ?? 0);
        }

        $sub = null;
        if ($localSubId > 0) {
            $stmt = $pdo->prepare('SELECT id, client_id, vps_id, status FROM subscriptions WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $localSubId]);
            $sub = $stmt->fetch();
        }

        if (!is_array($sub)) {
            $stmt = $pdo->prepare('SELECT id, client_id, vps_id, status FROM subscriptions WHERE stripe_subscription_id = :s LIMIT 1');
            $stmt->execute([':s' => $stripeSubId]);
            $sub = $stmt->fetch();
        }

        if (!is_array($sub)) {
            return;
        }

        $subId = (int) $sub['id'];
        $vpsId = (int) ($sub['vps_id'] ?? 0);
        $clienteId = (int) ($sub['client_id'] ?? 0);

        $up = $pdo->prepare('UPDATE subscriptions SET stripe_subscription_id = :s WHERE id = :id');
        $up->execute([':s' => $stripeSubId, ':id' => $subId]);

        $statusAnterior = strtoupper(trim((string) ($sub['status'] ?? '')));
        $statusStripe = strtolower(trim((string) ($obj->status ?? '')));

        $novoStatus = null;
        if ($statusStripe === 'active' || $statusStripe === 'trialing') {
            $novoStatus = 'ACTIVE';
        } elseif ($statusStripe === 'past_due' || $statusStripe === 'unpaid') {
            $novoStatus = 'OVERDUE';
        } elseif ($statusStripe === 'canceled' || $statusStripe === 'incomplete_expired') {
            $novoStatus = 'CANCELED';
        }

        if ($novoStatus === null) {
            return;
        }

        $this->atualizarAssinatura($pdo, $subId, $novoStatus, null);

        if ($vpsId <= 0) {
            return;
        }

        if ($novoStatus === 'ACTIVE' && $statusAnterior !== 'ACTIVE') {
            $this->concluirSuspensoesPendentes($pdo, $vpsId, $subId, 'Assinatura reativada (ACTIVE).');

            $upVps = $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id AND status IN ('pending_payment','suspended_payment')");
            $upVps->execute([':id' => $vpsId]);

            $repoJobs = new RepositorioJobs();
            $repoJobs->criar('alerta_billing', [
                'titulo' => 'Assinatura ativa (Stripe)',
                'mensagem' => "Assinatura marcada como ACTIVE via evento {$event->type}.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
            ]);
            $repoJobs->criar('reativar_vps', ['vps_id' => $vpsId]);
            $repoJobs->criar('provisionar_vps', ['vps_id' => $vpsId]);
            return;
        }

        if ($novoStatus === 'CANCELED' && $statusAnterior !== 'CANCELED') {
            $repoJobs = new RepositorioJobs();
            $repoJobs->criar('alerta_billing', [
                'titulo' => 'Assinatura cancelada (Stripe)',
                'mensagem' => "Assinatura marcada como CANCELED via evento {$event->type}.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
            ]);
            $repoJobs->criar('suspender_vps', [
                'vps_id' => $vpsId,
                'assinatura_id' => $subId,
            ]);
            return;
        }

        if ($novoStatus === 'OVERDUE' && $statusAnterior !== 'OVERDUE') {
            $dias = ConfiguracoesSistema::toleranciaPagamentoDias();
            $quando = (new DateTimeImmutable('now'))->add(new DateInterval('P' . $dias . 'D'));

            $repoJobs = new RepositorioJobs();
            $repoJobs->criar('alerta_billing', [
                'titulo' => 'Assinatura overdue (Stripe)',
                'mensagem' => "Assinatura marcada como OVERDUE via evento {$event->type}.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nTolerância (dias): {$dias}\nSuspensão agendada para: " . $quando->format('Y-m-d H:i:s'),
            ]);
            $repoJobs->criar('suspender_vps', [
                'vps_id' => $vpsId,
                'assinatura_id' => $subId,
            ], $quando);
        }
    }

    private function atualizarAssinatura(\PDO $pdo, int $id, string $status, ?string $nextDueDate): void
    {
        $params = [
            ':id' => $id,
        ];

        $setDue = false;
        if (is_string($nextDueDate)) {
            $d = trim($nextDueDate);
            if ($d !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1) {
                $setDue = true;
                $params[':n'] = $d;
            }
        }

        if ($setDue) {
            $up = $pdo->prepare('UPDATE subscriptions SET status = :s, next_due_date = :n WHERE id = :id');
            $params[':s'] = $status;
            $up->execute($params);
            return;
        }

        $up = $pdo->prepare('UPDATE subscriptions SET status = :s WHERE id = :id');
        $up->execute([
            ':s' => $status,
            ':id' => $id,
        ]);
    }

    private function concluirSuspensoesPendentes(\PDO $pdo, int $vpsId, int $assinaturaId, string $motivo): void
    {
        $stmt = $pdo->prepare("SELECT id, payload FROM jobs WHERE status = 'pending' AND type = 'suspender_vps' ORDER BY id DESC LIMIT 200");
        $stmt->execute();
        $jobs = $stmt->fetchAll();

        if (!is_array($jobs) || $jobs === []) {
            return;
        }

        foreach ($jobs as $j) {
            if (!is_array($j)) {
                continue;
            }

            $payloadStr = (string) ($j['payload'] ?? '');
            $payloadArr = json_decode($payloadStr, true);
            if (!is_array($payloadArr)) {
                continue;
            }

            $jVpsId = (int) ($payloadArr['vps_id'] ?? 0);
            $jAssId = (int) ($payloadArr['assinatura_id'] ?? 0);
            if ($jVpsId !== $vpsId || $jAssId !== $assinaturaId) {
                continue;
            }

            $jobId = (int) ($j['id'] ?? 0);
            if ($jobId <= 0) {
                continue;
            }

            $msg = "\n[CANCELADO] suspender_vps encerrado automaticamente. Motivo: " . $motivo . ' - ' . date('Y-m-d H:i:s');
            $up = $pdo->prepare("UPDATE jobs SET status = 'completed', log = CONCAT(COALESCE(log,''), :m), updated_at = :u WHERE id = :id AND status = 'pending'");
            $up->execute([
                ':m' => $msg,
                ':u' => date('Y-m-d H:i:s'),
                ':id' => $jobId,
            ]);
        }
    }
}
