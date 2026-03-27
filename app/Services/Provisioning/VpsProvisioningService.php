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
        if (in_array($statusAtual, ['pending_payment', 'suspended_payment'], true)) {
            $assinaturaAtiva = false;
            try {
                $st = $pdo->prepare('SELECT status FROM subscriptions WHERE vps_id = :id ORDER BY id DESC LIMIT 1');
                $st->execute([':id' => $vpsId]);
                $sub = $st->fetch();
                $assinaturaAtiva = is_array($sub) && strtoupper((string) ($sub['status'] ?? '')) === 'ACTIVE';
            } catch (\Throwable $e) {
                $assinaturaAtiva = false;
            }

            if (!$assinaturaAtiva) {
                if ($statusAtual === 'pending_payment') {
                    $log('Aguardando pagamento. Provisionamento ignorado.');
                } else {
                    $log('VPS suspensa por pagamento. Provisionamento ignorado.');
                }
                return;
            }
        }

        $containerIdExistente = (string) ($vps['container_id'] ?? '');

        // Se já tem container e está rodando, só criar subdomínio se necessário
        if ($statusAtual === 'running' && $containerIdExistente !== '') {
            $log('VPS já está em execução. Verificando subdomínio...');
            $this->criarSubdominioAutomatico($vpsId, (int)($vps['server_id'] ?? 0), $log);
            return;
        }

        // Se tem container mas não está rodando, iniciar
        if ($containerIdExistente !== '' && in_array($statusAtual, ['pending_provisioning', 'provisioning', 'stopped'], true)) {
            $serverId = (int) ($vps['server_id'] ?? 0);
            if ($serverId > 0 && $this->configurarDockerParaNode($serverId, $log)) {
                $log('VPS já possui container_id. Iniciando container...');
                try {
                    $out = $this->docker->iniciar($containerIdExistente);
                    $log('Saída: ' . $out);
                } catch (\Throwable $e) {
                    $log('Erro ao iniciar: ' . $e->getMessage());
                }
                $this->atualizarStatusVps($vpsId, 'running');
                $this->criarSubdominioAutomatico($vpsId, $serverId, $log);
                $log('VPS reiniciada.');
                return;
            }
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        if ($serverId <= 0) {
            $serverId = $this->selecionarNodeDisponivel((int) $vps['cpu'], (int) $vps['ram'], (int) $vps['storage'], (int) ($vps['client_id'] ?? 0));
            if ($serverId <= 0) {
                $this->notificarSemNode($vpsId, (int) $vps['cpu'], (int) $vps['ram'], (int) $vps['storage']);
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

        $containerId = $containerIdExistente;
        if ($containerId !== '') {
            // Container já existe mas não foi pego pelo bloco anterior — tentar iniciar
            if ($this->configurarDockerParaNode($serverId, $log)) {
                $log('Iniciando container existente...');
                try { $out = $this->docker->iniciar($containerId); $log('Saída: ' . $out); } catch (\Throwable) {}
                $this->atualizarStatusVps($vpsId, 'running');
                $this->criarSubdominioAutomatico($vpsId, $serverId, $log);
                return;
            }
        }

        $this->atualizarStatusVps($vpsId, 'provisioning');

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
            [
                'lrv.vps_id' => (string) $vpsId,
                'lrv.client_id' => (string) ((int) ($vps['client_id'] ?? 0)),
            ],
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

        // Criar subdomínio automático no Cloudflare
        $this->criarSubdominioAutomatico($vpsId, $serverId, $log);
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

        $statusAtual = (string) ($vps['status'] ?? '');
        if ($statusAtual === 'suspended_payment') {
            $log('VPS já está suspensa por pagamento.');
            return;
        }

        $serverId = (int) ($vps['server_id'] ?? 0);

        $dockerConfigurado = false;
        if ($serverId > 0) {
            if (!$this->configurarDockerParaNode($serverId, $log)) {
                $log('Não foi possível configurar Docker remoto. A VPS será marcada como suspensa, mas o container não foi parado.');
            } else {
                $dockerConfigurado = true;
            }
        }

        $containerId = (string) ($vps['container_id'] ?? '');

        if ($dockerConfigurado && $containerId !== '' && $this->docker->disponivel()) {
            $log('Parando container...');
            $out = $this->docker->parar($containerId);
            $log('Saída: ' . $out);
        } else {
            $log('Container não encontrado ou Docker indisponível.');
        }

        $this->atualizarStatusVps($vpsId, 'suspended_payment');
        $log('VPS suspensa por pagamento.');
    }

    public function reiniciar(int $vpsId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, server_id, container_id, status FROM vps WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            throw new \RuntimeException('VPS não encontrada.');
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        if ($serverId <= 0) {
            throw new \RuntimeException('VPS sem node associado.');
        }

        if (!$this->configurarDockerParaNode($serverId, $log)) {
            throw new \RuntimeException('Não foi possível configurar Docker remoto.');
        }

        $containerId = (string) ($vps['container_id'] ?? '');
        if ($containerId === '') {
            throw new \RuntimeException('VPS sem container_id.');
        }

        $log('Parando container...');
        $out = $this->docker->parar($containerId);
        $log('Saída stop: ' . $out);

        $log('Iniciando container...');
        $out = $this->docker->iniciar($containerId);
        $log('Saída start: ' . $out);

        $this->atualizarStatusVps($vpsId, 'running');
        $log('VPS reiniciada.');
    }

    public function remover(int $vpsId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, client_id, server_id, container_id, cpu, ram, storage, status FROM vps WHERE id = :id');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            throw new \RuntimeException('VPS não encontrada.');
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        $containerId = (string) ($vps['container_id'] ?? '');

        if ($serverId > 0 && $containerId !== '') {
            if ($this->configurarDockerParaNode($serverId, $log)) {
                $log('Removendo container...');
                try {
                    $r = $this->docker->removerContainer($containerId);
                    $log('Saída: ' . (string) ($r['saida'] ?? ''));
                } catch (\Throwable $e) {
                    $log('Aviso ao remover container: ' . $e->getMessage());
                }
            } else {
                $log('Docker indisponível. Container não removido remotamente.');
            }
        }

        // Liberar recursos do node
        if ($serverId > 0) {
            try {
                $cpu = (int) ($vps['cpu'] ?? 0);
                $ram = (int) ($vps['ram'] ?? 0);
                $storage = (int) ($vps['storage'] ?? 0);
                $upSrv = $pdo->prepare('UPDATE servers SET cpu_used = GREATEST(0, cpu_used - :cpu), ram_used = GREATEST(0, ram_used - :ram), storage_used = GREATEST(0, storage_used - :st) WHERE id = :id');
                $upSrv->execute([':cpu' => $cpu, ':ram' => $ram, ':st' => $storage, ':id' => $serverId]);
                $log('Recursos liberados no node #' . $serverId . '.');
            } catch (\Throwable $e) {
                $log('Aviso ao liberar recursos: ' . $e->getMessage());
            }
        }

        // Soft delete
        $up = $pdo->prepare('UPDATE vps SET status = :s, deleted_at = :d WHERE id = :id');
        $up->execute([':s' => 'removed', ':d' => date('Y-m-d H:i:s'), ':id' => $vpsId]);
        $log('VPS removida (soft delete).');
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

        $statusAtual = (string) ($vps['status'] ?? '');
        if ($statusAtual === 'running') {
            $log('VPS está marcada como running. Garantindo que o container esteja iniciado...');
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        if ($serverId > 0) {
            if (!$this->configurarDockerParaNode($serverId, $log)) {
                $log('Não foi possível configurar Docker remoto. Reativação não executada.');

                if ($statusAtual !== 'running') {
                    $this->atualizarStatusVps($vpsId, 'pending_provisioning');
                }
                return;
            }
        }

        $containerId = (string) ($vps['container_id'] ?? '');

        if ($containerId === '') {
            $this->atualizarStatusVps($vpsId, 'pending_provisioning');
            $log('Container não encontrado. VPS marcada como pending_provisioning para reprovisionamento.');
            return;
        }

        if (!$this->docker->disponivel()) {
            $log('Docker indisponível. Não foi possível reativar.');

            if ($statusAtual !== 'running') {
                $this->atualizarStatusVps($vpsId, 'pending_provisioning');
            }
            return;
        }

        $log('Iniciando container...');
        $out = $this->docker->iniciar($containerId);
        $log('Saída: ' . $out);

        $this->atualizarStatusVps($vpsId, 'running');
        $log('VPS reativada.');
    }

    private function criarSubdominioAutomatico(int $vpsId, int $serverId, callable $log): void
    {
        try {
            $tempBase = trim((string) Settings::obter('infra.temp_domain_base', ''));
            if ($tempBase === '') return;

            $pdo = BancoDeDados::pdo();

            // Verificar se já tem subdomínio
            $check = $pdo->prepare('SELECT temp_subdomain FROM vps WHERE id = :id');
            $check->execute([':id' => $vpsId]);
            $row = $check->fetch();
            if (is_array($row) && trim((string)($row['temp_subdomain'] ?? '')) !== '') return;

            // Buscar IP do servidor
            $srvStmt = $pdo->prepare('SELECT ip_address FROM servers WHERE id = :id');
            $srvStmt->execute([':id' => $serverId]);
            $srv = $srvStmt->fetch();
            $ip = is_array($srv) ? trim((string)($srv['ip_address'] ?? '')) : '';
            if ($ip === '') return;

            $subdomain = 'vps' . $vpsId . '.' . $tempBase;

            // Criar registro A no Cloudflare com proxy ativado
            $proxy = new \LRV\App\Services\Infra\NginxProxyService();
            $proxy->criarProxy($subdomain, $ip);

            // Salvar no banco
            $pdo->prepare('UPDATE vps SET temp_subdomain = :s WHERE id = :id')
                ->execute([':s' => $subdomain, ':id' => $vpsId]);

            $log('Subdomínio automático criado: ' . $subdomain);
        } catch (\Throwable $e) {
            $log('Aviso: não foi possível criar subdomínio automático: ' . $e->getMessage());
        }
    }

    private function atualizarStatusVps(int $vpsId, string $status): void
    {
        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare('UPDATE vps SET status = :s WHERE id = :id');
        $up->execute([':s' => $status, ':id' => $vpsId]);
    }

    private function selecionarNodeDisponivel(int $cpu, int $ram, int $storage, int $clientId = 0): int
    {
        $pdo = BancoDeDados::pdo();

        $maxUtil = ConfiguracoesSistema::infraNodeMaxUtilPercent();

        // Verificar se o cliente é tester
        $isTester = false;
        if ($clientId > 0) {
            try {
                $ct = $pdo->prepare('SELECT is_tester FROM clients WHERE id = :id LIMIT 1');
                $ct->execute([':id' => $clientId]);
                $row = $ct->fetch();
                $isTester = is_array($row) && (int)($row['is_tester'] ?? 0) === 1;
            } catch (\Throwable) {}
        }

        // Tester → só servidores de teste
        // Normal → só servidores que NÃO são de teste
        $testFilter = $isTester ? 'AND is_test = 1' : 'AND (is_test = 0 OR is_test IS NULL)';

        $candidatos = [];

        try {
            $sql = "SELECT id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used
                    FROM servers
                    WHERE status = 'active'
                      AND is_online = 1
                      AND (role = 'vps' OR role IS NULL)
                      {$testFilter}
                      AND COALESCE(ssh_user,'') <> ''
                      AND (COALESCE(ssh_key_id,'') <> '' OR COALESCE(ssh_password,'') <> '')
                      AND (ram_total - ram_used) >= :ram
                      AND (cpu_total - cpu_used) >= :cpu
                      AND (storage_total - storage_used) >= :st";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':ram' => $ram,
                ':cpu' => $cpu,
                ':st' => $storage,
            ]);
            $candidatos = $stmt->fetchAll();
        } catch (\Throwable $e) {
            try {
                $sql = "SELECT id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used
                        FROM servers
                        WHERE status = 'active'
                          AND COALESCE(ssh_user,'') <> ''
                          AND (COALESCE(ssh_key_id,'') <> '' OR COALESCE(ssh_password,'') <> '')
                          AND (ram_total - ram_used) >= :ram
                          AND (cpu_total - cpu_used) >= :cpu
                          AND (storage_total - storage_used) >= :st";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':ram' => $ram,
                    ':cpu' => $cpu,
                    ':st' => $storage,
                ]);
                $candidatos = $stmt->fetchAll();
            } catch (\Throwable $e2) {
                $sql = "SELECT id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used
                        FROM servers
                        WHERE status = 'active'
                          AND (ram_total - ram_used) >= :ram
                          AND (cpu_total - cpu_used) >= :cpu
                          AND (storage_total - storage_used) >= :st";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':ram' => $ram,
                    ':cpu' => $cpu,
                    ':st' => $storage,
                ]);
                $candidatos = $stmt->fetchAll();
            }
        }

        $melhorId = 0;
        $melhorScore = null;

        foreach (($candidatos ?: []) as $s) {
            if (!is_array($s)) {
                continue;
            }

            $id = (int) ($s['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $ramTotal = (int) ($s['ram_total'] ?? 0);
            $ramUsed = (int) ($s['ram_used'] ?? 0);
            $cpuTotal = (int) ($s['cpu_total'] ?? 0);
            $cpuUsed = (int) ($s['cpu_used'] ?? 0);
            $stTotal = (int) ($s['storage_total'] ?? 0);
            $stUsed = (int) ($s['storage_used'] ?? 0);

            if ($ramTotal <= 0 || $cpuTotal <= 0 || $stTotal <= 0) {
                continue;
            }

            $cpuPct = (($cpuUsed + $cpu) / $cpuTotal) * 100.0;
            $ramPct = (($ramUsed + $ram) / $ramTotal) * 100.0;
            $stPct = (($stUsed + $storage) / $stTotal) * 100.0;

            $score = max($cpuPct, $ramPct, $stPct);
            if ($score > (float) $maxUtil) {
                continue;
            }

            if ($melhorScore === null || $score < $melhorScore) {
                $melhorScore = $score;
                $melhorId = $id;
            }
        }

        return $melhorId;
    }

    private function notificarSemNode(int $vpsId, int $cpu, int $ram, int $storage): void
    {
        $pdo = BancoDeDados::pdo();

        $maxUtil = ConfiguracoesSistema::infraNodeMaxUtilPercent();
        if ($maxUtil <= 0) {
            $maxUtil = 85;
        }

        $agora = date('Y-m-d H:i:s');
        $limite = date('Y-m-d H:i:s', time() - 3600);

        try {
            $stmt = $pdo->prepare('SELECT id FROM notifications WHERE message LIKE :m AND created_at >= :c LIMIT 1');
            $stmt->execute([
                ':m' => '%[Infra] Sem node para VPS #' . $vpsId . '%',
                ':c' => $limite,
            ]);
            $existe = $stmt->fetch();
            if (is_array($existe)) {
                return;
            }
        } catch (\Throwable $e) {
        }

        $recCpu = (int) ceil(($cpu * 100) / $maxUtil);
        $recRam = (int) ceil(($ram * 100) / $maxUtil);
        $recSt = (int) ceil(($storage * 100) / $maxUtil);

        $ramGb = (int) ceil($recRam / 1024);
        $stGb = (int) ceil($recSt / 1024);

        $msg = "[Infra] Sem node para VPS #" . $vpsId . ".\n";
        $msg .= "Pedido: CPU=" . $cpu . ", RAM=" . (int) ceil($ram / 1024) . "GB, Storage=" . (int) ceil($storage / 1024) . "GB.\n";
        $msg .= "Limite de utilização: " . $maxUtil . "%.\n";
        $msg .= "Sugestão novo servidor (mínimo): CPU=" . $recCpu . ", RAM=" . $ramGb . "GB, Storage=" . $stGb . "GB.";

        $usuarios = [];
        try {
            $stmtUsers = $pdo->query("SELECT id FROM users WHERE status = 'active' AND role IN ('superadmin','admin','devops')");
            $usuarios = $stmtUsers->fetchAll();
        } catch (\Throwable $e) {
            $usuarios = [];
        }

        try {
            $ins = $pdo->prepare('INSERT INTO notifications (user_id, message, `read`, created_at) VALUES (:u,:m,0,:c)');
            foreach (($usuarios ?: []) as $u) {
                if (!is_array($u)) {
                    continue;
                }
                $uid = (int) ($u['id'] ?? 0);
                if ($uid <= 0) {
                    continue;
                }
                $ins->execute([
                    ':u' => $uid,
                    ':m' => $msg,
                    ':c' => $agora,
                ]);
            }
        } catch (\Throwable $e) {
        }
    }

    private function alocarRecursosNode(int $serverId, int $cpu, int $ram, int $storage, int $vpsId): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->beginTransaction();

        try {
            try {
                $stmt = $pdo->prepare('SELECT id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status, is_online FROM servers WHERE id = :id FOR UPDATE');
                $stmt->execute([':id' => $serverId]);
                $srv = $stmt->fetch();
            } catch (\Throwable $e) {
                $stmt = $pdo->prepare('SELECT id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status FROM servers WHERE id = :id FOR UPDATE');
                $stmt->execute([':id' => $serverId]);
                $srv = $stmt->fetch();
            }

            if (!is_array($srv) || (string) ($srv['status'] ?? '') !== 'active') {
                throw new \RuntimeException('Servidor indisponível.');
            }

            if (array_key_exists('is_online', $srv) && (int) ($srv['is_online'] ?? 0) !== 1) {
                throw new \RuntimeException('Servidor offline.');
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
            try {
                $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type, status, is_online FROM servers WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $serverId]);
                $srv = $stmt->fetch();
            } catch (\Throwable $e) {
                $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type, status FROM servers WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $serverId]);
                $srv = $stmt->fetch();
            }
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

        if (array_key_exists('is_online', $srv) && (int) ($srv['is_online'] ?? 0) !== 1) {
            $log('Node está offline: ' . $serverId);
            return false;
        }

        $host = trim((string) ($srv['ip_address'] ?? ''));
        if ($host === '') {
            $host = trim((string) ($srv['hostname'] ?? ''));
        }

        $porta = (int) ($srv['ssh_port'] ?? 22);
        $usuario = trim((string) ($srv['ssh_user'] ?? ''));
        $keyId = trim((string) ($srv['ssh_key_id'] ?? ''));
        $sshPassword = '';
        try { $sshPassword = trim((string) ($srv['ssh_password'] ?? '')); } catch (\Throwable) {}
        $authType = trim((string) ($srv['ssh_auth_type'] ?? 'key'));

        if ($host === '' || $porta <= 0 || $usuario === '') {
            $log('Node sem dados de SSH completos (host/porta/usuário).');
            return false;
        }

        if ($authType === 'password' && $sshPassword !== '') {
            // Autenticação por senha
            try {
                $decrypted = \LRV\App\Services\Infra\SshCrypto::decifrar($sshPassword);
                $this->docker->definirRemotoComSenha($host, $porta, $usuario, $decrypted);
            } catch (\Throwable $e) {
                $log('Falha ao configurar SSH com senha: ' . $e->getMessage());
                return false;
            }
            return true;
        }

        if ($keyId === '') {
            $log('Node sem chave SSH nem senha configurada.');
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
