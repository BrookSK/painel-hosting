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
            "SELECT s.id, s.status, s.vps_id,
                    s.asaas_subscription_id, s.stripe_subscription_id,
                    s.next_due_date, s.created_at,
                    p.name AS plan_name, p.price_monthly, p.price_monthly_usd, p.currency, p.cpu, p.ram, p.storage,
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
