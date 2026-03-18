<?php

declare(strict_types=1);

namespace LRV\Core;

final class Auth
{
    private const SESSAO_EQUIPE_ID = 'auth_equipe_id';
    private const SESSAO_CLIENTE_ID = 'auth_cliente_id';

    public static function equipeId(): ?int
    {
        $v = $_SESSION[self::SESSAO_EQUIPE_ID] ?? null;
        if ($v === null) {
            return null;
        }
        return (int) $v;
    }

    public static function clienteId(): ?int
    {
        $v = $_SESSION[self::SESSAO_CLIENTE_ID] ?? null;
        if ($v === null) {
            return null;
        }
        return (int) $v;
    }

    public static function entrarEquipe(string $email, string $senha): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, password, status FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $u = $stmt->fetch();

        if (!is_array($u)) {
            return false;
        }

        if (($u['status'] ?? '') !== 'active') {
            return false;
        }

        $hash = (string) ($u['password'] ?? '');
        if (!password_verify($senha, $hash)) {
            return false;
        }

        $_SESSION[self::SESSAO_EQUIPE_ID] = (int) $u['id'];
        unset($_SESSION[self::SESSAO_CLIENTE_ID]);

        return true;
    }

    public static function sairEquipe(): void
    {
        unset($_SESSION[self::SESSAO_EQUIPE_ID]);
    }

    public static function entrarCliente(string $email, string $senha): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, password FROM clients WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $c = $stmt->fetch();

        if (!is_array($c)) {
            return false;
        }

        $hash = (string) ($c['password'] ?? '');
        if (!password_verify($senha, $hash)) {
            return false;
        }

        $_SESSION[self::SESSAO_CLIENTE_ID] = (int) $c['id'];
        unset($_SESSION[self::SESSAO_EQUIPE_ID]);

        return true;
    }

    public static function sairCliente(): void
    {
        unset($_SESSION[self::SESSAO_CLIENTE_ID]);
    }

    public static function equipeExiste(): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM users');
        $r = $stmt->fetch();
        return ((int) ($r['total'] ?? 0)) > 0;
    }
}
