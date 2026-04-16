<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Billing\Asaas\AsaasApi;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AssinaturasController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT s.id, s.status, s.vps_id, s.plan_id,
                    s.asaas_subscription_id, s.stripe_subscription_id,
                    s.next_due_date, s.created_at,
                    p.name AS plan_name, p.plan_type, p.price_monthly, p.price_monthly_usd, p.currency, p.cpu, p.ram, p.storage,
                    v.status AS vps_status
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             LEFT JOIN vps v ON v.id = s.vps_id
             WHERE s.client_id = :c
             ORDER BY s.id DESC"
        );
        $stmt->execute([':c' => $clienteId]);
        $assinaturas = $stmt->fetchAll() ?: [];

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinaturas-listar.php', [
            'assinaturas' => $assinaturas,
        ]);

        return Resposta::html($html);
    }

    public function historico(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT s.id, s.status,
                    s.asaas_subscription_id, s.stripe_subscription_id,
                    s.next_due_date, s.created_at,
                    p.name AS plan_name, p.price_monthly, p.price_monthly_usd, p.currency
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.client_id = :c
             ORDER BY s.id DESC"
        );
        $stmt->execute([':c' => $clienteId]);
        $assinaturas = $stmt->fetchAll() ?: [];

        // Buscar cobranças Asaas
        $cobrancas = [];
        foreach ($assinaturas as $a) {
            $asaasSubId = trim((string) ($a['asaas_subscription_id'] ?? ''));
            if ($asaasSubId === '') {
                continue;
            }
            try {
                $api = new AsaasApi(new ClienteHttp());
                $resp = $api->listarCobrancasDaAssinatura($asaasSubId);
                $data = $resp['data'] ?? [];
                if (is_array($data)) {
                    foreach ($data as $c) {
                        if (is_array($c)) {
                            $cobrancas[] = array_merge($c, [
                                'subscription_id' => (int) ($a['id'] ?? 0),
                                'plan_name' => (string) ($a['plan_name'] ?? ''),
                            ]);
                        }
                    }
                }
            } catch (\Throwable) {}
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinaturas-historico.php', [
            'assinaturas' => $assinaturas,
            'cobrancas'   => $cobrancas,
        ]);

        return Resposta::html($html);
    }

    /**
     * Tela de upgrade/downgrade: mostra planos disponíveis do mesmo tipo.
     */
    public function upgrade(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $subId = (int)($req->query['sub'] ?? 0);
        if ($subId <= 0) return Resposta::redirecionar('/cliente/assinaturas');

        $svc = new \LRV\App\Services\Billing\UpgradeService();
        $result = $svc->planosDisponiveis($subId, $clienteId);

        if (!($result['ok'] ?? false)) {
            $_SESSION['flash_warning'] = $result['erro'] ?? 'Assinatura não encontrada.';
            return Resposta::redirecionar('/cliente/assinaturas');
        }

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-upgrade.php', [
            'subscription' => $result['subscription'],
            'planos' => $result['planos'],
            'current_plan_id' => $result['current_plan_id'],
        ]));
    }

    /**
     * Executa o upgrade/downgrade.
     */
    public function executarUpgrade(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $subId = (int)($req->post['subscription_id'] ?? 0);
        $newPlanId = (int)($req->post['new_plan_id'] ?? 0);

        if ($subId <= 0 || $newPlanId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Dados inválidos.'], 422);
        }

        $svc = new \LRV\App\Services\Billing\UpgradeService();

        try {
            $result = $svc->executar($subId, $newPlanId, $clienteId);

            (new \LRV\App\Services\Audit\AuditLogService())->registrar(
                'client', $clienteId, 'billing.upgrade_plan', 'subscription', $subId,
                ['subscription_id' => $subId, 'new_plan_id' => $newPlanId, 'type' => $result['type'] ?? 'upgrade'], $req
            );

            return Resposta::json([
                'ok' => true,
                'type' => $result['type'],
                'mensagem' => ($result['type'] === 'upgrade' ? 'Upgrade' : 'Downgrade') . ' realizado com sucesso! Plano alterado de ' . ($result['old_plan'] ?? '') . ' para ' . ($result['new_plan'] ?? '') . '.',
            ]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 400);
        }
    }

    /**
     * Tela de addons: mostra addons disponíveis e contratados.
     */
    public function addons(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $subId = (int)($req->query['sub'] ?? 0);
        if ($subId <= 0) return Resposta::redirecionar('/cliente/assinaturas');

        $svc = new \LRV\App\Services\Billing\UpgradeService();
        $result = $svc->addonsDisponiveis($subId, $clienteId);

        if (!($result['ok'] ?? false)) {
            $_SESSION['flash_warning'] = $result['erro'] ?? 'Assinatura não encontrada.';
            return Resposta::redirecionar('/cliente/assinaturas');
        }

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-addons.php', [
            'subscription' => $result['subscription'],
            'addons' => $result['addons'],
            'active_addon_ids' => $result['active_addon_ids'],
            'sub_id' => $subId,
        ]));
    }

    /**
     * Contrata um addon avulso.
     */
    public function contratarAddon(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $subId = (int)($req->post['subscription_id'] ?? 0);
        $addonId = (int)($req->post['addon_id'] ?? 0);

        if ($subId <= 0 || $addonId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Dados inválidos.'], 422);
        }

        $svc = new \LRV\App\Services\Billing\UpgradeService();

        try {
            $result = $svc->contratarAddon($subId, $addonId, $clienteId);

            (new \LRV\App\Services\Audit\AuditLogService())->registrar(
                'client', $clienteId, 'billing.addon_subscribe', 'subscription', $subId,
                ['subscription_id' => $subId, 'addon_id' => $addonId, 'addon_name' => $result['addon_name'] ?? ''], $req
            );

            return Resposta::json(['ok' => true, 'mensagem' => 'Serviço adicional "' . ($result['addon_name'] ?? '') . '" contratado com sucesso!']);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 400);
        }
    }

    /**
     * Cancela um addon contratado.
     */
    public function cancelarAddon(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $itemId = (int)($req->post['item_id'] ?? 0);
        if ($itemId <= 0) return Resposta::json(['ok' => false, 'erro' => 'Dados inválidos.'], 422);

        $svc = new \LRV\App\Services\Billing\UpgradeService();

        try {
            $result = $svc->cancelarAddon($itemId, $clienteId);
            return Resposta::json(['ok' => true, 'mensagem' => 'Serviço "' . ($result['addon_name'] ?? '') . '" cancelado.']);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 400);
        }
    }

    public function solicitarReembolso(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $subscriptionId = (int) ($req->post['subscription_id'] ?? 0);
        $motivo = trim((string) ($req->post['motivo'] ?? ''));

        if ($subscriptionId <= 0 || $motivo === '') {
            return Resposta::texto('Dados inválidos.', 400);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id, status FROM subscriptions WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subscriptionId, ':c' => $clienteId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return Resposta::texto('Assinatura não encontrada.', 403);
        }

        $agora = date('Y-m-d H:i:s');
        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare('INSERT INTO tickets (client_id, subject, status, priority, department, assigned_to, created_at, updated_at) VALUES (:c, :s, :st, :p, :d, NULL, :cr, :up)');
            $ins->execute([
                ':c'  => $clienteId,
                ':s'  => 'Solicitação de reembolso - Assinatura #' . $subscriptionId,
                ':st' => 'open',
                ':p'  => 'high',
                ':d'  => 'financeiro',
                ':cr' => $agora,
                ':up' => $agora,
            ]);
            $ticketId = (int) $pdo->lastInsertId();

            $msg = "Solicitação de reembolso para assinatura #{$subscriptionId}.\n\nMotivo: {$motivo}";
            $insMsg = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message, attachment_name, attachment_size, created_at) VALUES (:t, :ty, :sid, :m, NULL, NULL, :cr)');
            $insMsg->execute([':t' => $ticketId, ':ty' => 'client', ':sid' => $clienteId, ':m' => $msg, ':cr' => $agora]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Não foi possível criar a solicitação.', 500);
        }

        return Resposta::redirecionar('/cliente/tickets/ver?id=' . $ticketId);
    }
}
