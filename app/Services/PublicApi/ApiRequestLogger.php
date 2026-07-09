<?php

declare(strict_types=1);

namespace LRV\App\Services\PublicApi;

use LRV\Core\Http\Requisicao;

/**
 * Logger que registra automaticamente requisições da API pública.
 * Chamado após o envio da resposta para não afetar latência.
 */
final class ApiRequestLogger
{
    /**
     * Registra a requisição/resposta no banco.
     * Chamado com register_shutdown_function ou invocado manualmente.
     */
    public static function registrar(
        Requisicao $req,
        int $statusCode,
        ?string $responseBody = null,
    ): void {
        // Não logar se não é rota da API pública
        if (!str_starts_with($req->caminho, '/api/v1/')) {
            return;
        }

        $keyData = $req->apiKeyData ?? null;
        $apiKeyId = $keyData !== null ? (int) ($keyData['id'] ?? 0) : null;
        $clienteId = $keyData !== null ? (int) ($keyData['client_id'] ?? 0) : null;
        $startTime = $req->apiStartTime ?? null;
        $executionMs = $startTime !== null ? (int) ((microtime(true) - $startTime) * 1000) : null;

        $ip = self::ipDoRequest($req);
        $userAgent = (string) ($req->headers['user-agent'] ?? '');

        // Não logar body de requisições de upload
        $requestBody = null;
        $contentType = strtolower((string) ($req->headers['content-type'] ?? ''));
        if (str_contains($contentType, 'json') && strlen($req->corpoRaw) < 10000) {
            $requestBody = $req->corpoRaw;
        }

        $service = new ApiLogService();
        $service->registrar(
            $apiKeyId > 0 ? $apiKeyId : null,
            $clienteId > 0 ? $clienteId : null,
            $req->metodo,
            $req->caminho,
            $statusCode,
            $requestBody,
            $responseBody,
            $ip,
            $userAgent !== '' ? $userAgent : null,
            $executionMs,
        );
    }

    private static function ipDoRequest(Requisicao $req): string
    {
        $ip = (string) ($req->headers['x-forwarded-for'] ?? '');
        if ($ip !== '') {
            return trim(explode(',', $ip)[0]);
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }
}
