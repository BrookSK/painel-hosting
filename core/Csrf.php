<?php

declare(strict_types=1);

namespace LRV\Core;

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        if (PHP_SAPI === 'cli') {
            return '';
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        $t = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        if ($t !== '' && preg_match('/^[a-f0-9]{64}$/', $t) === 1) {
            return $t;
        }

        try {
            $t = bin2hex(random_bytes(32));
        } catch (\Throwable $e) {
            $t = bin2hex((string) microtime(true) . '|' . (string) mt_rand());
            if (strlen($t) > 64) {
                $t = substr($t, 0, 64);
            }
        }

        $_SESSION[self::SESSION_KEY] = $t;
        return $t;
    }

    public static function validar(string $token): bool
    {
        if (PHP_SAPI === 'cli') {
            return true;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $esperado = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        if ($esperado === '' || $token === '') {
            return false;
        }

        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return false;
        }

        return hash_equals($esperado, $token);
    }

    public static function invalidar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        unset($_SESSION[self::SESSION_KEY]);
    }
}
