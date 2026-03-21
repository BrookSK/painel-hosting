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
     * Executa um comando remoto via SSH com senha (requer sshpass instalado no servidor do painel).
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

        if (!$this->sshpassDisponivel()) {
            throw new \RuntimeException('sshpass não está instalado no servidor do painel. Execute: apt-get install -y sshpass');
        }

        $sshCmd = $this->montarComandoSenha($host, $porta, $usuario, $senha, $cmd);
        return $this->executarProcesso($sshCmd, $timeoutSegundos);
    }

    /**
     * Verifica se sshpass está disponível no sistema.
     */
    public function sshpassDisponivel(): bool
    {
        if (!function_exists('shell_exec')) return false;
        $out = @shell_exec('which sshpass 2>/dev/null');
        return $out !== null && trim((string)$out) !== '';
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

    private function montarComandoSenha(string $host, int $porta, string $usuario, string $senha, string $cmd): string
    {
        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        // sshpass -p SENHA ssh -o StrictHostKeyChecking=no ...
        return implode(' ', [
            'sshpass',
            '-p ' . escapeshellarg($senha),
            'ssh',
            '-p ' . $porta,
            '-o BatchMode=no',
            '-o ConnectTimeout=8',
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=' . $knownHosts,
            '-o PasswordAuthentication=yes',
            '-o PubkeyAuthentication=no',
            escapeshellarg($usuario . '@' . $host),
            escapeshellarg($cmd),
        ]);
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
