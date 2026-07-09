<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\App\Services\PublicApi\ApiAuthService;
use LRV\App\Services\PublicApi\ApiKeyService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints de autenticação da API Pública.
 * - POST /api/v1/auth/token      → Emitir tokens (access + refresh)
 * - POST /api/v1/auth/refresh    → Renovar token
 * - POST /api/v1/auth/revoke     → Revogar token/key
 */
final class AuthController extends BaseApiController
{
    /**
     * POST /api/v1/auth/token
     * Emite access_token + refresh_token usando uma API Key.
     */
    public function emitirToken(Requisicao $req): Resposta
    {
        $dados = $req->json();
        $apiKey = (string) ($dados['api_key'] ?? '');

        if ($apiKey === '') {
            return $this->erro('MISSING_API_KEY', 'The api_key field is required.', 400);
        }

        $keyService = new ApiKeyService();
        $keyData = $keyService->validar($apiKey);

        if ($keyData === null) {
            return $this->erro('INVALID_API_KEY', 'The provided API key is invalid or expired.', 401);
        }

        // Escopos opcionais (subconjunto da key)
        $requestedScopes = $dados['scopes'] ?? null;
        $scopes = null;
        if (is_array($requestedScopes) && !empty($requestedScopes)) {
            $keyScopes = $keyData['scopes_array'] ?? [];
            if (!in_array('*', $keyScopes, true)) {
                $invalid = array_diff($requestedScopes, $keyScopes);
                if (!empty($invalid)) {
                    return $this->erro('INVALID_SCOPES', 'Requested scopes exceed API key permissions.', 403, [
                        'invalid_scopes' => array_values($invalid),
                    ]);
                }
            }
            $scopes = $requestedScopes;
        }

        $authService = new ApiAuthService();
        $tokens = $authService->emitirTokens((int) $keyData['id'], $scopes);

        return $this->sucesso($tokens, 'Tokens issued successfully.', 201);
    }

    /**
     * POST /api/v1/auth/refresh
     * Renova tokens usando refresh_token.
     */
    public function renovarToken(Requisicao $req): Resposta
    {
        $dados = $req->json();
        $refreshToken = (string) ($dados['refresh_token'] ?? '');

        if ($refreshToken === '') {
            return $this->erro('MISSING_REFRESH_TOKEN', 'The refresh_token field is required.', 400);
        }

        $authService = new ApiAuthService();
        $tokens = $authService->renovarToken($refreshToken);

        if ($tokens === null) {
            return $this->erro('INVALID_REFRESH_TOKEN', 'The refresh token is invalid or expired.', 401);
        }

        return $this->sucesso($tokens, 'Tokens refreshed successfully.');
    }

    /**
     * POST /api/v1/auth/revoke
     * Revoga todos os tokens de uma API Key (requer autenticação).
     */
    public function revogar(Requisicao $req): Resposta
    {
        $keyData = $this->apiKeyData($req);
        if ($keyData === null) {
            return $this->naoAutorizado();
        }

        $authService = new ApiAuthService();
        $authService->revogarTodosTokens((int) $keyData['id']);

        return $this->sucesso(null, 'All tokens have been revoked.');
    }
}
