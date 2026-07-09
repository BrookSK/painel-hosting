<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Assinaturas (Billing) da API Pública.
 * - GET /api/v1/subscriptions           → Listar assinaturas
 * - GET /api/v1/subscriptions/show?id=  → Detalhes
 * - GET /api/v1/subscriptions/invoices  → Faturas de uma assinatura
 */
final class SubscriptionsController extends BaseApiController
{
    /**
     * GET /api/v1/subscriptions
     */
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'billing.read')) {
            return $this->proibido('Scope billing.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $where = ['s.client_id = :client_id'];
        $params = [':client_id' => $clienteId];

        $status = $req->query['status'] ?? null;
        if ($status !== null) {
            $where[] = 's.status = :status';
            $params[':status'] = strtoupper($status);
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM subscriptions s $whereSql");
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $sql = "SELECT s.id, s.status, s.billing_type, s.next_due_date, s.created_at,
                       p.name AS plan_name, p.plan_type, p.price_monthly, p.currency,
                       v.hostname AS vps_hostname, v.status AS vps_status
                FROM subscriptions s
                LEFT JOIN plans p ON p.id = s.plan_id
                LEFT JOIN vps v ON v.id = s.vps_id
                $whereSql ORDER BY s.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $pag['per_page'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll() ?: [];

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / $pag['per_page']),
        ];

        return $this->paginado($items, $meta, '/api/v1/subscriptions');
    }

    /**
     * GET /api/v1/subscriptions/show?id=
     */
    public function show(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'billing.read')) {
            return $this->proibido('Scope billing.read is required.');
        }

        $subId = (int) ($req->query['id'] ?? 0);
        if ($subId <= 0) {
            return $this->erro('MISSING_ID', 'The subscription id parameter is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT s.id, s.status, s.billing_type, s.next_due_date, s.created_at, s.updated_at,
                    p.name AS plan_name, p.plan_type, p.price_monthly, p.currency, p.cpu, p.ram, p.storage,
                    v.id AS vps_id, v.hostname, v.ip_address, v.status AS vps_status
             FROM subscriptions s
             LEFT JOIN plans p ON p.id = s.plan_id
             LEFT JOIN vps v ON v.id = s.vps_id
             WHERE s.id = :id AND s.client_id = :client_id
             LIMIT 1"
        );
        $stmt->execute([':id' => $subId, ':client_id' => $clienteId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return $this->naoEncontrado('Subscription');
        }

        return $this->sucesso($sub);
    }

    /**
     * GET /api/v1/subscriptions/invoices?subscription_id=
     */
    public function faturas(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'billing.read')) {
            return $this->proibido('Scope billing.read is required.');
        }

        $subId = (int) ($req->query['subscription_id'] ?? 0);
        if ($subId <= 0) {
            return $this->erro('MISSING_ID', 'The subscription_id parameter is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        // Verificar propriedade
        $check = $pdo->prepare("SELECT id FROM subscriptions WHERE id = :id AND client_id = :client_id");
        $check->execute([':id' => $subId, ':client_id' => $clienteId]);
        if (!$check->fetch()) {
            return $this->naoEncontrado('Subscription');
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM invoices WHERE subscription_id = :sid");
        $countStmt->execute([':sid' => $subId]);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT id, amount, currency, status, payment_method, due_date, paid_at, created_at
             FROM invoices WHERE subscription_id = :sid ORDER BY due_date DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':sid', $subId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $pag['per_page'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll() ?: [];

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / $pag['per_page']),
        ];

        return $this->paginado($items, $meta, '/api/v1/subscriptions/invoices?subscription_id=' . $subId);
    }
}
