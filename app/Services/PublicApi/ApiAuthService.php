<?php

declare(strict_types=1);

namespace LRV\App\Services\PublicApi;

use LRV\Core\BancoDeDados;

/**
 * Serviço de autenticação da API Pública.
 * Suporta: API Key (header), Bearer Token, Personal Access Token.
 */
final class ApiAuthService
{
    private const TOKEN_ACCESS_LIFETIME = 3600;      // 1h
    private const TOKEN_REFRESH_LIFETIME = 2592000;  // 30 dias

    private ?array $keyAtual = null;
    private ?array $tokenAtual = null;

    /**
     * Autentica a requisição a partir dos headers.
     * Tenta: Authorization: Bearer <token>, X-API-Key: <key>
     * @return array|null Dados da key autenticada ou null
     */
    public function autenticar(array $headers): ?array
    {
        // Tentar Bearer Token primeiro
        $auth = (string) ($headers['authorization'] ?? '');
        if (str_starts_with($auth, 'Bearer ')) {
            $token = substr($auth, 7);
            return $this->autenticarPorToken($token);
        }

        // Tentar X-API-Key header
        $apiKey = (string) ($headers['x-api-key'] ?? '');
        if ($apiKey !== '') {
            return $this->autenticarPorApiKey($apiKey);
        }

        return null;
    }

    /**
     * Autentica via API Key direta.
     */
    public function autenticarPorApiKey(string $rawKey): ?array
    {
        $service = new ApiKeyService();
        $data = $service->validar($rawKey);
        if ($data === null) {
            return null;
        }
        $this->keyAtual = $data;
        return $data;
    }

    /**
     * Autentica via Bearer Token (access token).
     */
    public function autenticarPorToken(string $rawToken): ?array
    {
        if (trim($rawToken) === '') {
            return null;
        }

        $hash = hash('sha256', $rawToken);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT t.id, t.api_key_id, t.type, t.scopes, t.expires_at, t.revoked_at,
                    k.client_id, k.name, k.environment, k.scopes AS key_scopes,
                    k.status AS key_status, k.rate_limit_per_minute
             FROM api_tokens t
             INNER JOIN api_keys k ON k.id = t.api_key_id
             WHERE t.token_hash = :hash
             LIMIT 1"
        );
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        // Verificar revogação
        if ($row['revoked_at'] !== null) {
            return null;
        }

        // Verificar expiração do token
        if (strtotime($row['expires_at']) < time()) {
            return null;
        }

        // Verificar key status
        if ($row['key_status'] !== 'active') {
            return null;
        }

        // Atualizar último uso do token
        $pdo->prepare("UPDATE api_tokens SET last_used_at = NOW() WHERE id = :id")
            ->execute([':id' => (int) $row['id']]);

        // Determinar escopos efetivos (interseção entre token e key)
        $keyScopes = json_decode((string) $row['key_scopes'], true) ?: [];
        $tokenScopes = $row['scopes'] !== null ? (json_decode((string) $row['scopes'], true) ?: []) : $keyScopes;
        $effectiveScopes = array_intersect($tokenScopes, $keyScopes);

        $result = [
            'id' => (int) $row['api_key_id'],
            'client_id' => (int) $row['client_id'],
            'name' => $row['name'],
            'environment' => $row['environment'],
            'scopes_array' => array_values($effectiveScopes),
            'rate_limit_per_minute' => (int) $row['rate_limit_per_minute'],
            'token_id' => (int) $row['id'],
            'token_type' => $row['type'],
        ];

        $this->keyAtual = $result;
        $this->tokenAtual = $row;
        return $result;
    }

    /**
     * Emite um par access_token + refresh_token para uma API Key.
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     */
    public function emitirTokens(int $apiKeyId, ?array $escopos = null): array
    {
        $accessRaw = $this->gerarToken();
        $refreshRaw = $this->gerarToken();

        $pdo = BancoDeDados::pdo();

        // Access token
        $stmt = $pdo->prepare(
            "INSERT INTO api_tokens (api_key_id, token_hash, token_hint, type, scopes, expires_at)
             VALUES (:key_id, :hash, :hint, 'access', :scopes, :expires_at)"
        );
        $stmt->execute([
            ':key_id' => $apiKeyId,
            ':hash' => hash('sha256', $accessRaw),
            ':hint' => substr($accessRaw, -4),
            ':scopes' => $escopos !== null ? json_encode($escopos) : null,
            ':expires_at' => date('Y-m-d H:i:s', time() + self::TOKEN_ACCESS_LIFETIME),
        ]);

        // Refresh token
        $stmt = $pdo->prepare(
            "INSERT INTO api_tokens (api_key_id, token_hash, token_hint, type, scopes, expires_at)
             VALUES (:key_id, :hash, :hint, 'refresh', :scopes, :expires_at)"
        );
        $stmt->execute([
            ':key_id' => $apiKeyId,
            ':hash' => hash('sha256', $refreshRaw),
            ':hint' => substr($refreshRaw, -4),
            ':scopes' => $escopos !== null ? json_encode($escopos) : null,
            ':expires_at' => date('Y-m-d H:i:s', time() + self::TOKEN_REFRESH_LIFETIME),
        ]);

        return [
            'access_token' => $accessRaw,
            'refresh_token' => $refreshRaw,
            'expires_in' => self::TOKEN_ACCESS_LIFETIME,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Renova tokens usando um refresh_token válido.
     */
    public function renovarToken(string $refreshTokenRaw): ?array
    {
        $hash = hash('sha256', $refreshTokenRaw);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT t.id, t.api_key_id, t.scopes, t.expires_at, t.revoked_at
             FROM api_tokens t
             WHERE t.token_hash = :hash AND t.type = 'refresh'
             LIMIT 1"
        );
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        if ($row['revoked_at'] !== null) {
            return null;
        }

        if (strtotime($row['expires_at']) < time()) {
            return null;
        }

        // Revogar refresh token atual (rotação)
        $pdo->prepare("UPDATE api_tokens SET revoked_at = NOW() WHERE id = :id")
            ->execute([':id' => (int) $row['id']]);

        // Revogar access tokens antigos desta key
        $pdo->prepare(
            "UPDATE api_tokens SET revoked_at = NOW() WHERE api_key_id = :key_id AND type = 'access' AND revoked_at IS NULL"
        )->execute([':key_id' => (int) $row['api_key_id']]);

        // Emitir novos tokens
        $scopes = $row['scopes'] !== null ? json_decode((string) $row['scopes'], true) : null;
        return $this->emitirTokens((int) $row['api_key_id'], is_array($scopes) ? $scopes : null);
    }

    /**
     * Revoga todos os tokens de uma API Key.
     */
    public function revogarTodosTokens(int $apiKeyId): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare("UPDATE api_tokens SET revoked_at = NOW() WHERE api_key_id = :key_id AND revoked_at IS NULL")
            ->execute([':key_id' => $apiKeyId]);
    }

    public function keyAtual(): ?array
    {
        return $this->keyAtual;
    }

    private function gerarToken(): string
    {
        $bytes = random_bytes(48);
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
