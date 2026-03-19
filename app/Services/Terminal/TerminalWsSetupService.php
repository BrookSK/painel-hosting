<?php

declare(strict_types=1);

namespace LRV\App\Services\Terminal;

use LRV\Core\ConfiguracoesSistema;

final class TerminalWsSetupService
{
    public function status(): array
    {
        $base = $this->caminhoProjeto();

        $script = $base . DIRECTORY_SEPARATOR . 'terminal-ws.php';
        $autoload = $base . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        $porta = ConfiguracoesSistema::terminalWsInternalPort();

        $pid = $this->lerPid();
        $running = $pid !== null && $this->processoRodando($pid);

        $composerPid = $this->lerPidComposer();
        $composerRunning = $composerPid !== null && $this->processoRodando($composerPid);

        return [
            'script_ok' => is_file($script),
            'vendor_ok' => is_file($autoload),
            'composer_ok' => $this->composerDisponivel(),
            'composer_pid' => $composerPid,
            'composer_running' => $composerRunning,
            'porta' => $porta,
            'pid' => $pid,
            'daemon_ok' => $running,
            'porta_ok' => $this->portaAberta('127.0.0.1', $porta, 0.2),
            'log_path' => $this->caminhoLog(),
            'pid_path' => $this->caminhoPid(),
            'composer_log_path' => $this->caminhoLogComposer(),
            'composer_pid_path' => $this->caminhoPidComposer(),
        ];
    }

    public function instalarDependencias(callable $log): void
    {
        if (!function_exists('shell_exec') && !function_exists('exec')) {
            throw new \RuntimeException('Nenhum método de execução disponível (exec/shell_exec).');
        }

        if (!$this->composerDisponivel()) {
            throw new \RuntimeException('Composer não encontrado no PATH do servidor.');
        }

        $pidAtual = $this->lerPidComposer();
        if ($pidAtual !== null && $this->processoRodando($pidAtual)) {
            $log('Composer já está em execução (pid=' . $pidAtual . ').');
            return;
        }

        $base = $this->caminhoProjeto();

        $this->garantirDiretorioTerminal($log);

        $logFile = $this->caminhoLogComposer();

        $log('Iniciando composer install em background (sem dev)...');

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'cmd /c start /B "composer-install" composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --working-dir=' . escapeshellarg($base) . ' > ' . escapeshellarg($logFile) . ' 2>&1';
            $this->exec($cmd);
            $this->salvarPidComposer(null);
            $log('Processo iniciado (Windows: PID não detectado automaticamente).');
            return;
        }

        $cmd = 'nohup composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --working-dir=' . escapeshellarg($base) . ' >> ' . escapeshellarg($logFile) . ' 2>&1 & echo $!';
        $out = trim($this->exec($cmd));
        $newPid = null;
        if ($out !== '' && ctype_digit($out)) {
            $newPid = (int) $out;
        }

