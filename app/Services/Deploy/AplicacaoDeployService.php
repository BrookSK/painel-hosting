<?php

declare(strict_types=1);

namespace LRV\App\Services\Deploy;

use LRV\App\Services\Provisioning\DockerCli;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Settings;

final class AplicacaoDeployService
{
    public function __construct(
        private readonly DockerCli $docker,
    ) {
    }

    public function deploy(int $applicationId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT a.id, a.vps_id, a.port, a.status, a.repository, v.server_id, v.client_id FROM applications a INNER JOIN vps v ON v.id = a.vps_id WHERE a.id = :id LIMIT 1');
        $stmt->execute([':id' => $applicationId]);
        $app = $stmt->fetch();

        if (!is_array($app)) {
            throw new \RuntimeException('Aplicação não encontrada.');
        }

        $repo = trim((string) ($app['repository'] ?? ''));
        if ($repo === '') {
            throw new \RuntimeException('Repositório/imagem não informado. Use o campo Repositório para informar a imagem Docker (ex: nginx:alpine).');
        }

        $port = (int) ($app['port'] ?? 0);
        if ($port <= 0 || $port > 65535) {
            throw new \RuntimeException('Porta inválida para deploy.');
        }

        $serverId = (int) ($app['server_id'] ?? 0);
        if ($serverId <= 0) {
            throw new \RuntimeException('VPS sem node associado.');
        }

        $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $srv = $stmt->fetch();

        if (!is_array($srv)) {
            throw new \RuntimeException('Node não encontrado.');
        }

        if ((string) ($srv['status'] ?? '') !== 'active') {
            throw new \RuntimeException('Node não está ativo.');
        }

        $host = trim((string) ($srv['ip_address'] ?? ''));
        if ($host === '') {
            $host = trim((string) ($srv['hostname'] ?? ''));
        }
        $sshPort = (int) ($srv['ssh_port'] ?? 22);
        $sshUser = trim((string) ($srv['ssh_user'] ?? ''));
        $keyId = trim((string) ($srv['ssh_key_id'] ?? ''));

        if ($host === '' || $sshPort <= 0 || $sshUser === '' || $keyId === '') {
            throw new \RuntimeException('Node sem dados de SSH completos.');
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        if ($keyDir === '') {
            throw new \RuntimeException('Diretório base das chaves SSH não configurado.');
        }

        $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
        if (!is_file($keyPath)) {
            throw new \RuntimeException('Arquivo de chave não encontrado: ' . $keyId);
        }

        $this->docker->definirRemoto($host, $sshPort, $sshUser, $keyPath);

        $log('Testando conexão SSH/Docker...');
        $t = $this->docker->testarConexao();
        if (empty($t['ok'])) {
            $saida = trim((string) ($t['saida'] ?? ''));
            throw new \RuntimeException('Falha ao validar SSH/Docker.' . ($saida !== '' ? "\n\n" . $saida : ''));
        }

        $rede = (string) Settings::obter('infra.docker_rede', 'lrvcloud_network');
        $nomeContainer = 'app_' . $applicationId;

        $log('Removendo container anterior (se existir)...');
        $rRm = $this->docker->removerContainer($nomeContainer);
        $log(trim((string) ($rRm['saida'] ?? '')));

        $log('Pull da imagem...');
        $rPull = $this->docker->executar('docker pull ' . escapeshellarg($repo));
        $log(trim((string) ($rPull['saida'] ?? '')));

        $log('Iniciando container...');
        $cmd = 'docker run -d'
            . ' --name ' . escapeshellarg($nomeContainer)
            . ' --network ' . escapeshellarg($rede)
            . ' -p ' . (int) $port . ':80'
            . ' ' . escapeshellarg($repo);

        $rRun = $this->docker->executar($cmd);
        $out = trim((string) ($rRun['saida'] ?? ''));
        $log($out);

        if ($out === '' || str_contains($out, 'Error') || str_contains($out, 'error')) {
            throw new \RuntimeException('Falha ao iniciar container de aplicação.');
        }

        $up = $pdo->prepare("UPDATE applications SET status = 'active' WHERE id = :id");
        $up->execute([':id' => $applicationId]);

        $log('Deploy concluído.');
    }
}
