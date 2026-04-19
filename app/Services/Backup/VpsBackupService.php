<?php

declare(strict_types=1);

namespace LRV\App\Services\Backup;

use LRV\App\Services\Provisioning\DockerCli;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Settings;

final class VpsBackupService
{
    public function __construct(
        private readonly DockerCli $docker,
    ) {
    }

    public function criar(int $backupId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT b.id, b.vps_id, b.status, v.client_id, v.server_id FROM backups b INNER JOIN vps v ON v.id = b.vps_id WHERE b.id = :id LIMIT 1');
        $stmt->execute([':id' => $backupId]);
        $bk = $stmt->fetch();

        if (!is_array($bk)) {
            throw new \RuntimeException('Backup não encontrado.');
        }

        $vpsId = (int) ($bk['vps_id'] ?? 0);
        $clientId = (int) ($bk['client_id'] ?? 0);
        $serverId = (int) ($bk['server_id'] ?? 0);

        if ($vpsId <= 0 || $clientId <= 0) {
            throw new \RuntimeException('Backup inválido.');
        }

        if ($serverId <= 0) {
            throw new \RuntimeException('VPS sem node associado.');
        }

        try {
            $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type, status, is_online FROM servers WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $serverId]);
            $srv = $stmt->fetch();
        } catch (\Throwable $e) {
            try {
                $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type, status FROM servers WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $serverId]);
                $srv = $stmt->fetch();
            } catch (\Throwable $e2) {
                $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $serverId]);
                $srv = $stmt->fetch();
            }
        }

        if (!is_array($srv)) {
            throw new \RuntimeException('Node não encontrado.');
        }

        if ((string) ($srv['status'] ?? '') !== 'active') {
            throw new \RuntimeException('Node não está ativo.');
        }

        if (array_key_exists('is_online', $srv) && (int) ($srv['is_online'] ?? 0) !== 1) {
            throw new \RuntimeException('Node está offline.');
        }

        $host = trim((string) ($srv['ip_address'] ?? ''));
        if ($host === '') {
            $host = trim((string) ($srv['hostname'] ?? ''));
        }
        $sshPort = (int) ($srv['ssh_port'] ?? 22);
        $sshUser = trim((string) ($srv['ssh_user'] ?? ''));
        $authType = (string) ($srv['ssh_auth_type'] ?? 'key');
        $keyPath = null;
        $senha = null;

        if ($host === '' || $sshPort <= 0 || $sshUser === '') {
            throw new \RuntimeException('Node sem dados de SSH completos.');
        }

        if ($authType === 'password') {
            $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string) ($srv['ssh_password'] ?? ''));
            if ($senha === '') {
                throw new \RuntimeException('Senha SSH do node não configurada.');
            }
            $this->docker->definirRemotoComSenha($host, $sshPort, $sshUser, $senha);
        } else {
            $keyId = trim((string) ($srv['ssh_key_id'] ?? ''));
            if ($keyId === '') {
                throw new \RuntimeException('Chave SSH do node não configurada.');
            }

            $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
            if ($keyDir === '') {
                throw new \RuntimeException('Diretório base das chaves SSH não configurado.');
            }

            $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
            if (!is_file((string)$keyPath)) {
                throw new \RuntimeException('Arquivo de chave não encontrado: ' . $keyId);
            }

            $this->docker->definirRemoto($host, $sshPort, $sshUser, (string)$keyPath);
        }

        $log('Testando conexão SSH...');
        $t = $this->docker->executar('echo ok');
        $outT = trim((string) ($t['saida'] ?? ''));
        if (!str_contains($outT, 'ok')) {
            throw new \RuntimeException('Falha ao validar conexão SSH.');
        }

        $volumeBase = (string) Settings::obter('infra.volume_base', '/vps');
        $volumeBase = rtrim($volumeBase, '/');

        // Usar volume_base_path do servidor se configurado
        try {
            $srvPathStmt = $pdo->prepare('SELECT s.volume_base_path FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :vid LIMIT 1');
            $srvPathStmt->execute([':vid' => $vpsId]);
            $srvPathRow = $srvPathStmt->fetch();
            $srvPath = trim((string)($srvPathRow['volume_base_path'] ?? ''));
            if ($srvPath !== '') $volumeBase = rtrim($srvPath, '/');
        } catch (\Throwable) {}

        $dirCliente = $volumeBase . '/client_' . $clientId;

        $remoteFile = '/tmp/backup_vps_' . $vpsId . '_' . $backupId . '_' . date('Ymd_His') . '.tar.gz';

        $log('Criando tar.gz no node...');
        $cmd = 'test -d ' . escapeshellarg($dirCliente)
            . ' && tar -czf ' . escapeshellarg($remoteFile)
            . ' -C ' . escapeshellarg($volumeBase)
            . ' ' . escapeshellarg('client_' . $clientId);

        $rTar = $this->docker->executar($cmd);
        $log(trim((string) ($rTar['saida'] ?? '')));

        $log('Backup concluído. Arquivo salvo no node: ' . $remoteFile);

        $size = 0;
        try {
            $rSize = $this->docker->executar('stat -c%s ' . escapeshellarg($remoteFile) . ' 2>/dev/null || echo 0');
            $size = (int)trim((string)($rSize['saida'] ?? '0'));
        } catch (\Throwable) {}

        $up = $pdo->prepare("UPDATE backups SET status='completed', file_path=:p, file_size=:s, completed_at=:c, error=NULL WHERE id=:id");
        $up->execute([
            ':p' => 'remote:' . $serverId . ':' . $remoteFile,
            ':s' => $size,
            ':c' => date('Y-m-d H:i:s'),
            ':id' => $backupId,
        ]);

        $log('Backup concluído. Tamanho: ' . $size . ' bytes.');
    }

    /**
     * Faz download de um backup remoto via SSH stream (cat + proc_open).
     * Retorna o path local temporário ou null se falhar.
     */
    public function baixarRemoto(string $filePath): ?string
    {
        // file_path formato: remote:SERVER_ID:/path/to/file.tar.gz
        if (!str_starts_with($filePath, 'remote:')) {
            // Arquivo local (legado)
            return is_file($filePath) ? $filePath : null;
        }

        $parts = explode(':', $filePath, 3);
        $serverId = (int)($parts[1] ?? 0);
        $remotePath = (string)($parts[2] ?? '');
        if ($serverId <= 0 || $remotePath === '') return null;

        $pdo = BancoDeDados::pdo();
        $srv = $pdo->prepare('SELECT ip_address, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type FROM servers WHERE id = :id');
        $srv->execute([':id' => $serverId]);
        $s = $srv->fetch();
        if (!is_array($s)) return null;

        $host = trim((string)($s['ip_address'] ?? ''));
        $porta = (int)($s['ssh_port'] ?? 22);
        $usuario = trim((string)($s['ssh_user'] ?? ''));
        $authType = (string)($s['ssh_auth_type'] ?? 'key');

        if ($authType === 'password') {
            $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($s['ssh_password'] ?? ''));
            $this->docker->definirRemotoComSenha($host, $porta, $usuario, $senha);
        } else {
            $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
            $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($s['ssh_key_id'] ?? '');
            $this->docker->definirRemoto($host, $porta, $usuario, $keyPath);
        }

        // Verificar se o arquivo existe no node
        try {
            $check = $this->docker->executar('test -f ' . escapeshellarg($remotePath) . ' && echo EXISTS || echo MISSING');
            if (!str_contains((string)($check['saida'] ?? ''), 'EXISTS')) return null;
        } catch (\Throwable) { return null; }

        // Baixar via base64 em chunks (funciona em servidor compartilhado)
        $localDir = dirname(__DIR__, 3) . '/storage/backups';
        if (!is_dir($localDir)) @mkdir($localDir, 0775, true);
        $localFile = $localDir . '/download_' . time() . '_' . mt_rand(1000, 9999) . '.tar.gz';

        try {
            // Dividir em chunks de 700KB base64 (~500KB binário)
            $r = $this->docker->executar('wc -c < ' . escapeshellarg($remotePath));
            $totalBytes = (int)trim((string)($r['saida'] ?? '0'));
            if ($totalBytes <= 0) return null;

            $chunkBin = 524288; // 512KB por chunk
            $offset = 0;
            $fh = fopen($localFile, 'wb');
            if (!$fh) return null;

            while ($offset < $totalBytes) {
                $cmd = 'dd if=' . escapeshellarg($remotePath) . ' bs=1 skip=' . $offset . ' count=' . $chunkBin . ' 2>/dev/null | base64';
                $r = $this->docker->executar($cmd);
                $b64 = trim((string)($r['saida'] ?? ''));
                if ($b64 === '') break;
                $decoded = base64_decode($b64, true);
                if ($decoded === false) break;
                fwrite($fh, $decoded);
                $offset += $chunkBin;
            }
            fclose($fh);

            if (!is_file($localFile) || filesize($localFile) < 100) {
                @unlink($localFile);
                return null;
            }

            return $localFile;
        } catch (\Throwable) {
            @unlink($localFile);
            return null;
        }
    }
}
