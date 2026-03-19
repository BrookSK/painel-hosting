<?php

declare(strict_types=1);

namespace LRV\App\Services\Setup;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Settings;

final class InicializacaoService
{
    public function status(): array
    {
        $itens = [];

        $itens['shell_exec'] = [
            'ok' => function_exists('shell_exec'),
            'detalhe' => function_exists('shell_exec') ? 'OK' : 'shell_exec indisponível',
        ];

        $pdoOk = false;
        $erroDb = '';
        try {
            BancoDeDados::pdo();
            $pdoOk = true;
        } catch (\Throwable $e) {
            $pdoOk = false;
            $erroDb = $e->getMessage();
        }

        $itens['banco'] = [
            'ok' => $pdoOk,
            'detalhe' => $pdoOk ? 'OK' : $erroDb,
        ];

        $itens['tabela_migrations'] = [
            'ok' => $pdoOk ? $this->tabelaExiste('migrations') : false,
            'detalhe' => $pdoOk ? ($this->tabelaExiste('migrations') ? 'OK' : 'Ausente') : 'Banco indisponível',
        ];

        $pendentes = [];
        if ($pdoOk && $this->tabelaExiste('migrations')) {
            $pendentes = $this->listarMigrationsPendentes();
        }

        $itens['migrations_pendentes'] = [
            'ok' => $pdoOk && $this->tabelaExiste('migrations') && count($pendentes) === 0,
            'detalhe' => !$pdoOk
                ? 'Banco indisponível'
                : (!$this->tabelaExiste('migrations') ? 'Tabela migrations ausente' : (string) count($pendentes) . ' pendente(s)'),
        ];

        $base = $this->caminhoProjeto();
        $dirBackups = $base . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';

        $itens['storage_backups'] = [
            'ok' => is_dir($dirBackups) && is_writable($dirBackups),
            'detalhe' => is_dir($dirBackups)
                ? (is_writable($dirBackups) ? 'OK' : 'Sem permissão de escrita')
                : 'Pasta inexistente',
        ];

        $itens['ssh_key_dir'] = [
            'ok' => trim(ConfiguracoesSistema::sshKeyDir()) !== '',
            'detalhe' => trim(ConfiguracoesSistema::sshKeyDir()) !== '' ? 'OK' : 'Não configurado',
        ];

        $itens['monitoring_token'] = [
            'ok' => trim(ConfiguracoesSistema::monitoringToken()) !== '',
            'detalhe' => trim(ConfiguracoesSistema::monitoringToken()) !== '' ? 'OK' : 'Não configurado',
        ];

        $workerToken = trim((string) Settings::obter('worker.http_token', ''));
        $itens['worker_http_token'] = [
            'ok' => $workerToken !== '',
            'detalhe' => $workerToken !== '' ? 'OK' : 'Não configurado',
        ];

        $sshOk = false;
        $scpOk = false;
        $detSsh = 'shell_exec indisponível';
        $detScp = 'shell_exec indisponível';

        if (function_exists('shell_exec')) {
            $out = (string) @shell_exec('ssh -V 2>&1');
            $sshOk = trim($out) !== '';
            $detSsh = $sshOk ? trim($out) : 'ssh não encontrado';

            $out2 = (string) @shell_exec('scp -V 2>&1');
            $scpOk = trim($out2) !== '';
            $detScp = $scpOk ? trim($out2) : 'scp não encontrado';
        }

        $itens['ssh'] = [
            'ok' => $sshOk,
            'detalhe' => $detSsh,
        ];

        $itens['scp'] = [
            'ok' => $scpOk,
            'detalhe' => $detScp,
        ];

        return [
            'itens' => $itens,
            'pendentes' => $pendentes,
        ];
    }

    public function aplicarSchema(callable $log): void
    {
        $pdo = BancoDeDados::pdo();

        $arquivo = $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema.sql';
        if (!is_file($arquivo)) {
            throw new \RuntimeException('schema.sql não encontrado.');
        }

        $sql = (string) file_get_contents($arquivo);
        $sql = trim($sql);
        if ($sql === '') {
            throw new \RuntimeException('schema.sql vazio.');
        }

        $log('Aplicando schema.sql...');

        foreach ($this->separarSqlEmComandos($sql) as $cmd) {
            $cmd = trim($cmd);
            if ($cmd === '') {
                continue;
            }
            $pdo->exec($cmd);
        }

        $log('Schema aplicado.');
    }

