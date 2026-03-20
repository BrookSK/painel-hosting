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
}
