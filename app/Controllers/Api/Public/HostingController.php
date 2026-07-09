<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Hosting (VPS) da API Pública.
 * - GET  /api/v1/hosting              → Listar VPS do cliente
 * - GET  /api/v1/hosting/show?id=     → Detalhes de uma VPS
 * - POST /api/v1/hosting/restart?id=  → Reiniciar VPS
 * - GET  /api/v1/hosting/metrics?id=  → Métricas da VPS
 */
final class HostingController extends BaseApiController
{
    /**
     * GET /api/v1/hosting
     */
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'hosting.read')) {
            return $this->proibido('Scope hosting.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        // Filtros
        $where = ['v.client_id = :client_id'];
        $params = [':client_id' => $clienteId];

        $status = $req->query['status'] ?? null;
        if ($status !== null && in_array($status, ['running', 'stopped', 'provisioning', 'error'], true)) {
            $where[] = 'v.status = :status';
            $params[':status'] = $status;
        }

        $search = trim((string) ($req->query['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(v.hostname LIKE :search OR v.ip_address LIKE :search2)';
            $params[':search'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        // Count
        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM vps v $whereSql");
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        // Dados
        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $sql = "SELECT v.id, v.hostname, v.ip_address, v.status, v.os, v.cpu, v.ram, v.storage,
                       v.created_at, v.updated_at,
                       p.name AS plan_name, p.plan_type
                FROM vps v
                LEFT JOIN subscriptions s ON s.vps_id = v.id
                LEFT JOIN plans p ON p.id = s.plan_id
                $whereSql
                ORDER BY v.id DESC LIMIT :limit OFFSET :offset";

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

        return $this->paginado($items, $meta, '/api/v1/hosting');
    }

    /**
     * GET /api/v1/hosting/show?id=
     */
    public function show(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'hosting.read')) {
            return $this->proibido('Scope hosting.read is required.');
        }

        $vpsId = (int) ($req->query['id'] ?? 0);
        if ($vpsId <= 0) {
            return $this->erro('MISSING_ID', 'The VPS id parameter is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT v.id, v.hostname, v.ip_address, v.status, v.os, v.cpu, v.ram, v.storage,
                    v.created_at, v.updated_at,
                    s.id AS subscription_id, s.status AS subscription_status, s.next_due_date,
                    p.name AS plan_name, p.plan_type, p.price_monthly
             FROM vps v
             LEFT JOIN subscriptions s ON s.vps_id = v.id
             LEFT JOIN plans p ON p.id = s.plan_id
             WHERE v.id = :id AND v.client_id = :client_id
             LIMIT 1"
        );
        $stmt->execute([':id' => $vpsId, ':client_id' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return $this->naoEncontrado('VPS');
        }

        return $this->sucesso($vps);
    }

    /**
     * POST /api/v1/hosting/restart?id=
     */
    public function reiniciar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'hosting.write')) {
            return $this->proibido('Scope hosting.write is required.');
        }

        $vpsId = (int) ($req->query['id'] ?? ($req->json()['id'] ?? 0));
        if ($vpsId <= 0) {
            return $this->erro('MISSING_ID', 'The VPS id is required.', 400);
        }

        // Sandbox: simular sem executar
        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('VPS restart', 'queued');
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        // Verificar propriedade
        $stmt = $pdo->prepare("SELECT id, status FROM vps WHERE id = :id AND client_id = :client_id");
        $stmt->execute([':id' => $vpsId, ':client_id' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return $this->naoEncontrado('VPS');
        }

        if ($vps['status'] !== 'running') {
            return $this->erro('VPS_NOT_RUNNING', 'VPS must be running to restart.', 409);
        }

        // Enfileirar job de reinicialização
        $stmt = $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('vps_restart', :payload, 'pending', NOW())"
        );
        $stmt->execute([':payload' => json_encode(['vps_id' => $vpsId])]);

        return $this->sucesso(['vps_id' => $vpsId, 'action' => 'restart', 'status' => 'queued'], 'VPS restart queued.', 202);
    }

    /**
     * GET /api/v1/hosting/metrics?id=&period=
     */
    public function metricas(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'monitoring.read')) {
            return $this->proibido('Scope monitoring.read is required.');
        }

        $vpsId = (int) ($req->query['id'] ?? 0);
        if ($vpsId <= 0) {
            return $this->erro('MISSING_ID', 'The VPS id parameter is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        // Verificar propriedade
        $stmt = $pdo->prepare("SELECT id FROM vps WHERE id = :id AND client_id = :client_id");
        $stmt->execute([':id' => $vpsId, ':client_id' => $clienteId]);
        if (!$stmt->fetch()) {
            return $this->naoEncontrado('VPS');
        }

        // Período (últimas N horas)
        $period = (int) ($req->query['hours'] ?? 24);
        if ($period < 1) $period = 1;
        if ($period > 720) $period = 720; // max 30 dias

        $since = date('Y-m-d H:i:s', time() - ($period * 3600));

        $stmt = $pdo->prepare(
            "SELECT cpu_usage, ram_usage, disk_usage, recorded_at
             FROM server_metrics
             WHERE server_id = (SELECT server_id FROM vps WHERE id = :vps_id LIMIT 1)
               AND recorded_at >= :since
             ORDER BY recorded_at ASC
             LIMIT 500"
        );
        $stmt->execute([':vps_id' => $vpsId, ':since' => $since]);
        $metrics = $stmt->fetchAll() ?: [];

        return $this->sucesso([
            'vps_id' => $vpsId,
            'period_hours' => $period,
            'metrics' => $metrics,
        ]);
    }
}
