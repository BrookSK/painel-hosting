<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;
use LRV\App\Services\Infra\SshCrypto;
use LRV\App\Services\Infra\SshExecutor;

final class CronJobsController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM client_cron_jobs WHERE client_id = :c ORDER BY id DESC');
        $stmt->execute([':c' => $clienteId]);
        $crons = $stmt->fetchAll() ?: [];

        $vpsStmt = $pdo->prepare("SELECT id, cpu, ram FROM vps WHERE client_id = :c AND status = 'running' ORDER BY id");
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/cron-jobs.php', [
            'crons' => $crons, 'vpsList' => $vpsList, 'cliente' => $cliente,
        ]));
    }

    public function salvar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->post['id'] ?? 0);
        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $name = trim((string)($req->post['name'] ?? ''));
        $taskType = (string)($req->post['task_type'] ?? 'command');
        $command = trim((string)($req->post['command'] ?? ''));
        $description = trim((string)($req->post['description'] ?? ''));
        $enabled = (int)($req->post['enabled'] ?? 1);

        // Montar schedule a partir dos campos do form
        $scheduleType = (string)($req->post['schedule_type'] ?? 'custom');
        if ($scheduleType === 'custom') {
            $schedule = trim((string)($req->post['schedule'] ?? '* * * * *'));
        } else {
            $minute = str_pad((string)($req->post['minute'] ?? '0'), 1, '0');
            $hour = str_pad((string)($req->post['hour'] ?? '0'), 1, '0');
            $schedule = match($scheduleType) {
                'every_minute' => '* * * * *',
                'every_5min' => '*/5 * * * *',
                'every_15min' => '*/15 * * * *',
                'every_30min' => '*/30 * * * *',
                'hourly' => '0 * * * *',
                'daily' => $minute . ' ' . $hour . ' * * *',
                'weekly' => $minute . ' ' . $hour . ' * * 0',
                'monthly' => $minute . ' ' . $hour . ' 1 * *',
                default => '0 * * * *',
            };
        }

        if (!in_array($taskType, ['command', 'url', 'php_script'])) $taskType = 'command';
        if ($name === '' || $command === '' || $vpsId <= 0) {
            return Resposta::redirecionar('/cliente/cron-jobs');
        }

        $pdo = BancoDeDados::pdo();

        // Validar VPS
        $vStmt = $pdo->prepare("SELECT id FROM vps WHERE id = :v AND client_id = :c AND status = 'running' LIMIT 1");
        $vStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        if (!$vStmt->fetch()) return Resposta::redirecionar('/cliente/cron-jobs');

        if ($id > 0) {
            $pdo->prepare('UPDATE client_cron_jobs SET vps_id=:v, name=:n, task_type=:tt, command=:cmd, schedule=:s, description=:d, enabled=:e WHERE id=:id AND client_id=:c')
                ->execute([':v'=>$vpsId, ':n'=>$name, ':tt'=>$taskType, ':cmd'=>$command, ':s'=>$schedule, ':d'=>$description!==''?$description:null, ':e'=>$enabled, ':id'=>$id, ':c'=>$clienteId]);
        } else {
            $pdo->prepare('INSERT INTO client_cron_jobs (client_id, vps_id, name, task_type, command, schedule, description, enabled) VALUES (:c,:v,:n,:tt,:cmd,:s,:d,:e)')
                ->execute([':c'=>$clienteId, ':v'=>$vpsId, ':n'=>$name, ':tt'=>$taskType, ':cmd'=>$command, ':s'=>$schedule, ':d'=>$description!==''?$description:null, ':e'=>$enabled]);
            $id = (int)$pdo->lastInsertId();
        }

        // Sincronizar crontab no servidor
        $this->sincronizarCrontab($clienteId, $vpsId);

        return Resposta::redirecionar('/cliente/cron-jobs');
    }

    public function excluir(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT vps_id FROM client_cron_jobs WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $row = $stmt->fetch();

        $pdo->prepare('DELETE FROM client_cron_jobs WHERE id = :id AND client_id = :c')->execute([':id' => $id, ':c' => $clienteId]);

        if (is_array($row)) {
            $this->sincronizarCrontab($clienteId, (int)$row['vps_id']);
        }

        return Resposta::redirecionar('/cliente/cron-jobs');
    }

    public function toggle(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT vps_id, enabled FROM client_cron_jobs WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $row = $stmt->fetch();
        if (!is_array($row)) return Resposta::json(['ok' => false, 'erro' => 'Não encontrado.'], 404);

        $newEnabled = ((int)$row['enabled'] === 1) ? 0 : 1;
        $pdo->prepare('UPDATE client_cron_jobs SET enabled = :e WHERE id = :id AND client_id = :c')
            ->execute([':e' => $newEnabled, ':id' => $id, ':c' => $clienteId]);

        $this->sincronizarCrontab($clienteId, (int)$row['vps_id']);

        return Resposta::json(['ok' => true, 'enabled' => $newEnabled]);
    }

    public function executarAgora(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            'SELECT cj.*, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM client_cron_jobs cj
             JOIN vps v ON v.id = cj.vps_id
             JOIN servers s ON s.id = v.server_id
             WHERE cj.id = :id AND cj.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $cron = $stmt->fetch();
        if (!is_array($cron)) return Resposta::json(['ok' => false, 'erro' => 'Não encontrado.'], 404);

        $cmd = $this->montarComando($cron);
        $pdo->prepare('UPDATE client_cron_jobs SET last_status="running", last_run_at=:t WHERE id=:id')
            ->execute([':t' => date('Y-m-d H:i:s'), ':id' => $id]);

        try {
            $result = $this->executarSsh($cron, $cmd . ' 2>&1', 60);
            $output = (string)($result['saida'] ?? '');
            // Limpar warnings SSH
            $lines = array_filter(explode("\n", $output), fn($l) => !str_contains($l, 'Warning: Permanently added') && !str_contains($l, 'known_hosts'));
            $output = implode("\n", $lines);

            $pdo->prepare('UPDATE client_cron_jobs SET last_status="success", last_output=:o WHERE id=:id')
                ->execute([':o' => substr($output, 0, 5000), ':id' => $id]);

            return Resposta::json(['ok' => true, 'output' => $output]);
        } catch (\Throwable $e) {
            $pdo->prepare('UPDATE client_cron_jobs SET last_status="error", last_output=:o WHERE id=:id')
                ->execute([':o' => $e->getMessage(), ':id' => $id]);
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    private function montarComando(array $cron): string
    {
        $taskType = (string)($cron['task_type'] ?? 'command');
        $command = (string)($cron['command'] ?? '');

        return match($taskType) {
            'url' => 'curl -sS -o /dev/null -w "%{http_code}" ' . escapeshellarg($command),
            'php_script' => 'php ' . escapeshellarg($command),
            default => $command,
        };
    }

    private function sincronizarCrontab(int $clienteId, int $vpsId): void
    {
        $pdo = BancoDeDados::pdo();

        // Buscar todos os crons ativos deste cliente nesta VPS
        $stmt = $pdo->prepare('SELECT * FROM client_cron_jobs WHERE client_id = :c AND vps_id = :v AND enabled = 1 ORDER BY id');
        $stmt->execute([':c' => $clienteId, ':v' => $vpsId]);
        $crons = $stmt->fetchAll() ?: [];

        // Buscar dados do servidor
        $srvStmt = $pdo->prepare(
            'SELECT s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :v LIMIT 1'
        );
        $srvStmt->execute([':v' => $vpsId]);
        $srv = $srvStmt->fetch();
        if (!is_array($srv)) return;

        // Montar crontab
        $marker = '# LRV-CRON-CLIENT-' . $clienteId;
        $lines = [$marker . '-START'];
        foreach ($crons as $c) {
            $cmd = $this->montarComando($c);
            $lines[] = (string)$c['schedule'] . ' ' . $cmd . ' >> /tmp/lrv_cron_' . (int)$c['id'] . '.log 2>&1 ' . $marker . '-' . (int)$c['id'];
        }
        $lines[] = $marker . '-END';
        $cronBlock = implode("\n", $lines);
        $cronB64 = base64_encode($cronBlock);

        // Remover bloco antigo e inserir novo
        $sshCmd = '(crontab -l 2>/dev/null | sed "/' . $marker . '-START/,/' . $marker . '-END/d") > /tmp/lrv_crontab_tmp'
            . ' && echo ' . escapeshellarg($cronB64) . ' | base64 -d >> /tmp/lrv_crontab_tmp'
            . ' && crontab /tmp/lrv_crontab_tmp'
            . ' && rm -f /tmp/lrv_crontab_tmp'
            . ' && echo lrv-cron-ok';

        try {
            $this->executarSsh($srv, $sshCmd, 15);
        } catch (\Throwable) {}
    }

    private function executarSsh(array $srv, string $cmd, int $timeout = 15): array
    {
        $exec = new SshExecutor();
        $host = (string)($srv['ip_address'] ?? '');
        $port = (int)($srv['ssh_port'] ?? 22);
        $user = (string)($srv['ssh_user'] ?? 'root');
        $authType = (string)($srv['ssh_auth_type'] ?? 'password');

        if ($authType === 'password') {
            $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
            return $exec->executarComSenha($host, $port, $user, $senha, $cmd, $timeout);
        }
        $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($srv['ssh_key_id'] ?? '');
        return $exec->executar($host, $port, $user, $keyPath, $cmd, $timeout);
    }
}
