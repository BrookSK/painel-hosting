<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de E-mails da API Pública.
 * - GET  /api/v1/emails          → Listar contas de email
 * - POST /api/v1/emails          → Criar conta de email
 * - POST /api/v1/emails/remove   → Remover conta de email
 */
final class EmailsController extends BaseApiController
{
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'emails.read')) {
            return $this->proibido('Scope emails.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM client_emails WHERE client_id = :cid");
        $countStmt->execute([':cid' => $clienteId]);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT id, email_address, domain, quota_mb, used_mb, status, created_at
             FROM client_emails WHERE client_id = :cid ORDER BY id DESC LIMIT :limit OFFSET :offset"
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

        return $this->paginado($stmt->fetchAll() ?: [], $meta, '/api/v1/emails');
    }

    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'emails.write')) {
            return $this->proibido('Scope emails.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['email_address', 'password']);
        if ($validacao !== null) {
            return $validacao;
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Email account', 'created');
        }

        $email = strtolower(trim((string) ($dados['email_address'] ?? '')));
        $password = (string) ($dados['password'] ?? '');
        $quotaMb = (int) ($dados['quota_mb'] ?? 1024);
        $clienteId = $this->clienteId($req);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->validacaoFalhou([['field' => 'email_address', 'message' => 'Invalid email format.']]);
        }

        if (strlen($password) < 8) {
            return $this->validacaoFalhou([['field' => 'password', 'message' => 'Password must be at least 8 characters.']]);
        }

        $domain = substr($email, strpos($email, '@') + 1);
        $pdo = BancoDeDados::pdo();

        // Verificar domínio pertence ao cliente
        $stmt = $pdo->prepare("SELECT id FROM client_domains WHERE client_id = :cid AND domain = :domain");
        $stmt->execute([':cid' => $clienteId, ':domain' => $domain]);
        if (!$stmt->fetch()) {
            return $this->erro('DOMAIN_NOT_FOUND', 'Domain does not belong to this client.', 403);
        }

        // Verificar duplicata
        $stmt = $pdo->prepare("SELECT id FROM client_emails WHERE email_address = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return $this->erro('EMAIL_EXISTS', 'This email address already exists.', 409);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO client_emails (client_id, email_address, domain, quota_mb, status, created_at)
             VALUES (:cid, :email, :domain, :quota, 'creating', NOW())"
        );
        $stmt->execute([
            ':cid' => $clienteId,
            ':email' => $email,
            ':domain' => $domain,
            ':quota' => $quotaMb,
        ]);
        $id = (int) $pdo->lastInsertId();

        // Enfileirar criação no Mailcow
        $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('email_create', :payload, 'pending', NOW())"
        )->execute([':payload' => json_encode(['email_id' => $id, 'email' => $email, 'password' => $password, 'quota_mb' => $quotaMb])]);

        return $this->criado([
            'id' => $id,
            'email_address' => $email,
            'domain' => $domain,
            'quota_mb' => $quotaMb,
            'status' => 'creating',
        ], 'Email account creation queued.');
    }

    public function remover(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'emails.write')) {
            return $this->proibido('Scope emails.write is required.');
        }

        $emailId = (int) ($req->query['id'] ?? ($req->json()['id'] ?? 0));
        if ($emailId <= 0) {
            return $this->erro('MISSING_ID', 'The email id is required.', 400);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Email account', 'deleted');
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, email_address FROM client_emails WHERE id = :id AND client_id = :cid");
        $stmt->execute([':id' => $emailId, ':cid' => $clienteId]);
        $email = $stmt->fetch();

        if (!is_array($email)) {
            return $this->naoEncontrado('Email account');
        }

        $pdo->prepare("UPDATE client_emails SET status = 'deleting' WHERE id = :id")->execute([':id' => $emailId]);
        $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('email_delete', :payload, 'pending', NOW())"
        )->execute([':payload' => json_encode(['email_id' => $emailId, 'email' => $email['email_address']])]);

        return $this->sucesso(['id' => $emailId, 'status' => 'deleting'], 'Email account deletion queued.');
    }
}
