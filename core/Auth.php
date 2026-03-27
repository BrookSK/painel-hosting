<?php

declare(strict_types=1);

namespace LRV\Core;

final class Auth
{
    private const SESSAO_EQUIPE_ID = 'auth_equipe_id';
    private const SESSAO_CLIENTE_ID = 'auth_cliente_id';

    private static function regenerarSessao(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        try {
            @session_regenerate_id(true);
        } catch (\Throwable $e) {
        }
    }

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

        self::regenerarSessao();
        Csrf::invalidar();

        $_SESSION[self::SESSAO_EQUIPE_ID] = (int) $u['id'];
        unset($_SESSION[self::SESSAO_CLIENTE_ID]);

        return true;
    }

    public static function sairEquipe(): void
    {
        unset($_SESSION[self::SESSAO_EQUIPE_ID]);
        Csrf::invalidar();
        self::regenerarSessao();
    }

    public static function entrarCliente(string $email, string $senha): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, password, preferred_lang FROM clients WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $c = $stmt->fetch();

        if (!is_array($c)) {
            return false;
        }

        $hash = (string) ($c['password'] ?? '');
        if (!password_verify($senha, $hash)) {
            return false;
        }

        self::regenerarSessao();
        Csrf::invalidar();

        $_SESSION[self::SESSAO_CLIENTE_ID] = (int) $c['id'];
        unset($_SESSION[self::SESSAO_EQUIPE_ID]);

        // Registrar último login
        try {
            $pdo->prepare('UPDATE clients SET last_login_at = :t WHERE id = :id')
                ->execute([':t' => date('Y-m-d H:i:s'), ':id' => (int) $c['id']]);
        } catch (\Throwable) {}

        // Aplicar idioma preferido do cliente
        $prefLang = trim((string) ($c['preferred_lang'] ?? ''));
        if ($prefLang !== '' && in_array($prefLang, ['pt-BR', 'en-US', 'es-ES'], true)) {
            I18n::definirIdioma($prefLang);
            if (PHP_SAPI !== 'cli') {
                setcookie('lang', $prefLang, [
                    'expires' => time() + 31536000,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
        }

        return true;
    }

    public static function sairCliente(): void
    {
        // Se estava impersonando, voltar para sessão da equipe
        if (isset($_SESSION['impersonating_from_equipe_id'])) {
            $equipeId = (int) $_SESSION['impersonating_from_equipe_id'];
            unset($_SESSION[self::SESSAO_CLIENTE_ID], $_SESSION['impersonating_from_equipe_id']);
            $_SESSION[self::SESSAO_EQUIPE_ID] = $equipeId;
            return;
        }
        unset($_SESSION[self::SESSAO_CLIENTE_ID]);
        Csrf::invalidar();
        self::regenerarSessao();
    }

    /**
     * Equipe loga como cliente (impersonação).
     * Mantém o ID da equipe em sessão para poder voltar.
     */
    public static function impersonarCliente(int $clienteId): void
    {
        $_SESSION['impersonating_from_equipe_id'] = $_SESSION[self::SESSAO_EQUIPE_ID] ?? null;
        $_SESSION[self::SESSAO_CLIENTE_ID] = $clienteId;
        unset($_SESSION[self::SESSAO_EQUIPE_ID]);
    }

    /** Retorna true se a sessão atual é uma impersonação da equipe. */
    public static function estaImpersonando(): bool
    {
        return isset($_SESSION['impersonating_from_equipe_id']) && $_SESSION['impersonating_from_equipe_id'] !== null;
    }

    /** Retorna true se o cliente logado é do tipo gerenciado (is_managed). */
    public static function clienteGerenciado(): bool
    {
        $id = self::clienteId();
        if ($id === null) {
            return false;
        }
        // Cache em sessão para evitar query repetida
        if (!isset($_SESSION['_cache_is_managed']) || ($_SESSION['_cache_is_managed_id'] ?? 0) !== $id) {
            $pdo = BancoDeDados::pdo();
            $s = $pdo->prepare('SELECT is_managed FROM clients WHERE id = :id');
            $s->execute([':id' => $id]);
            $r = $s->fetch();
            $_SESSION['_cache_is_managed'] = (bool) (int) ($r['is_managed'] ?? 0);
            $_SESSION['_cache_is_managed_id'] = $id;
        }
        return (bool) $_SESSION['_cache_is_managed'];
    }

    public static function equipeExiste(): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM users');
        $r = $stmt->fetch();
        return ((int) ($r['total'] ?? 0)) > 0;
    }
}
