<?php

declare(strict_types=1);

namespace LRV\Core;

/**
 * Bloqueia IPs após N tentativas de login falhas em uma janela de tempo.
 * Usa a tabela auth_logs para contar falhas.
 */
final class LoginBlocker
{
    private const MAX_FALHAS   = 10;
    private const JANELA_MIN   = 15;  // minutos para contar falhas
    private const BLOQUEIO_MIN = 30;  // minutos de bloqueio

    public static function estaBloqueado(string $ip): bool
    {
        if ($ip === '') {
            return false;
        }

        try {
            $pdo = BancoDeDados::pdo();
            $desde = date('Y-m-d H:i:s', time() - self::BLOQUEIO_MIN * 60);

            // Conta falhas recentes deste IP
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM auth_logs
                 WHERE ip_address = :ip
                   AND action = 'login_failed'
                   AND created_at >= :desde"
            );
            $stmt->execute([':ip' => $ip, ':desde' => $desde]);
            $count = (int) $stmt->fetchColumn();

            return $count >= self::MAX_FALHAS;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function registrarFalha(string $ip, string $actorType = 'unknown'): void
    {
        if ($ip === '') {
            return;
        }

        try {
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                'INSERT INTO auth_logs (actor_type, actor_id, action, ip_address, user_agent, created_at)
                 VALUES (:t, NULL, :a, :ip, NULL, :c)'
            );
            $stmt->execute([
                ':t'  => $actorType,
                ':a'  => 'login_failed',
                ':ip' => $ip,
                ':c'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
        }
    }

    public static function extrairIp(): string
    {
        $xff = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
        if ($xff !== '') {
            $partes = array_map('trim', explode(',', $xff));
            $ip = (string) ($partes[0] ?? '');
            if ($ip !== '') {
                return $ip;
            }
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }
}
