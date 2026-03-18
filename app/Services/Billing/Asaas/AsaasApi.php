<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing\Asaas;

use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\ConfiguracoesSistema;

final class AsaasApi
{
    public function __construct(
        private readonly ClienteHttp $http,
    ) {
    }

    public function criarCliente(array $dados): array
    {
        return $this->post('/customers', $dados);
    }

    public function criarAssinatura(array $dados): array
    {
        return $this->post('/subscriptions', $dados);
    }

    public function listarCobrancasDaAssinatura(string $subscriptionId): array
    {
        return $this->get('/subscriptions/' . rawurlencode($subscriptionId) . '/payments');
    }

    private function get(string $path): array
    {
        return $this->request('GET', $path, null);
    }

    private function post(string $path, array $body): array
    {
        return $this->request('POST', $path, $body);
    }

    private function request(string $metodo, string $path, ?array $body): array
    {
        $token = ConfiguracoesSistema::asaasToken();
        if ($token === '') {
            throw new \RuntimeException('Token do Asaas não configurado.');
        }

        $urlBase = rtrim(ConfiguracoesSistema::asaasUrlBase(), '/');
        $url = $urlBase . $path;

        $resp = $this->http->requestJson($metodo, $url, [
            'access_token' => $token,
            'User-Agent' => 'LRVCloudManager/1.0 (PHP)',
        ], $body);

        $status = (int) ($resp['status'] ?? 0);
        $json = $resp['json'] ?? null;

        if ($status < 200 || $status >= 300) {
            throw new AsaasExcecao('Falha na API do Asaas.', $status, is_array($json) ? $json : null);
        }

        if (!is_array($json)) {
            throw new AsaasExcecao('Resposta inválida do Asaas.', $status, null);
        }

        return $json;
    }
}
