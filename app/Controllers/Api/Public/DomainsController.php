<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Domínios da API Pública.
 * - GET  /api/v1/domains          → Listar domínios
 * - GET  /api/v1/domains/show?id= → Detalhes
 * - POST /api/v1/domains          → Adicionar domínio
 * - DELETE /api/v1/domains?id=    → Remover domínio
 */
final class DomainsController extends BaseApiController
{
    /**
     * GET /api/v1/domains
     */
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'domains.read')) {
            return $this->proibido('Scope domains.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM client_domains WHERE client_id = :cid");
        $countStmt->execute([':cid' => $clienteId]);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT id, domain, type, status, ssl_status, verified, vps_id, created_at
             FROM client_domains WHERE client_id = :cid ORDER BY id DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':cid', $clienteId, \PDO::PARAM_INT);
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

        return $this->paginado($items, $meta, '/api/v1/domains');
    }

    /**
     * GET /api/v1/domains/show?id=
     */
    public function show(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'domains.read')) {
            return $this->proibido('Scope domains.read is required.');
        }

        $domainId = (int) ($req->query['id'] ?? 0);
        if ($domainId <= 0) {
            return $this->erro('MISSING_ID', 'The domain id parameter is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT id, domain, type, status, ssl_status, verified, vps_id, dns_records, created_at, updated_at
             FROM client_domains WHERE id = :id AND client_id = :cid LIMIT 1"
        );
        $stmt->execute([':id' => $domainId, ':cid' => $clienteId]);
        $domain = $stmt->fetch();

        if (!is_array($domain)) {
            return $this->naoEncontrado('Domain');
        }

        // Parse dns_records JSON
        if (isset($domain['dns_records'])) {
            $domain['dns_records'] = json_decode((string) $domain['dns_records'], true) ?: [];
        }

        return $this->sucesso($domain);
    }

    /**
     * POST /api/v1/domains
     */
    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'domains.write')) {
            return $this->proibido('Scope domains.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['domain', 'vps_id']);
        if ($validacao !== null) {
            return $validacao;
        }

        // Sandbox: simular sem executar
        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Domain', 'created');
        }

        $domain = strtolower(trim((string) ($dados['domain'] ?? '')));
        $vpsId = (int) ($dados['vps_id'] ?? 0);
        $type = in_array($dados['type'] ?? '', ['primary', 'addon', 'subdomain'], true) ? $dados['type'] : 'addon';
        $clienteId = $this->clienteId($req);

        // Validar formato do domínio
        if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z]{2,})+$/', $domain)) {
            return $this->validacaoFalhou([['field' => 'domain', 'message' => 'Invalid domain format.']]);
        }

        $pdo = BancoDeDados::pdo();

        // Verificar VPS pertence ao cliente
        $stmt = $pdo->prepare("SELECT id FROM vps WHERE id = :id AND client_id = :cid");
        $stmt->execute([':id' => $vpsId, ':cid' => $clienteId]);
        if (!$stmt->fetch()) {
            return $this->erro('VPS_NOT_FOUND', 'VPS not found or does not belong to this client.', 404);
        }

        // Verificar domínio duplicado
        $stmt = $pdo->prepare("SELECT id FROM client_domains WHERE domain = :domain");
        $stmt->execute([':domain' => $domain]);
        if ($stmt->fetch()) {
            return $this->erro('DOMAIN_ALREADY_EXISTS', 'This domain is already registered.', 409);
        }

        // Inserir
        $stmt = $pdo->prepare(
            "INSERT INTO client_domains (client_id, vps_id, domain, type, status, verified, created_at)
             VALUES (:cid, :vps_id, :domain, :type, 'pending', 0, NOW())"
        );
        $stmt->execute([
            ':cid' => $clienteId,
            ':vps_id' => $vpsId,
            ':domain' => $domain,
            ':type' => $type,
        ]);

        $id = (int) $pdo->lastInsertId();

        return $this->criado([
            'id' => $id,
            'domain' => $domain,
            'type' => $type,
            'status' => 'pending',
            'vps_id' => $vpsId,
        ], 'Domain added successfully. DNS verification pending.');
    }

    /**
     * DELETE /api/v1/domains?id=
     */
    public function remover(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'domains.write')) {
            return $this->proibido('Scope domains.write is required.');
        }

        $domainId = (int) ($req->query['id'] ?? 0);
        if ($domainId <= 0) {
            return $this->erro('MISSING_ID', 'The domain id is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("DELETE FROM client_domains WHERE id = :id AND client_id = :cid");
        $stmt->execute([':id' => $domainId, ':cid' => $clienteId]);

        if ($stmt->rowCount() === 0) {
            return $this->naoEncontrado('Domain');
        }

        return $this->sucesso(null, 'Domain removed successfully.');
    }
}
