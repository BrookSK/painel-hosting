<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use LRV\Core\BancoDeDados;

final class ChatMessageService
{
    public function salvar(int $roomId, string $senderType, int $senderId, string $message): array
    {
        $message = trim($message);
        if ($message === '' || strlen($message) > 4000) {
            throw new \InvalidArgumentException('Mensagem inválida.');
        }

        if (!in_array($senderType, ['client', 'admin'], true)) {
            throw new \InvalidArgumentException('Tipo de remetente inválido.');
        }

        $agora = date('Y-m-d H:i:s');
        $pdo   = BancoDeDados::pdo();

        $pdo->prepare('INSERT INTO chat_messages (room_id, sender_type, sender_id, message, created_at) VALUES (:r,:t,:s,:m,:c)')
            ->execute([':r' => $roomId, ':t' => $senderType, ':s' => $senderId, ':m' => $message, ':c' => $agora]);

        $id = (int) $pdo->lastInsertId();

        // Atualizar updated_at da room
        $pdo->prepare('UPDATE chat_rooms SET updated_at = :u WHERE id = :id')
            ->execute([':u' => $agora, ':id' => $roomId]);

        return ['id' => $id, 'room_id' => $roomId, 'sender_type' => $senderType, 'sender_id' => $senderId, 'message' => $message, 'created_at' => $agora];
    }

    public function historico(int $roomId, int $limite = 100): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, sender_type, sender_id, message, created_at FROM chat_messages WHERE room_id = :r ORDER BY id ASC LIMIT ' . min(200, $limite));
        $stmt->execute([':r' => $roomId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
