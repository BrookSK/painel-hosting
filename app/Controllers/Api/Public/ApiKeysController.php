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

        // Anti-escalada: uma chave só cria outra com escopos que ELA MESMA já tem,
        // e uma chave/token de sandbox nunca cria chave de produção.
        $doChamador = $this->escoposDoChamador($req);
        if (empty($escopos)) {
            $escopos = $doChamador; // sem informar = herda (nunca "tudo")
        }
        $excedentes = array_diff($escopos, $doChamador);
        if (!empty($excedentes) || in_array('*', $escopos, true)) {
            return $this->proibido('A key can only create keys with scopes it already has. Not allowed: ' . implode(', ', $excedentes ?: ['*']));
        }
        if ($this->isSandbox($req) && $ambiente === 'production') {
            return $this->proibido('A sandbox key cannot create production keys.');
        }
        if ($expiraEm !== null && ($expiraEm === '' || strtotime($expiraEm) === false || strtotime($expiraEm) < time())) {
            return $this->validacaoFalhou([['field' => 'expires_at', 'message' => 'expires_at must be a future date (YYYY-MM-DD HH:MM:SS).']]);
        }
        if ($expiraEm !== null) {
            $expiraEm = date('Y-m-d H:i:s', (int) strtotime($expiraEm));
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

        if (!$this->podeGerenciarChave($req, $keyId, $clienteId)) {
            return $this->proibido('This key cannot manage a key with more permissions than itself.');
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

        if (!$this->podeGerenciarChave($req, $keyId, $clienteId)) {
            return $this->proibido('This key cannot manage a key with more permissions than itself.');
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

    /** Escopos efetivos da chave/token que está fazendo a chamada. */
    private function escoposDoChamador(Requisicao $req): array
    {
        $data = $this->apiKeyData($req);
        $scopes = is_array($data['scopes_array'] ?? null) ? $data['scopes_array'] : [];
        if (in_array('*', $scopes, true)) {
            return $this->escoposValidos();
        }
        return array_values(array_intersect($scopes, $this->escoposValidos()));
    }

    /** Escopos da chave-alvo ⊆ escopos do chamador (para revogar/rotacionar). */
    private function podeGerenciarChave(Requisicao $req, int $keyId, int $clienteId): bool
    {
        try {
            $st = \LRV\Core\BancoDeDados::pdo()->prepare('SELECT scopes FROM api_keys WHERE id = :id AND client_id = :c LIMIT 1');
            $st->execute([':id' => $keyId, ':c' => $clienteId]);
            $raw = $st->fetchColumn();
        } catch (\Throwable) {
            return false;
        }
        if ($raw === false) {
            return true; // inexistente: o serviço responde 404
        }
        $alvo = json_decode((string) $raw, true) ?: [];
        if (in_array('*', $this->apiKeyData($req)['scopes_array'] ?? [], true)) {
            return true;
        }
        if (in_array('*', $alvo, true)) {
            return false;
        }
        return array_diff($alvo, $this->escoposDoChamador($req)) === [];
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
