<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use Ratchet\MessageComponentInterface;

/**
 * WebSocket server para chat em tempo real.
 * Cada conexão é autenticada via token de uso único.
 * Mensagens são persistidas e retransmitidas para todos os participantes da room.
 */
final class ChatWsApp implements MessageComponentInterface
{
    /** @var \SplObjectStorage<object, array> */
    private \SplObjectStorage $conns;

    /** room_id => [conn, ...] */
    private array $rooms = [];

    private readonly ChatTokensService  $tokens;
    private readonly ChatMessageService $messages;
    private readonly ChatRoomService    $roomSvc;

    /** IP => [count, window_start] — rate limit simples em memória */
    private array $rateMap = [];
    private const RATE_MAX    = 30;  // mensagens por janela
    private const RATE_WINDOW = 10;  // segundos

    public function __construct()
    {
        $this->conns    = new \SplObjectStorage();
        $this->tokens   = new ChatTokensService();
        $this->messages = new ChatMessageService();
        $this->roomSvc  = new ChatRoomService();
    }

    public function onOpen($conn): void
    {
        try {
            $query = '';
            if (isset($conn->httpRequest)) {
                $query = (string) $conn->httpRequest->getUri()->getQuery();
            }

            parse_str($query, $q);
            $token = trim((string) ($q['token'] ?? ''));

            if ($token === '') {
                $conn->send($this->err('Token ausente.'));
                $conn->close();
                return;
            }

            $val = $this->tokens->consumir($token);
            if (empty($val['ok'])) {
                $conn->send($this->err((string) ($val['erro'] ?? 'Token inválido.')));
                $conn->close();
                return;
            }

            $roomId   = (int) ($val['room_id'] ?? 0);
            $clientId = $val['client_id'] ? (int) $val['client_id'] : null;
            $userId   = $val['user_id']   ? (int) $val['user_id']   : null;

            if ($roomId <= 0) {
                $conn->send($this->err('Room inválida.'));
                $conn->close();
                return;
            }

            $room = $this->roomSvc->buscarPorId($roomId);
            if ($room === null || (string) ($room['status'] ?? '') !== 'open') {
                $conn->send($this->err('Chat encerrado.'));
                $conn->close();
                return;
            }

            // Validar ownership: cliente só pode entrar na própria room
            if ($clientId !== null && (int) ($room['client_id'] ?? 0) !== $clientId) {
                $conn->send($this->err('Acesso negado.'));
                $conn->close();
                return;
            }

            $senderType = $clientId !== null ? 'client' : 'admin';
            $senderId   = $clientId ?? $userId ?? 0;

            if ($senderId <= 0) {
                $conn->send($this->err('Identidade inválida.'));
                $conn->close();
                return;
            }

            // Se admin entrou, atribuir à room
            if ($senderType === 'admin' && (int) ($room['user_id'] ?? 0) === 0) {
                $this->roomSvc->atribuir($roomId, $senderId);
            }

            $this->conns[$conn] = [
                'room_id'     => $roomId,
                'sender_type' => $senderType,
                'sender_id'   => $senderId,
                'ip'          => $this->extrairIp($conn),
            ];

            $this->rooms[$roomId][] = $conn;

            // Enviar histórico
            $historico = $this->messages->historico($roomId);
            $conn->send(json_encode(['type' => 'history', 'messages' => $historico]));

            // Notificar outros participantes
            $this->broadcast($roomId, $conn, json_encode([
                'type'        => 'system',
                'message'     => $senderType === 'client' ? 'Cliente conectado.' : 'Agente conectado.',
                'sender_type' => $senderType,
            ]));
        } catch (\Throwable $e) {
            try { $conn->send($this->err('Erro ao conectar.')); } catch (\Throwable $e2) {}
            try { $conn->close(); } catch (\Throwable $e3) {}
        }
    }

