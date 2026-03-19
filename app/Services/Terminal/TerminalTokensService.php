<?php

declare(strict_types=1);

namespace LRV\App\Services\Terminal;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class TerminalTokensService
{
    public function criarToken(int $equipeId, int $serverId, string $ip, string $userAgent): string
    {
        if ($equipeId <= 0 || $serverId <= 0) {
            throw new \InvalidArgumentException('Parâmetros inválidos.');
        }

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $ttl = ConfiguracoesSistema::terminalTokenTtlSegundos();
        $agora = date('Y-m-d H:i:s');
        $expira = date('Y-m-d H:i:s', time() + $ttl);

        $ip = trim($ip);
        if ($ip === '') {
            $ip = null;
        }

        $userAgent = trim($userAgent);
        if ($userAgent === '') {
            $userAgent = null;
        }

        if ($userAgent !== null && strlen($userAgent) > 255) {
            $userAgent = substr($userAgent, 0, 255);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('INSERT INTO terminal_tokens (token_hash, equipe_id, server_id, ip, user_agent, created_at, expires_at) VALUES (:h,:e,:s,:ip,:ua,:c,:x)');
        $stmt->execute([
            ':h' => $hash,
            ':e' => $equipeId,
            ':s' => $serverId,
            ':ip' => $ip,
            ':ua' => $userAgent,
            ':c' => $agora,
            ':x' => $expira,
        ]);

        return $token;
    }

    public function consumirToken(string $token): array
    {
        $token = trim($token);
        if ($token === '') {
            return ['ok' => false, 'erro' => 'Token ausente.'];
        }

        $hash = hash('sha256', $token);
        $agora = date('Y-m-d H:i:s');

        $pdo = BancoDeDados::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id, equipe_id, server_id, expires_at, revoked, used_at FROM terminal_tokens WHERE token_hash = :h LIMIT 1 FOR UPDATE');
            $stmt->execute([':h' => $hash]);
            $row = $stmt->fetch();

            if (!is_array($row)) {
                $pdo->rollBack();
                return ['ok' => false, 'erro' => 'Token inválido.'];
            }

            if (!empty($row['revoked'])) {
                $pdo->rollBack();
                return ['ok' => false, 'erro' => 'Token revogado.'];
            }

            if (!empty($row['used_at'])) {
                $pdo->rollBack();
                return ['ok' => false, 'erro' => 'Token já utilizado.'];
            }

            $expiraEm = (string) ($row['expires_at'] ?? '');
            if ($expiraEm !== '' && $expiraEm < $agora) {
                $pdo->rollBack();
                return ['ok' => false, 'erro' => 'Token expirado.'];
            }

            $upd = $pdo->prepare('UPDATE terminal_tokens SET used_at = :u WHERE id = :id');
            $upd->execute([
                ':u' => $agora,
                ':id' => (int) ($row['id'] ?? 0),
            ]);

            $pdo->commit();

            return [
                'ok' => true,
                'token_id' => (int) ($row['id'] ?? 0),
                'equipe_id' => (int) ($row['equipe_id'] ?? 0),
                'server_id' => (int) ($row['server_id'] ?? 0),
            ];
        } catch (\Throwable $e) {
            try {
                $pdo->rollBack();
            } catch (\Throwable $e2) {
            }

            return ['ok' => false, 'erro' => 'Erro ao validar token.'];
        }
    }
}