    public function aplicarMigrations(callable $log): int
    {
        $pdo = BancoDeDados::pdo();

        if (!$this->tabelaExiste('migrations')) {
            throw new \RuntimeException('Tabela migrations não existe. Execute o schema primeiro.');
        }

        $diretorio = $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
        if (!is_dir($diretorio)) {
            throw new \RuntimeException('Pasta de migrations não encontrada.');
        }

        $arquivos = glob($diretorio . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        sort($arquivos);

        $stmt = $pdo->query('SELECT file_name FROM migrations');
        $executadas = [];
        foreach ($stmt->fetchAll() as $l) {
            $executadas[(string) ($l['file_name'] ?? '')] = true;
        }

        $novas = 0;

        foreach ($arquivos as $arquivo) {
            $nome = basename($arquivo);
            if (isset($executadas[$nome])) {
                continue;
            }

            $sql = (string) file_get_contents($arquivo);
            $sql = trim($sql);
            if ($sql === '') {
                continue;
            }

            $log('Executando migration: ' . $nome);

            $pdo->beginTransaction();
            try {
                foreach ($this->separarSqlEmComandos($sql) as $cmd) {
                    $cmd = trim($cmd);
                    if ($cmd === '') {
                        continue;
                    }
                    $pdo->exec($cmd);
                }

                $ins = $pdo->prepare('INSERT INTO migrations (file_name, executed_at) VALUES (:f, :e)');
                $ins->execute([
                    ':f' => $nome,
                    ':e' => date('Y-m-d H:i:s'),
                ]);

                $pdo->commit();
                $novas++;
            } catch (\Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        $log($novas === 0 ? 'Nenhuma migration pendente.' : ('Migrations executadas: ' . $novas));

        return $novas;
    }

    public function criarDiretorios(callable $log): void
    {
        $base = $this->caminhoProjeto();

        $storage = $base . DIRECTORY_SEPARATOR . 'storage';
        $backups = $storage . DIRECTORY_SEPARATOR . 'backups';

        if (!is_dir($storage)) {
            @mkdir($storage, 0775, true);
        }
        if (!is_dir($backups)) {
            @mkdir($backups, 0775, true);
        }

        if (!is_dir($backups)) {
            throw new \RuntimeException('Não foi possível criar storage/backups.');
        }

        if (!is_writable($backups)) {
            $log('Aviso: storage/backups existe, mas não está com permissão de escrita para o processo atual.');
        }

        $log('Diretórios verificados/criados.');
    }

    public function garantirDefaultsESecrets(callable $log): void
    {
        $monitoring = trim((string) Settings::obter('monitoring.token', ''));
        if ($monitoring === '') {
            Settings::definir('monitoring.token', bin2hex(random_bytes(16)));
            $log('monitoring.token gerado.');
        } else {
            $log('monitoring.token já configurado.');
        }

        $workerToken = trim((string) Settings::obter('worker.http_token', ''));
        if ($workerToken === '') {
            Settings::definir('worker.http_token', bin2hex(random_bytes(16)));
            $log('worker.http_token gerado.');
        } else {
            $log('worker.http_token já configurado.');
        }

        $rede = trim((string) Settings::obter('infra.docker_rede', ''));
        if ($rede === '') {
            Settings::definir('infra.docker_rede', 'lrvcloud_network');
            $log('infra.docker_rede definido para lrvcloud_network.');
        }

        $volume = trim((string) Settings::obter('infra.volume_base', ''));
        if ($volume === '') {
            Settings::definir('infra.volume_base', '/vps');
            $log('infra.volume_base definido para /vps.');
        }

        $img = trim((string) Settings::obter('infra.imagem_base', ''));
        if ($img === '') {
            Settings::definir('infra.imagem_base', 'debian:12-slim');
            $log('infra.imagem_base definido para debian:12-slim.');
        }

        $nodeMax = Settings::obter('infra.node_max_util_percent', '');
        if ($nodeMax === '') {
            Settings::definir('infra.node_max_util_percent', 85);
            $log('infra.node_max_util_percent definido para 85.');
        }

        $wsPort = Settings::obter('terminal.ws_internal_port', '');
        if ($wsPort === '') {
            Settings::definir('terminal.ws_internal_port', 8081);
            $log('terminal.ws_internal_port definido para 8081.');
        }

        $tokenTtl = Settings::obter('terminal.token_ttl_seconds', '');
        if ($tokenTtl === '') {
            Settings::definir('terminal.token_ttl_seconds', 60);
            $log('terminal.token_ttl_seconds definido para 60.');
        }

        $idleTtl = Settings::obter('terminal.idle_timeout_seconds', '');
        if ($idleTtl === '') {
            Settings::definir('terminal.idle_timeout_seconds', 900);
            $log('terminal.idle_timeout_seconds definido para 900.');
        }

        $safeMode = Settings::obter('terminal.safe_mode', '');
        if ($safeMode === '') {
            Settings::definir('terminal.safe_mode', 1);
            $log('terminal.safe_mode definido para 1.');
        }
    }

    private function listarMigrationsPendentes(): array
    {
        $pdo = BancoDeDados::pdo();

        $diretorio = $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
        if (!is_dir($diretorio)) {
            return [];
        }

        $arquivos = glob($diretorio . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        sort($arquivos);

        $stmt = $pdo->query('SELECT file_name FROM migrations');
        $executadas = [];
        foreach ($stmt->fetchAll() as $l) {
            $executadas[(string) ($l['file_name'] ?? '')] = true;
        }

        $pendentes = [];
        foreach ($arquivos as $arquivo) {
            $nome = basename($arquivo);
            if (!isset($executadas[$nome])) {
                $pendentes[] = $nome;
            }
        }

        return $pendentes;
    }

    private function tabelaExiste(string $nome): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t');
        $stmt->execute([':t' => $nome]);
        $r = $stmt->fetch();
        return ((int) ($r['total'] ?? 0)) > 0;
    }

    private function separarSqlEmComandos(string $sql): array
    {
        $linhas = preg_split('/\R/', $sql) ?: [];
        $comandos = [];
        $buffer = '';

        foreach ($linhas as $linha) {
            $trim = trim($linha);
            if ($trim === '' || str_starts_with($trim, '--')) {
                continue;
            }

            $buffer .= $linha . "\n";

            if (str_ends_with(rtrim($linha), ';')) {
                $comandos[] = $buffer;
                $buffer = '';
            }
        }

        if (trim($buffer) !== '') {
            $comandos[] = $buffer;
        }

        return $comandos;
    }

    private function caminhoProjeto(): string
    {
        return dirname(__DIR__, 3);
    }
}
