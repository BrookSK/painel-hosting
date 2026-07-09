<?php

declare(strict_types=1);

namespace LRV\Core;

use LRV\App\Services\PublicApi\ApiAuthService;
use LRV\App\Services\PublicApi\ApiKeyService;
use LRV\App\Services\PublicApi\ApiLogService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Middlewares específicos para a API Pública.
 * Autenticação via API Key / Bearer Token, Rate Limiting por key, CORS e Logging.
 */
final class ApiMiddleware
{
    /**
     * Middleware de autenticação da API Pública.
     * Valida API Key ou Bearer Token e injeta dados na requisição.
     */
    public static function autenticar(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            $authService = new ApiAuthService();
            $keyData = $authService->autenticar($req->headers);

            if ($keyData === null) {
                return Resposta::json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Valid API key or Bearer token is required. Use X-API-Key header or Authorization: Bearer <token>.',
                    ],
                ], 401);
            }

            // Injetar dados da key na requisição para uso nos controllers
            $req->apiKeyData = $keyData;

            // Registrar uso
            $keyService = new ApiKeyService();
            $ip = self::ipDoRequest($req);
            $keyService->registrarUso((int) $keyData['id'], $ip);

            return null;
        };
    }

    /**
     * Middleware de Rate Limiting por API Key.
     * Usa o rate_limit_per_minute configurado na key.
     */
    public static function rateLimitApiKey(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            $keyData = $req->apiKeyData ?? null;
            if ($keyData === null) {
                return null; // Sem auth, outro middleware cuida
            }

            $limite = (int) ($keyData['rate_limit_per_minute'] ?? 60);
            $keyId = (int) ($keyData['id'] ?? 0);
            $key = 'api_key:' . $keyId;

            $result = RateLimiter::consumir($key, $limite, 60);

            if (!$result['ok']) {
                $retryAfter = (int) ($result['retry_after'] ?? 60);
                $resp = Resposta::json([
                    'success' => false,
                    'error' => [
                        'code' => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Too many requests. Please retry after ' . $retryAfter . ' seconds.',
                    ],
                ], 429);

                return $resp->comHeaders([
                    'Retry-After' => (string) $retryAfter,
                    'X-RateLimit-Limit' => (string) $limite,
                    'X-RateLimit-Remaining' => '0',
                    'X-RateLimit-Reset' => (string) ($result['reset_at'] ?? time() + 60),
                ]);
            }

            return null;
        };
    }

    /**
     * Middleware que verifica se a API Key tem determinado escopo.
     */
    public static function exigirEscopo(string $escopo): callable
    {
        return static function (Requisicao $req) use ($escopo): ?Resposta {
            $keyData = $req->apiKeyData ?? null;
            if ($keyData === null) {
                return Resposta::json([
                    'success' => false,
                    'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required.'],
                ], 401);
            }

            $scopes = $keyData['scopes_array'] ?? [];
            if (!in_array('*', $scopes, true) && !in_array($escopo, $scopes, true)) {
                return Resposta::json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => "Scope '$escopo' is required for this endpoint.",
                    ],
                ], 403);
            }

            return null;
        };
    }

    /**
     * Middleware de ambiente (sandbox vs production).
     * No sandbox: operações de escrita (POST) retornam respostas simuladas sem executar.
     */
    public static function sandboxGuard(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            $keyData = $req->apiKeyData ?? null;
            if ($keyData === null) {
                return null;
            }

            // Apenas sandbox é interceptado
            if (($keyData['environment'] ?? '') !== 'sandbox') {
                return null;
            }

            // GET requests passam normalmente (read-only)
            if ($req->metodo === 'GET') {
                return null;
            }

            // POST/PUT/PATCH/DELETE no sandbox → simular resposta sem executar
            // Marcar na requisição para que controllers saibam que é sandbox
            $req->isSandbox = true;

            return null;
        };
    }

    /**
     * Middleware de ambiente (sandbox vs production).
     * Rejeita se a key não pertence ao ambiente esperado.
     */
    public static function exigirAmbiente(string $ambiente): callable
    {
        return static function (Requisicao $req) use ($ambiente): ?Resposta {
            $keyData = $req->apiKeyData ?? null;
            if ($keyData === null) {
                return null;
            }

            if (($keyData['environment'] ?? '') !== $ambiente) {
                return Resposta::json([
                    'success' => false,
                    'error' => [
                        'code' => 'ENVIRONMENT_MISMATCH',
                        'message' => "This endpoint requires a '$ambiente' API key.",
                    ],
                ], 403);
            }

            return null;
        };
    }

    /**
     * Middleware CORS para API Pública.
     */
    public static function cors(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            $origin = (string) ($req->headers['origin'] ?? '*');

            // Adicionar headers CORS
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, X-Request-ID');
            header('Access-Control-Max-Age: 86400');
            header('Access-Control-Expose-Headers: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, Retry-After');

            // Responder OPTIONS (preflight) imediatamente
            if ($req->metodo === 'OPTIONS') {
                return Resposta::texto('', 204);
            }

            return null;
        };
    }

    /**
     * Middleware de logging de requisições da API.
     * Nota: O logging real é feito no dispatch final pois precisa capturar a resposta.
     * Este middleware marca o início do timer.
     */
    public static function iniciarTimer(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            $req->apiStartTime = microtime(true);
            return null;
        };
    }

    /**
     * Middleware de segurança: headers obrigatórios para API.
     */
    public static function securityHeaders(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            return null;
        };
    }

    /**
     * Rate limit por IP (sem autenticação, para endpoints públicos como /auth/token).
     */
    public static function rateLimitIpApi(int $limite = 30, int $janela = 60): callable
    {
        return static function (Requisicao $req) use ($limite, $janela): ?Resposta {
            $ip = self::ipDoRequest($req);
            $key = 'api_pub_ip:' . $ip;
            $result = RateLimiter::consumir($key, $limite, $janela);

            if (!$result['ok']) {
                $retryAfter = (int) ($result['retry_after'] ?? $janela);
                return Resposta::json([
                    'success' => false,
                    'error' => [
                        'code' => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Too many requests from this IP.',
                    ],
                ], 429)->comHeaders([
                    'Retry-After' => (string) $retryAfter,
                ]);
            }

            return null;
        };
    }

    private static function ipDoRequest(Requisicao $req): string
    {
        $ip = (string) ($req->headers['x-forwarded-for'] ?? '');
        if ($ip !== '') {
            $parts = explode(',', $ip);
            return trim($parts[0]);
        }
        $ip = (string) ($req->headers['x-real-ip'] ?? '');
        if ($ip !== '') {
            return trim($ip);
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }
}
