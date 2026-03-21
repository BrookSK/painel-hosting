<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Chat\ChatRoomService;
use LRV\App\Services\Chat\ChatTokensService;
use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ChatController
{
    public function listar(Requisicao $req): Resposta
    {
        $rooms = (new ChatRoomService())->listarAbertas();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-listar.php', [
            'rooms' => $rooms,
        ]);

        return Resposta::html($html);
    }

    public function ver(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $roomId = (int) ($req->query['id'] ?? 0);
        if ($roomId <= 0) {
            return Resposta::texto('Room inválida.', 400);
        }

        $room = (new ChatRoomService())->buscarPorId($roomId);
        if ($room === null) {
            return Resposta::texto('Room não encontrada.', 404);
        }

        $pdo = \LRV\Core\BancoDeDados::pdo();
        $clientId = (int) ($room['client_id'] ?? 0);

        // Dados do cliente
        $stmtC = $pdo->prepare('SELECT id, name, email, created_at FROM clients WHERE id = :id LIMIT 1');
        $stmtC->execute([':id' => $clientId]);
        $cliente = $stmtC->fetch() ?: [];

        // Tickets recentes
        $stmtT = $pdo->prepare('SELECT id, subject, status, created_at FROM tickets WHERE client_id = :c ORDER BY created_at DESC LIMIT 10');
        $stmtT->execute([':c' => $clientId]);
        $tickets = $stmtT->fetchAll() ?: [];

        // Assinaturas
        $stmtA = $pdo->prepare('SELECT s.id, s.status, s.created_at, p.name AS plan_name FROM subscriptions s LEFT JOIN plans p ON s.plan_id = p.id WHERE s.client_id = :c ORDER BY s.created_at DESC LIMIT 10');
        $stmtA->execute([':c' => $clientId]);
        $assinaturas = $stmtA->fetchAll() ?: [];

        // VPS
        $stmtV = $pdo->prepare('SELECT id, hostname, status FROM vps WHERE client_id = :c ORDER BY id DESC LIMIT 10');
        $stmtV->execute([':c' => $clientId]);
        $vps = $stmtV->fetchAll() ?: [];

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-ver.php', [
            'room'        => $room,
            'cliente'     => $cliente,
            'tickets'     => $tickets,
            'assinaturas' => $assinaturas,
            'vps'         => $vps,
        ]);

        return Resposta::html($html);
    }

    public function token(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $roomId = (int) ($req->post['room_id'] ?? 0);
        if ($roomId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Room inválida.'], 400);
        }

        $room = (new ChatRoomService())->buscarPorId($roomId);
        if ($room === null || (string) ($room['status'] ?? '') !== 'open') {
            return Resposta::json(['ok' => false, 'erro' => 'Chat encerrado.'], 404);
        }

        $token = (new ChatTokensService())->criarTokenAdmin($equipeId, $roomId);

        return Resposta::json(['ok' => true, 'token' => $token]);
    }

    public function fechar(Requisicao $req): Resposta
    {
        $roomId = (int) ($req->post['room_id'] ?? 0);
        if ($roomId <= 0) {
            return Resposta::texto('Room inválida.', 400);
        }

        (new ChatRoomService())->fechar($roomId);

        return Resposta::redirecionar('/equipe/chat');
    }
}
