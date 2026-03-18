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
            $ins = $pdo->prepare('INSERT INTO asaas_events (event_id, event_type, created_at) VALUES (:i, :t, :c)');
            $ins->execute([
                ':i' => $idEvento,
                ':t' => $tipo,
                ':c' => date('Y-m-d H:i:s'),
            ]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return;
        }

        $payment = $evento['payment'] ?? null;
        if (!is_array($payment)) {
            return;
        }

        $subscriptionId = (string) ($payment['subscription'] ?? '');
        if ($subscriptionId === '') {
            $subscriptionId = (string) ($payment['subscriptionId'] ?? '');
        }

        if ($subscriptionId === '') {
            return;
        }

        $stmt = $pdo->prepare('SELECT id, client_id, vps_id, status FROM subscriptions WHERE asaas_subscription_id = :s LIMIT 1');
        $stmt->execute([':s' => $subscriptionId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return;
        }

        $subId = (int) $sub['id'];
        $vpsId = (int) ($sub['vps_id'] ?? 0);

        if ($tipo === 'PAYMENT_CONFIRMED' || $tipo === 'PAYMENT_RECEIVED') {
            $this->marcarAssinaturaAtiva($subId);

            if ($vpsId > 0) {
                $repoJobs = new RepositorioJobs();
                $repoJobs->criar('reativar_vps', ['vps_id' => $vpsId]);
                $repoJobs->criar('provisionar_vps', ['vps_id' => $vpsId]);
            }

            return;
        }

        if ($tipo === 'PAYMENT_OVERDUE') {
            $this->marcarAssinaturaOverdue($subId);

            if ($vpsId > 0) {
                $dias = ConfiguracoesSistema::toleranciaPagamentoDias();
                $quando = (new DateTimeImmutable('now'))->add(new DateInterval('P' . $dias . 'D'));

                $repoJobs = new RepositorioJobs();
                $repoJobs->criar('suspender_vps', [
                    'vps_id' => $vpsId,
                    'assinatura_id' => $subId,
                ], $quando);
            }

            return;
        }

        if ($tipo === 'SUBSCRIPTION_CANCELED') {
            $this->marcarAssinaturaCancelada($subId);
            if ($vpsId > 0) {
                $repoJobs = new RepositorioJobs();
                $repoJobs->criar('suspender_vps', [
                    'vps_id' => $vpsId,
                    'assinatura_id' => $subId,
                ]);
            }
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
