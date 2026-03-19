<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class NodeHealthService
{
    public function verificarNode(int $serverId, callable $log): bool
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $srv = $stmt->fetch();

        if (!is_array($srv)) {
            throw new \RuntimeException('Node não encontrado.');
        }

        $host = trim((string) ($srv['ip_address'] ?? ''));
        if ($host === '') {
            $host = trim((string) ($srv['hostname'] ?? ''));
        }

        $sshPort = (int) ($srv['ssh_port'] ?? 22);
        $sshUser = trim((string) ($srv['ssh_user'] ?? ''));
        $keyId = trim((string) ($srv['ssh_key_id'] ?? ''));

        if ($host === '' || $sshPort <= 0 || $sshUser === '' || $keyId === '') {
            $this->atualizarOnline($serverId, false, 'Dados de SSH incompletos.');
            $log('Dados de SSH incompletos.');
            return false;
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        if ($keyDir === '') {
            $this->atualizarOnline($serverId, false, 'infra.ssh_key_dir não configurado.');
            $log('infra.ssh_key_dir não configurado.');
            return false;
        }

        $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
        if (!is_file($keyPath)) {
            $this->atualizarOnline($serverId, false, 'Chave não encontrada: ' . $keyId);
            $log('Chave não encontrada: ' . $keyId);
            return false;
        }

        $log('Testando conexão SSH/Docker...');
        $exec = new SshExecutor();
        $t = $exec->executar($host, $sshPort, $sshUser, $keyPath, 'docker version', 20);

        $saida = trim((string) ($t['saida'] ?? ''));
        $ok = (bool) ($t['ok'] ?? false);
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
        $pdo = BancoDeDados::pdo();

        try {
            $stmt = $pdo->prepare('UPDATE servers SET is_online = :o, last_check_at = :c, last_error = :e WHERE id = :id');
            $stmt->execute([
                ':o' => $online ? 1 : 0,
                ':c' => date('Y-m-d H:i:s'),
                ':e' => $erro !== null ? (function_exists('mb_substr') ? mb_substr($erro, 0, 255) : substr($erro, 0, 255)) : null,
                ':id' => $serverId,
            ]);
        } catch (\Throwable $e) {
        }
    }
}
