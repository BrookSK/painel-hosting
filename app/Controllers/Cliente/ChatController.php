<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Chat\ChatRoomService;
use LRV\App\Services\Chat\ChatTokensService;
use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ChatController
{
    public function index(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $room = (new ChatRoomService())->obterOuCriar($clienteId);

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/chat.php', [
            'room' => $room,
        ]);

        return Resposta::html($html);
    }

    public function token(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $room  = (new ChatRoomService())->obterOuCriar($clienteId);
        $token = (new ChatTokensService())->criarTokenCliente($clienteId, (int) $room['id']);

        return Resposta::json(['ok' => true, 'token' => $token, 'room_id' => (int) $room['id']]);
    }

    public function historico(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $pdo  = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT r.id, r.status, r.created_at, r.updated_at,
                    (SELECT COUNT(*) FROM chat_messages m WHERE m.room_id = r.id) AS total_messages
             FROM chat_rooms r
             WHERE r.client_id = :c
             ORDER BY r.id DESC LIMIT 20"
        );
        $stmt->execute([':c' => $clienteId]);
        $rooms = $stmt->fetchAll();

        return Resposta::json(['ok' => true, 'rooms' => is_array($rooms) ? $rooms : []]);
    }

    /** Polling: retorna mensagens após um determinado ID */
    public function poll(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false], 401);
        }

        $room = (new ChatRoomService())->obterOuCriar($clienteId);
        $roomId = (int) $room['id'];
        $afterId = (int) ($req->query['after'] ?? 0);

        $pdo = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT m.id, m.sender_type, m.sender_id, m.message, m.file_url, m.file_name, m.created_at,
                    CASE WHEN m.sender_type = \'admin\' THEN u.name ELSE NULL END AS sender_name
             FROM chat_messages m
             LEFT JOIN users u ON m.sender_type = \'admin\' AND u.id = m.sender_id
             WHERE m.room_id = :r AND m.id > :a ORDER BY m.id ASC LIMIT 50'
        );
        $stmt->execute([':r' => $roomId, ':a' => $afterId]);
        $msgs = $stmt->fetchAll() ?: [];

        return Resposta::json(['ok' => true, 'messages' => $msgs, 'room_id' => $roomId, 'status' => (string) ($room['status'] ?? 'open')]);
    }

    /** Enviar mensagem via HTTP (fallback quando WS não funciona) */
    public function enviar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false], 401);
        }

        $room = (new ChatRoomService())->obterOuCriar($clienteId);
        $roomId = (int) $room['id'];

        if ((string) ($room['status'] ?? '') !== 'open') {
            return Resposta::json(['ok' => false, 'erro' => 'Chat encerrado.']);
        }

        $message = trim((string) ($req->post['message'] ?? ''));
        $fileUrl = trim((string) ($req->post['file_url'] ?? ''));
        $fileName = trim((string) ($req->post['file_name'] ?? ''));

        if ($message === '' && $fileUrl === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Mensagem vazia.']);
        }

        $svc = new \LRV\App\Services\Chat\ChatMessageService();
        $saved = $svc->salvar($roomId, 'client', $clienteId, $message, $fileUrl ?: null, $fileName ?: null);

        return Resposta::json(['ok' => true, 'msg' => $saved]);
    }
}
