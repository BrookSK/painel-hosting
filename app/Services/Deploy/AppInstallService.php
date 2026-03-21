<?php

declare(strict_types=1);

namespace LRV\App\Services\Deploy;

use LRV\App\Services\Provisioning\DockerCli;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Settings;

final class AppInstallService
{
    public function __construct(private readonly DockerCli $docker) {}

    /**
     * Instala uma aplicação a partir de um template na VPS do cliente.
     * Chamado pelo job handler.
     */
    public function instalar(int $applicationId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            'SELECT a.id, a.vps_id, a.template_id, a.port, a.domain, a.repository,
                    a.environment_json, a.status,
                    v.server_id, v.client_id,
                    t.docker_image, t.docker_command, t.default_port,
                    t.requires_domain, t.requires_repo, t.environment_variables, t.slug
             FROM applications a
             INNER JOIN vps v ON v.id = a.vps_id
             LEFT JOIN app_templates t ON t.id = a.template_id
             WHERE a.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $applicationId]);
        $app = $stmt->fetch();

        if (!is_array($app)) {
            throw new \RuntimeException('Aplicação não encontrada.');
        }

        $image = trim((string) ($app['docker_image'] ?? ''));
        if ($image === '') {
            throw new \RuntimeException('Template sem imagem Docker.');
        }

        $port = (int) ($app['port'] ?? 0);
        if ($port <= 0) {
            $port = (int) ($app['default_port'] ?? 80);
        }

        $serverId = (int) ($app['server_id'] ?? 0);
        if ($serverId <= 0) {
            throw new \RuntimeException('VPS sem node associado.');
        }

        // Buscar servidor
        $srvStmt = $pdo->prepare('SELECT id, ip_address, hostname, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type, status FROM servers WHERE id = :id LIMIT 1');
        $srvStmt->execute([':id' => $serverId]);
        $srv = $srvStmt->fetch();

        if (!is_array($srv) || (string) ($srv['status'] ?? '') !== 'active') {
            throw new \RuntimeException('Node não encontrado ou inativo.');
        }

        $this->configurarSsh($srv);

        $log('Testando conexão SSH/Docker...');
        $t = $this->docker->testarConexao();
        if (empty($t['ok'])) {
            throw new \RuntimeException('Falha SSH/Docker: ' . trim((string) ($t['saida'] ?? '')));
        }

        $rede = (string) Settings::obter('infra.docker_rede', 'lrvcloud_network');
        $slug = (string) ($app['slug'] ?? 'app');
        $nomeContainer = 'app_' . $slug . '_' . $applicationId;

        // Remover container anterior se existir
        $log('Removendo container anterior (se existir)...');
        try { $this->docker->removerContainer($nomeContainer); } catch (\Throwable) {}

        // Pull
        $log('Pull da imagem ' . $image . '...');
        $this->docker->executar('docker pull ' . escapeshellarg($image));

        // Montar comando docker run
        $envVars = $this->mergeEnv(
            (string) ($app['environment_variables'] ?? ''),
            (string) ($app['environment_json'] ?? '')
        );

        $cmd = 'docker run -d'
            . ' --name ' . escapeshellarg($nomeContainer)
            . ' --network ' . escapeshellarg($rede)
            . ' -p ' . (int) $port . ':' . (int) ($app['default_port'] ?? 80)
            . ' --restart unless-stopped'
            . ' --label lrv.app_id=' . $applicationId
            . ' --label lrv.client_id=' . (int) ($app['client_id'] ?? 0);

        foreach ($envVars as $k => $v) {
            if ($v !== '') {
                $cmd .= ' -e ' . escapeshellarg($k . '=' . $v);
            }
        }

        // Repo mount
        $repo = trim((string) ($app['repository'] ?? ''));
        if ($repo !== '') {
            $volumeBase = rtrim((string) Settings::obter('infra.volume_base', '/vps'), '/');
            $appDir = $volumeBase . '/apps/' . $applicationId;
            $log('Clonando repositório...');
            try {
                $this->docker->executar('mkdir -p ' . escapeshellarg($appDir));
                $this->docker->executar('git clone --depth 1 ' . escapeshellarg($repo) . ' ' . escapeshellarg($appDir . '/src'));
            } catch (\Throwable $e) {
                $log('Aviso clone: ' . $e->getMessage());
            }
            $cmd .= ' -v ' . escapeshellarg($appDir . '/src') . ':/app';
        }

        // Docker command override
        $dockerCmd = trim((string) ($app['docker_command'] ?? ''));
        $cmd .= ' ' . escapeshellarg($image);
        if ($dockerCmd !== '') {
            $cmd .= ' ' . $dockerCmd;
        }

        $log('Iniciando container...');
        $rRun = $this->docker->executar($cmd);
        $out = trim((string) ($rRun['saida'] ?? ''));
        $log($out);

        if ($out === '' || str_contains(strtolower($out), 'error')) {
            $pdo->prepare("UPDATE applications SET status = 'error', logs = :l WHERE id = :id")
                ->execute([':l' => $out, ':id' => $applicationId]);
            throw new \RuntimeException('Falha ao iniciar container.');
        }

        $containerId = substr($out, 0, 12);
        $pdo->prepare("UPDATE applications SET status = 'running', container_id = :c, logs = :l WHERE id = :id")
            ->execute([':c' => $containerId, ':l' => 'Instalação concluída em ' . date('Y-m-d H:i:s'), ':id' => $applicationId]);

        $log('Instalação concluída. Container: ' . $containerId);
    }

    private function configurarSsh(array $srv): void
    {
        $host = trim((string) ($srv['ip_address'] ?? $srv['hostname'] ?? ''));
        $sshPort = (int) ($srv['ssh_port'] ?? 22);
        $sshUser = trim((string) ($srv['ssh_user'] ?? ''));
        $authType = (string) ($srv['ssh_auth_type'] ?? 'key');

        if ($authType === 'password') {
            $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string) ($srv['ssh_password'] ?? ''));
            $this->docker->definirRemotoComSenha($host, $sshPort, $sshUser, $senha);
        } else {
            $keyId = trim((string) ($srv['ssh_key_id'] ?? ''));
            $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
            $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
            $this->docker->definirRemoto($host, $sshPort, $sshUser, $keyPath);
        }
    }

    private function mergeEnv(string $templateEnv, string $userEnv): array
    {
        $base = [];
        if ($templateEnv !== '') {
            $d = json_decode($templateEnv, true);
            if (is_array($d)) $base = $d;
        }
        if ($userEnv !== '') {
            $d = json_decode($userEnv, true);
            if (is_array($d)) $base = array_merge($base, $d);
        }
        return $base;
    }
}
