<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Controller base para todos os endpoints da API Pública.
 * Define padrão de respostas, paginação e helpers comuns.
 */
abstract class BaseApiController
{
    /**
     * Resposta de sucesso padrão.
     */
    protected function sucesso(mixed $data = null, string $mensagem = '', int $status = 200, array $meta = [], array $links = []): Resposta
    {
        $body = [
            'success' => true,
        ];

        if ($mensagem !== '') {
            $body['message'] = $mensagem;
        }

        if ($data !== null) {
            $body['data'] = $data;
        }

        if (!empty($meta)) {
            $body['meta'] = $meta;
        }

        if (!empty($links)) {
            $body['links'] = $links;
        }

        return Resposta::json($body, $status);
    }

    /**
     * Resposta de erro padrão.
     */
    protected function erro(string $codigo, string $mensagem, int $status = 400, array $detalhes = []): Resposta
    {
        $body = [
            'success' => false,
            'error' => [
                'code' => $codigo,
                'message' => $mensagem,
            ],
        ];

        if (!empty($detalhes)) {
            $body['error']['details'] = $detalhes;
        }

        return Resposta::json($body, $status);
    }

    /**
     * Resposta de lista paginada.
     */
    protected function paginado(array $items, array $meta, string $baseUrl = ''): Resposta
    {
        $links = [];
        if ($baseUrl !== '') {
            $currentPage = (int) ($meta['current_page'] ?? 1);
            $lastPage = (int) ($meta['last_page'] ?? 1);
            $perPage = (int) ($meta['per_page'] ?? 25);

            $links['self'] = $baseUrl . '?page=' . $currentPage . '&per_page=' . $perPage;
            $links['first'] = $baseUrl . '?page=1&per_page=' . $perPage;
            $links['last'] = $baseUrl . '?page=' . $lastPage . '&per_page=' . $perPage;

            if ($currentPage < $lastPage) {
                $links['next'] = $baseUrl . '?page=' . ($currentPage + 1) . '&per_page=' . $perPage;
            }
            if ($currentPage > 1) {
                $links['previous'] = $baseUrl . '?page=' . ($currentPage - 1) . '&per_page=' . $perPage;
            }
        }

        return $this->sucesso($items, '', 200, $meta, $links);
    }

    /**
     * Resposta 401 Unauthorized.
     */
    protected function naoAutorizado(string $mensagem = 'Authentication required.'): Resposta
    {
        return $this->erro('UNAUTHORIZED', $mensagem, 401);
    }

    /**
     * Resposta 403 Forbidden.
     */
    protected function proibido(string $mensagem = 'Insufficient permissions.'): Resposta
    {
        return $this->erro('FORBIDDEN', $mensagem, 403);
    }

    /**
     * Resposta 404 Not Found.
     */
    protected function naoEncontrado(string $recurso = 'Resource'): Resposta
    {
        return $this->erro('NOT_FOUND', $recurso . ' not found.', 404);
    }

    /**
     * Resposta 422 Unprocessable Entity (validação).
     */
    protected function validacaoFalhou(array $erros): Resposta
    {
        return $this->erro('VALIDATION_ERROR', 'The given data was invalid.', 422, $erros);
    }

    /**
     * Resposta 429 Rate Limit Exceeded.
     */
    protected function rateLimitExcedido(int $retryAfter = 60): Resposta
    {
        $resp = $this->erro('RATE_LIMIT_EXCEEDED', 'Too many requests. Please retry after ' . $retryAfter . ' seconds.', 429);
        // Headers serão adicionados pelo middleware
        return $resp;
    }

    /**
     * Resposta 201 Created.
     */
    protected function criado(mixed $data, string $mensagem = 'Resource created successfully.'): Resposta
    {
        return $this->sucesso($data, $mensagem, 201);
    }

    /**
     * Resposta 204 No Content.
     */
    protected function semConteudo(): Resposta
    {
        return Resposta::json([], 204);
    }

    /**
     * Extrai parâmetros de paginação da query string.
     * @return array{page: int, per_page: int}
     */
    protected function paginacao(Requisicao $req, int $maxPorPagina = 100): array
    {
        $page = max(1, (int) ($req->query['page'] ?? 1));
        $perPage = max(1, min($maxPorPagina, (int) ($req->query['per_page'] ?? 25)));
        return ['page' => $page, 'per_page' => $perPage];
    }

    /**
     * Obtém dados da API Key autenticada (setados pelo middleware).
     */
    protected function apiKeyData(Requisicao $req): ?array
    {
        return $req->apiKeyData ?? null;
    }

    /**
     * Obtém o client_id da API Key autenticada.
     */
    protected function clienteId(Requisicao $req): ?int
    {
        $data = $this->apiKeyData($req);
        return $data !== null ? (int) $data['client_id'] : null;
    }

    /**
     * Verifica se a requisição está em modo Sandbox.
     * No sandbox, writes não executam de verdade — retornam resposta simulada.
     */
    protected function isSandbox(Requisicao $req): bool
    {
        return (bool) ($req->isSandbox ?? false);
    }

    /**
     * Retorna resposta simulada para operações de escrita em sandbox.
     */
    protected function respostaSandbox(string $recurso = 'resource', string $acao = 'created'): Resposta
    {
        return $this->sucesso([
            'id' => random_int(9000, 9999),
            'sandbox' => true,
            'note' => "This is a simulated response. No changes were made to production data.",
        ], "Sandbox: $recurso $acao (simulated).", $acao === 'created' ? 201 : 200);
    }

    /**
     * Verifica se a API Key tem um escopo específico.
     */
    protected function temEscopo(Requisicao $req, string $escopo): bool
    {
        $data = $this->apiKeyData($req);
        if ($data === null) {
            return false;
        }
        $scopes = $data['scopes_array'] ?? [];
        if (in_array('*', $scopes, true)) {
            return true;
        }
        return in_array($escopo, $scopes, true);
    }

    /**
     * Valida campos obrigatórios e retorna resposta de erro se falhar.
     * @return Resposta|null null se válido
     */
    protected function validarObrigatorios(array $dados, array $campos): ?Resposta
    {
        $erros = [];
        foreach ($campos as $campo) {
            if (!isset($dados[$campo]) || (is_string($dados[$campo]) && trim($dados[$campo]) === '')) {
                $erros[] = ['field' => $campo, 'message' => "The field '$campo' is required."];
            }
        }

        if (!empty($erros)) {
            return $this->validacaoFalhou($erros);
        }

        return null;
    }
}
