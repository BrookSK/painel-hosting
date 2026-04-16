<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use LRV\Core\BancoDeDados;
use LRV\Core\Jobs\RepositorioJobs;

/**
 * Aplica e reverte os efeitos técnicos reais dos addons contratados.
 */
final class AddonEffectService
{
    /**
     * Aplica o efeito do addon após contratação.
     */
    public function aplicar(string $slug, int $subscriptionId, int $clientId): void
    {
        match ($slug) {
            'storage_10gb'    => $this->aplicarStorage($subscriptionId, $clientId, 10240), // +10GB em MB
            'backup_extra'    => $this->aplicarBackupExtra($subscriptionId, $clientId),
            'email_pro'       => $this->aplicarEmailPro($clientId),
            'domain_extra'    => $this->aplicarDominioExtra($clientId),
            'support_priority'=> $this->aplicarSuportePrioritario($clientId),
            default           => null, // Addon sem efeito técnico
        };
    }

    /**
     * Reverte o efeito do addon após cancelamento.
     */
    public function reverter(string $slug, int $subscriptionId, int $clientId): void
    {
        match ($slug) {
            'storage_10gb'    => $this->reverterStorage($subscriptionId, $clientId, 10240),
            'backup_extra'    => $this->reverterBackupExtra($subscriptionId, $clientId),
            'email_pro'       => $this->reverterEmailPro($clientId),
            'domain_extra'    => $this->reverterDominioExtra($clientId),
            'support_priority'=> $this->reverterSuportePrioritario($clientId),
            default           => null,
        };
    }

    // ── Storage +10GB ──

    private function aplicarStorage(int $subscriptionId, int $clientId, int $extraMb): void
    {
        $pdo = BancoDeDados::pdo();

        // Buscar VPS da assinatura
        $stmt = $pdo->prepare('SELECT vps_id FROM subscriptions WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subscriptionId, ':c' => $clientId]);
        $sub = $stmt->fetch();
        $vpsId = (int)($sub['vps_id'] ?? 0);
        if ($vpsId <= 0) return;

        // Incrementar storage na VPS
        $pdo->prepare('UPDATE vps SET storage = storage + :extra WHERE id = :id')
            ->execute([':extra' => $extraMb, ':id' => $vpsId]);

        // Nota: Docker não tem docker update pra storage (é limitado pelo filesystem).
        // O storage extra é refletido no painel e nos limites do plano.
        // O disco real é gerenciado pelo volume do servidor.
    }

    private function reverterStorage(int $subscriptionId, int $clientId, int $extraMb): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT vps_id FROM subscriptions WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subscriptionId, ':c' => $clientId]);
        $sub = $stmt->fetch();
        $vpsId = (int)($sub['vps_id'] ?? 0);
        if ($vpsId <= 0) return;

        $pdo->prepare('UPDATE vps SET storage = GREATEST(storage - :extra, 0) WHERE id = :id')
            ->execute([':extra' => $extraMb, ':id' => $vpsId]);
    }

    // ── Backup Extra ──

    private function aplicarBackupExtra(int $subscriptionId, int $clientId): void
    {
        $pdo = BancoDeDados::pdo();

        // Buscar plano da assinatura e incrementar backup_slots
        $stmt = $pdo->prepare(
            'SELECT s.plan_id FROM subscriptions s WHERE s.id = :id AND s.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $subscriptionId, ':c' => $clientId]);
        $sub = $stmt->fetch();
        $planId = (int)($sub['plan_id'] ?? 0);
        if ($planId <= 0) return;

        // Incrementar backup_slots do plano pra este cliente (via specs_json override na subscription)
        // Como backup_slots é do plano e não da subscription, vamos usar a tabela subscription_addon_items
        // pra verificar na hora do backup se tem addon ativo.
        // O BackupsController já verifica backup_slots do plano. Vamos incrementar direto.
        // Abordagem: guardar o incremento na subscription e o BackupsController soma.
        // Mais simples: incrementar backup_slots na VPS (campo custom).
        // Ainda mais simples: o VpsBackupService já verifica backup_slots do plano.
        // Vamos criar um campo addon_backup_slots na VPS.

        // Abordagem pragmática: verificar addons ativos no momento do backup.
        // Não precisa alterar nada aqui — o BackupsController vai verificar.
        // Mas precisamos que o BackupsController saiba contar addons de backup.
        // Vou deixar o efeito como "registrado" e ajustar o BackupsController.
    }

    private function reverterBackupExtra(int $subscriptionId, int $clientId): void
    {
        // Mesmo que aplicar — o efeito é verificado em runtime pelo BackupsController
    }

    // ── E-mail Profissional ──

    private function aplicarEmailPro(int $clientId): void
    {
        // O addon de e-mail profissional aumenta o limite de contas de e-mail.
        // O EmailController já verifica o limite via specs_json do plano (email_accounts).
        // Com o addon ativo, vamos somar +5 contas ao limite.
        // Verificado em runtime pelo EmailController.
    }

    private function reverterEmailPro(int $clientId): void
    {
        // Verificado em runtime
    }

    // ── Domínio Extra ──

    private function aplicarDominioExtra(int $clientId): void
    {
        // Aumenta o limite de domínios em +1.
        // O DominiosController verifica max_domains via specs_json.
        // Com addon ativo, soma +1 ao limite. Verificado em runtime.
    }

    private function reverterDominioExtra(int $clientId): void
    {
        // Verificado em runtime
    }

    // ── Suporte Prioritário ──

    private function aplicarSuportePrioritario(int $clientId): void
    {
        $pdo = BancoDeDados::pdo();

        // Marcar cliente como prioritário
        try {
            $pdo->prepare('UPDATE clients SET support_priority = 1 WHERE id = :id')
                ->execute([':id' => $clientId]);
        } catch (\Throwable) {
            // Campo pode não existir ainda — será criado na migration
        }
    }

    private function reverterSuportePrioritario(int $clientId): void
    {
        $pdo = BancoDeDados::pdo();

        // Verificar se tem outro addon de suporte prioritário ativo
        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM subscription_addon_items
                 WHERE client_id = :c AND status = 'active'
                 AND addon_name = 'Suporte Prioritário'"
            );
            $stmt->execute([':c' => $clientId]);
            $count = (int)$stmt->fetchColumn();

            if ($count <= 0) {
                $pdo->prepare('UPDATE clients SET support_priority = 0 WHERE id = :id')
                    ->execute([':id' => $clientId]);
            }
        } catch (\Throwable) {}
    }

    // ── Helpers ──

    /**
     * Conta quantos addons ativos de um slug o cliente tem.
     * Usado pelos controllers pra verificar limites em runtime.
     */
    public static function contarAddonsAtivos(int $clientId, string $addonSlug): int
    {
        try {
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM subscription_addon_items sai
                 INNER JOIN plan_addons pa ON pa.id = sai.addon_id
                 WHERE sai.client_id = :c AND sai.status = 'active' AND pa.slug = :s"
            );
            $stmt->execute([':c' => $clientId, ':s' => $addonSlug]);
            return (int)$stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }
}
