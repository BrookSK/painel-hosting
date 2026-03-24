<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

/**
 * Executa comandos remotos via SSH.
 * Suporta autenticação por chave privada ou por senha (via sshpass).
 */
final class SshExecutor
{
    // -------------------------------------------------------------------------
    // Execução principal
    // -------------------------------------------------------------------------

    /**
     * Executa um comando remoto via SSH com chave privada.
     */
    public function executar(
        string $host,
        int $porta,
        string $usuario,
        string $caminhoChavePrivada,
        string $comandoRemoto,
        int $timeoutSegundos = 30
    ): array {
        $host    = trim($host);
        $usuario = trim($usuario);
        $chave   = trim($caminhoChavePrivada);
        $cmd     = trim($comandoRemoto);

        if ($host === '' || $porta <= 0 || $porta > 65535 || $usuario === '' || $chave === '' || $cmd === '') {
            throw new \InvalidArgumentException('Parâmetros inválidos para execução SSH.');
        }
        if (!is_file($chave)) {
            throw new \RuntimeException('Arquivo de chave SSH não encontrado: ' . $chave);
        }

        $sshCmd = $this->montarComandoChave($host, $porta, $usuario, $chave, $cmd);
        return $this->executarProcesso($sshCmd, $timeoutSegundos);
    }

    /**
     * Executa um comando remoto via SSH com senha.
     * Prioridade: ext-ssh2 → proc_open com pty → sshpass (fallback).
     */
    public function executarComSenha(
        string $host,
        int $porta,
        string $usuario,
        string $senha,
        string $comandoRemoto,
        int $timeoutSegundos = 30
    ): array {
        $host    = trim($host);
        $usuario = trim($usuario);
        $cmd     = trim($comandoRemoto);

        if ($host === '' || $porta <= 0 || $porta > 65535 || $usuario === '' || $senha === '' || $cmd === '') {
            throw new \InvalidArgumentException('Parâmetros inválidos para execução SSH com senha.');
        }

        // 1) ext-ssh2
        if (function_exists('ssh2_connect')) {
            return $this->executarViaSsh2($host, $porta, $usuario, $senha, $cmd, $timeoutSegundos);
        }

        // 2) proc_open com pseudo-terminal (pty)
        if (function_exists('proc_open')) {
            return $this->executarViaPty($host, $porta, $usuario, $senha, $cmd, $timeoutSegundos);
        }

        throw new \RuntimeException('Nenhum método disponível para SSH com senha. Instale a extensão ssh2 ou habilite proc_open.');
    }

    /**
     * Eleva um comando com sudo -S (lê senha do stdin via echo).
     * Se $sudoPassword for vazio, tenta sudo sem senha (NOPASSWD).
     * O comando resultante é seguro para passar ao SSH como argumento único.
     */
    public static function elevarComSudo(string $cmd, string $sudoPassword = ''): string
    {
        if ($sudoPassword !== '') {
            // echo 'SENHA' | sudo -S -p '' bash -c 'COMANDO'
            return 'echo ' . escapeshellarg($sudoPassword) . ' | sudo -S -p \'\' bash -c ' . escapeshellarg($cmd);
        }
        // sudo sem senha (NOPASSWD configurado)
        return 'sudo -n bash -c ' . escapeshellarg($cmd);
    }

    // -------------------------------------------------------------------------
    // SCP
    // -------------------------------------------------------------------------

    public function scpUpload(
        string $host,
        int $porta,
        string $usuario,
        string $caminhoChavePrivada,
        string $arquivoLocalTmp,
        string $caminhoRemoto,
        int $timeoutSegundos = 60
    ): array {
        if (!is_file($caminhoChavePrivada)) {
            throw new \RuntimeException('Arquivo de chave SSH não encontrado.');
        }
        if (!is_file($arquivoLocalTmp)) {
            throw new \RuntimeException('Arquivo local não encontrado.');
        }

        $caminhoRemoto = trim($caminhoRemoto);
        if ($caminhoRemoto === '' || str_contains($caminhoRemoto, '..') || preg_match('/[;&|`$]/', $caminhoRemoto)) {
            throw new \InvalidArgumentException('Caminho remoto inválido.');
        }

        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $destino    = $usuario . '@' . $host . ':' . $caminhoRemoto;

        $cmd = implode(' ', [
            'scp',
            '-i ' . escapeshellarg($caminhoChavePrivada),
            '-P ' . (int)$porta,
            '-o BatchMode=yes',
            '-o ConnectTimeout=8',
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=' . $knownHosts,
            escapeshellarg($arquivoLocalTmp),
            escapeshellarg($destino),
        ]);

        return $this->executarProcesso($cmd, $timeoutSegundos);
    }

