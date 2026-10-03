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

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Application install', 'queued');
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

    // ════════════════════════════════════════════════════════════
    // GIT DEPLOY (Fase 7) — aplicações conectadas a repositório Git
    // ════════════════════════════════════════════════════════════

    /**
     * POST /api/v1/applications/git
     * Cria uma aplicação conectada a um repositório Git numa VPS e dispara o deploy.
     *
     * Body: {
     *   "vps_id", "name", "git_repo", "git_branch"?, "runtime"? (php|nodejs|python|cpp),
     *   "domain"?, "deploy_path"?, "php_version"?, "app_port"?,
     *   "auth_token"? (repo privado), "staging_subdomain"? (bool), "client_id"?
     * }
     */
    public function criarGitDeploy(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'applications.write')) {
            return $this->proibido('Scope applications.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['vps_id', 'name', 'git_repo']);
        if ($validacao !== null) {
            return $validacao;
        }

        // Resolver cliente-alvo (próprio ou filho gerenciado) — validado inclusive em sandbox
        [$clienteId, $erroCliente] = $this->resolverClienteAlvo($req, $dados);
        if ($erroCliente !== null) {
            return $erroCliente;
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Git application', 'installing');
        }

        $vpsId = (int) ($dados['vps_id'] ?? 0);
        $name = trim((string) ($dados['name'] ?? ''));
        $repoUrl = trim((string) ($dados['git_repo'] ?? ''));
        $branch = trim((string) ($dados['git_branch'] ?? 'main')) ?: 'main';
        $runtime = in_array($dados['runtime'] ?? '', ['php', 'nodejs', 'python', 'cpp'], true) ? (string) $dados['runtime'] : 'php';
        $domain = strtolower(trim((string) ($dados['domain'] ?? '')));
        $deployPath = rtrim(trim((string) ($dados['deploy_path'] ?? '/var/www/html')), '/') ?: '/var/www/html';
        $phpVersion = trim((string) ($dados['php_version'] ?? '8.3')) ?: '8.3';
        $appPort = (int) ($dados['app_port'] ?? 3000);
        $authToken = trim((string) ($dados['auth_token'] ?? ''));
        $staging = (bool) ($dados['staging_subdomain'] ?? false);

        $pdo = BancoDeDados::pdo();

        // Ownership da VPS (precisa pertencer ao cliente-alvo)
        $stmt = $pdo->prepare("SELECT id FROM vps WHERE id = :id AND client_id = :cid LIMIT 1");
        $stmt->execute([':id' => $vpsId, ':cid' => $clienteId]);
        if (!$stmt->fetch()) {
            return $this->naoEncontrado('VPS');
        }

        // Domínio de homologação (staging) opcional — subdomínio temporário .lrvweb
        $tempDomain = null;
        if ($staging) {
            $tempDomain = $this->gerarDominioStaging($pdo, $vpsId, $clienteId, $name);
        }

        $tokenEnc = $authToken !== '' ? \LRV\App\Services\Infra\SshCrypto::cifrar($authToken) : null;
        $agora = date('Y-m-d H:i:s');

        try {
            $pdo->prepare(
                'INSERT INTO git_deployments
                   (client_id, vps_id, name, repo_url, auth_token_enc, branch, subdomain, temp_domain,
                    deploy_path, force_overwrite, php_version, app_type, app_port, auto_deploy, status, created_at)
                 VALUES
                   (:c, :v, :n, :r, :at, :b, :s, :td, :dp, 1, :pv, :tp, :ap, 0, :st, :cr)'
            )->execute([
                ':c' => $clienteId,
                ':v' => $vpsId,
                ':n' => $name,
                ':r' => $repoUrl,
                ':at' => $tokenEnc,
                ':b' => $branch,
                ':s' => $domain !== '' ? $domain : null,
                ':td' => $tempDomain,
                ':dp' => $deployPath,
                ':pv' => $phpVersion,
                ':tp' => $runtime,
                ':ap' => $appPort,
                ':st' => 'deploying',
                ':cr' => $agora,
            ]);
        } catch (\Throwable $e) {
            return $this->erro('APP_CREATE_FAILED', 'Could not create the git application: ' . $e->getMessage(), 500);
        }

        $deployId = (int) $pdo->lastInsertId();

        // Enfileirar o deploy (executado em CLI pelo worker, sem timeout)
        try {
            (new \LRV\Core\Jobs\RepositorioJobs())->criar('git_deploy', ['deployment_id' => $deployId]);
        } catch (\Throwable) {}

        // Webhook application.installed
        try {
            (new \LRV\App\Services\PublicApi\WebhookService())->disparar(
                (int) $this->clienteId($req),
                'application.installed',
                ['id' => $deployId, 'client_id' => $clienteId, 'vps_id' => $vpsId, 'type' => 'git', 'runtime' => $runtime]
            );
        } catch (\Throwable) {}

        $stagingUrl = $tempDomain !== null ? 'https://' . $tempDomain : null;

        return $this->sucesso([
            'id' => $deployId,
            'client_id' => $clienteId,
            'vps_id' => $vpsId,
            'name' => $name,
            'runtime' => $runtime,
            'git_repo' => $repoUrl,
            'git_branch' => $branch,
            'domain' => $domain !== '' ? $domain : null,
            'staging_url' => $stagingUrl,
            'status' => 'installing',
            'note' => 'Deploy queued. Track via GET /applications/git/show?id=' . $deployId . ' (deploying → active) or the application.installed / application.deployed webhooks.',
        ], 'Git application created and deploy queued.', 202);
    }

    /**
     * GET /api/v1/applications/git/show?id=
     */
    public function showGitDeploy(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'applications.read')) {
            return $this->proibido('Scope applications.read is required.');
        }

        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return $this->erro('MISSING_ID', 'The application id is required.', 400);
        }

        $ids = $this->clienteIdsGerenciados($req);
        if ($ids === []) {
            return $this->naoAutorizado();
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT id, client_id, vps_id, name, repo_url AS git_repo, branch AS git_branch,
                    app_type AS runtime, subdomain AS domain, temp_domain, deploy_path, php_version,
                    status, last_deployed_at, last_commit_hash, last_commit_message, last_commit_author,
                    error_message, created_at
             FROM git_deployments WHERE id = ? AND client_id IN ($placeholders) LIMIT 1"
        );
        $stmt->execute(array_merge([$id], $ids));
        $dep = $stmt->fetch();

        if (!is_array($dep)) {
            return $this->naoEncontrado('Application');
        }

        if (isset($dep['temp_domain']) && $dep['temp_domain']) {
            $dep['staging_url'] = 'https://' . $dep['temp_domain'];
        }

        return $this->sucesso($dep);
    }

    /**
     * POST /api/v1/applications/git/deploy?id=
     * Re-dispara o deploy (git pull + build) de uma aplicação Git existente.
     */
    public function redeployGitDeploy(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'applications.write')) {
            return $this->proibido('Scope applications.write is required.');
        }

        $id = (int) ($req->json()['id'] ?? ($req->query['id'] ?? 0));
        if ($id <= 0) {
            return $this->erro('MISSING_ID', 'The application id is required.', 400);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Git deploy', 'queued');
        }

        $ids = $this->clienteIdsGerenciados($req);
        if ($ids === []) {
            return $this->naoAutorizado();
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT id, client_id FROM git_deployments WHERE id = ? AND client_id IN ($placeholders) AND status != 'inactive' LIMIT 1");
        $stmt->execute(array_merge([$id], $ids));
        $dep = $stmt->fetch();

        if (!is_array($dep)) {
            return $this->naoEncontrado('Application');
        }

        $pdo->prepare('UPDATE git_deployments SET status = "deploying", error_message = NULL WHERE id = :id')->execute([':id' => $id]);

        try {
            (new \LRV\Core\Jobs\RepositorioJobs())->criar('git_deploy', ['deployment_id' => $id]);
        } catch (\Throwable $e) {
            return $this->erro('DEPLOY_QUEUE_FAILED', 'Could not queue the deploy: ' . $e->getMessage(), 500);
        }

        // Webhook application.deployed
        try {
            (new \LRV\App\Services\PublicApi\WebhookService())->disparar(
                (int) $this->clienteId($req),
                'application.deployed',
                ['id' => $id, 'client_id' => (int) $dep['client_id']]
            );
        } catch (\Throwable) {}

        return $this->sucesso(['id' => $id, 'status' => 'deploying'], 'Deploy queued.', 202);
    }

    /**
     * Gera um subdomínio temporário de homologação (.lrvweb) com proxy Nginx
     * apontando para a VPS. Retorna o domínio ou null se não configurável.
     */
    private function gerarDominioStaging(\PDO $pdo, int $vpsId, int $clienteId, string $name): ?string
    {
        $tempBase = trim((string) \LRV\Core\Settings::obter('infra.temp_domain_base', ''));
        if ($tempBase === '') {
            return null;
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        $slug = substr($slug, 0, 8) ?: 'app';
        $tempDomain = $slug . substr(bin2hex(random_bytes(3)), 0, 4) . '.' . $tempBase;

        try {
            $vpsIpStmt = $pdo->prepare('SELECT s.ip_address FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :v AND v.client_id = :c LIMIT 1');
            $vpsIpStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
            $vpsRow = $vpsIpStmt->fetch();
            $vpsIp = is_array($vpsRow) ? (string) ($vpsRow['ip_address'] ?? '') : '';
            if ($vpsIp !== '') {
                (new \LRV\App\Services\Infra\NginxProxyService())->criarProxy($tempDomain, $vpsIp, 80);
            }
        } catch (\Throwable) {
            // Domínio é retornado mesmo se o proxy falhar; o deploy reconfigura o vhost depois.
        }

        return $tempDomain;
    }
}
