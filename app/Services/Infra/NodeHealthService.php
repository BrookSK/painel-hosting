<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\App\Services\Infra\SshCrypto;

final class NodeHealthService
{
    public function verificarNode(int $serverId, callable $log): bool
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, ssh_password, ssh_auth_type, status FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $srv = $stmt->fetch();

        if (!is_array($srv)) {
            throw new \RuntimeException('Node não encontrado.');
        }

        $host     = trim((string)($srv['ip_address'] ?? ''));
        if ($host === '') $host = trim((string)($srv['hostname'] ?? ''));

        $sshPort  = (int)($srv['ssh_port'] ?? 22);
        $sshUser  = trim((string)($srv['ssh_user'] ?? ''));
        $authType = (string)($srv['ssh_auth_type'] ?? 'key');
        $useSudo  = (bool)(int)($srv['use_sudo'] ?? 0);

        if ($host === '' || $sshPort <= 0 || $sshUser === '') {
            $this->atualizarOnline($serverId, false, 'Dados de SSH incompletos.');
            $log('Dados de SSH incompletos.');
            return false;
        }

        $exec = new SshExecutor();

        // Monta o comando de verificação (docker version pode precisar de sudo em alguns setups)
        $cmdVerificar = 'docker version';
        if ($useSudo) {
            $sudoRaw = SshCrypto::decifrar((string)($srv['sudo_password'] ?? ''));
            if ($sudoRaw === '' && isset($srv['ssh_password'])) {
                $sudoRaw = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
            }
            $cmdVerificar = SshExecutor::elevarComSudo('docker version', $sudoRaw);
        }

        try {
            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
                if ($senha === '') {
                    $this->atualizarOnline($serverId, false, 'Senha SSH não configurada.');
                    $log('Senha SSH não configurada.');
                    return false;
                }
                $log('Testando conexão SSH/Docker (senha)…');
                $t = $exec->executarComSenha($host, $sshPort, $sshUser, $senha, $cmdVerificar, 20);
            } else {
                $keyId  = trim((string)($srv['ssh_key_id'] ?? ''));
                $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                if ($keyDir === '' || $keyId === '') {
                    $this->atualizarOnline($serverId, false, 'Chave SSH não configurada.');
                    $log('Chave SSH não configurada.');
                    return false;
                }
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
                if (!is_file($keyPath)) {
                    $this->atualizarOnline($serverId, false, 'Chave não encontrada: ' . $keyId);
                    $log('Chave não encontrada: ' . $keyId);
                    return false;
                }
                $log('Testando conexão SSH/Docker (chave)…');
                $t = $exec->executar($host, $sshPort, $sshUser, $keyPath, $cmdVerificar, 20);
            }
        } catch (\Throwable $e) {
            $this->atualizarOnline($serverId, false, $e->getMessage());
            $log($e->getMessage());
            return false;
        }

        $saida = trim((string)($t['saida'] ?? ''));
        $ok    = (bool)($t['ok'] ?? false);
        if ($ok) {
            $ok = str_contains($saida, 'Version') || str_contains($saida, 'Client:');
        }

        if ($ok) {
            $this->atualizarOnline($serverId, true, null);
            $log('OK');
            return true;
        }

        $this->atualizarOnline($serverId, false, $saida !== '' ? $saida : 'Falha ao validar.');
        $log($saida !== '' ? $saida : 'Falha ao validar.');
        return false;
    }

    private function atualizarOnline(int $serverId, bool $online, ?string $erro): void
    {
        try {
            $pdo  = BancoDeDados::pdo();
            $stmt = $pdo->prepare('UPDATE servers SET is_online=:o, last_check_at=:c, last_error=:e WHERE id=:id');
            $stmt->execute([
                ':o'  => $online ? 1 : 0,
                ':c'  => date('Y-m-d H:i:s'),
                ':e'  => $erro !== null ? mb_substr($erro, 0, 255) : null,
                ':id' => $serverId,
            ]);
        } catch (\Throwable) {}
    }
}
