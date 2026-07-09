<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Aplicações da API Pública.
 * - GET  /api/v1/applications             → Listar aplicações do cliente
 * - GET  /api/v1/applications/catalog     → Catálogo de templates
 * - POST /api/v1/applications/install     → Instalar aplicação
 * - GET  /api/v1/applications/status?id=  → Status de instalação
 */
final class ApplicationsController extends BaseApiController
{
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'applications.read')) {
            return $this->proibido('Scope applications.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM client_applications WHERE client_id = :cid");
        $countStmt->execute([':cid' => $clienteId]);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT a.id, a.vps_id, a.template_id, a.domain, a.status, a.container_id, a.created_at,
                    t.name AS template_name, t.category AS template_category
             FROM client_applications a
             LEFT JOIN app_templates t ON t.id = a.template_id
             WHERE a.client_id = :cid ORDER BY a.id DESC LIMIT :limit OFFSET :offset"
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

        return $this->paginado($stmt->fetchAll() ?: [], $meta, '/api/v1/applications');
    }

    public function catalogo(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query(
            "SELECT id, name, slug, category, description, min_ram, min_storage, is_active
             FROM app_templates WHERE is_active = 1 ORDER BY category, name"
        );
        $templates = $stmt->fetchAll() ?: [];

        return $this->sucesso($templates);
    }

    public function instalar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'applications.write')) {
            return $this->proibido('Scope applications.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['template_id', 'vps_id']);
        if ($validacao !== null) {
            return $validacao;
        }

        $templateId = (int) ($dados['template_id'] ?? 0);
        $vpsId = (int) ($dados['vps_id'] ?? 0);
        $domain = trim((string) ($dados['domain'] ?? ''));
        $envJson = (string) ($dados['env_json'] ?? '');
        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        // Verificar VPS
        $stmt = $pdo->prepare("SELECT id, status FROM vps WHERE id = :id AND client_id = :cid");
        $stmt->execute([':id' => $vpsId, ':cid' => $clienteId]);
        if (!$stmt->fetch()) {
            return $this->naoEncontrado('VPS');
        }

        // Verificar template
        $stmt = $pdo->prepare("SELECT id, name FROM app_templates WHERE id = :id AND is_active = 1");
        $stmt->execute([':id' => $templateId]);
        if (!$stmt->fetch()) {
            return $this->naoEncontrado('Template');
        }

        // Criar registro de aplicação
        $stmt = $pdo->prepare(
            "INSERT INTO client_applications (client_id, vps_id, template_id, domain, status, created_at)
             VALUES (:cid, :vps_id, :tid, :domain, 'installing', NOW())"
        );
        $stmt->execute([
            ':cid' => $clienteId,
            ':vps_id' => $vpsId,
            ':tid' => $templateId,
            ':domain' => $domain !== '' ? $domain : null,
        ]);
        $appId = (int) $pdo->lastInsertId();

        // Enfileirar job de instalação
        $stmt = $pdo->prepare(
            "INSERT INTO jobs (type, payload, status, created_at) VALUES ('app_install', :payload, 'pending', NOW())"
        );
        $stmt->execute([':payload' => json_encode([
            'application_id' => $appId,
            'template_id' => $templateId,
            'vps_id' => $vpsId,
            'domain' => $domain,
            'env_json' => $envJson,
        ])]);

        return $this->criado([
            'id' => $appId,
            'status' => 'installing',
            'vps_id' => $vpsId,
            'template_id' => $templateId,
        ], 'Application installation queued.');
    }

    public function status(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'applications.read')) {
            return $this->proibido('Scope applications.read is required.');
        }

        $appId = (int) ($req->query['id'] ?? 0);
        if ($appId <= 0) {
            return $this->erro('MISSING_ID', 'The application id is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT id, vps_id, template_id, domain, status, container_id, created_at, updated_at
             FROM client_applications WHERE id = :id AND client_id = :cid"
        );
        $stmt->execute([':id' => $appId, ':cid' => $clienteId]);
        $app = $stmt->fetch();

        if (!is_array($app)) {
            return $this->naoEncontrado('Application');
        }

        return $this->sucesso($app);
    }
}