    public function scpDownload(
        string $host,
        int $porta,
        string $usuario,
        string $caminhoChavePrivada,
        string $caminhoRemoto,
        int $timeoutSegundos = 60
    ): array {
        if (!is_file($caminhoChavePrivada)) {
            throw new \RuntimeException('Arquivo de chave SSH não encontrado.');
        }

        $caminhoRemoto = trim($caminhoRemoto);
        if ($caminhoRemoto === '' || str_contains($caminhoRemoto, '..') || preg_match('/[;&|`$]/', $caminhoRemoto)) {
            throw new \InvalidArgumentException('Caminho remoto inválido.');
        }

        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $localTmp   = sys_get_temp_dir() . '/lrv_dl_' . bin2hex(random_bytes(8));
        $origem     = $usuario . '@' . $host . ':' . $caminhoRemoto;

        $cmd = implode(' ', [
            'scp',
            '-i ' . escapeshellarg($caminhoChavePrivada),
            '-P ' . (int)$porta,
            '-o BatchMode=yes',
            '-o ConnectTimeout=8',
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=' . $knownHosts,
            escapeshellarg($origem),
            escapeshellarg($localTmp),
        ]);

        $result = $this->executarProcesso($cmd, $timeoutSegundos);
        $result['local_path'] = $localTmp;
        return $result;
    }

    // -------------------------------------------------------------------------
    // Internos
    // -------------------------------------------------------------------------

    private function montarComandoChave(string $host, int $porta, string $usuario, string $chave, string $cmd): string
    {
        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        return implode(' ', [
            'ssh',
            '-i ' . escapeshellarg($chave),
            '-p ' . $porta,
            '-o BatchMode=yes',
            '-o ConnectTimeout=8',
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=' . $knownHosts,
            escapeshellarg($usuario . '@' . $host),
            escapeshellarg($cmd),
        ]);
    }

    /**
     * Executa via extensão ext-ssh2 (ssh2_connect).
     */
    private function executarViaSsh2(string $host, int $porta, string $usuario, string $senha, string $cmd, int $timeout): array
    {
        $conn = @ssh2_connect($host, $porta);
        if (!$conn) {
            return ['ok' => false, 'comando' => "ssh2_connect({$host}:{$porta})", 'saida' => 'Não foi possível conectar via SSH.', 'codigo' => 255];
        }

        if (!@ssh2_auth_password($conn, $usuario, $senha)) {
            return ['ok' => false, 'comando' => "ssh2_auth_password({$usuario}@{$host})", 'saida' => 'Autenticação SSH por senha falhou.', 'codigo' => 255];
        }

        $stream = @ssh2_exec($conn, $cmd);
        if (!$stream) {
            return ['ok' => false, 'comando' => $cmd, 'saida' => 'Falha ao executar comando remoto.', 'codigo' => 255];
        }

        $stderrStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($stream, true);
        stream_set_blocking($stderrStream, true);

        if ($timeout > 0) {
            stream_set_timeout($stream, $timeout);
            stream_set_timeout($stderrStream, $timeout);
        }

        $stdout = (string)stream_get_contents($stream);
        $stderr = (string)stream_get_contents($stderrStream);

        fclose($stderrStream);
        fclose($stream);

        // ssh2_exec não retorna exit code diretamente; verificamos via echo $?
        $exitStream = @ssh2_exec($conn, 'echo $?');
        $exitCode = 0;
        if ($exitStream) {
            stream_set_blocking($exitStream, true);
            $exitCode = (int)trim((string)stream_get_contents($exitStream));
            fclose($exitStream);
        }

        return [
            'ok'      => $exitCode === 0,
            'comando' => $cmd,
            'saida'   => trim($stdout . "\n" . $stderr),
            'codigo'  => $exitCode,
        ];
    }

