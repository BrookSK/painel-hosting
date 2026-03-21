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
}
