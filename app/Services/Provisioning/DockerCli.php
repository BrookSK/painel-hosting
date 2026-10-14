<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

use LRV\App\Services\Infra\SshExecutor;

final class DockerCli
{
    private ?array $remoto = null;

    private readonly SshExecutor $exec;

    public function __construct(?SshExecutor $exec = null)
    {
        $this->exec = $exec ?? new SshExecutor();
    }

    public function definirRemoto(string $host, int $porta, string $usuario, string $caminhoChavePrivada): void
    {
        $host = trim($host);
        $usuario = trim($usuario);
        $caminhoChavePrivada = trim($caminhoChavePrivada);

        if ($host === '' || $porta <= 0 || $porta > 65535 || $usuario === '' || $caminhoChavePrivada === '') {
            throw new \InvalidArgumentException('Destino remoto inválido.');
        }

        $this->remoto = [
            'host'    => $host,
            'porta'   => $porta,
            'usuario' => $usuario,
            'chave'   => $caminhoChavePrivada,
            'senha'   => null,
        ];
    }

    public function definirRemotoComSenha(string $host, int $porta, string $usuario, string $senha): void
    {
        $host    = trim($host);
        $usuario = trim($usuario);

        if ($host === '' || $porta <= 0 || $porta > 65535 || $usuario === '' || $senha === '') {
            throw new \InvalidArgumentException('Destino remoto inválido.');
        }

        $this->remoto = [
            'host'    => $host,
            'porta'   => $porta,
            'usuario' => $usuario,
            'chave'   => null,
            'senha'   => $senha,
        ];
    }

    public function disponivel(): bool
    {
        if (!function_exists('shell_exec') && !function_exists('proc_open') && !function_exists('exec')) {
            return false;
        }

        $t = $this->testarConexao();
        return (bool) ($t['ok'] ?? false);
    }

    public function testarConexao(): array
    {
        if ($this->remoto === null) {
            return [
                'ok' => false,
                'comando' => '',
                'saida' => 'Destino remoto não configurado.',
            ];
        }

        if (!function_exists('shell_exec') && !function_exists('proc_open') && !function_exists('exec')) {
            return [
                'ok' => false,
                'comando' => '',
                'saida' => 'Nenhum método de execução disponível (proc_open/exec/shell_exec).',
            ];
        }

        try {
            $r = $this->executarComando('docker version');
            $out = (string) ($r['saida'] ?? '');

            $ok = str_contains($out, 'Version') || str_contains($out, 'Client:');

            return [
                'ok' => $ok,
                'comando' => (string) ($r['comando'] ?? ''),
                'saida' => $out,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'comando' => '',
                'saida' => $e->getMessage(),
            ];
        }
    }

    public function criarEIniciarContainer(
        string $nome,
        int $cpu,
        int $ramMb,
        string $volumeHost,
        string $rede,
        string $imagem,
        array $labels = [],
        array $cmd = []
    ): array {
        $this->validarNome($nome);
        $this->validarPathHost($volumeHost);
        $this->validarNome($rede);

        $args = [];
        $args[] = 'docker run -d';
        $args[] = '--name ' . escapeshellarg($nome);
        if ($cpu > 0) {
            $args[] = '--cpus=' . escapeshellarg((string) $cpu);
        }
        if ($ramMb > 0) {
            $args[] = '-m ' . escapeshellarg((string) $ramMb . 'm');
        }
        $args[] = '-v ' . escapeshellarg($volumeHost . ':/data');
        $args[] = '--network ' . escapeshellarg($rede);
        // Proteção do servidor: limite de processos (evita "fork bomb" derrubar o node)
        // e impede ganho de privilégio via binários setuid dentro do container.
        $args[] = '--pids-limit 1024';
        $args[] = '--security-opt no-new-privileges';
        if ($ramMb > 0) {
            $args[] = '--memory-swap ' . escapeshellarg((string) $ramMb . 'm');
        }

        foreach ($labels as $k => $v) {
            $k = trim((string) $k);
            $v = (string) $v;

            $this->validarLabelKey($k);
            $this->validarLabelValue($v);

            $args[] = '--label ' . escapeshellarg($k . '=' . $v);
        }

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

    private function validarLabelKey(string $k): void
    {
        if ($k === '' || strlen($k) > 120 || preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $k) !== 1) {
            throw new \InvalidArgumentException('Label key inválida.');
        }
    }

