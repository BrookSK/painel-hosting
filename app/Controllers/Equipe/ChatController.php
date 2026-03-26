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
        $svc = new ChatRoomService();
        $abertas    = $svc->listarAbertas();
        $encerradas = $svc->listarEncerradas();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-listar.php', [
            'rooms'      => $abertas,
            'encerradas' => $encerradas,
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
        $stmtV = $pdo->prepare('SELECT id, cpu, ram, storage, status FROM vps WHERE client_id = :c ORDER BY id DESC LIMIT 10');
        $stmtV->execute([':c' => $clientId]);
        $vps = $stmtV->fetchAll() ?: [];

        // Mensagens (para chats encerrados, carrega server-side)
        $mensagens = [];
        if ((string) ($room['status'] ?? '') === 'closed') {
            $mensagens = (new \LRV\App\Services\Chat\ChatMessageService())->historico($roomId);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-ver.php', [
            'room'        => $room,
            'cliente'     => $cliente,
            'tickets'     => $tickets,
            'assinaturas' => $assinaturas,
            'vps'         => $vps,
            'mensagens'   => $mensagens,
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

        $room = (new ChatRoomService())->buscarPorId($roomId);
        (new ChatRoomService())->fechar($roomId);

        // Trigger active chat_closed flow
        try {
            $flowSvc = new \LRV\App\Services\Chat\ChatFlowService();
            $closedFlows = $flowSvc->listarPorTrigger('chat_closed');
            if (!empty($closedFlows)) {
                $execSvc = new \LRV\App\Services\Chat\ChatFlowExecutionService();
                foreach ($closedFlows as $flow) {
                    $fid = (int) ($flow['id'] ?? 0);
                    if ($fid > 0 && !$execSvc->jaDisparadoNaSessao($roomId, $fid)) {
                        try {
                            $execSvc->iniciar($fid, $roomId, 'event');
                        } catch (\Throwable) {}
                    }
                }
            }
        } catch (\Throwable) {
            // No active chat_closed flow — close normally
        }

        // Enviar e-mail de pesquisa de satisfação ao cliente
        if ($room !== null) {
            $this->enviarPesquisaSatisfacao($room, $roomId);
        }

        return Resposta::redirecionar('/equipe/chat');
    }

    private function enviarPesquisaSatisfacao(array $room, int $roomId): void
    {
        try {
            $email = trim((string) ($room['client_email'] ?? ''));
            if ($email === '') {
                return;
            }

            $base = \LRV\Core\ConfiguracoesSistema::appUrlBase();
            $link = $base . '/cliente/avaliar?type=chat&id=' . $roomId;
            $nome = trim((string) ($room['client_name'] ?? ''));

            $corpo  = "Olá" . ($nome !== '' ? " {$nome}" : '') . ",\n\n";
            $corpo .= "Seu atendimento via chat foi encerrado.\n\n";
            $corpo .= "Gostaríamos de saber como foi sua experiência. Sua avaliação nos ajuda a melhorar nosso suporte.\n\n";
            $corpo .= "Avalie o atendimento: {$link}\n\n";
            $corpo .= "Obrigado por utilizar nossos serviços!\n";

            (new \LRV\App\Services\Email\SmtpMailer())->enviar(
                $email,
                'Avalie seu atendimento — Chat de Suporte',
                $corpo
            );
        } catch (\Throwable) {
            // Falha no envio não deve impedir o encerramento
        }
    }

    /** Polling: retorna mensagens após um determinado ID */
    public function poll(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::json(['ok' => false], 401);
        }

        $roomId = (int) ($req->query['room_id'] ?? 0);
        $afterId = (int) ($req->query['after'] ?? 0);
        if ($roomId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Room inválida.']);
        }

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

        return Resposta::json(['ok' => true, 'messages' => $msgs]);
    }

    /** Enviar mensagem via HTTP (fallback quando WS não funciona) */
    public function enviar(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::json(['ok' => false], 401);
        }

        $roomId = (int) ($req->post['room_id'] ?? 0);
        if ($roomId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Room inválida.']);
        }

        $room = (new ChatRoomService())->buscarPorId($roomId);
        if ($room === null || (string) ($room['status'] ?? '') !== 'open') {
            return Resposta::json(['ok' => false, 'erro' => 'Chat encerrado.']);
        }

        // Atribuir agente se ainda não atribuído
        if ((int) ($room['user_id'] ?? 0) === 0) {
            (new ChatRoomService())->atribuir($roomId, $equipeId);
        }

        $message = trim((string) ($req->post['message'] ?? ''));
        $fileUrl = trim((string) ($req->post['file_url'] ?? ''));
        $fileName = trim((string) ($req->post['file_name'] ?? ''));

        if ($message === '' && $fileUrl === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Mensagem vazia.']);
        }

        $svc = new \LRV\App\Services\Chat\ChatMessageService();
        $saved = $svc->salvar($roomId, 'admin', $equipeId, $message, $fileUrl ?: null, $fileName ?: null);

        return Resposta::json(['ok' => true, 'msg' => $saved]);
    }
}
