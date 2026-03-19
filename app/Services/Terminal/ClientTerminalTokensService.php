<?php

declare(strict_types=1);

namespace LRV\App\Services\Terminal;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class ClientTerminalTokensService
{
    public function criarToken(int $clientId, int $vpsId, string $ip, string $userAgent): string
    {
        if ($clientId <= 0 || $vpsId <= 0) {
            throw new \InvalidArgumentException('Parâmetros inválidos.');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, client_id, server_id, container_id, status FROM vps WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();

        if (!is_array($vps) || (int) ($vps['client_id'] ?? 0) !== $clientId) {
            throw new \RuntimeException('VPS não encontrada.');
        }

        if ((string) ($vps['status'] ?? '') !== 'running') {
            throw new \RuntimeException('VPS não está em execução.');
        }

        if ((int) ($vps['server_id'] ?? 0) <= 0 || trim((string) ($vps['container_id'] ?? '')) === '') {
            throw new \RuntimeException('VPS sem node/contêiner.');
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

        $ins = $pdo->prepare('INSERT INTO client_terminal_tokens (token_hash, client_id, vps_id, ip, user_agent, created_at, expires_at) VALUES (:h,:c,:v,:ip,:ua,:cr,:ex)');
        $ins->execute([
            ':h' => $hash,
            ':c' => $clientId,
            ':v' => $vpsId,
            ':ip' => $ip,
            ':ua' => $userAgent,
            ':cr' => $agora,
            ':ex' => $expira,
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
            $stmt = $pdo->prepare('SELECT id, client_id, vps_id, expires_at, revoked, used_at FROM client_terminal_tokens WHERE token_hash = :h LIMIT 1 FOR UPDATE');
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

            $upd = $pdo->prepare('UPDATE client_terminal_tokens SET used_at = :u WHERE id = :id');
            $upd->execute([
                ':u' => $agora,
                ':id' => (int) ($row['id'] ?? 0),
            ]);

            $pdo->commit();

            return [
                'ok' => true,
                'token_id' => (int) ($row['id'] ?? 0),
                'client_id' => (int) ($row['client_id'] ?? 0),
                'vps_id' => (int) ($row['vps_id'] ?? 0),
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
