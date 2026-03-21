<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use LRV\Core\BancoDeDados;

final class ChatRoomService
{
    /** Retorna room aberta do cliente ou cria uma nova */
    public function obterOuCriar(int $clientId): array
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, client_id, user_id, status, created_at FROM chat_rooms WHERE client_id = :c AND status = 'open' ORDER BY id DESC LIMIT 1");
        $stmt->execute([':c' => $clientId]);
        $room = $stmt->fetch();

        if (is_array($room)) {
            return $room;
        }

        $agora = date('Y-m-d H:i:s');
        $pdo->prepare('INSERT INTO chat_rooms (client_id, user_id, status, created_at, updated_at) VALUES (:c, NULL, :s, :cr, :up)')
            ->execute([':c' => $clientId, ':s' => 'open', ':cr' => $agora, ':up' => $agora]);

        $id = (int) $pdo->lastInsertId();

        return ['id' => $id, 'client_id' => $clientId, 'user_id' => null, 'status' => 'open', 'created_at' => $agora];
    }

    public function fechar(int $roomId): void
    {
        BancoDeDados::pdo()
            ->prepare("UPDATE chat_rooms SET status = 'closed', updated_at = :u WHERE id = :id")
            ->execute([':u' => date('Y-m-d H:i:s'), ':id' => $roomId]);
    }

    public function atribuir(int $roomId, int $userId): void
    {
        BancoDeDados::pdo()
            ->prepare('UPDATE chat_rooms SET user_id = :u, updated_at = :up WHERE id = :id')
            ->execute([':u' => $userId, ':up' => date('Y-m-d H:i:s'), ':id' => $roomId]);
    }

    public function listarAbertas(): array
    {
        return $this->listarPorStatus('open');
    }

    public function listarEncerradas(int $limite = 100): array
    {
        return $this->listarPorStatus('closed', $limite);
    }

    private function listarPorStatus(string $status, int $limite = 100): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT r.id, r.client_id, r.user_id, r.status, r.created_at, r.updated_at,
                    c.name AS client_name, c.email AS client_email,
                    u.name AS agent_name,
                    (SELECT COUNT(*) FROM chat_messages m WHERE m.room_id = r.id) AS total_messages
             FROM chat_rooms r
             INNER JOIN clients c ON c.id = r.client_id
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.status = :s
             ORDER BY r.updated_at DESC
             LIMIT " . min(200, $limite)
        );
        $stmt->execute([':s' => $status]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function buscarPorId(int $roomId): ?array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT r.id, r.client_id, r.user_id, r.status, r.created_at, c.name AS client_name, c.email AS client_email FROM chat_rooms r LEFT JOIN clients c ON c.id = r.client_id WHERE r.id = :id LIMIT 1');
        $stmt->execute([':id' => $roomId]);
        $r = $stmt->fetch();
        return is_array($r) ? $r : null;
    }
}
