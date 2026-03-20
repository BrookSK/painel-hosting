<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use LRV\Core\BancoDeDados;

final class ChatTokensService
{
    private const TTL = 120; // 2 minutos para conectar

    public function criarTokenCliente(int $clientId, int $roomId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);
        $expira = date('Y-m-d H:i:s', time() + self::TTL);

        $pdo = BancoDeDados::pdo();
        $pdo->prepare('INSERT INTO chat_tokens (token_hash, client_id, room_id, expires_at) VALUES (:h,:c,:r,:e)')
            ->execute([':h' => $hash, ':c' => $clientId, ':r' => $roomId, ':e' => $expira]);

        return $token;
    }

    public function criarTokenAdmin(int $userId, int $roomId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);
        $expira = date('Y-m-d H:i:s', time() + self::TTL);

        $pdo = BancoDeDados::pdo();
        $pdo->prepare('INSERT INTO chat_tokens (token_hash, user_id, room_id, expires_at) VALUES (:h,:u,:r,:e)')
            ->execute([':h' => $hash, ':u' => $userId, ':r' => $roomId, ':e' => $expira]);

        return $token;
    }

    public function consumir(string $token): array
    {
        $token = trim($token);
        if ($token === '') {
            return ['ok' => false, 'erro' => 'Token ausente.'];
        }

        $hash  = hash('sha256', $token);
        $agora = date('Y-m-d H:i:s');
        $pdo   = BancoDeDados::pdo();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id, client_id, user_id, room_id, expires_at, used_at FROM chat_tokens WHERE token_hash = :h LIMIT 1 FOR UPDATE');
            $stmt->execute([':h' => $hash]);
            $row = $stmt->fetch();

            if (!is_array($row)) {
                $pdo->rollBack();
                return ['ok' => false, 'erro' => 'Token inválido.'];
            }

            if (!empty($row['used_at'])) {
                $pdo->rollBack();
                return ['ok' => false, 'erro' => 'Token já utilizado.'];
            }

            if ((string) ($row['expires_at'] ?? '') < $agora) {
                $pdo->rollBack();
                return ['ok' => false, 'erro' => 'Token expirado.'];
            }

            $pdo->prepare('UPDATE chat_tokens SET used_at = :u WHERE id = :id')
                ->execute([':u' => $agora, ':id' => (int) $row['id']]);

            $pdo->commit();

            return [
                'ok'        => true,
                'client_id' => $row['client_id'] ? (int) $row['client_id'] : null,
                'user_id'   => $row['user_id']   ? (int) $row['user_id']   : null,
                'room_id'   => (int) $row['room_id'],
            ];
        } catch (\Throwable $e) {
            try { $pdo->rollBack(); } catch (\Throwable $e2) {}
            return ['ok' => false, 'erro' => 'Erro ao validar token.'];
        }
    }
}
