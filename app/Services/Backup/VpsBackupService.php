<?php

declare(strict_types=1);

namespace LRV\App\Services\Backup;

use LRV\App\Services\Provisioning\DockerCli;
use LRV\App\Services\Seguranca\ValidadorInfra;
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

        // Usuário SSH não-root precisa de sudo (mesma regra do NginxVhostService::needsSudo)
        $sudo = $sshUser !== 'root' ? 'sudo -n ' : '';
        $avisos = [];

        // a) Pastas extras configuradas pelo cliente (revalidadas a cada backup)
        $extrasCfg = [];
        try {
            $stE = $pdo->prepare('SELECT backup_extra_paths FROM vps WHERE id = :id LIMIT 1');
            $stE->execute([':id' => $vpsId]);
            $tmp = json_decode((string) ($stE->fetchColumn() ?: ''), true);
            if (is_array($tmp)) {
                $extrasCfg = array_values(array_filter($tmp, 'is_string'));
            }
        } catch (\Throwable) {
            // migration 0101 ainda não aplicada
        }

        $extras = [];
        foreach ($extrasCfg as $p) {
            try {
                $p = ValidadorInfra::exigirCaminhoBackupExtra($p, $clientId, $vpsId);
            } catch (\InvalidArgumentException $e) {
                $avisos[] = 'Pasta ' . $p . ' ignorada: ' . $e->getMessage();
                continue;
            }
            // b) Resolver symlinks no servidor e validar o caminho real
            $r = $this->docker->executar('r=$(' . $sudo . 'readlink -f -- ' . escapeshellarg($p) . ' 2>/dev/null) && ' . $sudo . 'test -d "$r" && printf "%s" "$r"');
            $real = trim((string) ($r['saida'] ?? ''));
            if ($real === '') {
                $avisos[] = 'Pasta ' . $p . ' ignorada: não existe no servidor.';
                continue;
            }
            try {
                $real = ValidadorInfra::exigirCaminhoBackupExtra($real, $clientId, $vpsId);
            } catch (\InvalidArgumentException $e) {
                $avisos[] = 'Pasta ' . $p . ' ignorada: aponta para um local não permitido.';
                continue;
            }
            $extras[$real] = true;
        }
        $extras = array_keys($extras);

        $remoteFile = '/tmp/backup_vps_' . $vpsId . '_' . $backupId . '_' . date('Ymd_His') . '.tar.gz';
        $stage = '/tmp/lrv_bk_' . $backupId . '_' . bin2hex(random_bytes(4));

        try {
            // c) Dump dos bancos do cliente nesta VPS
            $bancos = [];
            try {
                $stD = $pdo->prepare("SELECT d.db_name, d.db_user, d.db_password_enc, d.engine, d.container_id, d.db_port FROM client_databases d WHERE d.client_id = :c AND d.vps_id = :v AND d.status = 'active'");
                $stD->execute([':c' => $clientId, ':v' => $vpsId]);
                $bancos = $stD->fetchAll() ?: [];
            } catch (\Throwable) {
                $bancos = [];
            }

            $dumps = [];
            if ($bancos !== []) {
                $this->docker->executar('mkdir -m 700 ' . escapeshellarg($stage) . ' && mkdir -m 700 ' . escapeshellarg($stage . '/_lrv_db'));
                foreach ($bancos as $i => $db) {
                    $dbName = (string) ($db['db_name'] ?? '');
                    if (!ValidadorInfra::identificadorMysqlValido($dbName)) {
                        $avisos[] = 'Banco ' . $dbName . ': nome inválido, ignorado.';
                        continue;
                    }
                    $dbUser = (string) ($db['db_user'] ?? '');
                    $dbPass = \LRV\App\Services\Infra\SshCrypto::decifrar((string) ($db['db_password_enc'] ?? ''));
                    $esc = static fn (string $s): string => str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '', ''], $s);
                    $cnfTxt = "[client]\nuser=\"" . $esc($dbUser) . "\"\npassword=\"" . $esc($dbPass) . "\"\n";
                    $cnf = $stage . '/my_' . (int) $i . '.cnf';
                    $sql = $stage . '/_lrv_db/' . $dbName . '.sql';
                    $err = $stage . '/err';

                    // Senha só trafega em base64 dentro do arquivo (nunca na linha de comando do mysqldump)
                    $this->docker->executar('umask 077 && printf %s ' . escapeshellarg(base64_encode($cnfTxt)) . ' | base64 -d > ' . escapeshellarg($cnf) . ' && chmod 600 ' . escapeshellarg($cnf));

                    $opts = ' --single-transaction --routines --triggers ';
                    if ((string) ($db['engine'] ?? 'native') === 'docker') {
                        $cid = (string) ($db['container_id'] ?? '');
                        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,127}$/', $cid)) {
                            $avisos[] = 'Banco ' . $dbName . ': container inválido, ignorado.';
                            $this->docker->executar('rm -f ' . escapeshellarg($cnf));
                            continue;
                        }
                        $c = escapeshellarg($cid);
                        $cmdDump = $sudo . 'docker exec -i ' . $c . " sh -c 'cat > /tmp/lrv.cnf && chmod 600 /tmp/lrv.cnf' < " . escapeshellarg($cnf)
                            . ' && ' . $sudo . 'docker exec ' . $c . ' mysqldump --defaults-extra-file=/tmp/lrv.cnf' . $opts . escapeshellarg($dbName)
                            . ' > ' . escapeshellarg($sql) . ' 2> ' . escapeshellarg($err)
                            . '; rc=$?; ' . $sudo . 'docker exec ' . $c . ' rm -f /tmp/lrv.cnf; echo "rc=$rc"';
                    } else {
                        $porta = (int) ($db['db_port'] ?? 0);
                        $cmdDump = 'mysqldump --defaults-extra-file=' . escapeshellarg($cnf) . $opts
                            . '-h 127.0.0.1 ' . ($porta > 0 ? '-P ' . $porta . ' ' : '') . escapeshellarg($dbName)
                            . ' > ' . escapeshellarg($sql) . ' 2> ' . escapeshellarg($err) . '; echo "rc=$?"';
                    }
                    $rD = $this->docker->executar($cmdDump);
                    $this->docker->executar('rm -f ' . escapeshellarg($cnf));

                    $ok = str_contains((string) ($rD['saida'] ?? ''), 'rc=0');
                    if ($ok) {
                        $rS = $this->docker->executar('test -s ' . escapeshellarg($sql) . ' && echo CHEIO || echo VAZIO');
                        $ok = str_contains((string) ($rS['saida'] ?? ''), 'CHEIO');
                    }
                    if (!$ok) {
                        $rE = $this->docker->executar('cat ' . escapeshellarg($err) . ' 2>/dev/null; rm -f ' . escapeshellarg($sql));
                        $msg = 'Banco ' . $dbName . ': falha no dump: ' . substr(trim((string) ($rE['saida'] ?? '')), 0, 300);
                        $avisos[] = $msg;
                        $log($msg);
                        continue;
                    }
                    $dumps[] = $dbName;
                    $log('Dump do banco ' . $dbName . ' concluído.');
                }
            }

            // d) Montar o tar com o que existir
            $rDir = $this->docker->executar('test -d ' . escapeshellarg($dirCliente) . ' && echo SIM || echo NAO');
            $temDirCliente = str_contains((string) ($rDir['saida'] ?? ''), 'SIM');

            if (!$temDirCliente && $dumps === [] && $extras === []) {
                throw new \RuntimeException('Nada para incluir no backup.');
            }

            $log('Criando tar.gz no node...');
            $cmd = $sudo . 'tar -czf ' . escapeshellarg($remoteFile);
            if ($temDirCliente) {
                $cmd .= ' -C ' . escapeshellarg($volumeBase) . ' ' . escapeshellarg('client_' . $clientId);
            }
            if ($dumps !== []) {
                $cmd .= ' -C ' . escapeshellarg($stage) . ' _lrv_db';
            }
            foreach ($extras as $p) {
                $cmd .= ' -C / ' . escapeshellarg(ltrim($p, '/'));
            }
            $cmd .= '; echo "rc=$?"';

            $rTar = $this->docker->executar($cmd);
            $saidaTar = trim((string) ($rTar['saida'] ?? ''));
            $log($saidaTar);
            if (!preg_match('/rc=[01]\s*$/', $saidaTar)) {
                // rc=1 do GNU tar = arquivos mudaram durante a leitura (backup ainda é válido)
                throw new \RuntimeException('Falha ao criar o arquivo de backup.');
            }
            if ($sudo !== '') {
                $this->docker->executar($sudo . 'chown ' . escapeshellarg($sshUser) . ' ' . escapeshellarg($remoteFile));
            }
        } finally {
            try {
                $this->docker->executar('rm -rf ' . escapeshellarg($stage));
            } catch (\Throwable) {
            }
        }

        $log('Backup concluído. Arquivo salvo no node: ' . $remoteFile);

        $size = 0;
        try {
            $rSize = $this->docker->executar('stat -c%s ' . escapeshellarg($remoteFile) . ' 2>/dev/null || echo 0');
            $size = (int)trim((string)($rSize['saida'] ?? '0'));
        } catch (\Throwable) {}

        foreach ($avisos as $a) {
            $log('Aviso: ' . $a);
        }

        $params = [
            ':p' => 'remote:' . $serverId . ':' . $remoteFile,
            ':s' => $size,
            ':c' => date('Y-m-d H:i:s'),
            ':id' => $backupId,
        ];
        // e) Manifesto e avisos (fallback se a migration 0101 ainda não rodou)
        try {
            $up = $pdo->prepare("UPDATE backups SET status='completed', file_path=:p, file_size=:s, completed_at=:c, error=NULL, manifest=:m, warnings=:w WHERE id=:id");
            $up->execute($params + [
                ':m' => json_encode(['client_dir' => $temDirCliente, 'extra_paths' => $extras, 'databases' => $dumps], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ':w' => $avisos === [] ? null : implode("\n", $avisos),
            ]);
        } catch (\Throwable) {
            $up = $pdo->prepare("UPDATE backups SET status='completed', file_path=:p, file_size=:s, completed_at=:c, error=NULL WHERE id=:id");
            $up->execute($params);
        }

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
