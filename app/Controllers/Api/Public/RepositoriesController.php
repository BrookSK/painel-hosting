<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\App\Services\Git\GitHubService;
use LRV\App\Services\PublicApi\WebhookService;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Repositórios Git da API Pública (Fase 7 — provisionamento).
 * Cria repositórios na organização e concede/revoga acesso a desenvolvedores,
 * consumindo a API do GitHub (configurada em Settings github.*).
 *
 * - GET  /api/v1/git/repositories                   → Listar repositórios criados
 * - GET  /api/v1/git/repositories/show              → Detalhe de um repositório
 * - POST /api/v1/git/repositories                   → Criar repositório (síncrono, retorna clone_url)
 * - POST /api/v1/git/repositories/collaborators     → Conceder acesso a devs
 * - POST /api/v1/git/repositories/collaborators/remove → Revogar acesso
 */
final class RepositoriesController extends BaseApiController
{
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'git.read')) {
            return $this->proibido('Scope git.read is required.');
        }

        $ids = $this->clienteIdsGerenciados($req);
        if ($ids === []) {
            return $this->naoAutorizado();
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM git_repositories WHERE client_id IN ($placeholders)");
        $countStmt->execute($ids);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $sql = "SELECT id, client_id, provider, org, name, full_name, visibility, provider_repo_id,
                       html_url, clone_url, ssh_url, external_ref, created_at
                FROM git_repositories WHERE client_id IN ($placeholders)
                ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($ids, [$pag['per_page'], $offset]));

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / max(1, $pag['per_page'])),
        ];

        return $this->paginado($stmt->fetchAll() ?: [], $meta, '/api/v1/git/repositories');
    }

    public function show(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'git.read')) {
            return $this->proibido('Scope git.read is required.');
        }

        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return $this->erro('MISSING_ID', 'The repository id is required.', 400);
        }

        $ids = $this->clienteIdsGerenciados($req);
        if ($ids === []) {
            return $this->naoAutorizado();
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT id, client_id, provider, org, name, full_name, visibility, provider_repo_id,
                    html_url, clone_url, ssh_url, description, external_ref, created_at
             FROM git_repositories WHERE id = ? AND client_id IN ($placeholders) LIMIT 1"
        );
        $stmt->execute(array_merge([$id], $ids));
        $repo = $stmt->fetch();
        if (!is_array($repo)) {
            return $this->naoEncontrado('Repository');
        }

        // Colaboradores concedidos
        $collabStmt = $pdo->prepare('SELECT username, permission, status, created_at FROM git_repository_collaborators WHERE repository_id = :r ORDER BY id');
        $collabStmt->execute([':r' => $id]);
        $repo['collaborators'] = $collabStmt->fetchAll() ?: [];

        return $this->sucesso($repo);
    }

    /**
     * POST /api/v1/git/repositories
     * Cria um repositório na organização. SÍNCRONO: retorna clone_url/html_url reais.
     *
     * Body: { "name", "private"?, "org"?, "description"?, "external_ref"?, "client_id"? }
     */
    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'git.write')) {
            return $this->proibido('Scope git.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['name']);
        if ($validacao !== null) {
            return $validacao;
        }

        // Resolver cliente-alvo (próprio ou filho gerenciado) — validado inclusive em sandbox
        [$clienteId, $erroCliente] = $this->resolverClienteAlvo($req, $dados);
        if ($erroCliente !== null) {
            return $erroCliente;
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Git repository', 'created');
        }

        $name = trim((string) ($dados['name'] ?? ''));
        $private = array_key_exists('private', $dados) ? (bool) $dados['private'] : true;
        $org = trim((string) ($dados['org'] ?? ''));
        $description = trim((string) ($dados['description'] ?? ''));
        $externalRef = trim((string) ($dados['external_ref'] ?? ''));

        // Validar nome do repositório (regras do GitHub: letras, números, ., -, _)
        if (!preg_match('/^[A-Za-z0-9._-]{1,100}$/', $name)) {
            return $this->validacaoFalhou([['field' => 'name', 'message' => 'Invalid repository name. Use letters, numbers, dot, hyphen and underscore (max 100).']]);
        }

        $github = new GitHubService();
        if (!$github->configurado()) {
            return $this->erro('GIT_NOT_CONFIGURED', 'GitHub integration is not configured on the platform. Contact support.', 503);
        }

        try {
            $repo = $github->criarRepositorio($name, $private, $org !== '' ? $org : null, $description);
        } catch (\Throwable $e) {
            return $this->erro('GIT_REPO_CREATE_FAILED', $e->getMessage(), 502);
        }

        // Persistir o repositório criado
        $pdo = BancoDeDados::pdo();
        $pdo->prepare(
            'INSERT INTO git_repositories
               (client_id, provider, org, name, full_name, visibility, provider_repo_id, html_url, clone_url, ssh_url, description, external_ref, created_at)
             VALUES (:c, :prov, :org, :name, :full, :vis, :pid, :html, :clone, :ssh, :desc, :ref, :cr)'
        )->execute([
            ':c' => $clienteId,
            ':prov' => 'github',
            ':org' => $repo['org'] ?? null,
            ':name' => $name,
            ':full' => $repo['full_name'] ?? null,
            ':vis' => $repo['visibility'] ?? ($private ? 'private' : 'public'),
            ':pid' => $repo['id'] ?? null,
            ':html' => $repo['html_url'] ?? null,
            ':clone' => $repo['clone_url'] ?? null,
            ':ssh' => $repo['ssh_url'] ?? null,
            ':desc' => $description !== '' ? $description : null,
            ':ref' => $externalRef !== '' ? $externalRef : null,
            ':cr' => date('Y-m-d H:i:s'),
        ]);
        $repoId = (int) $pdo->lastInsertId();

        // Webhook git.repository.created
        try {
            (new WebhookService())->disparar(
                (int) $this->clienteId($req),
                'git.repository.created',
                [
                    'id' => $repoId,
                    'client_id' => $clienteId,
                    'full_name' => $repo['full_name'] ?? null,
                    'html_url' => $repo['html_url'] ?? null,
                    'clone_url' => $repo['clone_url'] ?? null,
                ]
            );
        } catch (\Throwable) {}

        return $this->criado([
            'id' => $repoId,
            'client_id' => $clienteId,
            'full_name' => $repo['full_name'] ?? null,
            'name' => $name,
            'org' => $repo['org'] ?? null,
            'visibility' => $repo['visibility'] ?? ($private ? 'private' : 'public'),
            'html_url' => $repo['html_url'] ?? null,
            'clone_url' => $repo['clone_url'] ?? null,
            'ssh_url' => $repo['ssh_url'] ?? null,
            'external_ref' => $externalRef !== '' ? $externalRef : null,
        ], 'Repository created successfully.');
    }

    /**
     * POST /api/v1/git/repositories/collaborators
     * Concede acesso a um ou mais desenvolvedores.
     *
     * Body: { "id" (repo), "usernames": ["dev1","dev2"], "permission"? (pull|push|maintain|admin) }
     */
    public function adicionarColaboradores(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'git.write')) {
            return $this->proibido('Scope git.write is required.');
        }

        $dados = $req->json();
        $repoId = (int) ($dados['id'] ?? 0);
        $usernames = $this->normalizarUsernames($dados['usernames'] ?? ($dados['username'] ?? []));
        $permission = (string) ($dados['permission'] ?? 'push');

        if ($repoId <= 0) {
            return $this->erro('MISSING_ID', 'The repository id is required.', 400);
        }
        if ($usernames === []) {
            return $this->validacaoFalhou([['field' => 'usernames', 'message' => 'Provide at least one GitHub username.']]);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Repository collaborators', 'added');
        }

        $repo = $this->buscarRepoDoCliente($req, $repoId);
        if ($repo === null) {
            return $this->naoEncontrado('Repository');
        }

        $github = new GitHubService();
        if (!$github->configurado()) {
            return $this->erro('GIT_NOT_CONFIGURED', 'GitHub integration is not configured on the platform. Contact support.', 503);
        }

        $fullName = (string) ($repo['full_name'] ?? '');
        $pdo = BancoDeDados::pdo();
        $resultados = [];
        $erros = [];

        foreach ($usernames as $username) {
            try {
                $r = $github->adicionarColaborador($fullName, $username, $permission);
                // Upsert local (UNIQUE repository_id + username)
                $pdo->prepare(
                    'INSERT INTO git_repository_collaborators (repository_id, username, permission, status, created_at)
                     VALUES (:r, :u, :p, :s, :cr)
                     ON DUPLICATE KEY UPDATE permission = VALUES(permission), status = VALUES(status)'
                )->execute([
                    ':r' => $repoId,
                    ':u' => $username,
                    ':p' => $r['permission'],
                    ':s' => $r['status'],
                    ':cr' => date('Y-m-d H:i:s'),
                ]);
                $resultados[] = $r;
            } catch (\Throwable $e) {
                $erros[] = ['username' => $username, 'message' => $e->getMessage()];
            }
        }

        // Webhook git.collaborator.added (um por colaborador adicionado com sucesso)
        if ($resultados !== []) {
            try {
                $wh = new WebhookService();
                foreach ($resultados as $r) {
                    $wh->disparar((int) $this->clienteId($req), 'git.collaborator.added', [
                        'repository_id' => $repoId,
                        'full_name' => $fullName,
                        'username' => $r['username'],
                        'permission' => $r['permission'],
                        'status' => $r['status'],
                    ]);
                }
            } catch (\Throwable) {}
        }

        // Se nada deu certo, retornar erro; se parcial, 207-like via 200 com erros
        if ($resultados === [] && $erros !== []) {
            return $this->erro('GIT_COLLAB_FAILED', 'Could not add any collaborator.', 502, $erros);
        }

        return $this->sucesso([
            'repository_id' => $repoId,
            'full_name' => $fullName,
            'collaborators' => $resultados,
            'errors' => $erros,
        ], 'Collaborators processed.');
    }

    /**
     * POST /api/v1/git/repositories/collaborators/remove
     * Revoga o acesso de um desenvolvedor.
     *
     * Body: { "id" (repo), "username" }
     */
    public function removerColaborador(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'git.write')) {
            return $this->proibido('Scope git.write is required.');
        }

        $dados = $req->json();
        $repoId = (int) ($dados['id'] ?? 0);
        $username = trim((string) ($dados['username'] ?? ''));

        if ($repoId <= 0) {
            return $this->erro('MISSING_ID', 'The repository id is required.', 400);
        }
        if ($username === '') {
            return $this->validacaoFalhou([['field' => 'username', 'message' => 'The GitHub username is required.']]);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Repository collaborator', 'removed');
        }

        $repo = $this->buscarRepoDoCliente($req, $repoId);
        if ($repo === null) {
            return $this->naoEncontrado('Repository');
        }

        $github = new GitHubService();
        if (!$github->configurado()) {
            return $this->erro('GIT_NOT_CONFIGURED', 'GitHub integration is not configured on the platform. Contact support.', 503);
        }

        try {
            $github->removerColaborador((string) ($repo['full_name'] ?? ''), $username);
        } catch (\Throwable $e) {
            return $this->erro('GIT_COLLAB_REMOVE_FAILED', $e->getMessage(), 502);
        }

        BancoDeDados::pdo()
            ->prepare('DELETE FROM git_repository_collaborators WHERE repository_id = :r AND username = :u')
            ->execute([':r' => $repoId, ':u' => $username]);

        // Webhook git.collaborator.removed
        try {
            (new WebhookService())->disparar((int) $this->clienteId($req), 'git.collaborator.removed', [
                'repository_id' => $repoId,
                'full_name' => $repo['full_name'] ?? null,
                'username' => $username,
            ]);
        } catch (\Throwable) {}

        return $this->sucesso(['repository_id' => $repoId, 'username' => $username], 'Collaborator removed.');
    }

    // ── helpers ──────────────────────────────────────────────────────

    /**
     * Busca um repositório garantindo que pertence a um cliente gerenciado pela key.
     * @return array|null
     */
    private function buscarRepoDoCliente(Requisicao $req, int $repoId): ?array
    {
        $ids = $this->clienteIdsGerenciados($req);
        if ($ids === []) {
            return null;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = BancoDeDados::pdo()->prepare(
            "SELECT id, client_id, full_name FROM git_repositories WHERE id = ? AND client_id IN ($placeholders) LIMIT 1"
        );
        $stmt->execute(array_merge([$repoId], $ids));
        $repo = $stmt->fetch();
        return is_array($repo) ? $repo : null;
    }

    /**
     * Normaliza a entrada de usernames (aceita string única ou array) e valida o formato.
     * @return string[]
     */
    private function normalizarUsernames(mixed $input): array
    {
        $lista = is_array($input) ? $input : [$input];
        $out = [];
        foreach ($lista as $u) {
            $u = trim((string) $u);
            // Username do GitHub: alfanumérico e hífen, até 39 chars
            if ($u !== '' && preg_match('/^[A-Za-z0-9-]{1,39}$/', $u)) {
                $out[$u] = $u;
            }
        }
        return array_values($out);
    }
}
