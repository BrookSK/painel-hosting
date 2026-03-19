<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

final class DockerCli
{
    private ?array $remoto = null;

    public function definirRemoto(string $host, int $porta, string $usuario, string $caminhoChavePrivada): void
    {
        $host = trim($host);
        $usuario = trim($usuario);
        $caminhoChavePrivada = trim($caminhoChavePrivada);

        if ($host === '' || $porta <= 0 || $porta > 65535 || $usuario === '' || $caminhoChavePrivada === '') {
            throw new \InvalidArgumentException('Destino remoto inválido.');
        }

        $this->remoto = [
            'host' => $host,
            'porta' => $porta,
            'usuario' => $usuario,
            'chave' => $caminhoChavePrivada,
        ];
    }

    public function disponivel(): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }

        $t = $this->testarConexao();
        return (bool) ($t['ok'] ?? false);
    }

    public function testarConexao(): array
    {
        if (!function_exists('shell_exec')) {
            return [
                'ok' => false,
                'comando' => '',
                'saida' => 'shell_exec indisponível.',
            ];
        }

        $r = $this->executarComando('docker version');
        $out = (string) ($r['saida'] ?? '');

        $ok = str_contains($out, 'Version') || str_contains($out, 'Client:');

        return [
            'ok' => $ok,
            'comando' => (string) ($r['comando'] ?? ''),
            'saida' => $out,
        ];
    }

    public function criarEIniciarContainer(
        string $nome,
        int $cpu,
        int $ramMb,
        string $volumeHost,
        string $rede,
        string $imagem,
        array $cmd = []
    ): array {
        $this->validarNome($nome);
        $this->validarPathHost($volumeHost);
        $this->validarNome($rede);

        $args = [];
        $args[] = 'docker run -d';
        $args[] = '--name ' . escapeshellarg($nome);
        $args[] = '--cpus=' . escapeshellarg((string) $cpu);
        $args[] = '-m ' . escapeshellarg((string) $ramMb . 'm');
        $args[] = '-v ' . escapeshellarg($volumeHost . ':/data');
        $args[] = '--network ' . escapeshellarg($rede);
        $args[] = escapeshellarg($imagem);

        foreach ($cmd as $c) {
            $args[] = escapeshellarg((string) $c);
        }

        $dockerCmd = implode(' ', $args);
        $r = $this->executarComando($dockerCmd);
        $comando = (string) ($r['comando'] ?? '');
        $saida = (string) ($r['saida'] ?? '');

        $containerId = trim($saida);

        return [
            'comando' => $comando,
            'saida' => $saida,
            'container_id' => $containerId,
        ];
    }

    public function parar(string $containerId): string
    {
        $this->validarIdContainer($containerId);
        $cmd = 'docker stop ' . escapeshellarg($containerId);
        $r = $this->executarComando($cmd);
        return (string) ($r['saida'] ?? '');
    }

    public function iniciar(string $containerId): string
    {
        $this->validarIdContainer($containerId);
        $cmd = 'docker start ' . escapeshellarg($containerId);
        $r = $this->executarComando($cmd);
        return (string) ($r['saida'] ?? '');
    }

    public function executar(string $cmd): array
    {
        return $this->executarComando($cmd);
    }

    public function removerContainer(string $nome): array
    {
        $this->validarNome($nome);
        $cmd = 'docker rm -f ' . escapeshellarg($nome);
        return $this->executarComando($cmd);
    }

    private function executarComando(string $dockerCmd): array
    {
        $dockerCmd = trim($dockerCmd);
        if ($dockerCmd === '') {
            throw new \InvalidArgumentException('Comando docker vazio.');
        }

        if ($this->remoto === null) {
            $comando = $dockerCmd;
        } else {
            $destino = (string) $this->remoto['usuario'] . '@' . (string) $this->remoto['host'];
            $porta = (int) ($this->remoto['porta'] ?? 22);
            $chave = (string) ($this->remoto['chave'] ?? '');

            $args = [];
            $args[] = 'ssh';
            $args[] = '-i ' . escapeshellarg($chave);
            $args[] = '-p ' . (int) $porta;
            $args[] = '-o BatchMode=yes';
            $args[] = '-o ConnectTimeout=8';
            $args[] = '-o StrictHostKeyChecking=no';

            $knownHosts = '/dev/null';
            if (PHP_OS_FAMILY === 'Windows') {
                $knownHosts = 'NUL';
            }
            $args[] = '-o UserKnownHostsFile=' . $knownHosts;
            $args[] = escapeshellarg($destino);
            $args[] = escapeshellarg($dockerCmd);

            $comando = implode(' ', $args);
        }

        $saida = (string) @shell_exec($comando . ' 2>&1');
        return [
            'comando' => $comando,
            'saida' => $saida,
        ];
    }

    private function validarIdContainer(string $id): void
    {
        if ($id === '' || preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $id) !== 1) {
            throw new \InvalidArgumentException('Container inválido.');
        }
    }

    private function validarNome(string $nome): void
    {
        if ($nome === '' || preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $nome) !== 1) {
            throw new \InvalidArgumentException('Nome inválido.');
        }
    }

    private function validarPathHost(string $path): void
    {
        if ($path === '' || str_contains($path, '\0')) {
            throw new \InvalidArgumentException('Path inválido.');
        }
    }
}
