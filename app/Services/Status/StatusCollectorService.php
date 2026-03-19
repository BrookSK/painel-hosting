<?php

declare(strict_types=1);

namespace LRV\App\Services\Status;

use LRV\App\Services\Infra\NodeHealthService;
use LRV\App\Services\Infra\SshExecutor;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class StatusCollectorService
{
    public function __construct(
        private readonly StatusRepository $repo = new StatusRepository(),
        private readonly NodeHealthService $health = new NodeHealthService(),
        private readonly SshExecutor $ssh = new SshExecutor(),
    ) {
    }

    public function coletar(callable $log): void
    {
        $pdo = BancoDeDados::pdo();

        try {
            $stmt = $pdo->query('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status, is_online, last_error FROM servers ORDER BY id DESC');
            $servers = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $stmt = $pdo->query('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers ORDER BY id DESC');
            $servers = $stmt->fetchAll();
        }

        $agora = new \DateTimeImmutable();

        foreach (($servers ?: []) as $srv) {
            if (!is_array($srv)) {
                continue;
            }

            $serverId = (int) ($srv['id'] ?? 0);
            if ($serverId <= 0) {
                continue;
            }

            $st = (string) ($srv['status'] ?? '');
            if ($st !== 'active') {
                continue;
            }

            $hostname = trim((string) ($srv['hostname'] ?? ''));
            $ip = trim((string) ($srv['ip_address'] ?? ''));
            $host = $ip !== '' ? $ip : $hostname;

            $log('Coletando status do node #' . $serverId . ' (' . ($host !== '' ? $host : 'sem host') . ')');

            $okNode = false;
            try {
                $okNode = $this->health->verificarNode($serverId, function (string $m) use ($log, $serverId): void {
                    $log('[node:' . $serverId . '] ' . $m);
                });
            } catch (\Throwable $e) {
                $okNode = false;
            }

            $srvAtual = $this->carregarServer($pdo, $serverId);

            $erroNode = '';
            if (is_array($srvAtual)) {
                $erroNode = trim((string) ($srvAtual['last_error'] ?? ''));
            }

            $statusNode = $okNode ? 'operational' : 'major_outage';
            $nodeKey = 'node:' . $serverId;

            $exNode = $this->repo->obterServicePorKey($nodeKey);
            $lastOkNode = $okNode ? $agora : $this->parseDt(($exNode['last_ok_at'] ?? null) ?? null);

            $nodeServiceId = $this->repo->upsertService(
                $nodeKey,
                'Node ' . ($hostname !== '' ? $hostname : ('#' . $serverId)),
                null,
                'public',
                null,
                $serverId,
                null,
                $statusNode,
                $agora,
                $lastOkNode,
                $okNode ? null : ($erroNode !== '' ? $erroNode : 'Node offline.'),
                [
                    'host' => $host,
                    'is_online' => $okNode,
                ],
            );

            $this->repo->registrarLog(
                $nodeServiceId,
                $statusNode,
                $okNode ? 'OK' : ($erroNode !== '' ? $erroNode : 'Falha ao validar SSH/Docker.'),
                null,
                $agora,
            );

            $vps = $this->listarVpsDoServer($pdo, $serverId);

            if (empty($vps)) {
                continue;
            }

            if (!$okNode) {
                foreach ($vps as $v) {
                    $this->registrarVpsOffline($v, $serverId, $agora, 'Node offline');
                }
                continue;
            }

            $conn = $this->resolverConexaoNode($srvAtual ?? $srv);
            if ($conn === null) {
                foreach ($vps as $v) {
                    $this->registrarVpsOffline($v, $serverId, $agora, 'Dados de SSH incompletos');
                }
                continue;
            }

            $inspect = $this->dockerInspect($conn, $vps);
            $stats = $this->dockerStats($conn, $vps);

            foreach ($vps as $v) {
                $vpsId = (int) ($v['id'] ?? 0);
                $clientId = (int) ($v['client_id'] ?? 0);
                $containerId = trim((string) ($v['container_id'] ?? ''));

                if ($vpsId <= 0 || $clientId <= 0 || $containerId === '') {
                    continue;
                }

                $expectedName = 'vps_client_' . $clientId . '_' . $vpsId;

                $i = $inspect[$expectedName] ?? null;
                if ($i === null) {
                    $i = $inspect[$containerId] ?? null;
                }

                $state = is_array($i) ? (string) ($i['state'] ?? '') : '';
                $health = is_array($i) ? (string) ($i['health'] ?? '') : '';
                $labelVps = is_array($i) ? trim((string) ($i['label_vps_id'] ?? '')) : '';
                $labelClient = is_array($i) ? trim((string) ($i['label_client_id'] ?? '')) : '';

                $svcStatus = 'unknown';
                $msg = null;
                $lastErr = null;

                if ($state === 'running') {
                    $svcStatus = 'operational';
                    $msg = 'running';
                    if ($health !== '' && $health !== 'healthy') {
                        $svcStatus = 'degraded';
                        $msg = 'health=' . $health;
                    }

                    if ($labelVps !== '' || $labelClient !== '') {
                        if ($labelVps !== (string) $vpsId || $labelClient !== (string) $clientId) {
                            $svcStatus = 'major_outage';
                            $msg = 'ownership_mismatch';
                            $lastErr = 'Labels do container não conferem com VPS/cliente.';
                        }
                    } else {
                        $svcStatus = 'degraded';
                        $msg = 'missing_labels';
                        $lastErr = 'Container sem labels lrv.vps_id/lrv.client_id.';
                    }
                } elseif ($state === 'exited' || $state === 'dead') {
                    $svcStatus = 'major_outage';
                    $msg = $state;
                    $lastErr = 'Container ' . $state;
                } elseif ($state !== '') {
                    $svcStatus = 'degraded';
                    $msg = $state;
                } else {
                    $svcStatus = 'major_outage';
                    $msg = 'container_not_found';
                    $lastErr = 'Container não encontrado.';
                }

                $svcKey = 'vps:' . $vpsId;
                $ex = $this->repo->obterServicePorKey($svcKey);
                $lastOk = $svcStatus === 'operational' ? $agora : $this->parseDt(($ex['last_ok_at'] ?? null) ?? null);

                $meta = [
                    'container_id' => $containerId,
                    'container_name' => $expectedName,
                    'state' => $state,
                    'health' => $health,
                    'label_vps_id' => $labelVps,
                    'label_client_id' => $labelClient,
                ];

                $m = $stats[$expectedName] ?? null;
                if (is_array($m)) {
                    $meta['cpu_perc'] = $m['cpu_perc'] ?? null;
                    $meta['mem_perc'] = $m['mem_perc'] ?? null;
                    $meta['mem_usage'] = $m['mem_usage'] ?? null;
                }

                $serviceId = $this->repo->upsertService(
                    $svcKey,
                    'VPS #' . $vpsId,
                    null,
                    'client',
                    $clientId,
                    $serverId,
                    $vpsId,
                    $svcStatus,
                    $agora,
                    $lastOk,
                    $lastErr,
                    $meta,
                );

                $this->repo->registrarLog(
                    $serviceId,
                    $svcStatus,
                    $msg,
                    $meta,
                    $agora,
                );
            }
        }
    }

    private function carregarServer(\PDO $pdo, int $serverId): ?array
    {
        try {
            $st = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status, is_online, last_error FROM servers WHERE id = :id LIMIT 1');
            $st->execute([':id' => $serverId]);
            $r = $st->fetch();
            return is_array($r) ? $r : null;
        } catch (\Throwable $e) {
            try {
                $st = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
                $st->execute([':id' => $serverId]);
                $r = $st->fetch();
                return is_array($r) ? $r : null;
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    private function listarVpsDoServer(\PDO $pdo, int $serverId): array
    {
        $stmt = $pdo->prepare('SELECT id, client_id, container_id FROM vps WHERE server_id = :sid AND COALESCE(container_id,\'\') <> \'\'');
        $stmt->execute([':sid' => $serverId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function resolverConexaoNode(array $srv): ?array
    {
        $host = trim((string) ($srv['ip_address'] ?? ''));
        if ($host === '') {
            $host = trim((string) ($srv['hostname'] ?? ''));
        }

        $sshPort = (int) ($srv['ssh_port'] ?? 22);
        $sshUser = trim((string) ($srv['ssh_user'] ?? ''));
        $keyId = trim((string) ($srv['ssh_key_id'] ?? ''));

        if ($host === '' || $sshPort <= 0 || $sshUser === '' || $keyId === '') {
            return null;
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        if ($keyDir === '') {
            return null;
        }

        $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
        if (!is_file($keyPath)) {
            return null;
        }

        return [
            'host' => $host,
            'ssh_port' => $sshPort,
            'ssh_user' => $sshUser,
            'ssh_key_path' => $keyPath,
        ];
    }

    private function dockerInspect(array $conn, array $vps): array
    {
        $ids = [];

        foreach ($vps as $v) {
            if (!is_array($v)) {
                continue;
            }
            $cid = trim((string) ($v['container_id'] ?? ''));
            $vpsId = (int) ($v['id'] ?? 0);
            $clientId = (int) ($v['client_id'] ?? 0);

            if ($cid === '' || $vpsId <= 0 || $clientId <= 0) {
                continue;
            }
            if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $cid) !== 1) {
                continue;
            }

            $ids[] = $cid;
        }

        if (empty($ids)) {
            return [];
        }

        $format = "{{.Id}}|{{.Name}}|{{.State.Status}}|{{if .State.Health}}{{.State.Health.Status}}{{end}}|{{ index .Config.Labels \"lrv.vps_id\" }}|{{ index .Config.Labels \"lrv.client_id\" }}";

        $quoted = [];
        foreach ($ids as $cid) {
            $quoted[] = escapeshellarg($cid);
        }

        $cmd = 'for c in ' . implode(' ', $quoted)
            . '; do docker inspect -f ' . escapeshellarg($format)
            . ' "$c" 2>/dev/null || true; done';

        try {
            $r = $this->ssh->executar(
                (string) $conn['host'],
                (int) $conn['ssh_port'],
                (string) $conn['ssh_user'],
                (string) $conn['ssh_key_path'],
                $cmd,
                30,
            );
        } catch (\Throwable $e) {
            return [];
        }

        if (empty($r['ok'])) {
            return [];
        }

        $out = trim((string) ($r['saida'] ?? ''));
        if ($out === '') {
            return [];
        }

        $map = [];
        foreach (preg_split('/\r?\n/', $out) as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) < 6) {
                continue;
            }

            $id = trim($parts[0]);
            $nameRaw = trim($parts[1]);
            $name = ltrim($nameRaw, '/');

            $map[$id] = [
                'state' => trim($parts[2] ?? ''),
                'health' => trim($parts[3] ?? ''),
                'label_vps_id' => trim($parts[4] ?? ''),
                'label_client_id' => trim($parts[5] ?? ''),
                'name' => $name,
            ];

            if ($name !== '') {
                $map[$name] = $map[$id];
            }
        }

        return $map;
    }

    private function dockerStats(array $conn, array $vps): array
    {
        $ids = [];

        foreach ($vps as $v) {
            if (!is_array($v)) {
                continue;
            }
            $cid = trim((string) ($v['container_id'] ?? ''));
            $vpsId = (int) ($v['id'] ?? 0);
            $clientId = (int) ($v['client_id'] ?? 0);

            if ($cid === '' || $vpsId <= 0 || $clientId <= 0) {
                continue;
            }
            if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $cid) !== 1) {
                continue;
            }

            $ids[] = $cid;
        }

        if (empty($ids)) {
            return [];
        }

        $format = '{{.Name}}|{{.CPUPerc}}|{{.MemPerc}}|{{.MemUsage}}';

        $quoted = [];
        foreach ($ids as $cid) {
            $quoted[] = escapeshellarg($cid);
        }

        $cmd = 'for c in ' . implode(' ', $quoted)
            . '; do docker stats --no-stream --format ' . escapeshellarg($format)
            . ' "$c" 2>/dev/null || true; done';

        try {
            $r = $this->ssh->executar(
                (string) $conn['host'],
                (int) $conn['ssh_port'],
                (string) $conn['ssh_user'],
                (string) $conn['ssh_key_path'],
                $cmd,
                30,
            );
        } catch (\Throwable $e) {
            return [];
        }

        if (empty($r['ok'])) {
            return [];
        }

        $out = trim((string) ($r['saida'] ?? ''));
        if ($out === '') {
            return [];
        }

        $map = [];
        foreach (preg_split('/\r?\n/', $out) as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) < 4) {
                continue;
            }

            $name = trim((string) ($parts[0] ?? ''));
            if ($name === '') {
                continue;
            }

            $map[$name] = [
                'cpu_perc' => trim((string) ($parts[1] ?? '')),
                'mem_perc' => trim((string) ($parts[2] ?? '')),
                'mem_usage' => trim((string) ($parts[3] ?? '')),
            ];
        }

        return $map;
    }

    private function registrarVpsOffline(array $v, int $serverId, \DateTimeImmutable $agora, string $motivo): void
    {
        $vpsId = (int) ($v['id'] ?? 0);
        $clientId = (int) ($v['client_id'] ?? 0);

        if ($vpsId <= 0 || $clientId <= 0) {
            return;
        }

        $svcKey = 'vps:' . $vpsId;
        $ex = $this->repo->obterServicePorKey($svcKey);
        $lastOk = $this->parseDt(($ex['last_ok_at'] ?? null) ?? null);

        $serviceId = $this->repo->upsertService(
            $svcKey,
            'VPS #' . $vpsId,
            null,
            'client',
            $clientId,
            $serverId > 0 ? $serverId : null,
            $vpsId,
            'major_outage',
            $agora,
            $lastOk,
            $motivo,
            null,
        );

        $this->repo->registrarLog($serviceId, 'major_outage', $motivo, null, $agora);
    }

    private function parseDt(mixed $v): ?\DateTimeImmutable
    {
        $s = trim((string) ($v ?? ''));
        if ($s === '') {
            return null;
        }
        try {
            return new \DateTimeImmutable($s);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
