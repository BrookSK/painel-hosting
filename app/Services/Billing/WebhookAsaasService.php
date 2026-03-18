<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use DateInterval;
use DateTimeImmutable;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Jobs\RepositorioJobs;

final class WebhookAsaasService
{
    public function processar(array $evento): void
    {
        $idEvento = (string) ($evento['id'] ?? '');
        $tipo = (string) ($evento['event'] ?? '');

        if ($idEvento === '' || $tipo === '') {
            return;
        }

        $pdo = BancoDeDados::pdo();

        $pdo->beginTransaction();
        try {
            $ja = $pdo->prepare('SELECT id FROM asaas_events WHERE event_id = :i LIMIT 1');
            $ja->execute([':i' => $idEvento]);
            if ($ja->fetch()) {
                $pdo->commit();
                return;
            }

            $ins = $pdo->prepare('INSERT INTO asaas_events (event_id, event_type, created_at) VALUES (:i, :t, :c)');
            $ins->execute([
                ':i' => $idEvento,
                ':t' => $tipo,
                ':c' => date('Y-m-d H:i:s'),
            ]);

            $subscriptionId = '';

            $payment = $evento['payment'] ?? null;
            if (is_array($payment)) {
                $subscriptionId = (string) ($payment['subscription'] ?? '');
                if ($subscriptionId === '') {
                    $subscriptionId = (string) ($payment['subscriptionId'] ?? '');
                }
            }

            if ($subscriptionId === '') {
                $subObj = $evento['subscription'] ?? null;
                if (is_string($subObj)) {
                    $subscriptionId = $subObj;
                } elseif (is_array($subObj)) {
                    $subscriptionId = (string) ($subObj['id'] ?? '');
                }
            }

            if ($subscriptionId === '') {
                $subscriptionId = (string) ($evento['subscriptionId'] ?? '');
            }

            if ($subscriptionId === '') {
                $pdo->commit();
                return;
            }

            $stmt = $pdo->prepare('SELECT id, client_id, vps_id, status FROM subscriptions WHERE asaas_subscription_id = :s LIMIT 1');
            $stmt->execute([':s' => $subscriptionId]);
            $sub = $stmt->fetch();

            if (!is_array($sub)) {
                $pdo->commit();
                return;
            }

            $subId = (int) $sub['id'];
            $vpsId = (int) ($sub['vps_id'] ?? 0);
            $clienteId = (int) ($sub['client_id'] ?? 0);

            if ($tipo === 'PAYMENT_CONFIRMED' || $tipo === 'PAYMENT_RECEIVED') {
                $this->marcarAssinaturaAtiva($subId);

                if ($vpsId > 0) {
                    $upVps = $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id AND status = 'pending_payment'");
                    $upVps->execute([':id' => $vpsId]);

                    $repoJobs = new RepositorioJobs();
                    $repoJobs->criar('alerta_billing', [
                        'titulo' => 'Pagamento confirmado (Asaas)',
                        'mensagem' => "Pagamento confirmado/recebido.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nEvento: {$tipo}",
                    ]);
                    $repoJobs->criar('reativar_vps', ['vps_id' => $vpsId]);
                    $repoJobs->criar('provisionar_vps', ['vps_id' => $vpsId]);
                }

                $pdo->commit();
                return;
            }

            if ($tipo === 'PAYMENT_OVERDUE') {
                $this->marcarAssinaturaOverdue($subId);

                if ($vpsId > 0) {
                    $dias = ConfiguracoesSistema::toleranciaPagamentoDias();
                    $quando = (new DateTimeImmutable('now'))->add(new DateInterval('P' . $dias . 'D'));

                    $repoJobs = new RepositorioJobs();
                    $repoJobs->criar('alerta_billing', [
                        'titulo' => 'Pagamento overdue (Asaas)',
                        'mensagem' => "Pagamento overdue.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nTolerância (dias): {$dias}\nSuspensão agendada para: " . $quando->format('Y-m-d H:i:s'),
                    ]);
                    $repoJobs->criar('suspender_vps', [
                        'vps_id' => $vpsId,
                        'assinatura_id' => $subId,
                    ], $quando);
                }

                $pdo->commit();
                return;
            }

            if ($tipo === 'SUBSCRIPTION_CANCELED') {
                $this->marcarAssinaturaCancelada($subId);

                if ($vpsId > 0) {
                    $repoJobs = new RepositorioJobs();
                    $repoJobs->criar('alerta_billing', [
                        'titulo' => 'Assinatura cancelada (Asaas)',
                        'mensagem' => "Assinatura cancelada.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}",
                    ]);
                    $repoJobs->criar('suspender_vps', [
                        'vps_id' => $vpsId,
                        'assinatura_id' => $subId,
                    ]);
                }

                $pdo->commit();
                return;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function marcarAssinaturaAtiva(int $id): void
    {
        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare("UPDATE subscriptions SET status = 'ACTIVE' WHERE id = :id");
        $up->execute([':id' => $id]);
    }

    private function marcarAssinaturaOverdue(int $id): void
    {
        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare("UPDATE subscriptions SET status = 'OVERDUE' WHERE id = :id");
        $up->execute([':id' => $id]);
    }

    private function marcarAssinaturaCancelada(int $id): void
    {
        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare("UPDATE subscriptions SET status = 'CANCELED' WHERE id = :id");
        $up->execute([':id' => $id]);
    }
}
