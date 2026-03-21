<?php

declare(strict_types=1);

namespace LRV\App\Services\Cloudflare;

use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\Settings;

/**
 * Estrutura preparada para automação DNS via Cloudflare API.
 * Implementação completa será feita em fase futura.
 *
 * Configuração via settings:
 *   cloudflare.api_token  — API Token com permissão Zone:Edit
 *   cloudflare.zone_id    — Zone ID (opcional, para domínio próprio do sistema)
 */
final class CloudflareService
{
    private const BASE_URL = 'https://api.cloudflare.com/client/v4';

    private string $apiToken;

    public function __construct(private readonly ClienteHttp $http = new ClienteHttp())
    {
        $this->apiToken = (string) Settings::obter('cloudflare.api_token', '');
    }

    /**
     * Verifica se um domínio usa Cloudflare como nameserver.
     * Faz lookup NS e verifica se algum termina em .ns.cloudflare.com
     */
    public function verificarSeDominioUsaCloudflare(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        $ns     = @dns_get_record($domain, DNS_NS);

        if (!is_array($ns)) {
            return false;
        }

        foreach ($ns as $record) {
            $target = strtolower((string) ($record['target'] ?? ''));
            if (str_ends_with($target, '.ns.cloudflare.com')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtém o Zone ID de um domínio via API Cloudflare.
     * Requer api_token configurado.
     * FASE FUTURA — estrutura preparada.
     */
    public function obterZoneId(string $domain): string
    {
        $this->validarConfig();

        $url  = self::BASE_URL . '/zones?name=' . rawurlencode($domain) . '&status=active';
        $resp = $this->http->requestJson('GET', $url, $this->headers(), []);
        $data = $resp['json'] ?? json_decode($resp['body'] ?? '', true);

        if (!is_array($data) || empty($data['result'])) {
            return '';
        }

        return (string) ($data['result'][0]['id'] ?? '');
    }

    /**
     * Cria registros DNS automaticamente para um domínio de email.
     * Cria MX, SPF (TXT) e DKIM (TXT).
     * FASE FUTURA — estrutura preparada.
     *
     * @param string $zoneId   Zone ID do Cloudflare
     * @param string $domain   Domínio do cliente
     * @param string $mxTarget Host do servidor de email (ex: mail.seudominio.com)
     * @param string $dkimTxt  Valor do registro DKIM
     */
    public function criarDNSAutomatico(string $zoneId, string $domain, string $mxTarget, string $dkimTxt): array
    {
        $this->validarConfig();

        $resultados = [];

        // MX
        $resultados['mx'] = $this->criarRegistro($zoneId, [
            'type'     => 'MX',
            'name'     => $domain,
            'content'  => $mxTarget,
            'priority' => 10,
            'ttl'      => 3600,
        ]);

        // SPF
        $resultados['spf'] = $this->criarRegistro($zoneId, [
            'type'    => 'TXT',
            'name'    => $domain,
            'content' => 'v=spf1 mx ~all',
            'ttl'     => 3600,
        ]);

        // DKIM
        if ($dkimTxt !== '') {
            $resultados['dkim'] = $this->criarRegistro($zoneId, [
                'type'    => 'TXT',
                'name'    => 'dkim._domainkey.' . $domain,
                'content' => $dkimTxt,
                'ttl'     => 3600,
            ]);
        }

        return $resultados;
    }

    private function criarRegistro(string $zoneId, array $record): array
    {
        $url  = self::BASE_URL . '/zones/' . $zoneId . '/dns_records';
        $resp = $this->http->requestJson('POST', $url, $this->headers(), $record);
        $data = $resp['json'] ?? json_decode($resp['body'] ?? '', true);
        return is_array($data) ? $data : [];
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->apiToken];
    }

    private function validarConfig(): void
    {
        if ($this->apiToken === '') {
            throw new \RuntimeException('Cloudflare API Token não configurado.');
        }
    }
}
