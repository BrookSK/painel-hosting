<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use LRV\Core\BancoDeDados;

final class ChatMessageService
{
    public function salvar(int $roomId, string $senderType, int $senderId, string $message, ?string $fileUrl = null, ?string $fileName = null): array
    {
        $message = trim($message);
        if ($message === '' && ($fileUrl === null || $fileUrl === '')) {
            throw new \InvalidArgumentException('Mensagem inválida.');
        }
        if (strlen($message) > 4000) {
            throw new \InvalidArgumentException('Mensagem muito longa.');
        }

        if (!in_array($senderType, ['client', 'admin'], true)) {
            throw new \InvalidArgumentException('Tipo de remetente inválido.');
        }

        $agora = date('Y-m-d H:i:s');
        $pdo   = BancoDeDados::pdo();

        $pdo->prepare('INSERT INTO chat_messages (room_id, sender_type, sender_id, message, file_url, file_name, created_at) VALUES (:r,:t,:s,:m,:fu,:fn,:c)')
            ->execute([':r' => $roomId, ':t' => $senderType, ':s' => $senderId, ':m' => $message, ':fu' => $fileUrl, ':fn' => $fileName, ':c' => $agora]);

        $id = (int) $pdo->lastInsertId();

        // Atualizar updated_at da room
        $pdo->prepare('UPDATE chat_rooms SET updated_at = :u WHERE id = :id')
            ->execute([':u' => $agora, ':id' => $roomId]);

        return ['id' => $id, 'room_id' => $roomId, 'sender_type' => $senderType, 'sender_id' => $senderId, 'message' => $message, 'file_url' => $fileUrl, 'file_name' => $fileName, 'created_at' => $agora];
    }

    public function historico(int $roomId, int $limite = 100): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT m.id, m.sender_type, m.sender_id, m.message, m.file_url, m.file_name, m.created_at,
                    CASE WHEN m.sender_type = \'admin\' THEN u.name ELSE NULL END AS sender_name
             FROM chat_messages m
             LEFT JOIN users u ON m.sender_type = \'admin\' AND u.id = m.sender_id
             WHERE m.room_id = :r ORDER BY m.id ASC LIMIT ' . min(200, $limite)
        );
        $stmt->execute([':r' => $roomId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
