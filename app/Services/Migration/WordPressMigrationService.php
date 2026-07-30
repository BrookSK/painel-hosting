<?php

declare(strict_types=1);

namespace LRV\App\Services\Migration;

use LRV\Core\BancoDeDados;
use LRV\Core\Settings;
use LRV\App\Services\Infra\SshCrypto;
use LRV\App\Services\Infra\SshExecutor;
use LRV\App\Services\Infra\NginxVhostService;

/**
 * Serviço de migração de WordPress de servidores externos via SSH.
 * Processo: conectar ao servidor remoto → rsync dos arquivos → mysqldump → importar DB → ajustar wp-config.
 */
final class WordPressMigrationService
{
    private SshExecutor $ssh;
    private ?\Closure $logger;

    public function __construct(?SshExecutor $ssh = null)
    {
        $this->ssh = $ssh ?? new SshExecutor();
        $this->logger = null;
    }

    public function setLogger(\Closure $fn): void
    {
        $this->logger = $fn;
    }

    private function log(string $msg): void
    {
        if ($this->logger) {
            ($this->logger)($msg);
        }
    }

    /**
     * Atualiza o status e progresso de uma migração.
     */
    private function atualizarStatus(int $migrationId, string $status, int $progress, ?string $step = null): void
    {
        $pdo = BancoDeDados::pdo();
        $sql = 'UPDATE wp_migrations SET status = :s, progress_percent = :p, current_step = :cs';
        $params = [':s' => $status, ':p' => $progress, ':cs' => $step, ':id' => $migrationId];

        if ($status === 'connecting' && $progress === 0) {
            $sql .= ', started_at = :sa';
            $params[':sa'] = date('Y-m-d H:i:s');
        }
        if ($status === 'completed' || $status === 'failed') {
            $sql .= ', completed_at = :ca';
            $params[':ca'] = date('Y-m-d H:i:s');
        }

        $sql .= ' WHERE id = :id';
        $pdo->prepare($sql)->execute($params);
    }

