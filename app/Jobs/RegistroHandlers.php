<?php

declare(strict_types=1);

namespace LRV\App\Jobs;

use LRV\App\Services\Deploy\AplicacaoDeployService;
use LRV\App\Services\Deploy\AppInstallService;
use LRV\App\Services\Alertas\NotificacoesService;
use LRV\App\Services\Backup\VpsBackupService;
use LRV\App\Services\Provisioning\DockerCli;
use LRV\App\Services\Provisioning\VpsProvisioningService;
use LRV\App\Services\Chat\ChatFlowExecutionService;
use LRV\App\Services\Chat\ChatFlowService;
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

        $p->registrar('install_app_template', static function (array $payload, ContextoJob $ctx): void {
            $appId = (int) ($payload['application_id'] ?? 0);
            if ($appId <= 0) {
                throw new \InvalidArgumentException('application_id inválido.');
            }

            $pdo = BancoDeDados::pdo();
            $pdo->prepare("UPDATE applications SET status = 'installing' WHERE id = :id")
                ->execute([':id' => $appId]);

            $svc = new AppInstallService(new DockerCli());

            try {
                $svc->instalar($appId, fn (string $m) => $ctx->log($m));
            } catch (\Throwable $e) {
                try {
                    $pdo->prepare("UPDATE applications SET status = 'error', logs = :l WHERE id = :id")
                        ->execute([':l' => $e->getMessage(), ':id' => $appId]);
                } catch (\Throwable) {}
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

        // Restaurar backup: envia tar.gz de volta para o node e extrai
        $p->registrar('restaurar_backup', static function (array $payload, ContextoJob $ctx): void {
            $backupId = (int)($payload['backup_id'] ?? 0);
            $vpsId = (int)($payload['vps_id'] ?? 0);
            if ($backupId <= 0 || $vpsId <= 0) throw new \InvalidArgumentException('Dados inválidos.');

            $pdo = BancoDeDados::pdo();
            $bk = $pdo->prepare("SELECT file_path FROM backups WHERE id = :id AND status = 'completed'");
            $bk->execute([':id' => $backupId]);
            $row = $bk->fetch();
            if (!is_array($row)) throw new \RuntimeException('Backup não encontrado.');

            $localFile = (string)($row['file_path'] ?? '');
            if ($localFile === '' || !is_file($localFile)) throw new \RuntimeException('Arquivo de backup não encontrado.');

            $vps = $pdo->prepare('SELECT client_id, server_id FROM vps WHERE id = :id');
            $vps->execute([':id' => $vpsId]);
            $v = $vps->fetch();
            if (!is_array($v)) throw new \RuntimeException('VPS não encontrada.');

            $clientId = (int)($v['client_id'] ?? 0);
            $serverId = (int)($v['server_id'] ?? 0);
            if ($serverId <= 0) throw new \RuntimeException('VPS sem node.');

            $docker = new DockerCli();
            $svc = new VpsProvisioningService($docker);

            // Configurar SSH para o node
            $srv = $pdo->prepare('SELECT ip_address, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type FROM servers WHERE id = :id');
            $srv->execute([':id' => $serverId]);
            $s = $srv->fetch();
            if (!is_array($s)) throw new \RuntimeException('Node não encontrado.');

            $host = trim((string)($s['ip_address'] ?? ''));
            $porta = (int)($s['ssh_port'] ?? 22);
            $usuario = trim((string)($s['ssh_user'] ?? ''));
            $authType = (string)($s['ssh_auth_type'] ?? 'key');

            if ($authType === 'password') {
                $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($s['ssh_password'] ?? ''));
                $docker->definirRemotoComSenha($host, $porta, $usuario, $senha);
            } else {
                $keyDir = rtrim(\LRV\Core\ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($s['ssh_key_id'] ?? '');
                $docker->definirRemoto($host, $porta, $usuario, $keyPath);
            }

            $volumeBase = (string)\LRV\Core\Settings::obter('infra.volume_base', '/vps');
            $remoteFile = '/tmp/restore_' . $backupId . '_' . time() . '.tar.gz';

            // Upload do backup para o node
            $ctx->log('Enviando backup para o node...');
            $exec = new \LRV\App\Services\Infra\SshExecutor();
            $b64 = base64_encode(file_get_contents($localFile));
            // Enviar em chunks via SSH
            $chunkSize = 500000; // ~500KB por comando
            $chunks = str_split($b64, $chunkSize);
            $docker->executar('rm -f ' . escapeshellarg($remoteFile));
            foreach ($chunks as $i => $chunk) {
                $docker->executar('echo ' . escapeshellarg($chunk) . ' >> ' . escapeshellarg($remoteFile . '.b64'));
            }
            $docker->executar('base64 -d ' . escapeshellarg($remoteFile . '.b64') . ' > ' . escapeshellarg($remoteFile) . ' && rm -f ' . escapeshellarg($remoteFile . '.b64'));

            // Extrair no volume
            $ctx->log('Restaurando arquivos...');
            $dirCliente = rtrim($volumeBase, '/') . '/client_' . $clientId;
            $docker->executar('rm -rf ' . escapeshellarg($dirCliente) . '/*');
            $docker->executar('tar -xzf ' . escapeshellarg($remoteFile) . ' -C ' . escapeshellarg($volumeBase));
            $docker->executar('rm -f ' . escapeshellarg($remoteFile));

            $ctx->log('Backup restaurado.');
        });

        // Backup automático: cria backups para todas as VPS com backup_slots > 0
        $p->registrar('backup_automatico', static function (array $payload, ContextoJob $ctx): void {
            $pdo = BancoDeDados::pdo();

            $stmt = $pdo->query("SELECT v.id AS vps_id, p.backup_slots
                FROM vps v
                INNER JOIN plans p ON p.id = v.plan_id
                WHERE v.deleted_at IS NULL AND v.status = 'running' AND p.backup_slots > 0");
            $vpsList = $stmt->fetchAll() ?: [];

            $ctx->log('VPS com backup habilitado: ' . count($vpsList));
            $repo = new \LRV\Core\Jobs\RepositorioJobs();

            foreach ($vpsList as $v) {
                $vpsId = (int)($v['vps_id'] ?? 0);
                $maxSlots = (int)($v['backup_slots'] ?? 0);
                if ($vpsId <= 0) continue;

                // Verificar se já tem backup recente (últimas 20h)
                $recent = $pdo->prepare("SELECT id FROM backups WHERE vps_id = :v AND created_at > DATE_SUB(NOW(), INTERVAL 20 HOUR) LIMIT 1");
                $recent->execute([':v' => $vpsId]);
                if ($recent->fetch()) {
                    $ctx->log('VPS #' . $vpsId . ': backup recente, pulando.');
                    continue;
                }

                // Rotação
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM backups WHERE vps_id = :v AND status IN ('completed','running','queued')");
                $countStmt->execute([':v' => $vpsId]);
                $existentes = (int)$countStmt->fetchColumn();

                if ($existentes >= $maxSlots) {
                    $oldStmt = $pdo->prepare("SELECT id, file_path FROM backups WHERE vps_id = :v AND status = 'completed' ORDER BY id ASC LIMIT 1");
                    $oldStmt->execute([':v' => $vpsId]);
                    $old = $oldStmt->fetch();
                    if (is_array($old)) {
                        $oldPath = (string)($old['file_path'] ?? '');
                        if ($oldPath !== '' && is_file($oldPath)) @unlink($oldPath);
                        $pdo->prepare('DELETE FROM backups WHERE id = :id')->execute([':id' => (int)$old['id']]);
                    }
                }

                // Criar backup
                $ins = $pdo->prepare("INSERT INTO backups (vps_id, job_id, status, created_at) VALUES (:v, NULL, 'queued', :c)");
                $ins->execute([':v' => $vpsId, ':c' => date('Y-m-d H:i:s')]);
                $backupId = (int)$pdo->lastInsertId();

                $jobId = $repo->criar('backup_vps', ['backup_id' => $backupId]);
                $pdo->prepare('UPDATE backups SET job_id = :j WHERE id = :id')->execute([':j' => $jobId, ':id' => $backupId]);

                $ctx->log('VPS #' . $vpsId . ': backup #' . $backupId . ' enfileirado (job #' . $jobId . ').');
            }

            // Reagendar para daqui 24h
            $reagendar = (bool)($payload['reagendar'] ?? true);
            if ($reagendar) {
                try {
                    $quando = new \DateTimeImmutable('now +24 hours');
                    $repo->criar('backup_automatico', ['reagendar' => true], $quando);
                    $ctx->log('Reagendado para: ' . $quando->format('Y-m-d H:i:s'));
                } catch (\Throwable $e) {
                    $ctx->log('Falha ao reagendar: ' . $e->getMessage());
                }
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

        $p->registrar('reiniciar_vps', static function (array $payload, ContextoJob $ctx): void {
            $vpsId = (int) ($payload['vps_id'] ?? 0);
            if ($vpsId <= 0) {
                throw new \InvalidArgumentException('vps_id inválido.');
            }

            $svc = new VpsProvisioningService(new DockerCli());
            $svc->reiniciar($vpsId, fn (string $m) => $ctx->log($m));
        });

        $p->registrar('remover_vps', static function (array $payload, ContextoJob $ctx): void {
            $vpsId = (int) ($payload['vps_id'] ?? 0);
            if ($vpsId <= 0) {
                throw new \InvalidArgumentException('vps_id inválido.');
            }

            $svc = new VpsProvisioningService(new DockerCli());
            $svc->remover($vpsId, fn (string $m) => $ctx->log($m));
        });

        // Resize VPS: atualiza limites de CPU/RAM do container Docker
        $p->registrar('resize_vps', static function (array $payload, ContextoJob $ctx): void {
            $vpsId = (int)($payload['vps_id'] ?? 0);
            $newCpu = (int)($payload['cpu'] ?? 0);
            $newRam = (int)($payload['ram'] ?? 0);
            $upgradeId = (int)($payload['upgrade_id'] ?? 0);

            if ($vpsId <= 0) {
                throw new \InvalidArgumentException('vps_id inválido.');
            }

            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                'SELECT v.id, v.container_id, v.server_id,
                        s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
                 FROM vps v
                 JOIN servers s ON s.id = v.server_id
                 WHERE v.id = :id LIMIT 1'
            );
            $stmt->execute([':id' => $vpsId]);
            $vps = $stmt->fetch();

            if (!is_array($vps)) {
                $ctx->log('VPS não encontrada ou sem servidor associado.');
                return;
            }

            $containerId = trim((string)($vps['container_id'] ?? ''));
            if ($containerId === '') {
                $ctx->log('VPS sem container_id — resize não necessário (será aplicado no próximo provisionamento).');
                return;
            }

            $ctx->log("Resize VPS #{$vpsId}: CPU={$newCpu}, RAM={$newRam}MB");

            // Montar comando docker update
            $cmd = 'docker update';
            if ($newCpu > 0) {
                $cmd .= ' --cpus=' . escapeshellarg((string)$newCpu);
            }
            if ($newRam > 0) {
                $cmd .= ' -m ' . escapeshellarg($newRam . 'm');
                $cmd .= ' --memory-swap ' . escapeshellarg($newRam . 'm');
            }
            $cmd .= ' ' . escapeshellarg($containerId) . ' 2>&1';

            try {
                $docker = new DockerCli();
                $authType = (string)($vps['ssh_auth_type'] ?? 'password');
                if ($authType === 'password') {
                    $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($vps['ssh_password'] ?? ''));
                    $docker->definirRemotoComSenha(
                        (string)$vps['ip_address'], (int)$vps['ssh_port'],
                        (string)$vps['ssh_user'], $senha
                    );
                } else {
                    $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($vps['ssh_key_id'] ?? '');
                    $docker->definirRemoto(
                        (string)$vps['ip_address'], (int)$vps['ssh_port'],
                        (string)$vps['ssh_user'], $keyPath
                    );
                }

                $result = $docker->executar($cmd);
                $output = trim((string)($result['saida'] ?? ''));
                $ctx->log('docker update: ' . $output);

                if (str_contains(strtolower($output), 'error')) {
                    $ctx->log('Aviso: docker update retornou erro. O container pode precisar de restart.');
                    // Tentar restart pra aplicar os novos limites
                    try {
                        $docker->executar('docker restart ' . escapeshellarg($containerId) . ' 2>&1');
                        $ctx->log('Container reiniciado com sucesso.');
                    } catch (\Throwable $re) {
                        $ctx->log('Falha ao reiniciar: ' . $re->getMessage());
                    }
                } else {
                    $ctx->log('Resize concluído com sucesso.');
                }
            } catch (\Throwable $e) {
                $ctx->log('Falha no resize: ' . $e->getMessage());
            }
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

        $p->registrar('flow_cron', static function (array $payload, ContextoJob $ctx): void {
            $pdo = BancoDeDados::pdo();
            $execSvc = new ChatFlowExecutionService();
            $flowSvc = new ChatFlowService();

            // 1. Resume pending executions (delay steps with next_run_at <= NOW)
            try {
                $stmt = $pdo->prepare(
                    "SELECT id FROM chat_flow_executions WHERE status = 'running' AND next_run_at IS NOT NULL AND next_run_at <= NOW()"
                );
                $stmt->execute();
                $pending = $stmt->fetchAll() ?: [];
                foreach ($pending as $row) {
                    try {
                        $execSvc->processarProximoPasso((int) $row['id']);
                        $ctx->log('Retomada execução #' . $row['id']);
                    } catch (\Throwable $e) {
                        $ctx->log('Erro ao retomar execução #' . $row['id'] . ': ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                $ctx->log('Erro ao buscar execuções pendentes: ' . $e->getMessage());
            }

            // 2. Check for inactive rooms and trigger client_inactive flows
            try {
                $inactiveFlows = $flowSvc->listarPorTrigger('client_inactive');
                if (!empty($inactiveFlows)) {
                    $timeoutMin = (int) Settings::obter('chat_flow.inactivity_minutes', 10);
                    if ($timeoutMin < 1) $timeoutMin = 10;

                    $stmt = $pdo->prepare(
                        "SELECT r.id AS room_id
                         FROM chat_rooms r
                         INNER JOIN (
                             SELECT room_id, MAX(id) AS last_msg_id
                             FROM chat_messages
                             GROUP BY room_id
                         ) lm ON lm.room_id = r.id
                         INNER JOIN chat_messages m ON m.id = lm.last_msg_id
                         WHERE r.status = 'open'
                           AND m.sender_type = 'admin'
                           AND m.created_at <= DATE_SUB(NOW(), INTERVAL :mins MINUTE)"
                    );
                    $stmt->execute([':mins' => $timeoutMin]);
                    $inactiveRooms = $stmt->fetchAll() ?: [];

                    foreach ($inactiveRooms as $room) {
                        $roomId = (int) $room['room_id'];
                        foreach ($inactiveFlows as $flow) {
                            $flowId = (int) $flow['id'];
                            if ($execSvc->jaDisparadoNaSessao($roomId, $flowId)) {
                                continue;
                            }
                            try {
                                $execSvc->iniciar($flowId, $roomId, 'cron');
                                $ctx->log("Fluxo #{$flowId} disparado para sala #{$roomId}");
                            } catch (\Throwable $e) {
                                $ctx->log("Erro ao disparar fluxo #{$flowId} para sala #{$roomId}: " . $e->getMessage());
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                $ctx->log('Erro na detecção de inatividade: ' . $e->getMessage());
            }

            // 3. Re-schedule self
            try {
                $interval = (int) Settings::obter('chat_flow.cron_interval_seconds', 60);
                if ($interval < 10) $interval = 60;
                $repo = new RepositorioJobs();
                $quando = new \DateTimeImmutable('now +' . $interval . ' seconds');
                $repo->criar('flow_cron', [], $quando);
                $ctx->log('Reagendado flow_cron para: ' . $quando->format('Y-m-d H:i:s'));
            } catch (\Throwable $e) {
                $ctx->log('Falha ao reagendar flow_cron: ' . $e->getMessage());
            }
        });

        $p->registrar('billing_reminders', static function (array $payload, ContextoJob $ctx): void {
            $svc = new \LRV\App\Services\Billing\BillingReminderService();
            $svc->processar(fn (string $m) => $ctx->log($m));

            // Reagendar para o próximo dia às 08:00
            try {
                $repo = new RepositorioJobs();
                $amanha = new \DateTimeImmutable('tomorrow 08:00');
                $repo->criar('billing_reminders', [], $amanha);
                $ctx->log('Reagendado billing_reminders para: ' . $amanha->format('Y-m-d H:i:s'));
            } catch (\Throwable $e) {
                $ctx->log('Falha ao reagendar: ' . $e->getMessage());
            }
        });

        $p->registrar('limpar_pendentes_expirados', static function (array $payload, ContextoJob $ctx): void {
            $pdo = BancoDeDados::pdo();
            $horasExpiracao = (int) ($payload['horas'] ?? 48);
            if ($horasExpiracao < 1) $horasExpiracao = 48;

            // Buscar assinaturas PENDING criadas há mais de X horas
            $stmt = $pdo->prepare(
                "SELECT s.id AS sub_id, s.vps_id, s.client_id
                 FROM subscriptions s
                 WHERE s.status = 'PENDING'
                   AND s.created_at <= DATE_SUB(NOW(), INTERVAL :h HOUR)"
            );
            $stmt->execute([':h' => $horasExpiracao]);
            $expiradas = $stmt->fetchAll() ?: [];

            $count = 0;
            foreach ($expiradas as $row) {
                $subId = (int) ($row['sub_id'] ?? 0);
                $vpsId = (int) ($row['vps_id'] ?? 0);

                try {
                    $pdo->prepare("UPDATE subscriptions SET status = 'EXPIRED' WHERE id = :id AND status = 'PENDING'")
                        ->execute([':id' => $subId]);

                    if ($vpsId > 0) {
                        $pdo->prepare("UPDATE vps SET status = 'expired' WHERE id = :id AND status = 'pending_payment'")
                            ->execute([':id' => $vpsId]);
                    }

                    $count++;
                    $ctx->log("Expirada: assinatura #{$subId}, VPS #{$vpsId}");
                } catch (\Throwable $e) {
                    $ctx->log("Erro ao expirar assinatura #{$subId}: " . $e->getMessage());
                }
            }

            $ctx->log("Total expiradas: {$count}");

            // Reagendar
            try {
                $repo = new RepositorioJobs();
                $quando = new \DateTimeImmutable('now +1 hour');
                $repo->criar('limpar_pendentes_expirados', ['horas' => $horasExpiracao], $quando);
                $ctx->log('Reagendado para: ' . $quando->format('Y-m-d H:i:s'));
            } catch (\Throwable $e) {
                $ctx->log('Falha ao reagendar: ' . $e->getMessage());
            }
        });
    }
}
