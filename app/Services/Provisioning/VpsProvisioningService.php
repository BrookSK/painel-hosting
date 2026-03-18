<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Settings;

final class VpsProvisioningService
{
    public function __construct(
        private readonly DockerCli $docker,
    ) {
    }

    public function provisionar(int $vpsId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM vps WHERE id = :id');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            throw new \RuntimeException('VPS não encontrada.');
        }

        $statusAtual = (string) ($vps['status'] ?? '');
        $containerIdExistente = (string) ($vps['container_id'] ?? '');
        if ($statusAtual === 'provisioning') {
            $log('VPS já está em status provisioning.');
            return;
        }

        if ($statusAtual === 'running' && $containerIdExistente !== '') {
            $log('VPS já está em execução.');
            return;
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        if ($serverId <= 0) {
            $serverId = $this->selecionarNodeDisponivel((int) $vps['cpu'], (int) $vps['ram'], (int) $vps['storage']);
            if ($serverId <= 0) {
                $this->atualizarStatusVps($vpsId, 'pending_node');
                $log('Nenhum node disponível. VPS marcada como pending_node.');
                return;
            }

            $this->alocarRecursosNode($serverId, (int) $vps['cpu'], (int) $vps['ram'], (int) $vps['storage'], $vpsId);
            $log('Node selecionado: ' . $serverId);
        }

        if (!$this->configurarDockerParaNode($serverId, $log)) {
            $this->atualizarStatusVps($vpsId, 'pending_provisioning');
            $log('Não foi possível configurar Docker remoto. VPS marcada como pending_provisioning.');
            return;
        }

        $this->atualizarStatusVps($vpsId, 'provisioning');

        $containerId = $containerIdExistente;
        if ($containerId !== '') {
            $log('VPS já possui container_id.');
            $this->atualizarStatusVps($vpsId, 'running');
            return;
        }

        $rede = (string) Settings::obter('infra.docker_rede', 'lrvcloud_network');
        $volumeBase = (string) Settings::obter('infra.volume_base', '/vps');
        $imagemBase = (string) Settings::obter('infra.imagem_base', 'debian:12-slim');

        $nomeContainer = 'vps_client_' . (int) $vps['client_id'] . '_' . $vpsId;
        $volumeHost = rtrim($volumeBase, '/') . '/client_' . (int) $vps['client_id'];

        if (!$this->docker->disponivel()) {
            $this->atualizarStatusVps($vpsId, 'pending_provisioning');
            $log('Docker CLI indisponível. VPS marcada como pending_provisioning.');
            return;
        }

        $log('Criando container...');

        $resp = $this->docker->criarEIniciarContainer(
            $nomeContainer,
            (int) $vps['cpu'],
            (int) $vps['ram'],
            $volumeHost,
            $rede,
            $imagemBase,
            ['tail', '-f', '/dev/null'],
        );

        $cid = trim((string) ($resp['container_id'] ?? ''));
        $log('Comando: ' . (string) ($resp['comando'] ?? ''));
        $log('Saída: ' . (string) ($resp['saida'] ?? ''));

        if ($cid === '' || str_contains($cid, 'Error')) {
            $this->atualizarStatusVps($vpsId, 'error');
            throw new \RuntimeException('Falha ao criar container.');
        }

        $up = $pdo->prepare('UPDATE vps SET container_id = :cid WHERE id = :id');
        $up->execute([':cid' => $cid, ':id' => $vpsId]);

        $this->atualizarStatusVps($vpsId, 'running');
        $log('VPS provisionada e em execução.');
    }

    public function suspenderPorPagamento(int $vpsId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, server_id, container_id, status FROM vps WHERE id = :id');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            throw new \RuntimeException('VPS não encontrada.');
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        if ($serverId > 0) {
            if (!$this->configurarDockerParaNode($serverId, $log)) {
                $log('Não foi possível configurar Docker remoto. Suspensão não executada.');
                return;
            }
        }

        $containerId = (string) ($vps['container_id'] ?? '');

        if ($containerId !== '' && $this->docker->disponivel()) {
            $log('Parando container...');
            $out = $this->docker->parar($containerId);
            $log('Saída: ' . $out);
        } else {
            $log('Container não encontrado ou Docker indisponível.');
        }

        $this->atualizarStatusVps($vpsId, 'suspended_payment');
        $log('VPS suspensa por pagamento.');
    }

    public function reativarPorPagamento(int $vpsId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, server_id, container_id, status FROM vps WHERE id = :id');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            throw new \RuntimeException('VPS não encontrada.');
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        if ($serverId > 0) {
            if (!$this->configurarDockerParaNode($serverId, $log)) {
                $log('Não foi possível configurar Docker remoto. Reativação não executada.');
                return;
            }
        }

        $containerId = (string) ($vps['container_id'] ?? '');

        if ($containerId === '') {
            $log('Container não encontrado. Nada para reativar.');
            return;
        }

        if (!$this->docker->disponivel()) {
            $log('Docker indisponível. Não foi possível reativar.');
            return;
        }

        $log('Iniciando container...');
        $out = $this->docker->iniciar($containerId);
        $log('Saída: ' . $out);

        $this->atualizarStatusVps($vpsId, 'running');
        $log('VPS reativada.');
    }

