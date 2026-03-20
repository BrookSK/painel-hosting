<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

final class SshExecutor
{
    /**
     * Faz upload de um arquivo local para o servidor remoto via SCP.
     * Retorna ['ok'=>bool, 'saida'=>string, 'codigo'=>int]
     */
    public function scpUpload(
        string $host,
        int $porta,
        string $usuario,
        string $caminhoChavePrivada,
        string $arquivoLocalTmp,
        string $caminhoRemoto,
        int $timeoutSegundos = 60
    ): array {
        $this->validarParametrosBase($host, $porta, $usuario, $caminhoChavePrivada);

        if (!is_file($arquivoLocalTmp)) {
            throw new \RuntimeException('Arquivo local não encontrado.');
        }

        $caminhoRemoto = trim($caminhoRemoto);
        if ($caminhoRemoto === '' || str_contains($caminhoRemoto, '..') || preg_match('/[;&|`$]/', $caminhoRemoto)) {
            throw new \InvalidArgumentException('Caminho remoto inválido.');
        }

        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $destino = $usuario . '@' . $host . ':' . $caminhoRemoto;

        $args = [
            'scp',
            '-i ' . escapeshellarg($caminhoChavePrivada),
            '-P ' . (int) $porta,
            '-o BatchMode=yes',
            '-o ConnectTimeout=8',
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=' . $knownHosts,
            escapeshellarg($arquivoLocalTmp),
            escapeshellarg($destino),
        ];

        return $this->executarComando(implode(' ', $args), $timeoutSegundos);
    }

    /**
     * Faz download de um arquivo remoto para um arquivo local temporário via SCP.
     * Retorna ['ok'=>bool, 'local_path'=>string, 'saida'=>string, 'codigo'=>int]
     */
    public function scpDownload(
        string $host,
        int $porta,
        string $usuario,
        string $caminhoChavePrivada,
        string $caminhoRemoto,
        int $timeoutSegundos = 60
    ): array {
        $this->validarParametrosBase($host, $porta, $usuario, $caminhoChavePrivada);

        $caminhoRemoto = trim($caminhoRemoto);
        if ($caminhoRemoto === '' || str_contains($caminhoRemoto, '..') || preg_match('/[;&|`$]/', $caminhoRemoto)) {
            throw new \InvalidArgumentException('Caminho remoto inválido.');
        }

        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $localTmp = sys_get_temp_dir() . '/lrv_dl_' . bin2hex(random_bytes(8));
        $origem = $usuario . '@' . $host . ':' . $caminhoRemoto;

        $args = [
            'scp',
            '-i ' . escapeshellarg($caminhoChavePrivada),
            '-P ' . (int) $porta,
            '-o BatchMode=yes',
            '-o ConnectTimeout=8',
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=' . $knownHosts,
            escapeshellarg($origem),
            escapeshellarg($localTmp),
        ];

        $result = $this->executarComando(implode(' ', $args), $timeoutSegundos);
        $result['local_path'] = $localTmp;
        return $result;
    }

    private function validarParametrosBase(string $host, int $porta, string $usuario, string $caminhoChavePrivada): void
    {
        if (trim($host) === '' || $porta <= 0 || $porta > 65535 || trim($usuario) === '' || trim($caminhoChavePrivada) === '') {
            throw new \InvalidArgumentException('Parâmetros inválidos para SCP.');
        }
        if (!is_file($caminhoChavePrivada)) {
            throw new \RuntimeException('Arquivo de chave SSH não encontrado.');
        }
    }

    private function executarComando(string $cmd, int $timeoutSegundos): array
    {
        if (!function_exists('proc_open')) {
            throw new \RuntimeException('proc_open indisponível.');
        }

        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open($cmd, $descriptorspec, $pipes);
        if (!is_resource($process)) {
            return ['ok' => false, 'saida' => 'Falha ao iniciar processo.', 'codigo' => 255];
        }

        @fclose($pipes[0]);
        @stream_set_blocking($pipes[1], false);
        @stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $inicio = microtime(true);

        while (true) {
            $stdout .= (string) @stream_get_contents($pipes[1]);
            $stderr .= (string) @stream_get_contents($pipes[2]);
            $status = proc_get_status($process);
            if (!is_array($status) || empty($status['running'])) {
                break;
            }
            if ($timeoutSegundos > 0 && (microtime(true) - $inicio) > $timeoutSegundos) {
                @proc_terminate($process);
                break;
            }
            usleep(50_000);
        }

        foreach ([1, 2] as $i) {
            if (isset($pipes[$i]) && is_resource($pipes[$i])) {
                @fclose($pipes[$i]);
            }
        }

        $codigo = (int) @proc_close($process);
        return ['ok' => $codigo === 0, 'saida' => trim($stdout . "\n" . $stderr), 'codigo' => $codigo];
    }