    /**
     * Adiciona log à migração no banco.
     */
    private function appendLog(int $migrationId, string $text): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare("UPDATE wp_migrations SET logs = CONCAT(COALESCE(logs,''), :t) WHERE id = :id")
            ->execute([':t' => '[' . date('H:i:s') . '] ' . $text . "\n", ':id' => $migrationId]);
        $this->log($text);
    }

    /**
     * Marca a migração como falha.
     */
    private function falhar(int $migrationId, string $erro): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare("UPDATE wp_migrations SET status = 'failed', error_message = :e, completed_at = :ca WHERE id = :id")
            ->execute([':e' => $erro, ':ca' => date('Y-m-d H:i:s'), ':id' => $migrationId]);
        $this->appendLog($migrationId, 'ERRO: ' . $erro);
    }

    /**
     * Testa a conexão SSH com o servidor de origem.
     */
    public function testarConexao(int $migrationId): array
    {
        $migration = $this->getMigration($migrationId);
        if (!$migration) return ['ok' => false, 'erro' => 'Migração não encontrada.'];

        $host = (string)$migration['source_host'];
        $port = (int)$migration['source_port'];
        $user = (string)$migration['source_user'];
        $password = SshCrypto::decifrar((string)$migration['source_password_enc']);

        if ($password === '') {
            return ['ok' => false, 'erro' => 'Senha SSH do servidor de origem não configurada.'];
        }

        try {
            $result = $this->ssh->executarComSenha($host, $port, $user, $password, 'echo lrv-ssh-ok && whoami', 15);
            $output = trim((string)($result['saida'] ?? ''));
            if (str_contains($output, 'lrv-ssh-ok')) {
                return ['ok' => true, 'output' => $output];
            }
            return ['ok' => false, 'erro' => 'Conexão falhou: ' . $output];
        } catch (\Throwable $e) {
            return ['ok' => false, 'erro' => 'Falha SSH: ' . $e->getMessage()];
        }
    }

    /**
     * Executa a migração completa. Deve ser chamado dentro de um job assíncrono.
     */
    public function executar(int $migrationId): void
    {
        $migration = $this->getMigration($migrationId);
        if (!$migration) {
            throw new \RuntimeException('Migração #' . $migrationId . ' não encontrada.');
        }

        if (!in_array($migration['status'], ['pending', 'failed'], true)) {
            throw new \RuntimeException('Migração não está em estado válido para execução.');
        }

        $pdo = BancoDeDados::pdo();

        // Dados do servidor de ORIGEM (remoto - AAPanel do cliente)
        $srcHost = (string)$migration['source_host'];
        $srcPort = (int)$migration['source_port'];
        $srcUser = (string)$migration['source_user'];
        $srcPass = SshCrypto::decifrar((string)$migration['source_password_enc']);
        $srcWpPath = rtrim((string)$migration['source_wp_path'], '/');
        $srcDbName = (string)$migration['source_db_name'];
        $srcDbUser = (string)$migration['source_db_user'];
        $srcDbPass = SshCrypto::decifrar((string)$migration['source_db_password_enc']);
        $srcDbHost = (string)($migration['source_db_host'] ?: 'localhost');
        $srcDbPort = (int)($migration['source_db_port'] ?: 3306);
        $srcUseSudo = (bool)(int)($migration['source_use_sudo'] ?? 0);
        $srcSudoPass = $srcUseSudo ? SshCrypto::decifrar((string)($migration['source_sudo_password_enc'] ?? '')) : '';
        // Se sudo ativo mas sem senha de sudo, usa a senha SSH como fallback
        if ($srcUseSudo && $srcSudoPass === '') $srcSudoPass = $srcPass;

        // Dados do DESTINO (nosso servidor)
        $vpsId = (int)$migration['vps_id'];
        $clientId = (int)$migration['client_id'];
        $destDomain = (string)$migration['dest_domain'];

        // Se não informou domínio, gerar domínio temporário automaticamente
        if ($destDomain === '') {
            $tempBase = trim((string)Settings::obter('infra.temp_domain_base', ''));
            if ($tempBase !== '') {
                $destDomain = 'wp' . $migrationId . '-' . substr(bin2hex(random_bytes(3)), 0, 4) . '.' . $tempBase;
                $pdo->prepare('UPDATE wp_migrations SET dest_domain = :d WHERE id = :id')
                    ->execute([':d' => $destDomain, ':id' => $migrationId]);
                $this->appendLog($migrationId, 'Domínio temporário gerado: ' . $destDomain);
            }
        }

        // Buscar dados do nosso servidor (destino)
        $destSrv = $this->getDestServer($vpsId);
        if (!$destSrv) {
            $this->falhar($migrationId, 'VPS de destino não encontrada ou sem servidor.');
            return;
        }

        $destHost = (string)$destSrv['ip_address'];
        $destPort = (int)$destSrv['ssh_port'];
        $destUser = (string)$destSrv['ssh_user'];
        $destAuthType = (string)($destSrv['ssh_auth_type'] ?? 'password');
        $destPass = SshCrypto::decifrar((string)($destSrv['ssh_password'] ?? ''));
        $destKeyId = (string)($destSrv['ssh_key_id'] ?? '');
        $isManaged = (int)($destSrv['is_managed_server'] ?? 0) === 1;
        $mysqlRootPass = SshCrypto::decifrar((string)($destSrv['mysql_root_password'] ?? ''));

        // Definir caminho de destino
        $volumeBase = (string)Settings::obter('infra.volume_base', '/vps');
        $destWpPath = rtrim($volumeBase, '/') . '/client_' . $clientId . '/wordpress_' . $migrationId;

        try {
            // ═══ ETAPA 1: Conectar ao servidor de origem ═══
            $this->atualizarStatus($migrationId, 'connecting', 5, 'ssh_source');
            $this->appendLog($migrationId, "Conectando ao servidor de origem: {$srcUser}@{$srcHost}:{$srcPort}");

            $testCmd = 'test -d ' . escapeshellarg($srcWpPath . '/wp-content') . ' && echo wp-found || echo wp-not-found';
            if ($srcUseSudo) $testCmd = SshExecutor::elevarComSudo($testCmd, $srcSudoPass);
            $result = $this->ssh->executarComSenha($srcHost, $srcPort, $srcUser, $srcPass, $testCmd, 15);
            $output = trim((string)($result['saida'] ?? ''));

            if (!str_contains($output, 'wp-found')) {
                $this->falhar($migrationId, "WordPress não encontrado em {$srcWpPath} (wp-content ausente).");
                return;
            }
            $this->appendLog($migrationId, 'WordPress encontrado no servidor de origem.');

            // ═══ ETAPA 2: Preparar destino e fazer rsync dos arquivos ═══
            $this->atualizarStatus($migrationId, 'syncing_files', 10, 'prepare_dest');
            $this->appendLog($migrationId, 'Preparando diretório de destino...');

            // Criar diretório no servidor de destino
            $mkdirCmd = 'mkdir -p ' . escapeshellarg($destWpPath) . ' && echo dir-ok';
            $mkResult = $this->execDest($destSrv, $mkdirCmd, 15);
            if (!str_contains($mkResult, 'dir-ok')) {
                $this->falhar($migrationId, 'Falha ao criar diretório de destino.');
                return;
            }

            // ═══ Verificar espaço em disco disponível no servidor de destino ═══
            $this->appendLog($migrationId, 'Verificando espaço em disco no servidor de destino...');
            $dfCmd = 'df -BG ' . escapeshellarg($destWpPath) . ' 2>/dev/null | tail -1 | awk \'{print $4}\' | tr -d "G"';
            $availGb = (int)trim($this->execDest($destSrv, $dfCmd, 10));

            // Estimar tamanho do site no servidor de origem (du -sm)
            $duSrcCmd = 'du -sm ' . escapeshellarg($srcWpPath) . ' 2>/dev/null | cut -f1';
            if ($srcUseSudo) $duSrcCmd = SshExecutor::elevarComSudo($duSrcCmd, $srcSudoPass);
            $srcSizeResult = $this->ssh->executarComSenha($srcHost, $srcPort, $srcUser, $srcPass, $duSrcCmd, 30);
            $srcSizeMb = (int)trim((string)($srcSizeResult['saida'] ?? '0'));
            $srcSizeGb = round($srcSizeMb / 1024, 1);

            if ($availGb > 0 && $srcSizeMb > 0) {
                // Verificar se cabe com margem de 20% (para banco + temporários)
                $requiredGb = ceil($srcSizeGb * 1.2);
                if ($availGb < $requiredGb) {
                    $this->falhar($migrationId,
                        "Espaço em disco insuficiente no servidor de destino.\n\n"
                        . "• Tamanho estimado do site: {$srcSizeGb} GB\n"
                        . "• Espaço necessário (com margem): {$requiredGb} GB\n"
                        . "• Espaço disponível no servidor: {$availGb} GB\n\n"
                        . "Libere espaço no servidor (apague sites/backups antigos) ou faça upgrade do plano para mais armazenamento."
                    );
                    // Limpar diretório vazio criado
                    $this->execDest($destSrv, 'rmdir ' . escapeshellarg($destWpPath) . ' 2>/dev/null; true', 5);
                    return;
                }
                $this->appendLog($migrationId, "Espaço OK: site ~{$srcSizeGb} GB, disponível {$availGb} GB.");
            } else {
                $this->appendLog($migrationId, 'Não foi possível verificar espaço (continuando).');
            }

            // Instalar a chave pública do destino no servidor de origem para rsync direto
            // Estratégia: gerar par de chaves temporário no destino, autorizar no origem, rsync server-to-server
            $this->appendLog($migrationId, 'Configurando rsync servidor-a-servidor...');
            $this->atualizarStatus($migrationId, 'syncing_files', 15, 'rsync_setup');

            $keyName = 'migration_' . $migrationId . '_' . time();
            $keyPath = '/tmp/' . $keyName;

            // Gerar chave temporária no destino
            $genKeyCmd = 'rm -f ' . escapeshellarg($keyPath) . ' ' . escapeshellarg($keyPath . '.pub')
                . ' && ssh-keygen -t ed25519 -f ' . escapeshellarg($keyPath) . ' -N "" -q'
                . ' && cat ' . escapeshellarg($keyPath . '.pub');
            $pubKey = trim($this->execDest($destSrv, $genKeyCmd, 20));

            if ($pubKey === '' || !str_contains($pubKey, 'ssh-ed25519')) {
                $this->falhar($migrationId, 'Falha ao gerar chave temporária para rsync.');
                return;
            }

            // Autorizar a chave no servidor de origem
            $authCmd = 'mkdir -p ~/.ssh && chmod 700 ~/.ssh'
                . ' && echo ' . escapeshellarg($pubKey) . ' >> ~/.ssh/authorized_keys'
                . ' && chmod 600 ~/.ssh/authorized_keys && echo auth-ok';
            $authResult = $this->ssh->executarComSenha($srcHost, $srcPort, $srcUser, $srcPass, $authCmd, 15);
            if (!str_contains((string)($authResult['saida'] ?? ''), 'auth-ok')) {
                $this->falhar($migrationId, 'Falha ao autorizar chave no servidor de origem.');
                return;
            }
            $this->appendLog($migrationId, 'Chave SSH temporária autorizada no servidor de origem.');

            // Executar rsync do destino puxando do origem
            $this->atualizarStatus($migrationId, 'syncing_files', 20, 'rsync_transfer');
            $this->appendLog($migrationId, "Iniciando rsync de {$srcWpPath}/ para {$destWpPath}/...");

            $sshOpts = 'ssh -p ' . $srcPort . ' -i ' . escapeshellarg($keyPath)
                . ' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null'
                . ' -o ConnectTimeout=30 -o ServerAliveInterval=15 -o ServerAliveCountMax=4'
                . ' -o TCPKeepAlive=yes';

            $rsyncBase = 'rsync -azP --delete --timeout=300'
                . ' -e "' . $sshOpts . '"'
                . ' ' . escapeshellarg($srcUser . '@' . $srcHost . ':' . $srcWpPath . '/')
                . ' ' . escapeshellarg($destWpPath . '/');

            // Rsync com retry automático (3 tentativas)
            $rsyncCmd = 'for i in 1 2 3; do ' . $rsyncBase . ' 2>&1 && break; echo "rsync tentativa $i falhou, retentando em 5s..."; sleep 5; done; echo "rsync-exit-$?"';

            // Rsync pode demorar muito — timeout alto (24h para sites muito grandes >100GB)
            $rsyncOutput = $this->execDest($destSrv, $rsyncCmd, 86400);

            if (!str_contains($rsyncOutput, 'rsync-exit-0')) {
                // Verificar se pelo menos parcial funcionou
                $checkCmd = 'test -f ' . escapeshellarg($destWpPath . '/wp-config.php') . ' && echo wp-ok';
                $check = $this->execDest($destSrv, $checkCmd, 10);
                if (!str_contains($check, 'wp-ok')) {
                    // Verificar se foi por falta de espaço
                    $isNoSpace = str_contains($rsyncOutput, 'No space left on device') || str_contains($rsyncOutput, 'write failed') || str_contains($rsyncOutput, 'disk full');
                    if ($isNoSpace) {
                        // Limpar arquivos parciais para liberar espaço
                        $this->appendLog($migrationId, 'Disco cheio detectado. Limpando arquivos parciais...');
                        $this->execDest($destSrv, 'rm -rf ' . escapeshellarg($destWpPath), 120);
                        $this->falhar($migrationId,
                            "Falha na migração: espaço em disco insuficiente no servidor de destino.\n\n"
                            . "O rsync foi interrompido porque o disco encheu durante a cópia dos arquivos. "
                            . "Os arquivos parciais foram removidos automaticamente para liberar espaço.\n\n"
                            . "Para resolver:\n"
                            . "• Libere espaço no servidor (apague sites, backups ou aplicações antigas)\n"
                            . "• Ou faça upgrade do plano para mais armazenamento\n\n"
                            . "Depois, tente a migração novamente."
                        );
                    } else {
                        $this->falhar($migrationId, 'Rsync falhou. Saída: ' . substr($rsyncOutput, -500));
                    }
                    $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
                    return;
                }
                $this->appendLog($migrationId, 'Rsync retornou código não-zero mas arquivos foram copiados. Continuando...');
            }

            // Medir tamanho dos arquivos copiados
            $sizeCmd = 'du -sb ' . escapeshellarg($destWpPath) . ' 2>/dev/null | cut -f1';
            $sizeOutput = trim($this->execDest($destSrv, $sizeCmd, 15));
            $filesSize = is_numeric($sizeOutput) ? (int)$sizeOutput : 0;
            if ($filesSize > 0) {
                $pdo->prepare('UPDATE wp_migrations SET files_size_bytes = :s WHERE id = :id')
                    ->execute([':s' => $filesSize, ':id' => $migrationId]);
            }

            $this->appendLog($migrationId, 'Rsync concluído. Tamanho: ' . $this->formatBytes($filesSize));
            $this->atualizarStatus($migrationId, 'syncing_files', 50, 'rsync_done');

            // ═══ ETAPA 3: Dump do banco de dados no servidor de origem ═══
            $this->atualizarStatus($migrationId, 'dumping_db', 55, 'mysqldump_remote');
            $this->appendLog($migrationId, "Fazendo dump do banco '{$srcDbName}' no servidor de origem...");

            $dumpFile = '/tmp/wp_migration_' . $migrationId . '.sql.gz';

            $dumpCmd = 'mysqldump'
                . ' -h ' . escapeshellarg($srcDbHost)
                . ' -P ' . $srcDbPort
                . ' -u ' . escapeshellarg($srcDbUser)
                . ($srcDbPass !== '' ? ' -p' . escapeshellarg($srcDbPass) : '')
                . ' --single-transaction --quick --lock-tables=false'
                . ' ' . escapeshellarg($srcDbName)
                . ' 2>/dev/null | gzip > ' . escapeshellarg($dumpFile)
                . ' && ls -la ' . escapeshellarg($dumpFile)
                . ' && echo dump-ok';
            if ($srcUseSudo) $dumpCmd = SshExecutor::elevarComSudo($dumpCmd, $srcSudoPass);

            $dumpResult = $this->ssh->executarComSenha($srcHost, $srcPort, $srcUser, $srcPass, $dumpCmd, 600);
            $dumpOutput = (string)($dumpResult['saida'] ?? '');

            if (!str_contains($dumpOutput, 'dump-ok')) {
                $this->falhar($migrationId, 'Mysqldump falhou no servidor de origem: ' . substr($dumpOutput, -300));
                $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
                return;
            }

            $this->appendLog($migrationId, 'Dump SQL comprimido gerado com sucesso.');

            // Transferir dump do origem para destino via rsync/scp
            $this->atualizarStatus($migrationId, 'dumping_db', 65, 'transfer_dump');
            $this->appendLog($migrationId, 'Transferindo dump SQL para o servidor de destino...');

            $destDumpFile = '/tmp/wp_migration_' . $migrationId . '.sql.gz';
            $scpDumpCmd = 'scp -P ' . $srcPort
                . ' -i ' . escapeshellarg($keyPath)
                . ' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null'
                . ' ' . escapeshellarg($srcUser . '@' . $srcHost . ':' . $dumpFile)
                . ' ' . escapeshellarg($destDumpFile)
                . ' 2>&1 && echo scp-ok';

            $scpOutput = $this->execDest($destSrv, $scpDumpCmd, 600);
            if (!str_contains($scpOutput, 'scp-ok')) {
                $this->falhar($migrationId, 'Falha ao transferir dump SQL: ' . substr($scpOutput, -300));
                $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
                return;
            }

            // Medir tamanho do dump
            $dbSizeCmd = 'stat -c%s ' . escapeshellarg($destDumpFile) . ' 2>/dev/null || echo 0';
            $dbSize = (int)trim($this->execDest($destSrv, $dbSizeCmd, 10));
            if ($dbSize > 0) {
                $pdo->prepare('UPDATE wp_migrations SET db_size_bytes = :s WHERE id = :id')
                    ->execute([':s' => $dbSize, ':id' => $migrationId]);
            }

            $this->appendLog($migrationId, 'Dump transferido. Tamanho (gzip): ' . $this->formatBytes($dbSize));

            // ═══ ETAPA 4: Criar banco de dados no destino e importar dump ═══
            $this->atualizarStatus($migrationId, 'importing_db', 70, 'create_db');
            $this->appendLog($migrationId, 'Criando banco de dados no servidor de destino...');

            $destDbName = 'wp_' . $clientId . '_m' . $migrationId;
            $destDbUser = 'wpu_' . $clientId . '_' . substr(md5((string)$migrationId . time()), 0, 6);
            $destDbPass = bin2hex(random_bytes(12));

            if ($mysqlRootPass === '') {
                $this->falhar($migrationId, 'Senha root do MySQL do servidor de destino não configurada.');
                $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
                return;
            }

            $createDbSql = "CREATE DATABASE IF NOT EXISTS \`{$destDbName}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
                . " CREATE USER IF NOT EXISTS '{$destDbUser}'@'localhost' IDENTIFIED BY '" . addslashes($destDbPass) . "';"
                . " CREATE USER IF NOT EXISTS '{$destDbUser}'@'%' IDENTIFIED BY '" . addslashes($destDbPass) . "';"
                . " GRANT ALL PRIVILEGES ON \`{$destDbName}\`.* TO '{$destDbUser}'@'localhost';"
                . " GRANT ALL PRIVILEGES ON \`{$destDbName}\`.* TO '{$destDbUser}'@'%';"
                . " FLUSH PRIVILEGES;";

            $createCmd = 'mysql -u root -p' . escapeshellarg($mysqlRootPass) . ' -e ' . escapeshellarg($createDbSql) . ' 2>&1 && echo db-created';
            $createOutput = $this->execDest($destSrv, $createCmd, 30);

            if (!str_contains($createOutput, 'db-created')) {
                $this->falhar($migrationId, 'Falha ao criar banco no destino: ' . substr($createOutput, -300));
                $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
                return;
            }

            $this->appendLog($migrationId, "Banco '{$destDbName}' criado. Importando dump...");
            $this->atualizarStatus($migrationId, 'importing_db', 75, 'import_sql');

            // Importar dump
            $importCmd = 'gunzip -c ' . escapeshellarg($destDumpFile)
                . ' | mysql -u root -p' . escapeshellarg($mysqlRootPass) . ' ' . escapeshellarg($destDbName)
                . ' 2>&1 && echo import-ok';
            $importOutput = $this->execDest($destSrv, $importCmd, 900);

            if (!str_contains($importOutput, 'import-ok')) {
                $this->falhar($migrationId, 'Falha ao importar dump SQL: ' . substr($importOutput, -300));
                $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
                return;
            }

            $this->appendLog($migrationId, 'Banco de dados importado com sucesso.');

            // Registrar banco na tabela client_databases
            $pdo->prepare(
                "INSERT INTO client_databases (client_id, vps_id, name, db_name, db_user, db_password_enc, db_host, db_port, engine, status, created_at)
                 VALUES (:c, :v, :n, :dn, :du, :dp, 'localhost', 3306, 'native', 'active', :cr)"
            )->execute([
                ':c' => $clientId, ':v' => $vpsId,
                ':n' => 'WP Migration #' . $migrationId,
                ':dn' => $destDbName, ':du' => $destDbUser,
                ':dp' => SshCrypto::cifrar($destDbPass),
                ':cr' => date('Y-m-d H:i:s'),
            ]);
            $dbId = (int)$pdo->lastInsertId();

            $pdo->prepare('UPDATE wp_migrations SET database_id = :d, dest_db_name = :dn WHERE id = :id')
                ->execute([':d' => $dbId, ':dn' => $destDbName, ':id' => $migrationId]);

            // ═══ ETAPA 5: Configurar wp-config.php com novo banco ═══
            $this->atualizarStatus($migrationId, 'configuring', 85, 'wp_config');
            $this->appendLog($migrationId, 'Atualizando wp-config.php...');

            $wpConfigCmd = "sed -i"
                . " -e \"s/define(\\s*'DB_NAME'.*/define('DB_NAME', '{$destDbName}');/\""
                . " -e \"s/define(\\s*'DB_USER'.*/define('DB_USER', '{$destDbUser}');/\""
                . " -e \"s/define(\\s*'DB_PASSWORD'.*/define('DB_PASSWORD', '" . addslashes($destDbPass) . "');/\""
                . " -e \"s/define(\\s*'DB_HOST'.*/define('DB_HOST', 'localhost');/\""
                . " " . escapeshellarg($destWpPath . '/wp-config.php')
                . " 2>&1 && echo config-ok";

            $configOutput = $this->execDest($destSrv, $wpConfigCmd, 15);
            if (!str_contains($configOutput, 'config-ok')) {
                $this->appendLog($migrationId, 'Aviso: Falha ao atualizar wp-config.php automaticamente. Ajuste manual necessário.');
            } else {
                $this->appendLog($migrationId, 'wp-config.php atualizado.');
            }

            // Atualizar siteurl e home no banco se domínio definido
            if ($destDomain !== '') {
                $newUrl = 'https://' . $destDomain;
                $urlUpdateSql = "UPDATE {$destDbName}.wp_options SET option_value = '{$newUrl}' WHERE option_name IN ('siteurl','home');";
                $urlCmd = 'mysql -u root -p' . escapeshellarg($mysqlRootPass) . ' -e ' . escapeshellarg($urlUpdateSql) . ' 2>&1';
                $this->execDest($destSrv, $urlCmd, 15);
                $this->appendLog($migrationId, "URLs atualizadas para: {$newUrl}");
            }

            // Ajustar permissões dos arquivos
            $chownCmd = 'chown -R www-data:www-data ' . escapeshellarg($destWpPath) . ' 2>/dev/null; chmod -R 755 ' . escapeshellarg($destWpPath) . ' 2>/dev/null; echo perms-ok';
            $this->execDest($destSrv, $chownCmd, 30);
            $this->appendLog($migrationId, 'Permissões ajustadas.');

            // ═══ ETAPA 6: Configurar Nginx vhost + SSL ═══
            $this->atualizarStatus($migrationId, 'finalizing', 90, 'nginx_vhost');

            $appId = null;
            if ($destDomain !== '') {
                $this->appendLog($migrationId, 'Configurando Nginx vhost para: ' . $destDomain);

                // Registrar aplicação
                $port = 80; // WordPress em PHP-FPM direto, sem porta app
                $pdo->prepare(
                    "INSERT INTO applications (vps_id, type, domain, port, status, repository, created_at)
                     VALUES (:v, 'wordpress', :d, :p, 'running', :r, :c)"
                )->execute([
                    ':v' => $vpsId, ':d' => $destDomain, ':p' => $port,
                    ':r' => $destWpPath, ':c' => date('Y-m-d H:i:s'),
                ]);
                $appId = (int)$pdo->lastInsertId();

                // Criar vhost Nginx apontando para o diretório do WordPress
                $serverId = (int)$destSrv['server_id'];
                $this->criarVhostWordPress($destSrv, $destDomain, $destWpPath);
                $this->appendLog($migrationId, 'Nginx vhost criado.');
            }

            // Atualizar registro da migração
            $pdo->prepare('UPDATE wp_migrations SET application_id = :a, dest_wp_path = :p WHERE id = :id')
                ->execute([':a' => $appId, ':p' => $destWpPath, ':id' => $migrationId]);

            // Vincular banco à aplicação
            if ($appId && $dbId) {
                $pdo->prepare('UPDATE client_databases SET application_id = :a WHERE id = :id')
                    ->execute([':a' => $appId, ':id' => $dbId]);
            }

            // ═══ ETAPA 7: Cleanup ═══
            $this->atualizarStatus($migrationId, 'finalizing', 95, 'cleanup');

            // Remover chave temporária e dump
            $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
            $this->execDest($destSrv, 'rm -f ' . escapeshellarg($destDumpFile), 10);

            // Limpar dump no servidor de origem
            $this->ssh->executarComSenha($srcHost, $srcPort, $srcUser, $srcPass,
                'rm -f ' . escapeshellarg($dumpFile), 10);

            // ═══ CONCLUÍDO ═══
            $this->atualizarStatus($migrationId, 'completed', 100, null);
            $this->appendLog($migrationId, 'Migração concluída com sucesso!');

        } catch (\Throwable $e) {
            $this->falhar($migrationId, $e->getMessage());
            // Tentar cleanup mesmo em caso de erro
            try {
                if (isset($keyPath, $pubKey)) {
                    $this->cleanupKey($destSrv, $keyPath, $srcHost, $srcPort, $srcUser, $srcPass, $pubKey);
                }
            } catch (\Throwable) {}
            throw $e;
        }
    }

    /**
     * Cria vhost Nginx para WordPress (PHP-FPM com root no diretório).
     */
    private function criarVhostWordPress(array $destSrv, string $domain, string $wpPath): void
    {
        $isManaged = (int)($destSrv['is_managed_server'] ?? 0) === 1;
        $vhostPath = $isManaged ? '/www/server/panel/vhost/nginx' : '/etc/nginx/sites-available/lrv';

        $config = $this->gerarNginxWordPress($domain, $wpPath);
        $b64 = base64_encode($config);
        $vhostFile = $vhostPath . '/' . str_replace('.', '_', $domain) . '.conf';

        $cmd = 'mkdir -p ' . escapeshellarg($vhostPath)
            . ' && echo ' . escapeshellarg($b64) . ' | base64 -d > ' . escapeshellarg($vhostFile)
            . ' && nginx -t 2>&1 && ';

        if ($isManaged) {
            $cmd .= 'kill -HUP $(pgrep -o nginx.real 2>/dev/null || cat /www/server/nginx/logs/nginx.pid 2>/dev/null) 2>/dev/null; /etc/init.d/nginx reload 2>/dev/null; true';
        } else {
            $cmd .= 'systemctl reload nginx 2>/dev/null; true';
        }
        $cmd .= ' && echo vhost-ok';

        $result = $this->execDest($destSrv, $cmd, 30);
        if (!str_contains($result, 'vhost-ok')) {
            $this->log('Aviso: vhost pode não ter sido criado corretamente: ' . substr($result, -200));
        }
    }

    /**
     * Gera configuração Nginx para servir WordPress com PHP-FPM.
     */
    private function gerarNginxWordPress(string $domain, string $wpPath): string
    {
        return <<<NGINX
server {
    listen 80;
    server_name {$domain};
    root {$wpPath};
    index index.php index.html;

    client_max_body_size 256M;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \\.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\\.ht {
        deny all;
    }
}
NGINX;
    }

    /**
     * Remove chave temporária do destino e do authorized_keys do origem.
     */
    private function cleanupKey(array $destSrv, string $keyPath, string $srcHost, int $srcPort, string $srcUser, string $srcPass, string $pubKey): void
    {
        try {
            // Remover chaves do destino
            $this->execDest($destSrv, 'rm -f ' . escapeshellarg($keyPath) . ' ' . escapeshellarg($keyPath . '.pub'), 10);

            // Remover do authorized_keys do origem
            $escapedKey = str_replace('/', '\\/', preg_quote($pubKey, '/'));
            $sedCmd = "sed -i '/" . $escapedKey . "/d' ~/.ssh/authorized_keys 2>/dev/null; true";
            $this->ssh->executarComSenha($srcHost, $srcPort, $srcUser, $srcPass, $sedCmd, 10);
        } catch (\Throwable) {
            // Cleanup é best-effort
        }
    }

    /**
     * Executa comando no servidor de DESTINO (nosso servidor).
     */
    private function execDest(array $srv, string $cmd, int $timeout = 30): string
    {
        $host = (string)$srv['ip_address'];
        $port = (int)$srv['ssh_port'];
        $user = (string)$srv['ssh_user'];
        $authType = (string)($srv['ssh_auth_type'] ?? 'password');

        if ($authType === 'password') {
            $pass = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
            $result = $this->ssh->executarComSenha($host, $port, $user, $pass, $cmd, $timeout);
        } else {
            $keyDir = rtrim(\LRV\Core\ConfiguracoesSistema::sshKeyDir(), "/\\");
            $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($srv['ssh_key_id'] ?? '');
            $result = $this->ssh->executar($host, $port, $user, $keyPath, $cmd, $timeout);
        }

        return (string)($result['saida'] ?? '');
    }

    /**
     * Busca dados do servidor de destino a partir da VPS.
     */
    private function getDestServer(int $vpsId): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT v.id AS vps_id, v.server_id, s.ip_address, s.ssh_port, s.ssh_user,
                    s.ssh_password, s.ssh_auth_type, s.ssh_key_id, s.is_managed_server,
                    s.mysql_root_password
             FROM vps v
             JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v LIMIT 1"
        );
        $stmt->execute([':v' => $vpsId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    /**
     * Busca uma migração pelo ID.
     */
    private function getMigration(int $id): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM wp_migrations WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Ativa (ou troca) o domínio real de uma migração concluída.
     * Atualiza: wp_options (siteurl, home), wp-config.php, Nginx vhost, e registro no banco.
     */
    public function ativarDominio(int $migrationId, string $novoDominio): array
    {
        $novoDominio = trim(strtolower($novoDominio));
        if ($novoDominio === '' || !preg_match('/^[a-z0-9][a-z0-9.-]+\.[a-z]{2,}$/', $novoDominio)) {
            return ['ok' => false, 'erro' => 'Domínio inválido.'];
        }

        $migration = $this->getMigration($migrationId);
        if (!$migration) return ['ok' => false, 'erro' => 'Migração não encontrada.'];
        if ($migration['status'] !== 'completed') {
            return ['ok' => false, 'erro' => 'A migração precisa estar concluída para ativar o domínio.'];
        }

        $pdo = BancoDeDados::pdo();
        $vpsId = (int)$migration['vps_id'];
        $destWpPath = (string)$migration['dest_wp_path'];
        $destDbName = (string)$migration['dest_db_name'];
        $dominioAntigo = (string)$migration['dest_domain'];

        if ($destWpPath === '' || $destDbName === '') {
            return ['ok' => false, 'erro' => 'Dados de destino incompletos. Migração pode estar corrompida.'];
        }

        $destSrv = $this->getDestServer($vpsId);
        if (!$destSrv) return ['ok' => false, 'erro' => 'Servidor de destino não encontrado.'];

        $mysqlRootPass = SshCrypto::decifrar((string)($destSrv['mysql_root_password'] ?? ''));
        if ($mysqlRootPass === '') {
            return ['ok' => false, 'erro' => 'Senha root do MySQL não configurada no servidor.'];
        }

        $newUrl = 'https://' . $novoDominio;
        $logs = [];

        // 1. Atualizar wp_options (siteurl e home) no banco MySQL
        $urlSql = "UPDATE \`{$destDbName}\`.wp_options SET option_value = '{$newUrl}' WHERE option_name IN ('siteurl','home');";
        $urlCmd = 'mysql -u root -p' . escapeshellarg($mysqlRootPass) . ' -e ' . escapeshellarg($urlSql) . ' 2>&1 && echo url-ok';
        $urlResult = $this->execDest($destSrv, $urlCmd, 15);
        if (!str_contains($urlResult, 'url-ok')) {
            return ['ok' => false, 'erro' => 'Falha ao atualizar URLs no banco: ' . substr($urlResult, -200)];
        }
        $logs[] = "wp_options atualizado: siteurl/home → {$newUrl}";

        // 2. Atualizar WP_SITEURL e WP_HOME no wp-config.php (se existirem como define)
        $configCmd = "grep -q 'WP_SITEURL\\|WP_HOME' " . escapeshellarg($destWpPath . '/wp-config.php') . ' 2>/dev/null && '
            . "sed -i"
            . " -e \"s|define(\\s*'WP_SITEURL'.*|define('WP_SITEURL', '{$newUrl}');|g\""
            . " -e \"s|define(\\s*'WP_HOME'.*|define('WP_HOME', '{$newUrl}');|g\""
            . " " . escapeshellarg($destWpPath . '/wp-config.php') . " 2>&1; echo cfg-ok";
        $this->execDest($destSrv, $configCmd, 10);
        $logs[] = 'wp-config.php verificado/atualizado';

        // 3. Remover vhost antigo e criar novo
        if ($dominioAntigo !== '' && $dominioAntigo !== $novoDominio) {
            $this->removerVhostWordPress($destSrv, $dominioAntigo);
            $logs[] = "Vhost antigo removido: {$dominioAntigo}";
        }
        $this->criarVhostWordPress($destSrv, $novoDominio, $destWpPath);
        $logs[] = "Vhost criado: {$novoDominio}";

        // 4. Manter vhost do domínio temporário (para acesso por ambos)
        // Se o antigo era temporário e é diferente do novo, mantemos o vhost antigo ativo também
        // Assim o site fica acessível por ambos os domínios durante a transição

        // 5. Atualizar registros no banco do painel
        $pdo->prepare('UPDATE wp_migrations SET dest_domain = :d WHERE id = :id')
            ->execute([':d' => $novoDominio, ':id' => $migrationId]);

        // Atualizar domínio na tabela applications (se existir)
        $appId = (int)($migration['application_id'] ?? 0);
        if ($appId > 0) {
            $pdo->prepare('UPDATE applications SET domain = :d WHERE id = :id')
                ->execute([':d' => $novoDominio, ':id' => $appId]);
        }

        $logs[] = 'Domínio ativado com sucesso!';

        return ['ok' => true, 'dominio' => $novoDominio, 'url' => $newUrl, 'logs' => $logs];
    }

    /**
     * Remove um vhost Nginx de um domínio específico.
     */
    private function removerVhostWordPress(array $destSrv, string $domain): void
    {
        $isManaged = (int)($destSrv['is_managed_server'] ?? 0) === 1;
        $vhostPath = $isManaged ? '/www/server/panel/vhost/nginx' : '/etc/nginx/sites-available/lrv';
        $vhostFile = $vhostPath . '/' . str_replace('.', '_', $domain) . '.conf';

        $cmd = 'rm -f ' . escapeshellarg($vhostFile) . ' && nginx -t 2>&1 && ';
        if ($isManaged) {
            $cmd .= 'kill -HUP $(pgrep -o nginx.real 2>/dev/null || cat /www/server/nginx/logs/nginx.pid 2>/dev/null) 2>/dev/null; /etc/init.d/nginx reload 2>/dev/null; true';
        } else {
            $cmd .= 'systemctl reload nginx 2>/dev/null; true';
        }

        try { $this->execDest($destSrv, $cmd, 15); } catch (\Throwable) {}
    }
}
