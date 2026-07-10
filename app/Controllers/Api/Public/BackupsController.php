<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Backups da API Pública.
 * - GET  /api/v1/backups           → Listar backups
 * - POST /api/v1/backups           → Criar backup sob demanda
 * - POST /api/v1/backups/restore   → Restaurar backup
 */
final class BackupsController extends BaseApiController
{
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'backups.read')) {
            return $this->proibido('Scope backups.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $where = ['b.client_id = :cid'];
        $params = [':cid' => $clienteId];

        $vpsId = (int) ($req->query['vps_id'] ?? 0);
        if ($vpsId > 0) {
            $where[] = 'b.vps_id = :vps_id';
            $params[':vps_id'] = $vpsId;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM backups b $whereSql");
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT b.id, b.vps_id, b.type, b.status, b.size_mb, b.file_path, b.created_at, b.completed_at
             FROM backups b $whereSql ORDER BY b.id DESC LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $pag['per_page'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / $pag['per_page']),
        ];

        return $this->paginado($stmt->fetchAll() ?: [], $meta, '/api/v1/backups');
    }

    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'backups.write')) {
            return $this->proibido('Scope backups.write is required.');
        }

        $dados = $req->json();
        $vpsId = (int) ($dados['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return $this->validacaoFalhou([['field' => 'vps_id', 'message' => 'VPS id is required.']]);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Backup', 'queued');
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        // Verificar propriedade
        $stmt = $pdo->prepare("SELECT id FROM vps WHERE id = :id AND client_id = :cid");
        $stmt->execute([':id' => $vpsId, ':cid' => $clienteId]);
        if (!$stmt->fetch()) {
            return $this->naoEncontrado('VPS');
        }

        // Enfileirar backup
        $stmt = $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('backup_create', :payload, 'pending', NOW())"
        );
        $stmt->execute([':payload' => json_encode(['vps_id' => $vpsId, 'client_id' => $clienteId, 'type' => 'manual'])]);

        return $this->sucesso(['vps_id' => $vpsId, 'status' => 'queued'], 'Backup creation queued.', 202);
    }

    public function restaurar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'backups.write')) {
            return $this->proibido('Scope backups.write is required.');
        }

        $dados = $req->json();
        $backupId = (int) ($dados['backup_id'] ?? 0);
        if ($backupId <= 0) {
            return $this->validacaoFalhou([['field' => 'backup_id', 'message' => 'Backup id is required.']]);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Backup restore', 'queued');
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, vps_id FROM backups WHERE id = :id AND client_id = :cid AND status = 'completed'");
        $stmt->execute([':id' => $backupId, ':cid' => $clienteId]);
        $backup = $stmt->fetch();

        if (!is_array($backup)) {
            return $this->naoEncontrado('Backup');
        }

        // Enfileirar restauração
        $stmt = $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('backup_restore', :payload, 'pending', NOW())"
        );
        $stmt->execute([':payload' => json_encode(['backup_id' => $backupId, 'vps_id' => (int) $backup['vps_id']])]);

        return $this->sucesso(['backup_id' => $backupId, 'status' => 'queued'], 'Backup restore queued.', 202);
    }
}
