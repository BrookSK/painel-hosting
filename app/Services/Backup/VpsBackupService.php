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
        $dirCliente = $volumeBase . '/client_' . $clientId;

        $remoteFile = '/tmp/backup_vps_' . $vpsId . '_' . $backupId . '_' . date('Ymd_His') . '.tar.gz';

        $log('Criando tar.gz no node...');
        $cmd = 'test -d ' . escapeshellarg($dirCliente)
            . ' && tar -czf ' . escapeshellarg($remoteFile)
            . ' -C ' . escapeshellarg($volumeBase)
            . ' ' . escapeshellarg('client_' . $clientId);

        $rTar = $this->docker->executar($cmd);
        $log(trim((string) ($rTar['saida'] ?? '')));

        $log('Baixando backup via scp...');
        $localDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($localDir)) {
            @mkdir($localDir, 0775, true);
        }

        $localFile = $localDir . DIRECTORY_SEPARATOR . basename($remoteFile);

        $scpOut = $this->executarScp($host, $sshPort, $sshUser, $authType, $keyPath ?? '', $senha ?? '', $remoteFile, $localFile);
        $scpOutTrim = trim($scpOut);
        if ($scpOutTrim !== '') {
            $log($scpOutTrim);
        }

        if (!is_file($localFile)) {
            throw new \RuntimeException('Falha ao baixar backup via scp.');
        }

        $log('Removendo arquivo temporário no node...');
        $rRm = $this->docker->executar('rm -f ' . escapeshellarg($remoteFile));
        $log(trim((string) ($rRm['saida'] ?? '')));

        $size = (int) (@filesize($localFile) ?: 0);

        $up = $pdo->prepare("UPDATE backups SET status='completed', file_path=:p, file_size=:s, completed_at=:c, error=NULL WHERE id=:id");
        $up->execute([
            ':p' => $localFile,
            ':s' => $size,
            ':c' => date('Y-m-d H:i:s'),
            ':id' => $backupId,
        ]);

        $log('Backup concluído.');
    }

    private function executarScp(string $host, int $porta, string $usuario, string $authType, string $keyPath, string $senha, string $remoteFile, string $localFile): string
    {
        // Para auth por senha, baixar via SSH cat em vez de scp
        if ($authType === 'password' && $senha !== '') {
            $exec = new \LRV\App\Services\Infra\SshExecutor();
            $r = $exec->executarComSenha($host, $porta, $usuario, $senha, 'base64 ' . escapeshellarg($remoteFile), 300);
            $b64 = trim((string) ($r['saida'] ?? ''));
            if (empty($r['ok']) || $b64 === '') {
                return 'Falha ao baixar via SSH: ' . $b64;
            }
            $decoded = base64_decode($b64, true);
            if ($decoded === false) {
                return 'Falha ao decodificar base64 do backup.';
            }
            file_put_contents($localFile, $decoded);
            return '';
        }

        // Auth por chave: usar scp
        if (!function_exists('shell_exec') && !function_exists('exec')) {
            throw new \RuntimeException('Nenhum método de execução disponível (exec/shell_exec).');
        }

        $knownHosts = '/dev/null';
        if (PHP_OS_FAMILY === 'Windows') {
            $knownHosts = 'NUL';
        }

        $src = $usuario . '@' . $host . ':' . $remoteFile;

        $args = [];
        $args[] = 'scp';
        $args[] = '-i ' . escapeshellarg($keyPath);
        $args[] = '-P ' . (int) $porta;
        $args[] = '-o BatchMode=yes';
        $args[] = '-o ConnectTimeout=10';
        $args[] = '-o StrictHostKeyChecking=no';
        $args[] = '-o UserKnownHostsFile=' . $knownHosts;
        $args[] = escapeshellarg($src);
        $args[] = escapeshellarg($localFile);

        $cmd = implode(' ', $args);

        if (function_exists('exec')) {
            $linhas = [];
            $codigo = 0;
            @exec($cmd . ' 2>&1', $linhas, $codigo);
            return trim(implode("\n", $linhas));
        }

        return (string) @shell_exec($cmd . ' 2>&1');
    }
}