    private function validarLabelValue(string $v): void
    {
        if (str_contains($v, "\0") || strlen($v) > 200) {
            throw new \InvalidArgumentException('Label value inválida.');
        }
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

    /**
     * @param bool $comVolumesAnonimos true = também apaga os volumes anônimos do container
     *        (ex.: /var/lib/mysql de containers de banco). Pastas montadas do host e volumes
     *        com nome não são afetados.
     */
    public function removerContainer(string $nome, bool $comVolumesAnonimos = false): array
    {
        $this->validarNome($nome);
        $cmd = 'docker rm -f ' . ($comVolumesAnonimos ? '-v ' : '') . escapeshellarg($nome);
        return $this->executarComando($cmd);
    }

    /**
     * Rede Docker exclusiva do cliente (lrv_c{ID}). Com ela, os containers de um cliente
     * (VPS, bancos, aplicações) não enxergam os de outros clientes no mesmo servidor.
     * O phpMyAdmin compartilhado é conectado à rede para continuar funcionando.
     *
     * Se não for possível criar (ex.: Docker sem faixas de IP livres), devolve $fallback
     * e quem chamou registra o aviso — melhor do que travar o provisionamento.
     */
    public function garantirRedeCliente(int $clientId, string $fallback): string
    {
        if ($clientId <= 0) {
            return $fallback;
        }
        $rede = 'lrv_c' . $clientId;
        try {
            $r = $this->executarComando(
                'docker network inspect ' . escapeshellarg($rede) . ' >/dev/null 2>&1'
                . ' || docker network create --label lrv.client_id=' . (int) $clientId . ' ' . escapeshellarg($rede) . ' >/dev/null 2>&1;'
                . ' docker network inspect ' . escapeshellarg($rede) . ' >/dev/null 2>&1 && echo LRV_NET_OK;'
                . ' docker network connect ' . escapeshellarg($rede) . ' lrv_phpmyadmin >/dev/null 2>&1; true'
            );
            if (str_contains((string) ($r['saida'] ?? ''), 'LRV_NET_OK')) {
                return $rede;
            }
        } catch (\Throwable) {}
        return $fallback;
    }

    /** Lista os nomes de todos os containers do node (rodando ou parados). */
    public function listarNomesContainers(): array
    {
        $r = $this->executarComando("docker ps -a --format '{{.Names}}'");
        $nomes = preg_split('/\R/', trim((string) ($r['saida'] ?? ''))) ?: [];
        return array_values(array_filter(array_map('trim', $nomes), fn ($n) => $n !== ''));
    }

    private function executarComando(string $dockerCmd): array
    {
        $dockerCmd = trim($dockerCmd);
        if ($dockerCmd === '') {
            throw new \InvalidArgumentException('Comando docker vazio.');
        }

        if ($this->remoto === null) {
            throw new \RuntimeException('Destino remoto não configurado. O painel não deve executar Docker localmente.');
        }

        $host    = (string)($this->remoto['host'] ?? '');
        $porta   = (int)($this->remoto['porta'] ?? 22);
        $usuario = (string)($this->remoto['usuario'] ?? '');
        $chave   = (string)($this->remoto['chave'] ?? '');
        $senha   = (string)($this->remoto['senha'] ?? '');

        if ($senha !== '') {
            $r = $this->exec->executarComSenha($host, $porta, $usuario, $senha, $dockerCmd, 60);
        } else {
            $r = $this->exec->executar($host, $porta, $usuario, $chave, $dockerCmd, 60);
        }
        $comando = (string) ($r['comando'] ?? '');
        $saida = (string) ($r['saida'] ?? '');

        if (empty($r['ok'])) {
            throw new \RuntimeException('Falha ao executar comando remoto via SSH.' . ($saida !== '' ? "\n\n" . $saida : ''));
        }
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
