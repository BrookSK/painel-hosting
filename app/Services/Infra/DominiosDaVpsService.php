<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\BancoDeDados;

/**
 * Descobre todos os domínios/subdomínios que respondem por uma VPS.
 *
 * Não existe uma tabela única de domínios — eles ficam espalhados em:
 *  - git_deployments (subdomain + temp_domain)
 *  - applications (domain + temp_domain)
 *  - vps.temp_subdomain (subdomínio automático .lrvweb)
 *
 * Este serviço centraliza a coleta, para que a suspensão/reativação via Nginx
 * consiga bloquear TODOS os domínios de uma VPS de uma vez.
 */
final class DominiosDaVpsService
{
    /**
     * Retorna a lista de domínios (strings, sem duplicatas) que apontam para a VPS,
     * junto com o server_id resolvido.
     *
     * @return array{server_id:int, dominios: string[]}
     */
    public function listar(int $vpsId): array
    {
        $pdo = BancoDeDados::pdo();
        $dominios = [];

        // server_id da VPS
        $serverId = 0;
        try {
            $stmt = $pdo->prepare('SELECT server_id, temp_subdomain FROM vps WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $vpsId]);
            $vps = $stmt->fetch();
            if (is_array($vps)) {
                $serverId = (int)($vps['server_id'] ?? 0);
                $ts = trim((string)($vps['temp_subdomain'] ?? ''));
                if ($ts !== '') {
                    $dominios[] = $ts;
                }
            }
        } catch (\Throwable) {}

        // git_deployments: subdomain e temp_domain
        try {
            $stmt = $pdo->prepare('SELECT subdomain, temp_domain FROM git_deployments WHERE vps_id = :id');
            $stmt->execute([':id' => $vpsId]);
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $sd = trim((string)($row['subdomain'] ?? ''));
                $td = trim((string)($row['temp_domain'] ?? ''));
                if ($sd !== '') $dominios[] = $sd;
                if ($td !== '') $dominios[] = $td;
            }
        } catch (\Throwable) {}

        // applications: domain e temp_domain
        try {
            $stmt = $pdo->prepare('SELECT domain, temp_domain FROM applications WHERE vps_id = :id');
            $stmt->execute([':id' => $vpsId]);
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $d = trim((string)($row['domain'] ?? ''));
                $td = trim((string)($row['temp_domain'] ?? ''));
                if ($d !== '') $dominios[] = $d;
                if ($td !== '') $dominios[] = $td;
            }
        } catch (\Throwable) {}

        // Normalizar: minúsculas, sem duplicatas, sem vazios, sem esquema/porta
        $limpos = [];
        foreach ($dominios as $dom) {
            $dom = strtolower(trim($dom));
            $dom = preg_replace('#^https?://#', '', $dom);
            $dom = explode('/', $dom)[0];
            $dom = explode(':', $dom)[0];
            if ($dom !== '' && !in_array($dom, $limpos, true)) {
                $limpos[] = $dom;
            }
        }

        return ['server_id' => $serverId, 'dominios' => $limpos];
    }
}
