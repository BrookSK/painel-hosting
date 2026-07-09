<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Bancos de Dados da API Pública.
 * - GET  /api/v1/databases         → Listar bancos
 * - POST /api/v1/databases         → Criar banco
 * - POST /api/v1/databases/remove  → Remover banco
 */
final class DatabasesController extends BaseApiController
{
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'databases.read')) {
            return $this->proibido('Scope databases.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM client_databases WHERE client_id = :cid");
        $countStmt->execute([':cid' => $clienteId]);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT id, vps_id, application_id, db_name, db_user, db_type, status, size_mb, created_at
             FROM client_databases WHERE client_id = :cid ORDER BY id DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':cid', $clienteId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $pag['per_page'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / $pag['per_page']),
        ];

        return $this->paginado($stmt->fetchAll() ?: [], $meta, '/api/v1/databases');
    }

    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'databases.write')) {
            return $this->proibido('Scope databases.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['vps_id', 'db_name']);
        if ($validacao !== null) {
            return $validacao;
        }

        // Sandbox: simular sem executar
        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Database', 'created');
        }

        $vpsId = (int) ($dados['vps_id'] ?? 0);
        $dbName = trim((string) ($dados['db_name'] ?? ''));
        $dbUser = trim((string) ($dados['db_user'] ?? $dbName));
        $dbType = in_array($dados['db_type'] ?? '', ['mysql', 'postgresql'], true) ? $dados['db_type'] : 'mysql';
        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        // Verificar VPS
        $stmt = $pdo->prepare("SELECT id FROM vps WHERE id = :id AND client_id = :cid");
        $stmt->execute([':id' => $vpsId, ':cid' => $clienteId]);
        if (!$stmt->fetch()) {
            return $this->naoEncontrado('VPS');
        }

        // Validar nome
        if (!preg_match('/^[a-z][a-z0-9_]{1,48}$/', $dbName)) {
            return $this->validacaoFalhou([['field' => 'db_name', 'message' => 'Database name must be lowercase alphanumeric (2-49 chars, start with letter).']]);
        }

        // Verificar duplicata
        $stmt = $pdo->prepare("SELECT id FROM client_databases WHERE vps_id = :vps AND db_name = :name");
        $stmt->execute([':vps' => $vpsId, ':name' => $dbName]);
        if ($stmt->fetch()) {
            return $this->erro('DATABASE_EXISTS', 'A database with this name already exists on this VPS.', 409);
        }

        // Gerar senha
        $dbPassword = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare(
            "INSERT INTO client_databases (client_id, vps_id, db_name, db_user, db_type, db_password_encrypted, status, created_at)
             VALUES (:cid, :vps, :name, :user, :type, :pass, 'creating', NOW())"
        );
        $stmt->execute([
            ':cid' => $clienteId,
            ':vps' => $vpsId,
            ':name' => $dbName,
            ':user' => $dbUser,
            ':type' => $dbType,
            ':pass' => $dbPassword, // Em produção, encriptar
        ]);
        $id = (int) $pdo->lastInsertId();

        // Enfileirar criação
        $stmt = $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('database_create', :payload, 'pending', NOW())"
        );
        $stmt->execute([':payload' => json_encode([
            'database_id' => $id, 'vps_id' => $vpsId, 'db_name' => $dbName,
            'db_user' => $dbUser, 'db_type' => $dbType,
        ])]);

        return $this->criado([
            'id' => $id,
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'db_type' => $dbType,
            'password' => $dbPassword,
            'status' => 'creating',
            'warning' => 'Store the password securely. It will not be shown again.',
        ], 'Database creation queued.');
    }

    public function remover(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'databases.write')) {
            return $this->proibido('Scope databases.write is required.');
        }

        $dbId = (int) ($req->query['id'] ?? ($req->json()['id'] ?? 0));
        if ($dbId <= 0) {
            return $this->erro('MISSING_ID', 'The database id is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, vps_id, db_name FROM client_databases WHERE id = :id AND client_id = :cid");
        $stmt->execute([':id' => $dbId, ':cid' => $clienteId]);
        $db = $stmt->fetch();

        if (!is_array($db)) {
            return $this->naoEncontrado('Database');
        }

        // Enfileirar remoção
        $pdo->prepare("UPDATE client_databases SET status = 'deleting' WHERE id = :id")->execute([':id' => $dbId]);
        $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('database_delete', :payload, 'pending', NOW())"
        )->execute([':payload' => json_encode(['database_id' => $dbId, 'vps_id' => (int) $db['vps_id'], 'db_name' => $db['db_name']])]);

        return $this->sucesso(['id' => $dbId, 'status' => 'deleting'], 'Database deletion queued.');
    }
}