        if ($newPid !== null) {
            $this->salvarPidComposer($newPid);
            $log('Processo iniciado (pid=' . $newPid . ').');
        } else {
            $log('Processo iniciado (pid não detectado).');
        }
    }

    public function iniciarDaemon(callable $log): void
    {
        if (!function_exists('shell_exec') && !function_exists('exec')) {
            throw new \RuntimeException('Nenhum método de execução disponível (exec/shell_exec).');
        }

        $st = $this->status();

        if (empty($st['script_ok'])) {
            throw new \RuntimeException('Arquivo terminal-ws.php não encontrado no projeto.');
        }

        if (empty($st['vendor_ok'])) {
            throw new \RuntimeException('Dependências não instaladas (vendor/autoload.php ausente).');
        }

        $pid = $this->lerPid();
        if ($pid !== null && $this->processoRodando($pid)) {
            $log('Daemon já está em execução (pid=' . $pid . ').');
            return;
        }

        $this->garantirDiretorioTerminal($log);

        $php = $this->phpCli();
        $base = $this->caminhoProjeto();
        $script = $base . DIRECTORY_SEPARATOR . 'terminal-ws.php';

        $logFile = $this->caminhoLog();

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'cmd /c start /B "terminal-ws" ' . escapeshellarg($php) . ' ' . escapeshellarg($script) . ' > ' . escapeshellarg($logFile) . ' 2>&1';
            $this->exec($cmd);
            $log('Daemon iniciado (Windows: PID não detectado automaticamente).');
            $this->salvarPid(null);
            return;
        }

        $cmd = 'nohup ' . escapeshellarg($php) . ' ' . escapeshellarg($script) . ' >> ' . escapeshellarg($logFile) . ' 2>&1 & echo $!';
        $out = trim($this->exec($cmd));

        $newPid = null;
        if ($out !== '' && ctype_digit($out)) {
            $newPid = (int) $out;
        }

        if ($newPid !== null) {
            $this->salvarPid($newPid);
            $log('Daemon iniciado (pid=' . $newPid . ').');
        } else {
            $log('Daemon iniciado (pid não detectado).');
        }
    }

    public function pararDaemon(callable $log): void
    {
        $pid = $this->lerPid();
        if ($pid === null) {
            $log('PID não encontrado.');
            return;
        }

        if (!$this->processoRodando($pid)) {
            $log('Processo não está rodando (pid=' . $pid . ').');
            $this->salvarPid(null);
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            if (!function_exists('shell_exec') && !function_exists('exec')) {
                throw new \RuntimeException('Nenhum método de execução disponível (exec/shell_exec).');
            }
            $this->exec('taskkill /F /PID ' . (int) $pid);
            $log('Processo finalizado (pid=' . $pid . ').');
            $this->salvarPid(null);
            return;
        }

        if (function_exists('posix_kill')) {
            @posix_kill($pid, 15);
            usleep(200_000);
            if ($this->processoRodando($pid)) {
                @posix_kill($pid, 9);
            }
        } else {
            if (!function_exists('shell_exec') && !function_exists('exec')) {
                throw new \RuntimeException('Nenhum método de execução disponível (exec/shell_exec).');
            }
            $this->exec('kill ' . (int) $pid . ' 2>&1');
        }

        $log('Processo finalizado (pid=' . $pid . ').');
        $this->salvarPid(null);
    }

    private function composerDisponivel(): bool
    {
        if (!function_exists('shell_exec') && !function_exists('exec')) {
            return false;
        }

        $out = trim($this->exec('composer --version 2>&1'));
        return $out !== '' && (str_contains($out, 'Composer') || str_contains($out, 'composer'));
    }

    private function phpCli(): string
    {
        return 'php';
    }

    private function exec(string $cmd): string
    {
        if (function_exists('exec')) {
            $linhas = [];
            $codigo = 0;
            @exec($cmd, $linhas, $codigo);
            return implode("\n", $linhas);
        }

        return (string) @shell_exec($cmd);
    }

    private function limitarSaida(string $out): string
    {
        if (strlen($out) <= 8000) {
            return $out;
        }
        return substr($out, 0, 8000) . "\n... (saída truncada)";
    }

    private function caminhoProjeto(): string
    {
        return dirname(__DIR__, 3);
    }

    private function garantirDiretorioTerminal(callable $log): void
    {
        $dir = $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'terminal';

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        if (!is_dir($dir)) {
            throw new \RuntimeException('Não foi possível criar storage/terminal.');
        }

        if (!is_writable($dir)) {
            $log('Aviso: storage/terminal existe, mas não está com permissão de escrita para o processo atual.');
        }
    }

    private function caminhoPid(): string
    {
        return $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'terminal' . DIRECTORY_SEPARATOR . 'terminal-ws.pid';
    }

    private function caminhoLog(): string
    {
        return $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'terminal' . DIRECTORY_SEPARATOR . 'terminal-ws.log';
    }

    private function caminhoPidComposer(): string
    {
        return $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'terminal' . DIRECTORY_SEPARATOR . 'terminal-composer.pid';
    }

    private function caminhoLogComposer(): string
    {
        return $this->caminhoProjeto() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'terminal' . DIRECTORY_SEPARATOR . 'terminal-composer.log';
    }

    private function salvarPid(?int $pid): void
    {
        $pidFile = $this->caminhoPid();

        if ($pid === null) {
            if (is_file($pidFile)) {
                @unlink($pidFile);
            }
            return;
        }

        @file_put_contents($pidFile, (string) $pid);
    }

    private function salvarPidComposer(?int $pid): void
    {
        $pidFile = $this->caminhoPidComposer();

        if ($pid === null) {
            if (is_file($pidFile)) {
                @unlink($pidFile);
            }
            return;
        }

        @file_put_contents($pidFile, (string) $pid);
    }

    private function lerPid(): ?int
    {
        $pidFile = $this->caminhoPid();
        if (!is_file($pidFile)) {
            return null;
        }

        $raw = trim((string) @file_get_contents($pidFile));
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        $pid = (int) $raw;
        return $pid > 0 ? $pid : null;
    }

    private function lerPidComposer(): ?int
    {
        $pidFile = $this->caminhoPidComposer();
        if (!is_file($pidFile)) {
            return null;
        }

        $raw = trim((string) @file_get_contents($pidFile));
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        $pid = (int) $raw;
        return $pid > 0 ? $pid : null;
    }

    private function processoRodando(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            if (!function_exists('shell_exec') && !function_exists('exec')) {
                return false;
            }
            $out = trim($this->exec('tasklist /FI "PID eq ' . (int) $pid . '"'));
            return $out !== '' && str_contains($out, (string) $pid);
        }

        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        if (!function_exists('shell_exec') && !function_exists('exec')) {
            return false;
        }

        $out = trim($this->exec('ps -p ' . (int) $pid . ' -o pid= 2>/dev/null'));
        return $out !== '' && ctype_digit(trim($out));
    }

    private function portaAberta(string $host, int $porta, float $timeout): bool
    {
        if ($porta <= 0 || $porta > 65535) {
            return false;
        }

        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($host, $porta, $errno, $errstr, $timeout);
        if (is_resource($fp)) {
            @fclose($fp);
            return true;
        }

        return false;
    }
}