    private function atualizarStatusVps(int $vpsId, string $status): void
    {
        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare('UPDATE vps SET status = :s WHERE id = :id');
        $up->execute([':s' => $status, ':id' => $vpsId]);
    }

    private function selecionarNodeDisponivel(int $cpu, int $ram, int $storage): int
    {
        $pdo = BancoDeDados::pdo();

        try {
            $sql = "SELECT id,
                           (ram_total - ram_used) AS ram_available,
                           (cpu_total - cpu_used) AS cpu_available,
                           (storage_total - storage_used) AS storage_available
                    FROM servers
                    WHERE status = 'active'
                      AND COALESCE(ssh_user,'') <> ''
                      AND COALESCE(ssh_key_id,'') <> ''
                      AND (ram_total - ram_used) >= :ram
                      AND (cpu_total - cpu_used) >= :cpu
                      AND (storage_total - storage_used) >= :st
                    ORDER BY ram_available DESC
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':ram' => $ram,
                ':cpu' => $cpu,
                ':st' => $storage,
            ]);
        } catch (\Throwable $e) {
            $sql = "SELECT id,
                           (ram_total - ram_used) AS ram_available,
                           (cpu_total - cpu_used) AS cpu_available,
                           (storage_total - storage_used) AS storage_available
                    FROM servers
                    WHERE status = 'active'
                      AND (ram_total - ram_used) >= :ram
                      AND (cpu_total - cpu_used) >= :cpu
                      AND (storage_total - storage_used) >= :st
                    ORDER BY ram_available DESC
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':ram' => $ram,
                ':cpu' => $cpu,
                ':st' => $storage,
            ]);
        }

        $s = $stmt->fetch();
        return is_array($s) ? (int) ($s['id'] ?? 0) : 0;
    }

    private function alocarRecursosNode(int $serverId, int $cpu, int $ram, int $storage, int $vpsId): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status FROM servers WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $serverId]);
            $srv = $stmt->fetch();

            if (!is_array($srv) || (string) ($srv['status'] ?? '') !== 'active') {
                throw new \RuntimeException('Servidor indisponível.');
            }

            $ramAvail = (int) $srv['ram_total'] - (int) $srv['ram_used'];
            $cpuAvail = (int) $srv['cpu_total'] - (int) $srv['cpu_used'];
            $stAvail = (int) $srv['storage_total'] - (int) $srv['storage_used'];

            if ($ramAvail < $ram || $cpuAvail < $cpu || $stAvail < $storage) {
                throw new \RuntimeException('Servidor sem capacidade.');
            }

            $upSrv = $pdo->prepare('UPDATE servers SET ram_used = ram_used + :ram, cpu_used = cpu_used + :cpu, storage_used = storage_used + :st WHERE id = :id');
            $upSrv->execute([
                ':ram' => $ram,
                ':cpu' => $cpu,
                ':st' => $storage,
                ':id' => $serverId,
            ]);

            $upVps = $pdo->prepare('UPDATE vps SET server_id = :sid WHERE id = :vid');
            $upVps->execute([
                ':sid' => $serverId,
                ':vid' => $vpsId,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function configurarDockerParaNode(int $serverId, callable $log): bool
    {
        if ($serverId <= 0) {
            return false;
        }

        $pdo = BancoDeDados::pdo();

        try {
            $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $serverId]);
            $srv = $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }

        if (!is_array($srv)) {
            $log('Node não encontrado: ' . $serverId);
            return false;
        }

        if ((string) ($srv['status'] ?? '') !== 'active') {
            $log('Node não está ativo: ' . $serverId);
            return false;
        }

        $host = trim((string) ($srv['ip_address'] ?? ''));
        if ($host === '') {
            $host = trim((string) ($srv['hostname'] ?? ''));
        }

        $porta = (int) ($srv['ssh_port'] ?? 22);
        $usuario = trim((string) ($srv['ssh_user'] ?? ''));
        $keyId = trim((string) ($srv['ssh_key_id'] ?? ''));

        if ($host === '' || $porta <= 0 || $usuario === '' || $keyId === '') {
            $log('Node sem dados de SSH completos (host/porta/usuário/chave).');
            return false;
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        if ($keyDir === '') {
            $log('Diretório base das chaves SSH não configurado (infra.ssh_key_dir).');
            return false;
        }

        $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
        if (!is_file($keyPath)) {
            $log('Arquivo de chave não encontrado: ' . $keyId);
            return false;
        }

        try {
            $this->docker->definirRemoto($host, $porta, $usuario, $keyPath);
        } catch (\Throwable $e) {
            $log('Falha ao configurar destino remoto: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
