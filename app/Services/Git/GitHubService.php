<?php

declare(strict_types=1);

namespace LRV\App\Services\Git;

use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\Settings;

/**
 * Integração com a API do GitHub para criação de repositórios na organização
 * e gestão de colaboradores (devs). Credenciais ficam em Settings:
 *   - github.token    : Personal Access Token / token de app com permissão de repo+org
 *   - github.org      : organização padrão onde criar os repositórios
 *   - github.api_url  : base da API (default https://api.github.com)
 *
 * Lança \RuntimeException com mensagem amigável quando a configuração falta ou
 * o GitHub responde erro, para o controller converter em resposta HTTP adequada.
 */
final class GitHubService
{
    private string $token;
    private string $orgPadrao;
    private string $apiUrl;

    public function __construct(private readonly ClienteHttp $http = new ClienteHttp())
    {
        $this->token = trim((string) Settings::obter('github.token', ''));
        $this->orgPadrao = trim((string) Settings::obter('github.org', ''));
        $this->apiUrl = rtrim(trim((string) Settings::obter('github.api_url', 'https://api.github.com')), '/');
        if ($this->apiUrl === '') {
            $this->apiUrl = 'https://api.github.com';
        }
    }

    /**
     * Indica se a integração está configurada (token presente).
     */
    public function configurado(): bool
    {
        return $this->token !== '';
    }

    public function orgPadrao(): string
    {
        return $this->orgPadrao;
    }

    /**
     * Cria um repositório na organização.
     *
     * @return array{id:string, full_name:string, html_url:string, clone_url:string, ssh_url:string, visibility:string, org:string}
     */
    public function criarRepositorio(string $name, bool $private = true, ?string $org = null, string $description = ''): array
    {
        $this->exigirConfig();

        $org = trim($org ?? '') !== '' ? trim((string) $org) : $this->orgPadrao;
        if ($org === '') {
            throw new \RuntimeException('Nenhuma organização GitHub configurada (github.org) nem informada no pedido.');
        }

        $resp = $this->http->requestJson(
            'POST',
            $this->apiUrl . '/orgs/' . rawurlencode($org) . '/repos',
            $this->headers(),
            [
                'name' => $name,
                'private' => $private,
                'description' => $description,
                'auto_init' => true,
            ]
        );

        $status = (int) ($resp['status'] ?? 0);
        $json = is_array($resp['json'] ?? null) ? $resp['json'] : [];

        if ($status === 422) {
            // Nome já existe na org (ou validação do GitHub)
            throw new \RuntimeException('O repositório já existe ou o nome é inválido: ' . $this->mensagemErro($json));
        }
        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('GitHub retornou erro ao criar o repositório (HTTP ' . $status . '): ' . $this->mensagemErro($json));
        }

        return [
            'id'         => (string) ($json['id'] ?? ''),
            'full_name'  => (string) ($json['full_name'] ?? ($org . '/' . $name)),
            'html_url'   => (string) ($json['html_url'] ?? ''),
            'clone_url'  => (string) ($json['clone_url'] ?? ''),
            'ssh_url'    => (string) ($json['ssh_url'] ?? ''),
            'visibility' => (string) ($json['visibility'] ?? ($private ? 'private' : 'public')),
            'org'        => $org,
        ];
    }

    /**
     * Adiciona (ou convida) um colaborador a um repositório.
     *
     * @param string $fullName  owner/repo (ex.: minha-org/meu-repo)
     * @param string $permission pull|triage|push|maintain|admin
     * @return array{username:string, permission:string, status:string}
     */
    public function adicionarColaborador(string $fullName, string $username, string $permission = 'push'): array
    {
        $this->exigirConfig();

        $permission = $this->normalizarPermissao($permission);
        [$owner, $repo] = $this->dividirFullName($fullName);

        $resp = $this->http->requestJson(
            'PUT',
            $this->apiUrl . '/repos/' . rawurlencode($owner) . '/' . rawurlencode($repo) . '/collaborators/' . rawurlencode($username),
            $this->headers(),
            ['permission' => $permission]
        );

        $status = (int) ($resp['status'] ?? 0);
        $json = is_array($resp['json'] ?? null) ? $resp['json'] : [];

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('GitHub retornou erro ao adicionar colaborador "' . $username . '" (HTTP ' . $status . '): ' . $this->mensagemErro($json));
        }

        // 201 = convite criado (usuário externo); 204 = já era colaborador / adicionado direto
        $estado = $status === 201 ? 'invited' : 'active';

        return ['username' => $username, 'permission' => $permission, 'status' => $estado];
    }

    /**
     * Remove um colaborador (ou cancela convite) de um repositório.
     */
    public function removerColaborador(string $fullName, string $username): bool
    {
        $this->exigirConfig();

        [$owner, $repo] = $this->dividirFullName($fullName);

        $resp = $this->http->requestJson(
            'DELETE',
            $this->apiUrl . '/repos/' . rawurlencode($owner) . '/' . rawurlencode($repo) . '/collaborators/' . rawurlencode($username),
            $this->headers()
        );

        $status = (int) ($resp['status'] ?? 0);
        if ($status < 200 || $status >= 300) {
            $json = is_array($resp['json'] ?? null) ? $resp['json'] : [];
            throw new \RuntimeException('GitHub retornou erro ao remover colaborador "' . $username . '" (HTTP ' . $status . '): ' . $this->mensagemErro($json));
        }

        return true;
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
            'User-Agent'    => 'LRVCloud/1.0',
        ];
    }

    private function exigirConfig(): void
    {
        if ($this->token === '') {
            throw new \RuntimeException('Integração com o GitHub não configurada. Defina o token em Configurações da equipe (github.token).');
        }
    }

    private function normalizarPermissao(string $permission): string
    {
        $permission = strtolower(trim($permission));
        return in_array($permission, ['pull', 'triage', 'push', 'maintain', 'admin'], true) ? $permission : 'push';
    }

    /**
     * @return array{0:string,1:string} [owner, repo]
     */
    private function dividirFullName(string $fullName): array
    {
        $fullName = trim($fullName, " /\t\n\r");
        if (!str_contains($fullName, '/')) {
            // Só o nome do repo: assume a organização padrão
            if ($this->orgPadrao === '') {
                throw new \RuntimeException('Informe o repositório no formato "org/repo" (nenhuma org padrão configurada).');
            }
            return [$this->orgPadrao, $fullName];
        }
        $partes = explode('/', $fullName, 2);
        return [trim($partes[0]), trim($partes[1])];
    }

    private function mensagemErro(array $json): string
    {
        $msg = (string) ($json['message'] ?? 'erro desconhecido');
        if (!empty($json['errors']) && is_array($json['errors'])) {
            $detalhes = [];
            foreach ($json['errors'] as $e) {
                if (is_array($e) && !empty($e['message'])) {
                    $detalhes[] = (string) $e['message'];
                } elseif (is_array($e) && !empty($e['field'])) {
                    $detalhes[] = (string) $e['field'] . ' ' . (string) ($e['code'] ?? '');
                }
            }
            if ($detalhes !== []) {
                $msg .= ' (' . implode('; ', $detalhes) . ')';
            }
        }
        return $msg;
    }
}
