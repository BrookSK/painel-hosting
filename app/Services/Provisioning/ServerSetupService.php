<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
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

        return [
            [
                'name'           => 'Testar SSH',
                'cmd'            => 'echo lrv-ok',
                'ok_if_contains' => 'lrv-ok',
                'fatal'          => true,
                'precisa_root'   => false,
                'timeout'        => 15,
            ],
            [
                'name'         => 'Atualizar pacotes',
                'cmd'          => 'export DEBIAN_FRONTEND=noninteractive; apt-get update -qq 2>&1',
                'fatal'        => false,
                'precisa_root' => true,
                'timeout'      => 120,
            ],
            [
                'name'         => 'Instalar dependências',
                'cmd'          => 'export DEBIAN_FRONTEND=noninteractive; apt-get install -y -qq ca-certificates curl gnupg lsb-release 2>&1',
                'fatal'        => false,
                'precisa_root' => true,
                'timeout'      => 180,
            ],
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
            [
                'name'         => 'Criar rede Docker lrv-net',
                'cmd'          => 'docker network inspect lrv-net >/dev/null 2>&1 && echo "already exists" || docker network create lrv-net 2>&1',
                'fatal'        => false,
                'precisa_root' => false,
                'timeout'      => 30,
            ],
            [
                'name'         => 'Criar usuário terminal',
                'cmd'          => 'id ' . escapeshellarg($termUser) . ' >/dev/null 2>&1 && echo "already exists" || useradd -m -s /bin/bash ' . escapeshellarg($termUser) . ' 2>&1',
                'fatal'        => false,
                'precisa_root' => true,
                'timeout'      => 20,
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
