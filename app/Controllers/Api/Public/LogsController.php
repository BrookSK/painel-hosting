<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\App\Services\PublicApi\ApiLogService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints de Logs da API Pública.
 * - GET /api/v1/logs → Consultar logs de requisições do cliente
 */
final class LogsController extends BaseApiController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);

        $method = isset($req->query['method']) ? strtoupper((string) $req->query['method']) : null;
        $endpoint = $req->query['endpoint'] ?? null;
        $statusCode = isset($req->query['status_code']) ? (int) $req->query['status_code'] : null;
        $apiKeyId = isset($req->query['api_key_id']) ? (int) $req->query['api_key_id'] : null;
        $criadoApos = $req->query['created_after'] ?? null;
        $criadoAntes = $req->query['created_before'] ?? null;

        $service = new ApiLogService();
        $result = $service->buscar(
            clienteId: $clienteId,
            apiKeyId: $apiKeyId,
            method: $method,
            endpoint: $endpoint,
            statusCode: $statusCode,
            criadoApos: $criadoApos,
            criadoAntes: $criadoAntes,
            pagina: $pag['page'],
            porPagina: $pag['per_page'],
        );

        return $this->paginado($result['data'], $result['meta'], '/api/v1/logs');
    }
}
