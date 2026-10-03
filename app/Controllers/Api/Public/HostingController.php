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
     * POST /api/v1/hosting
     * Provisiona uma nova VPS para um cliente (o próprio da key ou um cliente-filho).
     * Assíncrono: cria o registro, enfileira o provisionamento e retorna 202.
     *
     * Body: { "plan" (id ou nome), "client_id"?, "hostname"?, "os"?, "region"? }
     */
    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'hosting.write')) {
            return $this->proibido('Scope hosting.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['plan']);
        if ($validacao !== null) {
            return $validacao;
        }

        // Resolver cliente-alvo (próprio ou filho gerenciado) — validado inclusive em sandbox
        [$clienteId, $erroCliente] = $this->resolverClienteAlvo($req, $dados);
        if ($erroCliente !== null) {
            return $erroCliente;
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Hosting (VPS)', 'provisioning');
        }

        $pdo = BancoDeDados::pdo();

        // Localizar o plano por id (numérico) ou por nome exato (case-insensitive)
        $plano = $this->localizarPlano($pdo, (string) $dados['plan']);
        if ($plano === null) {
            return $this->erro('PLAN_NOT_FOUND', 'The requested plan was not found or is not active.', 404);
        }

        $hostname = trim((string) ($dados['hostname'] ?? ''));
        $os = trim((string) ($dados['os'] ?? ''));
        $region = trim((string) ($dados['region'] ?? ''));
        $externalRef = trim((string) ($dados['external_ref'] ?? ''));
        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            // Montar INSERT dinâmico para tolerar colunas opcionais (hostname/os) que
            // podem não existir em instalações antigas do schema.
            $colunas = ['client_id', 'server_id', 'container_id', 'cpu', 'ram', 'storage', 'status', 'created_at', 'plan_id'];
            $valores = [':c', 'NULL', 'NULL', ':cpu', ':ram', ':st', ':status', ':cr', ':pid'];
            $params = [
                ':c' => $clienteId,
                ':cpu' => (int) $plano['cpu'],
                ':ram' => (int) $plano['ram'],
                ':st' => (int) $plano['storage'],
                ':status' => 'pending_provisioning',
                ':cr' => $agora,
                ':pid' => (int) $plano['id'],
            ];
            if ($hostname !== '' && $this->colunaExiste($pdo, 'vps', 'hostname')) {
                $colunas[] = 'hostname';
                $valores[] = ':hostname';
                $params[':hostname'] = $hostname;
            }
            if ($os !== '' && $this->colunaExiste($pdo, 'vps', 'os')) {
                $colunas[] = 'os';
                $valores[] = ':os';
                $params[':os'] = $os;
            }

            $sql = 'INSERT INTO vps (' . implode(', ', $colunas) . ') VALUES (' . implode(', ', $valores) . ')';
            $pdo->prepare($sql)->execute($params);
            $vpsId = (int) $pdo->lastInsertId();

            // Assinatura ativa (sem gateway — cobrança é externa / revenda)
            $pdo->prepare(
                'INSERT INTO subscriptions (client_id, vps_id, plan_id, status, next_due_date, created_at)
                 VALUES (:c, :v, :p, :s, :n, :cr)'
            )->execute([
                ':c' => $clienteId,
                ':v' => $vpsId,
                ':p' => (int) $plano['id'],
                ':s' => 'active',
                ':n' => null,
                ':cr' => $agora,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return $this->erro('HOSTING_CREATE_FAILED', 'Could not provision VPS: ' . $e->getMessage(), 500);
        }

        // Enfileirar provisionamento automático
        try {
            (new \LRV\Core\Jobs\RepositorioJobs())->criar('provisionar_vps', ['vps_id' => $vpsId]);
        } catch (\Throwable) {}

        // Webhook hosting.created (notifica a conta-pai / revenda)
        try {
            (new \LRV\App\Services\PublicApi\WebhookService())->disparar(
                (int) $this->clienteId($req),
                'hosting.created',
                ['id' => $vpsId, 'client_id' => $clienteId, 'plan' => $plano['name'], 'status' => 'provisioning']
            );
        } catch (\Throwable) {}

        return $this->sucesso([
            'id' => $vpsId,
            'client_id' => $clienteId,
            'plan' => $plano['name'],
            'status' => 'provisioning',
            'hostname' => $hostname !== '' ? $hostname : null,
            'os' => $os !== '' ? $os : null,
            'region' => $region !== '' ? $region : null,
            'external_ref' => $externalRef !== '' ? $externalRef : null,
            'note' => 'VPS provisioning started. Track status via GET /hosting/show?id=' . $vpsId . ' (provisioning → running) or the hosting.created / hosting.ready webhooks.',
        ], 'VPS provisioning queued.', 202);
    }

    /**
     * POST /api/v1/hosting/suspend
     * Suspende a VPS (ex.: inadimplência no sistema externo). Body: { "id" }
     */
    public function suspender(Requisicao $req): Resposta
    {
        return $this->mudarEstado($req, 'suspend');
    }

    /**
     * POST /api/v1/hosting/stop
     * Para a VPS (desliga o container). Body: { "id" }
     */
    public function parar(Requisicao $req): Resposta
    {
        return $this->mudarEstado($req, 'stop');
    }

    /**
     * Lógica compartilhada de suspend/stop.
     */
    private function mudarEstado(Requisicao $req, string $acao): Resposta
    {
        if (!$this->temEscopo($req, 'hosting.write')) {
            return $this->proibido('Scope hosting.write is required.');
        }

        $vpsId = (int) ($req->json()['id'] ?? ($req->query['id'] ?? 0));
        if ($vpsId <= 0) {
            return $this->erro('MISSING_ID', 'The VPS id is required.', 400);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('VPS ' . $acao, 'queued');
        }

        $pdo = BancoDeDados::pdo();

        // Ownership: VPS do próprio cliente OU de um cliente-filho gerenciado
        $ids = $this->clienteIdsGerenciados($req);
        if ($ids === []) {
            return $this->naoAutorizado();
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, status, client_id FROM vps WHERE id = ? AND client_id IN ($placeholders) LIMIT 1");
        $stmt->execute(array_merge([$vpsId], $ids));
        $vps = $stmt->fetch();
        if (!is_array($vps)) {
            return $this->naoEncontrado('VPS');
        }

        $jobType = $acao === 'suspend' ? 'suspender_vps' : 'parar_vps';
        // Reportar o estado real que o worker vai persistir (suspender_vps grava
        // 'suspended_payment' via suspenderPorPagamento; parar_vps grava 'stopped').
        $novoStatus = $acao === 'suspend' ? 'suspending' : 'stopping';

        $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES (:t, :payload, 'pending', NOW())"
        )->execute([':t' => $jobType, ':payload' => json_encode(['vps_id' => $vpsId])]);

        // Webhook
        if ($acao === 'suspend') {
            try {
                (new \LRV\App\Services\PublicApi\WebhookService())->disparar(
                    (int) $this->clienteId($req),
                    'hosting.suspended',
                    ['id' => $vpsId, 'client_id' => (int) $vps['client_id']]
                );
            } catch (\Throwable) {}
        }

        return $this->sucesso(['vps_id' => $vpsId, 'action' => $acao, 'status' => $novoStatus], 'VPS ' . $acao . ' queued.', 202);
    }

    /**
     * Localiza um plano ativo por id numérico ou por nome exato (case-insensitive).
     * @return array|null
     */
    private function localizarPlano(\PDO $pdo, string $plan): ?array
    {
        $plan = trim($plan);
        if ($plan === '') {
            return null;
        }

        if (ctype_digit($plan)) {
            $stmt = $pdo->prepare("SELECT id, name, cpu, ram, storage FROM plans WHERE id = :id AND status = 'active' LIMIT 1");
            $stmt->execute([':id' => (int) $plan]);
        } else {
            $stmt = $pdo->prepare("SELECT id, name, cpu, ram, storage FROM plans WHERE LOWER(name) = LOWER(:name) AND status = 'active' LIMIT 1");
            $stmt->execute([':name' => $plan]);
        }

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    /**
     * Verifica se uma coluna existe numa tabela (para INSERTs tolerantes a schema).
     */
    private function colunaExiste(\PDO $pdo, string $tabela, string $coluna): bool
    {
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $tabela . '` LIKE :c');
            $stmt->execute([':c' => $coluna]);
            return $stmt->fetch() !== false;
        } catch (\Throwable) {
            return false;
        }
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
