<?php

declare(strict_types=1);

namespace LRV\App\Jobs;

use LRV\App\Services\Deploy\AplicacaoDeployService;
use LRV\App\Services\Alertas\NotificacoesService;
use LRV\App\Services\Backup\VpsBackupService;
use LRV\App\Services\Provisioning\DockerCli;
use LRV\App\Services\Provisioning\VpsProvisioningService;
use LRV\App\Services\Status\StatusCollectorService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\BancoDeDados;
use LRV\Core\Jobs\ContextoJob;
use LRV\Core\Jobs\ProcessadorJobs;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\Settings;

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
            $userId = (int) ($payload['user_id'] ?? 0);

            if ($titulo === '' || $mensagem === '') {
                throw new \InvalidArgumentException('Payload inválido para alerta_ticket.');
            }

            $svc = new NotificacoesService(new ClienteHttp());

            try {
                if ($userId > 0) {
                    try {
                        $pdo = BancoDeDados::pdo();
                        $ins = $pdo->prepare('INSERT INTO notifications (user_id, message, `read`, created_at) VALUES (:u,:m,0,:c)');
                        $ins->execute([
                            ':u' => $userId,
                            ':m' => '[Ticket] ' . $titulo . "\n" . $mensagem,
                            ':c' => date('Y-m-d H:i:s'),
                        ]);
                    } catch (\Throwable $e) {
                    }
                }
                $svc->alertarAdmin($titulo, $mensagem);
                $ctx->log('Alerta enviado.');
            } catch (\Throwable $e) {
                $ctx->log('Falha ao enviar alerta: ' . $e->getMessage());
            }
        });

        $p->registrar('alerta_billing', static function (array $payload, ContextoJob $ctx): void {
            $titulo = trim((string) ($payload['titulo'] ?? ''));
            $mensagem = trim((string) ($payload['mensagem'] ?? ''));
            $userId = (int) ($payload['user_id'] ?? 0);

            if ($titulo === '' || $mensagem === '') {
                throw new \InvalidArgumentException('Payload inválido para alerta_billing.');
            }

            $svc = new NotificacoesService(new ClienteHttp());

            try {
                try {
                    $pdo = BancoDeDados::pdo();

                    if ($userId > 0) {
                        $ins = $pdo->prepare('INSERT INTO notifications (user_id, message, `read`, created_at) VALUES (:u,:m,0,:c)');
                        $ins->execute([
                            ':u' => $userId,
                            ':m' => '[Billing] ' . $titulo . "\n" . $mensagem,
                            ':c' => date('Y-m-d H:i:s'),
                        ]);
                    } else {
                        $stmtUsers = $pdo->query("SELECT id FROM users WHERE status = 'active' AND role IN ('superadmin','admin')");
                        $admins = $stmtUsers->fetchAll();
                        $ins = $pdo->prepare('INSERT INTO notifications (user_id, message, `read`, created_at) VALUES (:u,:m,0,:c)');
                        foreach (($admins ?: []) as $a) {
                            $uid = (int) ($a['id'] ?? 0);
                            if ($uid > 0) {
                                $ins->execute([
                                    ':u' => $uid,
                                    ':m' => '[Billing] ' . $titulo . "\n" . $mensagem,
                                    ':c' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                }
                $svc->alertarAdmin($titulo, $mensagem);
                $ctx->log('Alerta enviado.');
            } catch (\Throwable $e) {
                $ctx->log('Falha ao enviar alerta: ' . $e->getMessage());
            }
        });

        $p->registrar('deploy_application', static function (array $payload, ContextoJob $ctx): void {
            $appId = (int) ($payload['application_id'] ?? 0);
            if ($appId <= 0) {
                throw new \InvalidArgumentException('application_id inválido.');
            }

            $pdo = BancoDeDados::pdo();
            $up = $pdo->prepare("UPDATE applications SET status = 'deploying' WHERE id = :id");
            $up->execute([':id' => $appId]);

            $svc = new AplicacaoDeployService(new DockerCli());

            try {
                $svc->deploy($appId, fn (string $m) => $ctx->log($m));
            } catch (\Throwable $e) {
                try {
                    $up2 = $pdo->prepare("UPDATE applications SET status = 'error' WHERE id = :id");
                    $up2->execute([':id' => $appId]);
                } catch (\Throwable $e2) {
                }
                throw $e;
            }
        });

        $p->registrar('backup_vps', static function (array $payload, ContextoJob $ctx): void {
            $backupId = (int) ($payload['backup_id'] ?? 0);
            if ($backupId <= 0) {
                throw new \InvalidArgumentException('backup_id inválido.');
            }

            $pdo = BancoDeDados::pdo();
            $up = $pdo->prepare("UPDATE backups SET status = 'running' WHERE id = :id");
            $up->execute([':id' => $backupId]);

            $svc = new VpsBackupService(new DockerCli());

            try {
                $svc->criar($backupId, fn (string $m) => $ctx->log($m));
            } catch (\Throwable $e) {
                try {
                    $up2 = $pdo->prepare("UPDATE backups SET status='failed', error=:e WHERE id=:id");
                    $up2->execute([
                        ':e' => $e->getMessage(),
                        ':id' => $backupId,
                    ]);
                } catch (\Throwable $e2) {
                }
                throw $e;
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

        $p->registrar('coletar_status', static function (array $payload, ContextoJob $ctx): void {
            $svc = new StatusCollectorService();
            $svc->coletar(fn (string $m) => $ctx->log($m));

            $reagendar = (bool) ($payload['reagendar'] ?? true);
            if (!$reagendar) {
                $ctx->log('Coleta concluída (sem reagendamento).');
                return;
            }

            $min = Settings::obter('status.coleta_interval_minutos', 2);
            $min = is_int($min) ? $min : (int) $min;
            if ($min < 1) {
                $min = 2;
            }
            if ($min > 60) {
                $min = 60;
            }

            try {
                $repo = new RepositorioJobs();
                $quando = new \DateTimeImmutable('now +' . $min . ' minutes');
                $repo->criar('coletar_status', ['reagendar' => true], $quando);
                $ctx->log('Reagendado coletar_status para: ' . $quando->format('Y-m-d H:i:s'));
            } catch (\Throwable $e) {
                $ctx->log('Falha ao reagendar coletar_status: ' . $e->getMessage());
            }
        });
    }
}
