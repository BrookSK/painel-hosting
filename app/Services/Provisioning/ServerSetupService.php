<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\App\Services\Infra\SshExecutor;
use LRV\App\Services\Infra\SshCrypto;

/**
 * Prepara um servidor para uso via SSH.
 * Suporta "continuar de onde parou": passos já marcados como 'ok' são pulados.
 */
final class ServerSetupService
{
    private SshExecutor $exec;

    public function __construct(?SshExecutor $exec = null)
    {
        $this->exec = $exec ?? new SshExecutor();
    }

    /**
     * Executa o setup. Se $retomar=true, pula passos já concluídos com 'ok'.
     * Retorna ['ok' => bool, 'steps' => [...], 'total' => int, 'concluidos' => int]
     */
    public function executar(int $serverId, bool $retomar = false): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $srv = $stmt->fetch();

        if (!is_array($srv)) {
            return $this->erroFatal('Servidor não encontrado.');
        }

        $host     = trim((string)($srv['ip_address'] ?? $srv['hostname'] ?? ''));
        $port     = (int)($srv['ssh_port'] ?? 22);
        $user     = trim((string)($srv['ssh_user'] ?? ''));
        $authType = (string)($srv['ssh_auth_type'] ?? 'key');
        $useSudo  = (bool)(int)($srv['use_sudo'] ?? 0);

        if ($host === '' || $port <= 0 || $user === '') {
            return $this->erroFatal('Dados SSH incompletos. Configure o servidor antes de inicializar.');
        }

        // Resolve credencial conforme tipo de autenticação
        $keyPath    = null;
        $senha      = null;
        $sudoSenha  = '';

        if ($authType === 'password') {
            $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
            if ($senha === '') {
                return $this->erroFatal('Senha SSH não configurada.');
            }
        } else {
            $keyId   = trim((string)($srv['ssh_key_id'] ?? ''));
            $keyDir  = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
            $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
            if (!is_file($keyPath)) {
                return $this->erroFatal('Chave SSH não encontrada: ' . $keyId);
            }
        }

        // Resolve senha do sudo
        if ($useSudo) {
            $sudoRaw = SshCrypto::decifrar((string)($srv['sudo_password'] ?? ''));
            // Se não tiver sudo_password separada, usa a própria senha SSH
            $sudoSenha = $sudoRaw !== '' ? $sudoRaw : ($senha ?? '');
        }

        // Marca servidor como "inicializando"
        $this->atualizarSetupStatus($pdo, $serverId, 'initializing');

        $passos = $this->definirPassos($srv);
        $total  = count($passos);
        $logsPrevios = [];
        if ($retomar) {
            $stLogs = $pdo->prepare('SELECT step, status FROM server_setup_logs WHERE server_id = :id ORDER BY id ASC');
            $stLogs->execute([':id' => $serverId]);
            foreach ($stLogs->fetchAll() as $l) {
                $logsPrevios[$l['step']] = $l['status'];
            }
        } else {
            // Reinício completo: apaga logs anteriores
            $pdo->prepare('DELETE FROM server_setup_logs WHERE server_id = :id')->execute([':id' => $serverId]);
        }

        $allOk     = true;
        $concluidos = 0;

        foreach ($passos as $passo) {
            $nome = $passo['name'];

            // Pular passos já concluídos (modo retomar)
            if ($retomar && ($logsPrevios[$nome] ?? '') === 'ok') {
                $concluidos++;
                continue;
            }

            // Garante que existe uma linha no log (insere ou atualiza para 'running')
            $this->upsertLog($pdo, $serverId, $nome, 'running', '');

            try {
                // Aplica sudo se necessário e o passo exige privilégio
                $cmdFinal = ($useSudo && !empty($passo['precisa_root']))
                    ? SshExecutor::elevarComSudo($passo['cmd'], $sudoSenha)
                    : $passo['cmd'];

                if ($senha !== null) {
                    $r = $this->exec->executarComSenha($host, $port, $user, $senha, $cmdFinal, $passo['timeout'] ?? 120);
                } else {
                    $r = $this->exec->executar($host, $port, $user, (string)$keyPath, $cmdFinal, $passo['timeout'] ?? 120);
                }
                $saida = trim((string)($r['saida'] ?? ''));
                $ok    = (bool)($r['ok'] ?? false);

                // Alguns comandos retornam exit 1 mas são considerados ok pelo conteúdo
                if (!$ok && !empty($passo['ok_if_contains']) && str_contains($saida, $passo['ok_if_contains'])) {
                    $ok = true;
                }
                // Comandos idempotentes: "already exists" também é ok
                if (!$ok && str_contains($saida, 'already exists')) {
                    $ok = true;
                }

                $status = $ok ? 'ok' : 'error';
                $this->upsertLog($pdo, $serverId, $nome, $status, $saida);

                if ($ok) {
                    $concluidos++;
                } else {
                    $allOk = false;
                    if (!empty($passo['fatal'])) {
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $this->upsertLog($pdo, $serverId, $nome, 'error', $e->getMessage());
                $allOk = false;
                if (!empty($passo['fatal'])) {
                    break;
                }
            }
        }

        // Atualiza status final do servidor
        if ($allOk) {
            $pdo->prepare("UPDATE servers SET setup_status='ready', status='active', is_online=1, last_check_at=:c, last_error=NULL WHERE id=:id")
                ->execute([':c' => date('Y-m-d H:i:s'), ':id' => $serverId]);
        } else {
            $pdo->prepare("UPDATE servers SET setup_status='error', is_online=0, last_check_at=:c WHERE id=:id")
                ->execute([':c' => date('Y-m-d H:i:s'), ':id' => $serverId]);
        }

        $stLogs = $pdo->prepare('SELECT step, status, output FROM server_setup_logs WHERE server_id = :id ORDER BY id ASC');
        $stLogs->execute([':id' => $serverId]);

        return [
            'ok'        => $allOk,
            'total'     => $total,
            'concluidos' => $concluidos,
            'steps'     => $stLogs->fetchAll(),
        ];
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
        // Tenta atualizar linha existente; se não existir, insere
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
            // coluna pode não existir ainda em instâncias antigas
        }
    }

    private function erroFatal(string $msg): array
    {
        return [
            'ok'        => false,
            'total'     => 0,
            'concluidos' => 0,
            'steps'     => [['step' => 'Validação', 'status' => 'error', 'output' => $msg]],
        ];
    }
}