    public function onMessage($from, $msg): void
    {
        if (!isset($this->conns[$from])) {
            return;
        }

        $meta = $this->conns[$from];
        $ip   = (string) ($meta['ip'] ?? '');

        // Rate limit por IP
        if (!$this->checkRate($ip)) {
            $from->send($this->err('Muitas mensagens. Aguarde.'));
            return;
        }

        $texto = trim((string) $msg);
        if ($texto === '' || strlen($texto) > 4000) {
            return;
        }

        // Aceitar JSON {"message":"...", "file_url":"...", "file_name":"..."} ou texto puro
        $fileUrl = null;
        $fileName = null;
        if ($texto[0] === '{') {
            $decoded = json_decode($texto, true);
            if (is_array($decoded)) {
                $texto = trim((string) ($decoded['message'] ?? ''));
                $fileUrl = isset($decoded['file_url']) ? trim((string) $decoded['file_url']) : null;
                $fileName = isset($decoded['file_name']) ? trim((string) $decoded['file_name']) : null;
            }
        }

        if ($texto === '' && ($fileUrl === null || $fileUrl === '')) {
            return;
        }

        // Validar file_url se presente (deve ser path interno)
        if ($fileUrl !== null && $fileUrl !== '') {
            if (!str_starts_with($fileUrl, '/uploads/chat/')) {
                $fileUrl = null;
                $fileName = null;
            }
        }

        $roomId     = (int) ($meta['room_id'] ?? 0);
        $senderType = (string) ($meta['sender_type'] ?? '');
        $senderId   = (int) ($meta['sender_id'] ?? 0);

        try {
            $saved = $this->messages->salvar($roomId, $senderType, $senderId, $texto, $fileUrl, $fileName);
        } catch (\Throwable $e) {
            $from->send($this->err('Não foi possível enviar a mensagem.'));
            return;
        }

        $payload = json_encode([
            'type'        => 'message',
            'id'          => $saved['id'],
            'sender_type' => $senderType,
            'message'     => $texto,
            'file_url'    => $saved['file_url'] ?? null,
            'file_name'   => $saved['file_name'] ?? null,
            'created_at'  => $saved['created_at'],
        ]);

        // Enviar para todos na room (incluindo remetente)
        $this->broadcastAll($roomId, $payload);
    }

    public function onClose($conn): void
    {
        if (!isset($this->conns[$conn])) {
            return;
        }

        $meta   = $this->conns[$conn];
        $roomId = (int) ($meta['room_id'] ?? 0);

        $this->conns->detach($conn);

        if (isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = array_filter(
                $this->rooms[$roomId],
                static fn ($c) => $c !== $conn
            );
            if (empty($this->rooms[$roomId])) {
                unset($this->rooms[$roomId]);
            }
        }
    }

    public function onError($conn, \Exception $e): void
    {
        try { $conn->close(); } catch (\Throwable $e2) {}
        $this->onClose($conn);
    }

    private function broadcast(int $roomId, $exclude, string $payload): void
    {
        foreach ($this->rooms[$roomId] ?? [] as $c) {
            if ($c !== $exclude) {
                try { $c->send($payload); } catch (\Throwable $e) {}
            }
        }
    }

    private function broadcastAll(int $roomId, string $payload): void
    {
        foreach ($this->rooms[$roomId] ?? [] as $c) {
            try { $c->send($payload); } catch (\Throwable $e) {}
        }
    }

    private function err(string $msg): string
    {
        return json_encode(['type' => 'error', 'message' => $msg]);
    }

    private function extrairIp($conn): string
    {
        if (isset($conn->httpRequest)) {
            $xff = (string) $conn->httpRequest->getHeaderLine('X-Forwarded-For');
            if ($xff !== '') {
                return trim(explode(',', $xff)[0]);
            }
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    private function checkRate(string $ip): bool
    {
        $now = time();
        if (!isset($this->rateMap[$ip])) {
            $this->rateMap[$ip] = ['count' => 0, 'start' => $now];
        }

        if (($now - $this->rateMap[$ip]['start']) > self::RATE_WINDOW) {
            $this->rateMap[$ip] = ['count' => 0, 'start' => $now];
        }

        $this->rateMap[$ip]['count']++;
        return $this->rateMap[$ip]['count'] <= self::RATE_MAX;
    }
}
