<?php

declare(strict_types=1);

namespace LRV\App\Jobs;

use LRV\App\Services\Alertas\NotificacoesService;
use LRV\App\Services\Provisioning\DockerCli;
use LRV\App\Services\Provisioning\VpsProvisioningService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\BancoDeDados;
use LRV\Core\Jobs\ContextoJob;
use LRV\Core\Jobs\ProcessadorJobs;
use LRV\Core\Jobs\RepositorioJobs;

final class RegistroHandlers
{
    public static function registrar(ProcessadorJobs $p): void
    {
        $p->registrar('noop', static function (array $payload, ContextoJob $ctx): void {
            $ctx->log('Job de teste executado.');
        });

        $p->registrar('alerta_ticket', static function (array $payload, ContextoJob $ctx): void {
            $titulo = trim((string) ($payload['titulo'] ?? ''));
            $mensagem = trim((string) ($payload['mensagem'] ?? ''));

            if ($titulo === '' || $mensagem === '') {
                throw new \InvalidArgumentException('Payload inválido para alerta_ticket.');
            }

            $svc = new NotificacoesService(new ClienteHttp());

            try {
                $svc->alertarAdmin($titulo, $mensagem);
                $ctx->log('Alerta enviado.');
            } catch (\Throwable $e) {
                $ctx->log('Falha ao enviar alerta: ' . $e->getMessage());
            }
        });

        $p->registrar('alerta_billing', static function (array $payload, ContextoJob $ctx): void {
            $titulo = trim((string) ($payload['titulo'] ?? ''));
            $mensagem = trim((string) ($payload['mensagem'] ?? ''));

            if ($titulo === '' || $mensagem === '') {
                throw new \InvalidArgumentException('Payload inválido para alerta_billing.');
            }

            $svc = new NotificacoesService(new ClienteHttp());

            try {
                $svc->alertarAdmin($titulo, $mensagem);
                $ctx->log('Alerta enviado.');
            } catch (\Throwable $e) {
                $ctx->log('Falha ao enviar alerta: ' . $e->getMessage());
            }
        });

        $p->registrar('provisionar_vps', static function (array $payload, ContextoJob $ctx): void {
            $vpsId = (int) ($payload['vps_id'] ?? 0);
            if ($vpsId <= 0) {
                throw new \InvalidArgumentException('vps_id inválido.');
            }

            $svc = new VpsProvisioningService(new DockerCli());
            $svc->provisionar($vpsId, fn (string $m) => $ctx->log($m));

            try {
                $pdo = BancoDeDados::pdo();
                $stmt = $pdo->prepare('SELECT status FROM vps WHERE id = :id');
                $stmt->execute([':id' => $vpsId]);
                $vps = $stmt->fetch();

                $st = is_array($vps) ? (string) ($vps['status'] ?? '') : '';
                if (in_array($st, ['pending_node', 'pending_provisioning'], true)) {
                    $repo = new RepositorioJobs();
                    $quando = new \DateTimeImmutable('now + 5 minutes');
                    $repo->criar('provisionar_vps', ['vps_id' => $vpsId], $quando);
                    $ctx->log('Reagendado provisionar_vps para: ' . $quando->format('Y-m-d H:i:s'));
                }
            } catch (\Throwable $e) {
                $ctx->log('Falha ao reagendar provisionamento: ' . $e->getMessage());
            }
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

            if ($assinaturaId > 0) {
                try {
                    $pdo = BancoDeDados::pdo();
                    $stAtualStmt = $pdo->prepare('SELECT status FROM subscriptions WHERE id = :id');
                    $stAtualStmt->execute([':id' => $assinaturaId]);
                    $sub = $stAtualStmt->fetch();

                    $stAtual = is_array($sub) ? (string) ($sub['status'] ?? '') : '';
                    if ($stAtual === 'OVERDUE') {
                        $up = $pdo->prepare("UPDATE subscriptions SET status = 'SUSPENDED' WHERE id = :id");
                        $up->execute([':id' => $assinaturaId]);
                        $ctx->log('Assinatura marcada como SUSPENDED.');
                    } else {
                        $ctx->log('Assinatura mantida como ' . ($stAtual !== '' ? $stAtual : '(desconhecido)') . '.');
                    }
                } catch (\Throwable $e) {
                    $ctx->log('Falha ao marcar assinatura como SUSPENDED.');
                }
            }
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