    /**
     * Executa via proc_open com pseudo-terminal (pty) — envia senha interativamente.
     * Não requer sshpass.
     */
    private function executarViaPty(string $host, int $porta, string $usuario, string $senha, string $cmd, int $timeout): array
    {
        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $sshCmd = implode(' ', [
            'ssh',
            '-p ' . $porta,
            '-o ConnectTimeout=8',
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=' . $knownHosts,
            '-o PasswordAuthentication=yes',
            '-o PubkeyAuthentication=no',
            '-o NumberOfPasswordPrompts=1',
            escapeshellarg($usuario . '@' . $host),
            escapeshellarg($cmd),
        ]);

        $descriptorspec = [
            0 => ['pty'],
            1 => ['pty'],
            2 => ['pty'],
        ];

        $process = @proc_open($sshCmd, $descriptorspec, $pipes);
        if (!is_resource($process)) {
            return ['ok' => false, 'comando' => $sshCmd, 'saida' => 'Falha ao iniciar processo SSH com pty.', 'codigo' => 255];
        }

        // Aguarda prompt de senha e envia
        usleep(500_000);
        @fwrite($pipes[0], $senha . "\n");
        @fflush($pipes[0]);

        stream_set_blocking($pipes[1], false);

        $output = '';
        $inicio = microtime(true);

        while (true) {
            $chunk = (string)@fread($pipes[1], 8192);
            if ($chunk !== '') $output .= $chunk;

            $status = proc_get_status($process);
            if (!is_array($status) || empty($status['running'])) break;

            if ($timeout > 0 && (microtime(true) - $inicio) > $timeout) {
                @proc_terminate($process);
                $output .= (string)@fread($pipes[1], 8192);
                @fclose($pipes[0]);
                @fclose($pipes[1]);
                @proc_close($process);
                return ['ok' => false, 'comando' => $sshCmd, 'saida' => trim($output), 'codigo' => 124];
            }

            usleep(50_000);
        }

        @fclose($pipes[0]);
        @fclose($pipes[1]);
        if (isset($pipes[2]) && is_resource($pipes[2])) @fclose($pipes[2]);
        $codigo = (int)@proc_close($process);

        // Remove linhas de prompt de senha do output
        $lines = explode("\n", $output);
        $filtered = [];
        foreach ($lines as $line) {
            $lower = strtolower(trim($line));
            if (str_contains($lower, 'password:') || str_contains($lower, 'senha:')) continue;
            $filtered[] = $line;
        }

        return [
            'ok'      => $codigo === 0,
            'comando' => $sshCmd,
            'saida'   => trim(implode("\n", $filtered)),
            'codigo'  => $codigo,
        ];
    }

    private function executarProcesso(string $cmd, int $timeoutSegundos): array
    {
        if (!function_exists('proc_open')) {
            // Fallback para exec
            if (function_exists('exec')) {
                $linhas = [];
                $codigo = 0;
                @exec($cmd . ' 2>&1', $linhas, $codigo);
                return [
                    'ok'      => $codigo === 0,
                    'comando' => $cmd,
                    'saida'   => trim(implode("\n", $linhas)),
                    'codigo'  => (int)$codigo,
                ];
            }
            throw new \RuntimeException('proc_open e exec indisponíveis.');
        }

        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open($cmd, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            return ['ok' => false, 'comando' => $cmd, 'saida' => 'Falha ao iniciar processo SSH.', 'codigo' => 255];
        }

        @fclose($pipes[0]);
        @stream_set_blocking($pipes[1], false);
        @stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $inicio = microtime(true);

        while (true) {
            $stdout .= (string)@stream_get_contents($pipes[1]);
            $stderr .= (string)@stream_get_contents($pipes[2]);

            $status = proc_get_status($process);
            if (!is_array($status) || empty($status['running'])) break;

            if ($timeoutSegundos > 0 && (microtime(true) - $inicio) > $timeoutSegundos) {
                @proc_terminate($process);
                $stdout .= (string)@stream_get_contents($pipes[1]);
                $stderr .= (string)@stream_get_contents($pipes[2]);
                foreach ([1, 2] as $i) { if (isset($pipes[$i]) && is_resource($pipes[$i])) @fclose($pipes[$i]); }
                @proc_close($process);
                return ['ok' => false, 'comando' => $cmd, 'saida' => trim($stdout . "\n" . $stderr), 'codigo' => 124];
            }

            usleep(50_000);
        }

        foreach ([1, 2] as $i) { if (isset($pipes[$i]) && is_resource($pipes[$i])) @fclose($pipes[$i]); }
        $codigo = (int)@proc_close($process);

        return [
            'ok'      => $codigo === 0,
            'comando' => $cmd,
            'saida'   => trim($stdout . "\n" . $stderr),
            'codigo'  => $codigo,
        ];
    }
}
