<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\App\Services\PublicApi\ApiKeyService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints de gerenciamento de API Keys.
 * - GET    /api/v1/keys           → Listar keys do cliente autenticado
 * - POST   /api/v1/keys           → Criar nova key
 * - DELETE  /api/v1/keys/{id}     → Revogar key
 * - POST   /api/v1/keys/{id}/rotate → Rotacionar key
 */
final class ApiKeysController extends BaseApiController
{
    /**
     * GET /api/v1/keys
     */
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = $this->clienteId($req);
        if ($clienteId === null) {
            return $this->naoAutorizado();
        }

        $ambiente = $req->query['environment'] ?? null;
        $service = new ApiKeyService();
        $keys = $service->listarPorCliente($clienteId, $ambiente);

        // Nunca expor hashes
        foreach ($keys as $i => $k) {
            unset($keys[$i]['key_hash']);
            $keys[$i]['scopes'] = json_decode((string) ($k['scopes'] ?? '[]'), true) ?: [];
        }

        return $this->sucesso($keys);
    }

    /**
     * POST /api/v1/keys
     */
    public function criar(Requisicao $req): Resposta
    {
        $clienteId = $this->clienteId($req);
        if ($clienteId === null) {
            return $this->naoAutorizado();
        }

        $dados = $req->json();

        $validacao = $this->validarObrigatorios($dados, ['name']);
        if ($validacao !== null) {
            return $validacao;
        }

        $nome = trim((string) ($dados['name'] ?? ''));
        $descricao = trim((string) ($dados['description'] ?? '')) ?: null;
        $ambiente = in_array($dados['environment'] ?? '', ['sandbox', 'production'], true) ? $dados['environment'] : 'production';
        $escopos = is_array($dados['scopes'] ?? null) ? $dados['scopes'] : [];
        $rateLimit = (int) ($dados['rate_limit_per_minute'] ?? 60);
        $expiraEm = isset($dados['expires_at']) ? (string) $dados['expires_at'] : null;

        if ($rateLimit < 1) {
            $rateLimit = 60;
        }
        if ($rateLimit > 1000) {
            $rateLimit = 1000;
        }

        // Validar escopos
        if (!empty($escopos)) {
            $validScopes = $this->escoposValidos();
            $invalid = array_diff($escopos, $validScopes);
            if (!empty($invalid)) {
                return $this->validacaoFalhou([
                    ['field' => 'scopes', 'message' => 'Invalid scopes: ' . implode(', ', $invalid)],
                ]);
            }
        }

        $service = new ApiKeyService();
        $result = $service->criar($clienteId, $nome, $ambiente, $escopos, $descricao, $expiraEm, $rateLimit);

        return $this->criado([
            'id' => $result['id'],
            'key' => $result['key'],
            'prefix' => $result['prefix'],
            'hint' => $result['hint'],
            'name' => $nome,
            'environment' => $ambiente,
            'scopes' => $escopos,
            'rate_limit_per_minute' => $rateLimit,
            'expires_at' => $expiraEm,
            'warning' => 'Store this key securely. It will not be shown again.',
        ], 'API key created successfully.');
    }

    /**
     * DELETE /api/v1/keys/{id}
     * Nota: o router atual não suporta path params, então usamos query param ?id=
     */
    public function revogar(Requisicao $req): Resposta
    {
        $clienteId = $this->clienteId($req);
        if ($clienteId === null) {
            return $this->naoAutorizado();
        }

        $keyId = (int) ($req->query['id'] ?? 0);
        if ($keyId <= 0) {
            return $this->erro('MISSING_ID', 'The key ID is required.', 400);
        }

        $service = new ApiKeyService();
        $revoked = $service->revogar($keyId, $clienteId);

        if (!$revoked) {
            return $this->naoEncontrado('API Key');
        }

        return $this->sucesso(null, 'API key revoked successfully.');
    }

    /**
     * POST /api/v1/keys/rotate?id=
     */
    public function rotacionar(Requisicao $req): Resposta
    {
        $clienteId = $this->clienteId($req);
        if ($clienteId === null) {
            return $this->naoAutorizado();
        }

        $keyId = (int) ($req->query['id'] ?? ($req->json()['id'] ?? 0));
        if ($keyId <= 0) {
            return $this->erro('MISSING_ID', 'The key ID is required.', 400);
        }

        $service = new ApiKeyService();
        $result = $service->rotacionar($keyId, $clienteId);

        if ($result === null) {
            return $this->naoEncontrado('API Key');
        }

        return $this->sucesso([
            'id' => $result['id'],
            'key' => $result['key'],
            'prefix' => $result['prefix'],
            'hint' => $result['hint'],
            'warning' => 'The previous key has been revoked. Store this new key securely.',
        ], 'API key rotated successfully.');
    }

    private function escoposValidos(): array
    {
        try {
            $pdo = \LRV\Core\BancoDeDados::pdo();
            $stmt = $pdo->query("SELECT scope FROM api_scopes ORDER BY scope");
            $rows = $stmt->fetchAll() ?: [];
            return array_column($rows, 'scope');
        } catch (\Throwable) {
            return [];
        }
    }
}
