<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

final class DockerCli
{
    public function disponivel(): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }

        $out = @shell_exec('docker version 2>&1');
        if (!is_string($out)) {
            return false;
        }

        return str_contains($out, 'Version') || str_contains($out, 'Client:');
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

        $comando = implode(' ', $args) . ' 2>&1';
        $saida = (string) @shell_exec($comando);

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
        $cmd = 'docker stop ' . escapeshellarg($containerId) . ' 2>&1';
        return (string) @shell_exec($cmd);
    }

    public function iniciar(string $containerId): string
    {
        $this->validarIdContainer($containerId);
        $cmd = 'docker start ' . escapeshellarg($containerId) . ' 2>&1';
        return (string) @shell_exec($cmd);
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
