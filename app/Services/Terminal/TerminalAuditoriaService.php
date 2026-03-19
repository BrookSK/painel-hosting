<?php

declare(strict_types=1);

namespace LRV\App\Services\Terminal;

use LRV\Core\BancoDeDados;

final class TerminalAuditoriaService
{
    public function iniciarSessao(int $equipeId, int $serverId, string $ip, string $userAgent): array
    {
        if ($equipeId <= 0 || $serverId <= 0) {
            throw new \InvalidArgumentException('Parâmetros inválidos.');
        }

        $agora = date('Y-m-d H:i:s');

        $ip = trim($ip);
        if ($ip === '') {
            $ip = null;
        }

        $userAgent = trim($userAgent);
        if ($userAgent === '') {
            $userAgent = null;
        }

        if ($userAgent !== null && strlen($userAgent) > 255) {
            $userAgent = substr($userAgent, 0, 255);
        }

        $uid = $this->uuidV4();

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('INSERT INTO terminal_sessions (session_uid, equipe_id, server_id, ip, user_agent, started_at) VALUES (:u,:e,:s,:ip,:ua,:st)');
        $stmt->execute([
            ':u' => $uid,
            ':e' => $equipeId,
            ':s' => $serverId,
            ':ip' => $ip,
            ':ua' => $userAgent,
            ':st' => $agora,
        ]);

        return [
            'session_id' => (int) $pdo->lastInsertId(),
            'session_uid' => $uid,
        ];
    }

    public function encerrarSessao(int $sessionId): void
    {
        if ($sessionId <= 0) {
            return;
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('UPDATE terminal_sessions SET ended_at = :e WHERE id = :id AND ended_at IS NULL');
        $stmt->execute([
            ':e' => date('Y-m-d H:i:s'),
            ':id' => $sessionId,
        ]);
    }

    public function registrarComando(int $sessionId, string $comando): void
    {
        if ($sessionId <= 0) {
            return;
        }

        $comando = rtrim($comando, "\r\n");
        if (trim($comando) === '') {
            return;
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('INSERT INTO terminal_session_commands (session_id, command, created_at) VALUES (:s,:c,:t)');
        $stmt->execute([
            ':s' => $sessionId,
            ':c' => $comando,
            ':t' => date('Y-m-d H:i:s'),
        ]);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        $hex = bin2hex($data);

        return substr($hex, 0, 8) . '-' .
            substr($hex, 8, 4) . '-' .
            substr($hex, 12, 4) . '-' .
            substr($hex, 16, 4) . '-' .
            substr($hex, 20, 12);
    }
}