    public function executar(
        string $host,
        int $porta,
        string $usuario,
        string $caminhoChavePrivada,
        string $comandoRemoto,
        int $timeoutSegundos = 30
    ): array {
        $host = trim($host);
        $usuario = trim($usuario);
        $caminhoChavePrivada = trim($caminhoChavePrivada);
        $comandoRemoto = trim($comandoRemoto);

        if ($host === '' || $porta <= 0 || $porta > 65535 || $usuario === '' || $caminhoChavePrivada === '' || $comandoRemoto === '') {
            throw new \InvalidArgumentException('Parâmetros inválidos para execução SSH.');
        }

        if (!is_file($caminhoChavePrivada)) {
            throw new \RuntimeException('Arquivo de chave SSH não encontrado.');
        }

        if (!function_exists('proc_open') && !function_exists('exec') && !function_exists('shell_exec')) {
            throw new \RuntimeException('Nenhum método de execução disponível (proc_open/exec/shell_exec).');
        }

        $knownHosts = '/dev/null';
        if (PHP_OS_FAMILY === 'Windows') {
            $knownHosts = 'NUL';
        }

        $destino = $usuario . '@' . $host;

        $args = [];
        $args[] = 'ssh';
        $args[] = '-i ' . escapeshellarg($caminhoChavePrivada);
        $args[] = '-p ' . (int) $porta;
        $args[] = '-o BatchMode=yes';
        $args[] = '-o ConnectTimeout=8';
        $args[] = '-o StrictHostKeyChecking=no';
        $args[] = '-o UserKnownHostsFile=' . $knownHosts;
        $args[] = escapeshellarg($destino);
        $args[] = escapeshellarg($comandoRemoto);

        $cmd = implode(' ', $args);

        if (!function_exists('proc_open')) {
            if (function_exists('exec')) {
                $linhas = [];
                $codigo = 0;
                @exec($cmd . ' 2>&1', $linhas, $codigo);
                $saida = trim(implode("\n", $linhas));
                return [
                    'ok' => $codigo === 0,
                    'comando' => $cmd,
                    'saida' => $saida,
                    'codigo' => (int) $codigo,
                ];
            }

            $argsExit = $args;
            $argsExit[count($argsExit) - 1] = escapeshellarg($comandoRemoto . '; echo __LRV_EXIT:$?');
            $cmdExit = implode(' ', $argsExit);
            $raw = (string) @shell_exec($cmdExit . ' 2>&1');
            $raw = trim($raw);

            $codigo = 255;
            if (preg_match('/__LRV_EXIT:(\d+)/', $raw, $m) === 1) {
                $codigo = (int) $m[1];
                $raw = trim(str_replace($m[0], '', $raw));
            }

            return [
                'ok' => $codigo === 0,
                'comando' => $cmdExit,
                'saida' => $raw,
                'codigo' => $codigo,
            ];
        }

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($cmd, $descriptorspec, $pipes);
        if (!is_resource($process)) {
            return [
                'ok' => false,
                'comando' => $cmd,
                'saida' => 'Falha ao iniciar processo SSH.',
                'codigo' => 255,
            ];
        }

        @fclose($pipes[0]);
        @stream_set_blocking($pipes[1], false);
        @stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $inicio = microtime(true);

        while (true) {
            $stdout .= (string) @stream_get_contents($pipes[1]);
            $stderr .= (string) @stream_get_contents($pipes[2]);

            $status = proc_get_status($process);
            if (!is_array($status) || empty($status['running'])) {
                break;
            }

            $decorrido = microtime(true) - $inicio;
            if ($timeoutSegundos > 0 && $decorrido > $timeoutSegundos) {
                @proc_terminate($process);
                $stdout .= (string) @stream_get_contents($pipes[1]);
                $stderr .= (string) @stream_get_contents($pipes[2]);

                foreach ([1, 2] as $i) {
                    if (isset($pipes[$i]) && is_resource($pipes[$i])) {
                        @fclose($pipes[$i]);
                    }
                }

                @proc_close($process);

                return [
                    'ok' => false,
                    'comando' => $cmd,
                    'saida' => trim($stdout . "\n" . $stderr),
                    'codigo' => 124,
                ];
            }

            usleep(50_000);
        }

        foreach ([1, 2] as $i) {
            if (isset($pipes[$i]) && is_resource($pipes[$i])) {
                @fclose($pipes[$i]);
            }
        }

        $codigo = (int) @proc_close($process);
        $saida = trim($stdout . "\n" . $stderr);

        return [
            'ok' => $codigo === 0,
            'comando' => $cmd,
            'saida' => $saida,
            'codigo' => $codigo,
        ];
    }
}
