<?php

declare(strict_types=1);

namespace LRV\App\Jobs;

use LRV\App\Services\Provisioning\DockerCli;
use LRV\App\Services\Provisioning\VpsProvisioningService;
use LRV\Core\BancoDeDados;
use LRV\Core\Jobs\ContextoJob;
use LRV\Core\Jobs\ProcessadorJobs;

final class RegistroHandlers
{
    public static function registrar(ProcessadorJobs $p): void
    {
        $p->registrar('noop', static function (array $payload, ContextoJob $ctx): void {
            $ctx->log('Job de teste executado.');
        });

        $p->registrar('provisionar_vps', static function (array $payload, ContextoJob $ctx): void {
            $vpsId = (int) ($payload['vps_id'] ?? 0);
            if ($vpsId <= 0) {
                throw new \InvalidArgumentException('vps_id inválido.');
            }

            $svc = new VpsProvisioningService(new DockerCli());
            $svc->provisionar($vpsId, fn (string $m) => $ctx->log($m));
        });

        $p->registrar('suspender_vps', static function (array $payload, ContextoJob $ctx): void {
            $vpsId = (int) ($payload['vps_id'] ?? 0);
            $assinaturaId = (int) ($payload['assinatura_id'] ?? 0);

            if ($vpsId <= 0) {
                throw new \InvalidArgumentException('vps_id inválido.');
            }

            if ($assinaturaId > 0) {
                $pdo = BancoDeDados::pdo();
                $stmt = $pdo->prepare('SELECT status FROM subscriptions WHERE id = :id');
                $stmt->execute([':id' => $assinaturaId]);
                $sub = $stmt->fetch();

                if (is_array($sub)) {
                    $st = (string) ($sub['status'] ?? '');
                    if (!in_array($st, ['OVERDUE', 'CANCELED', 'SUSPENDED'], true)) {
                        $ctx->log('Assinatura não está inadimplente/cancelada. Job ignorado.');
                        return;
                    }
                }
            }

            $svc = new VpsProvisioningService(new DockerCli());
            $svc->suspenderPorPagamento($vpsId, fn (string $m) => $ctx->log($m));
        });

        $p->registrar('reativar_vps', static function (array $payload, ContextoJob $ctx): void {
            $vpsId = (int) ($payload['vps_id'] ?? 0);
            if ($vpsId <= 0) {
                throw new \InvalidArgumentException('vps_id inválido.');
            }

            $svc = new VpsProvisioningService(new DockerCli());
            $svc->reativarPorPagamento($vpsId, fn (string $m) => $ctx->log($m));
        });
    }
}
