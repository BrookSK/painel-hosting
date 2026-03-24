<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Settings;
use LRV\App\Services\Infra\SshExecutor;
use LRV\App\Services\Infra\SshCrypto;

/**
 * Prepara um servidor para uso via SSH.
 * Execução passo-a-passo para evitar timeout do proxy reverso.
 */
final class ServerSetupService
{
    private SshExecutor $exec;

    public function __construct(?SshExecutor $exec = null)
    {
        $this->exec = $exec ?? new SshExecutor();
    }

    /**
     * Retorna a lista de passos e quais já foram concluídos.
     */
    public function listarPassos(int $serverId): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $srv = $stmt->fetch();

        if (!is_array($srv)) {
            return ['ok' => false, 'erro' => 'Servidor não encontrado.'];
        }

        $passos = $this->definirPassos($srv);
        $nomes = array_map(fn($p) => $p['name'], $passos);

        // Busca logs existentes
        $stLogs = $pdo->prepare('SELECT step, status FROM server_setup_logs WHERE server_id = :id ORDER BY id ASC');
        $stLogs->execute([':id' => $serverId]);
        $logs = [];
        foreach ($stLogs->fetchAll() as $l) {
            $logs[$l['step']] = $l['status'];
        }

        return [
            'ok' => true,
            'steps' => array_map(fn($n) => [
                'name' => $n,
                'status' => $logs[$n] ?? 'pending',
            ], $nomes),
            'total' => count($nomes),
        ];
    }

    /**
     * Executa UM passo pelo nome. Retorna resultado imediato.
     */
    public function executarPasso(int $serverId, string $stepName, bool $forcar = false): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(360);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $srv = $stmt->fetch();

        if (!is_array($srv)) {
            return ['ok' => false, 'step' => $stepName, 'status' => 'error', 'output' => 'Servidor não encontrado.'];
        }

        $passos = $this->definirPassos($srv);
        $passo = null;
        foreach ($passos as $p) {
            if ($p['name'] === $stepName) { $passo = $p; break; }
        }
        if ($passo === null) {
            return ['ok' => false, 'step' => $stepName, 'status' => 'error', 'output' => 'Passo não encontrado.'];
        }

        // Se já concluído e não forçar, pula
        if (!$forcar) {
            $stLog = $pdo->prepare('SELECT status FROM server_setup_logs WHERE server_id = :id AND step = :s LIMIT 1');
            $stLog->execute([':id' => $serverId, ':s' => $stepName]);
            $prev = $stLog->fetchColumn();
            if ($prev === 'ok') {
                return ['ok' => true, 'step' => $stepName, 'status' => 'ok', 'output' => '(já concluído)', 'skipped' => true];
            }
        }

        // Resolve credenciais
        $host     = trim((string)($srv['ip_address'] ?? $srv['hostname'] ?? ''));
        $port     = (int)($srv['ssh_port'] ?? 22);
        $user     = trim((string)($srv['ssh_user'] ?? ''));
        $authType = (string)($srv['ssh_auth_type'] ?? 'key');
        $useSudo  = (bool)(int)($srv['use_sudo'] ?? 0);

        if ($host === '' || $port <= 0 || $user === '') {
            return ['ok' => false, 'step' => $stepName, 'status' => 'error', 'output' => 'Dados SSH incompletos.'];
        }

        $keyPath = null;
        $senha = null;
        $sudoSenha = '';

        try {
            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
                if ($senha === '') {
                    return ['ok' => false, 'step' => $stepName, 'status' => 'error', 'output' => 'Senha SSH não configurada.'];
                }
            } else {
                $keyId = trim((string)($srv['ssh_key_id'] ?? ''));
                $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
                if (!is_file($keyPath)) {
                    return ['ok' => false, 'step' => $stepName, 'status' => 'error', 'output' => 'Chave SSH não encontrada: ' . $keyId];
                }
            }
            if ($useSudo) {
                $sudoRaw = SshCrypto::decifrar((string)($srv['sudo_password'] ?? ''));
                $sudoSenha = $sudoRaw !== '' ? $sudoRaw : ($senha ?? '');
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'step' => $stepName, 'status' => 'error', 'output' => $e->getMessage()];
        }

        // Marca como running
        $this->upsertLog($pdo, $serverId, $stepName, 'running', '');

        try {
            $cmdFinal = ($useSudo && !empty($passo['precisa_root']))
                ? SshExecutor::elevarComSudo($passo['cmd'], $sudoSenha)
                : $passo['cmd'];

            if ($senha !== null) {
                $r = $this->exec->executarComSenha($host, $port, $user, $senha, $cmdFinal, $passo['timeout'] ?? 120);
            } else {
                $r = $this->exec->executar($host, $port, $user, (string)$keyPath, $cmdFinal, $passo['timeout'] ?? 120);
            }

            $saida = trim((string)($r['saida'] ?? ''));
            $ok = (bool)($r['ok'] ?? false);

            if (!$ok && !empty($passo['ok_if_contains']) && str_contains($saida, $passo['ok_if_contains'])) {
                $ok = true;
            }
            if (!$ok && str_contains($saida, 'already exists')) {
                $ok = true;
            }

            $status = $ok ? 'ok' : 'error';
            $this->upsertLog($pdo, $serverId, $stepName, $status, $saida);

            return [
                'ok' => $ok,
                'step' => $stepName,
                'status' => $status,
                'output' => $saida,
                'fatal' => !empty($passo['fatal']) && !$ok,
            ];
        } catch (\Throwable $e) {
            $this->upsertLog($pdo, $serverId, $stepName, 'error', $e->getMessage());
            return [
                'ok' => false,
                'step' => $stepName,
                'status' => 'error',
                'output' => $e->getMessage(),
                'fatal' => !empty($passo['fatal']),
            ];
        }
    }

    /**
     * Atualiza o status final do servidor após todos os passos.
     */
    public function finalizarSetup(int $serverId): array
    {
        $pdo = BancoDeDados::pdo();

        $stLogs = $pdo->prepare('SELECT step, status, output FROM server_setup_logs WHERE server_id = :id ORDER BY id ASC');
        $stLogs->execute([':id' => $serverId]);
        $logs = $stLogs->fetchAll();

        $allOk = true;
        $concluidos = 0;
        foreach ($logs as $l) {
            if ($l['status'] === 'ok') { $concluidos++; }
            else { $allOk = false; }
        }

        if ($allOk && $concluidos > 0) {
            $pdo->prepare("UPDATE servers SET setup_status='ready', status='active', is_online=1, last_check_at=:c, last_error=NULL WHERE id=:id")
                ->execute([':c' => date('Y-m-d H:i:s'), ':id' => $serverId]);
        } else {
            $pdo->prepare("UPDATE servers SET setup_status='error', is_online=0, last_check_at=:c WHERE id=:id")
                ->execute([':c' => date('Y-m-d H:i:s'), ':id' => $serverId]);
        }

        return ['ok' => $allOk, 'total' => count($logs), 'concluidos' => $concluidos, 'steps' => $logs];
    }

    /**
     * Prepara o servidor para inicialização (limpa logs se não retomar).
     */
    public function prepararSetup(int $serverId, bool $retomar): void
    {
        $pdo = BancoDeDados::pdo();
        $this->atualizarSetupStatus($pdo, $serverId, 'initializing');
        if (!$retomar) {
            $pdo->prepare('DELETE FROM server_setup_logs WHERE server_id = :id')->execute([':id' => $serverId]);
        }
    }

    /** Retorna os logs mais recentes de um servidor */
    public function obterLogs(int $serverId): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT step, status, output, updated_at FROM server_setup_logs WHERE server_id = :id ORDER BY id ASC');
        $stmt->execute([':id' => $serverId]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------------------------

    private function definirPassos(array $srv): array
    {
        $termUser = trim((string)($srv['terminal_ssh_user'] ?? 'lrv-terminal'));

        // Rede Docker usada pelo provisionamento de VPS
        $redeVps = trim((string)Settings::obter('infra.docker_rede', 'lrvcloud_network'));
        if ($redeVps === '') $redeVps = 'lrvcloud_network';

        // Diretório base dos volumes de VPS
        $volumeBase = rtrim(trim((string)Settings::obter('infra.volume_base', '/vps')), '/');
        if ($volumeBase === '') $volumeBase = '/vps';

        // Imagem base para containers
        $imagemBase = trim((string)Settings::obter('infra.imagem_base', 'debian:12-slim'));
        if ($imagemBase === '') $imagemBase = 'debian:12-slim';

        // ── Wrapper script para ForceCommand ──
        $wrapperScript = <<<'BASH'
    #!/usr/bin/env bash
    set -euo pipefail
    MODE="" CLIENT_ID="" VPS_ID="" CONTAINER_ID="" SESSION=""
    ORIG="${SSH_ORIGINAL_COMMAND:-}"
    read -r -a ARGS <<< "$ORIG"
    if [[ "${#ARGS[@]}" -lt 1 ]] || [[ "${ARGS[0]}" != "lrv-terminal" ]]; then
      echo "Comando invalido." >&2; exit 1
    fi
    for a in "${ARGS[@]:1}"; do
      case "$a" in
    --mode=*) MODE="${a#--mode=}";;
    --client-id=*) CLIENT_ID="${a#--client-id=}";;
    --vps-id=*) VPS_ID="${a#--vps-id=}";;
    --container-id=*) CONTAINER_ID="${a#--container-id=}";;
    --session=*) SESSION="${a#--session=}";;
      esac
    done
    if [[ "$MODE" == "client" ]]; then
      if [[ -z "$CLIENT_ID" || -z "$VPS_ID" || -z "$CONTAINER_ID" ]]; then
    echo "Contexto ausente." >&2; exit 1
      fi
      [[ "$CLIENT_ID" =~ ^[0-9]+$ ]] || { echo "Cliente invalido." >&2; exit 1; }
      [[ "$VPS_ID" =~ ^[0-9]+$ ]] || { echo "VPS invalida." >&2; exit 1; }
      [[ "$CONTAINER_ID" =~ ^[a-zA-Z0-9][a-zA-Z0-9_.-]+$ ]] || { echo "Container invalido." >&2; exit 1; }
      docker inspect "$CONTAINER_ID" >/dev/null 2>&1 || { echo "Container nao encontrado." >&2; exit 1; }
      LABELS=$(docker inspect -f '{{ index .Config.Labels "lrv.vps_id" }}|{{ index .Config.Labels "lrv.client_id" }}' "$CONTAINER_ID" 2>/dev/null || true)
      L_VPS="${LABELS%%|*}"; L_CLIENT="${LABELS#*|}"
      [[ -n "$L_VPS" && -n "$L_CLIENT" ]] || { echo "Container sem labels." >&2; exit 1; }
      [[ "$L_VPS" == "$VPS_ID" && "$L_CLIENT" == "$CLIENT_ID" ]] || { echo "Container nao pertence a VPS/cliente." >&2; exit 1; }
      logger -t lrv-terminal "mode=client vps_id=$VPS_ID container=$CONTAINER_ID user=$USER session=$SESSION"
      exec docker exec -it "$CONTAINER_ID" bash
    fi
    echo "Modo invalido." >&2; exit 1
    BASH;

        $installWrapper = 'cat > /usr/local/bin/lrv-terminal << ' . "'LRVEOF'\n" . $wrapperScript . "\nLRVEOF\n"
            . 'chmod 0755 /usr/local/bin/lrv-terminal && chown root:root /usr/local/bin/lrv-terminal && echo lrv-wrapper-ok';

        // Bloco Match User para sshd_config
        $matchBlock = "Match User " . $termUser . "\n"
            . "  ForceCommand /usr/local/bin/lrv-terminal\n"
            . "  PermitTTY yes\n"
            . "  AllowAgentForwarding no\n"
            . "  PermitUserEnvironment no\n"
            . "  X11Forwarding no\n"
            . "  AllowTcpForwarding no\n"
            . "  GatewayPorts no\n"
            . "  PermitTunnel no";

        $sshdCmd = 'grep -q ' . escapeshellarg('Match User ' . $termUser) . ' /etc/ssh/sshd_config'
            . ' && echo "already exists"'
            . ' || { printf ' . escapeshellarg("\n" . $matchBlock . "\n") . ' >> /etc/ssh/sshd_config'
            . ' && (systemctl restart sshd 2>/dev/null || systemctl restart ssh 2>&1) && echo lrv-sshd-ok; }';

        // ── Script de monitoramento (cron) ──
        // Coleta CPU/RAM/Disco e envia para o painel via curl
        $monitoringToken = trim((string)Settings::obter('monitoring.token', ''));
        if ($monitoringToken === '') {
            $monitoringToken = bin2hex(random_bytes(32));
            Settings::definir('monitoring.token', $monitoringToken);
        }
        $baseUrl = rtrim(trim((string)Settings::obter('app.base_url', '')), '/');
        $serverId = (int)($srv['id'] ?? 0);

        $monitorScript = <<<BASH
    #!/bin/bash
    CPU=\$(top -bn1 | grep 'Cpu(s)' | awk '{print \$2+\$4}' 2>/dev/null || echo 0)
    RAM=\$(free | awk '/Mem:/{printf "%.1f", \$3/\$2*100}' 2>/dev/null || echo 0)
    DISK=\$(df / | awk 'NR==2{print \$5}' | tr -d '%' 2>/dev/null || echo 0)
    curl -s -X POST {$baseUrl}/api/metrics/servers \
      -H "Content-Type: application/json" \
      -H "x-monitoring-token: {$monitoringToken}" \
      -d "{\"server_id\":{$serverId},\"cpu_usage\":\$CPU,\"ram_usage\":\$RAM,\"disk_usage\":\$DISK}" \
      >/dev/null 2>&1
    BASH;

        $installMonitor = 'cat > /usr/local/bin/lrv-monitor << ' . "'LRVEOF'\n" . $monitorScript . "\nLRVEOF\n"
            . 'chmod 0755 /usr/local/bin/lrv-monitor && echo lrv-monitor-ok';

        $cronCmd = '(crontab -l 2>/dev/null | grep -v lrv-monitor; echo "*/5 * * * * /usr/local/bin/lrv-monitor") | crontab - 2>&1 && echo lrv-cron-ok';

        // ── Firewall (ufw) ──
        $ufwCmd = 'which ufw >/dev/null 2>&1 || (export DEBIAN_FRONTEND=noninteractive; apt-get install -y -qq ufw 2>&1);'
            . ' ufw allow 22/tcp 2>&1;'
            . ' ufw allow 80/tcp 2>&1;'
            . ' ufw allow 443/tcp 2>&1;'
            . ' ufw --force enable 2>&1;'
            . ' echo lrv-ufw-ok';

        // ── Swap (2GB se não existir) ──
        $swapCmd = 'swapon --show | grep -q /swapfile && echo "already exists"'
            . ' || { fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile'
            . ' && grep -q /swapfile /etc/fstab || echo "/swapfile none swap sw 0 0" >> /etc/fstab'
            . ' && echo lrv-swap-ok; }';

        // ── Fail2ban ──
        $fail2banCmd = 'which fail2ban-server >/dev/null 2>&1 && echo "already exists"'
            . ' || (export DEBIAN_FRONTEND=noninteractive; apt-get install -y -qq fail2ban 2>&1'
            . ' && systemctl enable fail2ban 2>&1 && systemctl start fail2ban 2>&1 && echo lrv-f2b-ok)';

        // ── Timezone ──
        $tzCmd = 'timedatectl set-timezone America/Sao_Paulo 2>&1 || ln -sf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime 2>&1; echo lrv-tz-ok';

        return [
            // ── 1. Conectividade ──
            [
                'name'           => 'Testar SSH',
                'cmd'            => 'echo lrv-ok',
                'ok_if_contains' => 'lrv-ok',
                'fatal'          => true,
                'precisa_root'   => false,
                'timeout'        => 15,
            ],

            // ── 2. Sistema base ──
            [
                'name'         => 'Atualizar pacotes',
                'cmd'          => 'export DEBIAN_FRONTEND=noninteractive; apt-get update -qq 2>&1',
                'fatal'        => false,
                'precisa_root' => true,
                'timeout'      => 120,
            ],
            [
                'name'         => 'Instalar dependências',
                'cmd'          => 'export DEBIAN_FRONTEND=noninteractive; apt-get install -y -qq ca-certificates curl gnupg lsb-release htop net-tools unzip 2>&1',
                'fatal'        => false,
                'precisa_root' => true,
                'timeout'      => 180,
            ],
            [
                'name'           => 'Configurar timezone',
                'cmd'            => $tzCmd,
                'ok_if_contains' => 'lrv-tz-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 15,
            ],
            [
                'name'           => 'Configurar swap (2GB)',
                'cmd'            => $swapCmd,
                'ok_if_contains' => 'lrv-swap-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 30,
            ],

            // ── 3. Docker ──
            [
                'name'         => 'Instalar Docker',
                'cmd'          => 'which docker >/dev/null 2>&1 && echo "docker already exists" || (curl -fsSL https://get.docker.com | sh 2>&1)',
                'fatal'        => true,
                'precisa_root' => true,
                'timeout'      => 300,
            ],
            [
                'name'           => 'Verificar Docker',
                'cmd'            => 'docker version 2>&1',
                'ok_if_contains' => 'Version',
                'fatal'          => true,
                'precisa_root'   => false,
                'timeout'        => 20,
            ],
            [
                'name'         => 'Habilitar Docker no boot',
                'cmd'          => 'systemctl enable docker 2>&1 && systemctl start docker 2>&1; echo done',
                'fatal'        => false,
                'precisa_root' => true,
                'timeout'      => 30,
            ],

            // ── 4. Redes Docker ──
            [
                'name'         => 'Criar rede Docker lrv-net',
                'cmd'          => 'docker network inspect lrv-net >/dev/null 2>&1 && echo "already exists" || docker network create lrv-net 2>&1',
                'fatal'        => false,
                'precisa_root' => false,
                'timeout'      => 30,
            ],
            [
                'name'         => 'Criar rede Docker ' . $redeVps,
                'cmd'          => 'docker network inspect ' . escapeshellarg($redeVps) . ' >/dev/null 2>&1 && echo "already exists" || docker network create ' . escapeshellarg($redeVps) . ' 2>&1',
                'fatal'        => false,
                'precisa_root' => false,
                'timeout'      => 30,
            ],

            // ── 5. Diretórios e imagem base ──
            [
                'name'           => 'Criar diretório base VPS (' . $volumeBase . ')',
                'cmd'            => 'mkdir -p ' . escapeshellarg($volumeBase) . ' && mkdir -p ' . escapeshellarg($volumeBase . '/apps') . ' && echo lrv-dir-ok',
                'ok_if_contains' => 'lrv-dir-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 15,
            ],
            [
                'name'         => 'Puxar imagem base (' . $imagemBase . ')',
                'cmd'          => 'docker image inspect ' . escapeshellarg($imagemBase) . ' >/dev/null 2>&1 && echo "already exists" || docker pull ' . escapeshellarg($imagemBase) . ' 2>&1',
                'fatal'        => false,
                'precisa_root' => false,
                'timeout'      => 180,
            ],

            // ── 6. Terminal seguro (cliente) ──
            [
                'name'         => 'Criar usuário terminal',
                'cmd'          => 'id ' . escapeshellarg($termUser) . ' >/dev/null 2>&1 && echo "already exists" || (useradd -m -s /bin/bash ' . escapeshellarg($termUser) . ' 2>&1)',
                'fatal'        => false,
                'precisa_root' => true,
                'timeout'      => 20,
            ],
            [
                'name'           => 'Adicionar terminal ao grupo docker',
                'cmd'            => 'usermod -aG docker ' . escapeshellarg($termUser) . ' 2>&1 && echo lrv-group-ok',
                'ok_if_contains' => 'lrv-group-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 15,
            ],
            [
                'name'           => 'Instalar wrapper ForceCommand',
                'cmd'            => $installWrapper,
                'ok_if_contains' => 'lrv-wrapper-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 20,
            ],
            [
                'name'           => 'Configurar ForceCommand no sshd',
                'cmd'            => $sshdCmd,
                'ok_if_contains' => 'lrv-sshd-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 30,
            ],

            // ── 7. Segurança ──
            [
                'name'           => 'Instalar e configurar firewall (ufw)',
                'cmd'            => $ufwCmd,
                'ok_if_contains' => 'lrv-ufw-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 60,
            ],
            [
                'name'           => 'Instalar fail2ban',
                'cmd'            => $fail2banCmd,
                'ok_if_contains' => 'lrv-f2b-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 120,
            ],

            // ── 8. Monitoramento ──
            [
                'name'           => 'Instalar agente de monitoramento',
                'cmd'            => $installMonitor,
                'ok_if_contains' => 'lrv-monitor-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 15,
            ],
            [
                'name'           => 'Configurar cron de monitoramento (5min)',
                'cmd'            => $cronCmd,
                'ok_if_contains' => 'lrv-cron-ok',
                'fatal'          => false,
                'precisa_root'   => true,
                'timeout'        => 15,
            ],
        ];
    }


    private function upsertLog(\PDO $pdo, int $serverId, string $step, string $status, string $output): void
    {
        $upd = $pdo->prepare('UPDATE server_setup_logs SET status=:st, output=:o WHERE server_id=:s AND step=:n');
        $upd->execute([':st' => $status, ':o' => mb_substr($output, 0, 4000), ':s' => $serverId, ':n' => $step]);

        if ($upd->rowCount() === 0) {
            $pdo->prepare('INSERT INTO server_setup_logs (server_id, step, status, output) VALUES (:s,:n,:st,:o)')
                ->execute([':s' => $serverId, ':n' => $step, ':st' => $status, ':o' => mb_substr($output, 0, 4000)]);
        }
    }

    private function atualizarSetupStatus(\PDO $pdo, int $serverId, string $setupStatus): void
    {
        try {
            $pdo->prepare('UPDATE servers SET setup_status=:ss WHERE id=:id')
                ->execute([':ss' => $setupStatus, ':id' => $serverId]);
        } catch (\Throwable) {
            // coluna pode não existir ainda
        }
    }
}
