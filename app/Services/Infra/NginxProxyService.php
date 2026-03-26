<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\App\Services\Cloudflare\CloudflareService;
use LRV\Core\Settings;

/**
 * Gerencia domínios temporários via Cloudflare DNS API.
 * Cria registros A individuais apontando pro IP da VPS do cliente.
 * Não precisa de root em nenhum servidor.
 */
final class NginxProxyService
{
    private CloudflareService $cf;

    public function __construct()
    {
        $this->cf = new CloudflareService();
    }

    /**
     * Cria um registro DNS A para o domínio temporário apontando pro IP da VPS.
     * O Cloudflare proxy (orange cloud) dá SSL automático.
     */
    public function criarProxy(string $tempDomain, string $vpsIp, int $vpsPort = 80): void
    {
        $zoneId = $this->cf->obterZoneIdDoTempDomain();
        if ($zoneId === '') {
            throw new \RuntimeException('Zone ID do Cloudflare não encontrado. Configure cloudflare.zone_id nas settings.');
        }

        $result = $this->cf->criarRegistroA($zoneId, $tempDomain, $vpsIp, true);

        $success = (bool)($result['success'] ?? false);
        if (!$success) {
            $errors = $result['errors'] ?? [];
            $msg = is_array($errors) && !empty($errors) ? json_encode($errors) : 'Erro desconhecido';
            throw new \RuntimeException('Falha ao criar registro DNS: ' . $msg);
        }
    }

    /**
     * Remove o registro DNS A do domínio temporário.
     */
    public function removerProxy(string $tempDomain): void
    {
        $zoneId = $this->cf->obterZoneIdDoTempDomain();
        if ($zoneId === '') return;

        $this->cf->removerRegistroPorNome($zoneId, $tempDomain);
    }
}
