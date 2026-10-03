<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\App\Services\PublicApi\WebhookService;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Clientes da API Pública (Fase 7 — provisionamento).
 * Permite que uma revenda/parceiro (ex.: helpdeskON) cadastre e consulte os
 * clientes finais que ele gerencia, isolados por created_by_client_id.
 *
 * - GET  /api/v1/clients        → Listar clientes criados por esta conta
 * - GET  /api/v1/clients/show   → Detalhe de um cliente
 * - POST /api/v1/clients        → Criar cliente final
 */
final class ClientsController extends BaseApiController
{
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'clients.read')) {
            return $this->proibido('Scope clients.read is required.');
        }

        $self = $this->clienteId($req);
        if ($self === null) {
            return $this->naoAutorizado();
        }

        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        // Lista o próprio cliente (da key) + os clientes-filhos que ele criou via API.
        $where = '(id = :self OR created_by_client_id = :self2)';

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM clients WHERE $where");
        $countStmt->execute([':self' => $self, ':self2' => $self]);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT id, name, email, cpf_cnpj AS document, phone, external_ref,
                    created_by_client_id, created_at
             FROM clients WHERE $where ORDER BY id DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':self', $self, \PDO::PARAM_INT);
        $stmt->bindValue(':self2', $self, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $pag['per_page'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = array_map([$this, 'formatar'], $stmt->fetchAll() ?: []);

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / max(1, $pag['per_page'])),
        ];

        return $this->paginado($rows, $meta, '/api/v1/clients');
    }

    public function show(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'clients.read')) {
            return $this->proibido('Scope clients.read is required.');
        }

        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return $this->erro('MISSING_ID', 'The client id is required.', 400);
        }

        $self = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT id, name, email, cpf_cnpj AS document, phone, external_ref,
                    created_by_client_id, notes, created_at
             FROM clients
             WHERE id = :id AND (id = :self OR created_by_client_id = :self2) LIMIT 1"
        );
        $stmt->execute([':id' => $id, ':self' => $self, ':self2' => $self]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return $this->naoEncontrado('Client');
        }

        return $this->sucesso($this->formatar($row));
    }

    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'clients.write')) {
            return $this->proibido('Scope clients.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['name', 'email']);
        if ($validacao !== null) {
            return $validacao;
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Client', 'created');
        }

        $self = $this->clienteId($req);
        if ($self === null) {
            return $this->naoAutorizado();
        }

        $name = trim((string) ($dados['name'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $document = trim((string) ($dados['document'] ?? ''));
        $phone = trim((string) ($dados['phone'] ?? ''));
        $notes = trim((string) ($dados['notes'] ?? ''));
        $externalRef = trim((string) ($dados['external_ref'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->validacaoFalhou([['field' => 'email', 'message' => 'Invalid email address.']]);
        }

        $pdo = BancoDeDados::pdo();

        // E-mail já cadastrado? (clients.email é UNIQUE)
        $dup = $pdo->prepare('SELECT id, created_by_client_id FROM clients WHERE email = :e LIMIT 1');
        $dup->execute([':e' => $email]);
        $existente = $dup->fetch();
        if (is_array($existente)) {
            return $this->erro('CLIENT_ALREADY_EXISTS', 'A client with this email already exists.', 409, [
                'id' => (int) $existente['id'],
            ]);
        }

        // Senha aleatória: a conta é gerenciada via API; o cliente final pode usar
        // "esqueci minha senha" no painel caso precise de acesso direto.
        $senhaAleatoria = bin2hex(random_bytes(16));

        try {
            $pdo->prepare(
                'INSERT INTO clients (name, email, cpf_cnpj, phone, password, is_managed, created_by_client_id, external_ref, notes, created_at)
                 VALUES (:n, :e, :doc, :ph, :pw, 1, :by, :ref, :notes, :cr)'
            )->execute([
                ':n' => $name,
                ':e' => $email,
                ':doc' => $document !== '' ? $document : null,
                ':ph' => $phone !== '' ? $phone : null,
                ':pw' => password_hash($senhaAleatoria, PASSWORD_BCRYPT),
                ':by' => $self,
                ':ref' => $externalRef !== '' ? $externalRef : null,
                ':notes' => $notes !== '' ? $notes : null,
                ':cr' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return $this->erro('CLIENT_CREATE_FAILED', 'Could not create the client: ' . $e->getMessage(), 500);
        }

        $id = (int) $pdo->lastInsertId();

        // Disparar webhook para a conta-pai (revenda) acompanhar
        try {
            (new WebhookService())->disparar($self, 'client.created', [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'created_by_client_id' => $self,
            ]);
        } catch (\Throwable) {}

        return $this->criado([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'document' => $document !== '' ? $document : null,
            'phone' => $phone !== '' ? $phone : null,
            'external_ref' => $externalRef !== '' ? $externalRef : null,
            'status' => 'active',
        ], 'Client created successfully.');
    }

    /**
     * Normaliza uma linha de cliente para a resposta da API.
     */
    private function formatar(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => (string) ($row['name'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'document' => $row['document'] !== null ? (string) $row['document'] : null,
            'phone' => $row['phone'] !== null ? (string) $row['phone'] : null,
            'external_ref' => isset($row['external_ref']) && $row['external_ref'] !== null ? (string) $row['external_ref'] : null,
            'notes' => isset($row['notes']) && $row['notes'] !== null ? (string) $row['notes'] : null,
            'created_by_client_id' => isset($row['created_by_client_id']) && $row['created_by_client_id'] !== null ? (int) $row['created_by_client_id'] : null,
            'status' => 'active',
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
}
