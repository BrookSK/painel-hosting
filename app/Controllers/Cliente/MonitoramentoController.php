<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class MonitoramentoController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $sql = "SELECT v.id AS vps_id, v.server_id, v.cpu, v.ram, v.storage, v.status,
                       m.cpu_usage, m.ram_usage, m.disk_usage, m.timestamp
                FROM vps v
                LEFT JOIN (
                    SELECT sm.*
                    FROM server_metrics sm
                    INNER JOIN (
                        SELECT server_id, MAX(timestamp) AS ts
                        FROM server_metrics
                        GROUP BY server_id
                    ) ult ON ult.server_id = sm.server_id AND ult.ts = sm.timestamp
                ) m ON m.server_id = v.server_id
                WHERE v.client_id = :c AND v.status NOT IN ('expired','removed')
                ORDER BY v.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':c' => $clienteId]);
        $linhas = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/monitoramento-listar.php', [
            'linhas' => is_array($linhas) ? $linhas : [],
        ]);

        return Resposta::html($html);
    }

    public function ver(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $vpsId = (int) ($req->query['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT v.id, v.server_id, v.container_id, v.cpu, v.ram, v.storage, v.status, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_auth_type, s.ssh_key_id, s.ssh_password FROM vps v LEFT JOIN servers s ON s.id = v.server_id WHERE v.id = :id AND v.client_id = :c LIMIT 1');
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return Resposta::texto('Acesso negado.', 403);
        }

        // Coletar métricas do container Docker em tempo real
        $containerStats = null;
        $statsErro = '';
        $containerId = trim((string)($vps['container_id'] ?? ''));
        if ($containerId !== '' && (string)($vps['status'] ?? '') === 'running') {
            try {
                // Tentar com container_id, fallback para nome do container
                $containerRef = $containerId;
                $clientIdForName = 0;
                try {
                    $cStmt = $pdo->prepare('SELECT client_id FROM vps WHERE id = :id');
                    $cStmt->execute([':id' => $vpsId]);
                    $cRow = $cStmt->fetch();
                    $clientIdForName = (int)($cRow['client_id'] ?? 0);
                } catch (\Throwable) {}
                $containerName = 'vps_client_' . $clientIdForName . '_' . $vpsId;

                $fmt = "'{{.CPUPerc}}|{{.MemUsage}}|{{.MemPerc}}|{{.BlockIO}}'";
                $dockerCmd = 'docker stats ' . escapeshellarg($containerRef) . ' --no-stream --format ' . $fmt . ' 2>&1 || docker stats ' . escapeshellarg($containerName) . ' --no-stream --format ' . $fmt . ' 2>&1';
                $ssh = new \LRV\App\Services\Infra\SshExecutor();
                $ip = (string)($vps['ip_address'] ?? '');
                $porta = (int)($vps['ssh_port'] ?? 22);
                $usuario = (string)($vps['ssh_user'] ?? 'root');
                $authType = (string)($vps['ssh_auth_type'] ?? 'key');

                if ($ip === '') {
                    $statsErro = 'Servidor sem IP configurado.';
                } else {
                    if ($authType === 'password') {
                        $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($vps['ssh_password'] ?? ''));
                        $result = $ssh->executarComSenha($ip, $porta, $usuario, $senha, $dockerCmd, 15);
                    } else {
                        $keyDir = rtrim(\LRV\Core\ConfiguracoesSistema::sshKeyDir(), "/\\");
                        $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($vps['ssh_key_id'] ?? '');
                        $result = $ssh->executar($ip, $porta, $usuario, $keyPath, $dockerCmd, 15);
                    }

                    $output = trim((string)($result['saida'] ?? ''));
                    $exitCode = (int)($result['codigo'] ?? -1);

                    // Filtrar warnings do SSH
                    $lines = explode("\n", $output);
                    $cleanLines = [];
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line === '' || str_contains($line, 'Warning:') || str_contains($line, 'Permanently added') || str_contains($line, 'known_hosts')) continue;
                        if (str_contains($line, 'Error response from daemon')) continue;
                        $cleanLines[] = $line;
                    }
                    $output = implode("\n", $cleanLines);

                    if ($output !== '' && $exitCode === 0 && str_contains($output, '|')) {
                        $parts = explode('|', $output);
                        $containerStats = [
                            'cpu_percent' => (float)str_replace('%', '', $parts[0] ?? '0'),
                            'mem_usage'   => trim($parts[1] ?? '0'),
                            'mem_percent' => (float)str_replace('%', '', $parts[2] ?? '0'),
                            'block_io'    => trim($parts[3] ?? '0'),
                        ];
                    } else {
                        $statsErro = $output !== '' ? $output : 'Sem resposta do docker stats (exit: ' . $exitCode . ')';
                    }
                }
            } catch (\Throwable $e) {
                $statsErro = $e->getMessage();
            }
        } elseif ($containerId === '') {
            $statsErro = 'Container não atribuído.';
        }

        // Métricas históricas do servidor (limitadas a 12)
        $serverId = (int)($vps['server_id'] ?? 0);
        $metricas = [];
        if ($serverId > 0) {
            $stmt = $pdo->prepare('SELECT cpu_usage, ram_usage, disk_usage, timestamp FROM server_metrics WHERE server_id = :id ORDER BY timestamp DESC LIMIT 12');
            $stmt->execute([':id' => $serverId]);
            $metricas = $stmt->fetchAll() ?: [];
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/monitoramento-ver.php', [
            'vps' => [
                'id' => (int)$vps['id'],
                'cpu' => (int)($vps['cpu'] ?? 0),
                'ram' => (int)($vps['ram'] ?? 0),
                'storage' => (int)($vps['storage'] ?? 0),
                'status' => (string)($vps['status'] ?? ''),
            ],
            'container_stats' => $containerStats,
            'stats_erro' => $statsErro,
            'metricas' => $metricas,
        ]);

        return Resposta::html($html);
    }
}
