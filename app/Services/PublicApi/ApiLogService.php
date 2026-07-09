<?php

declare(strict_types=1);

namespace LRV\App\Services\PublicApi;

use LRV\Core\BancoDeDados;

/**
 * Serviço de logging de requisições da API Pública.
 * Registra todas as requisições para auditoria e análise.
 */
final class ApiLogService
{
    /**
     * Registra uma requisição da API.
     */
    public function registrar(
        ?int $apiKeyId,
        ?int $clienteId,
        string $method,
        string $endpoint,
        int $statusCode,
        ?string $requestBody,
        ?string $responseBody,
        string $ip,
        ?string $userAgent,
        ?int $executionTimeMs,
    ): void {
        // Truncar bodies para evitar explodir o banco
        if ($requestBody !== null && strlen($requestBody) > 10000) {
            $requestBody = substr($requestBody, 0, 10000) . '...[truncated]';
        }
        if ($responseBody !== null && strlen($responseBody) > 10000) {
            $responseBody = substr($responseBody, 0, 10000) . '...[truncated]';
        }

        try {
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                "INSERT INTO api_logs (api_key_id, client_id, method, endpoint, status_code, request_body, response_body, ip, user_agent, execution_time_ms)
                 VALUES (:key_id, :client_id, :method, :endpoint, :status, :req_body, :res_body, :ip, :ua, :exec_time)"
            );
            $stmt->execute([
                ':key_id' => $apiKeyId,
                ':client_id' => $clienteId,
                ':method' => $method,
                ':endpoint' => $endpoint,
                ':status' => $statusCode,
                ':req_body' => $requestBody,
                ':res_body' => $responseBody,
                ':ip' => $ip,
                ':ua' => $userAgent !== null ? substr($userAgent, 0, 500) : null,
                ':exec_time' => $executionTimeMs,
            ]);
        } catch (\Throwable) {
            // Não falhar a requisição por erro de log
        }
    }

    /**
     * Busca logs com filtros e paginação.
     */
    public function buscar(
        ?int $clienteId = null,
        ?int $apiKeyId = null,
        ?string $method = null,
        ?string $endpoint = null,
        ?int $statusCode = null,
        ?string $criadoApos = null,
        ?string $criadoAntes = null,
        int $pagina = 1,
        int $porPagina = 25,
    ): array {
        $pdo = BancoDeDados::pdo();
        $where = [];
        $params = [];

        if ($clienteId !== null) {
            $where[] = 'client_id = :client_id';
            $params[':client_id'] = $clienteId;
        }
        if ($apiKeyId !== null) {
            $where[] = 'api_key_id = :key_id';
            $params[':key_id'] = $apiKeyId;
        }
        if ($method !== null) {
            $where[] = 'method = :method';
            $params[':method'] = strtoupper($method);
        }
        if ($endpoint !== null) {
            $where[] = 'endpoint LIKE :endpoint';
            $params[':endpoint'] = '%' . $endpoint . '%';
        }
        if ($statusCode !== null) {
            $where[] = 'status_code = :status';
            $params[':status'] = $statusCode;
        }
        if ($criadoApos !== null) {
            $where[] = 'created_at >= :after';
            $params[':after'] = $criadoApos;
        }
        if ($criadoAntes !== null) {
            $where[] = 'created_at <= :before';
            $params[':before'] = $criadoAntes;
        }

        $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        // Total
        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM api_logs $whereSql");
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        // Paginação
        $offset = ($pagina - 1) * $porPagina;
        $sql = "SELECT id, api_key_id, client_id, method, endpoint, status_code, ip, user_agent, execution_time_ms, created_at
                FROM api_logs $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll() ?: [];

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $pagina,
                'per_page' => $porPagina,
                'total' => $total,
                'last_page' => (int) ceil($total / $porPagina),
            ],
        ];
    }
}
