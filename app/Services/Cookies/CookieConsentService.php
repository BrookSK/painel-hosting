<?php

declare(strict_types=1);

namespace LRV\App\Services\Cookies;

use LRV\Core\BancoDeDados;

final class CookieConsentService
{
    private const CATEGORIAS = ['necessary', 'analytics', 'marketing', 'preferences'];
    private const COOKIE_NAME = 'cookie_consent';
    private const COOKIE_DAYS = 365;

    public function salvarConsentimento(
        array $prefs,
        ?int $userId,
        ?string $sessionId,
        ?string $ip,
        ?string $userAgent
    ): void {
        $prefs = $this->normalizarPreferencias($prefs);
        $json = json_encode($prefs, JSON_UNESCAPED_UNICODE);

        $pdo = BancoDeDados::pdo();

        // Atualizar registro existente ou criar novo
        $existing = $this->buscarRegistro($userId, $sessionId);

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE cookie_consents SET preferences_json = :pj, ip = :ip, user_agent = :ua, user_id = COALESCE(:uid, user_id), updated_at = :now WHERE id = :id');
            $stmt->execute([
                ':pj'  => $json,
                ':ip'  => $ip ? substr($ip, 0, 45) : null,
                ':ua'  => $userAgent ? substr($userAgent, 0, 255) : null,
                ':uid' => $userId,
                ':now' => date('Y-m-d H:i:s'),
                ':id'  => (int) $existing['id'],
            ]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO cookie_consents (user_id, session_id, ip, user_agent, preferences_json, created_at, updated_at) VALUES (:uid, :sid, :ip, :ua, :pj, :now, :now2)');
            $stmt->execute([
                ':uid'  => $userId,
                ':sid'  => $sessionId ? substr($sessionId, 0, 128) : null,
                ':ip'   => $ip ? substr($ip, 0, 45) : null,
                ':ua'   => $userAgent ? substr($userAgent, 0, 255) : null,
                ':pj'   => $json,
                ':now'  => date('Y-m-d H:i:s'),
                ':now2' => date('Y-m-d H:i:s'),
            ]);
        }

        // Setar cookie no navegador
        $this->setCookie($prefs);
    }

    public function obterConsentimento(?int $userId, ?string $sessionId): ?array
    {
        // Primeiro tenta do cookie
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $decoded = json_decode($_COOKIE[self::COOKIE_NAME], true);
            if (is_array($decoded)) {
                return $this->normalizarPreferencias($decoded);
            }
        }

        // Fallback: banco de dados
        $row = $this->buscarRegistro($userId, $sessionId);
        if ($row && !empty($row['preferences_json'])) {
            $decoded = json_decode($row['preferences_json'], true);
            if (is_array($decoded)) {
                return $this->normalizarPreferencias($decoded);
            }
        }

        return null;
    }

    public function verificarPermissao(string $categoria, ?int $userId = null, ?string $sessionId = null): bool
    {
        if ($categoria === 'necessary') {
            return true;
        }

        $prefs = $this->obterConsentimento($userId, $sessionId);
        if ($prefs === null) {
            return false;
        }

        return !empty($prefs[$categoria]);
    }

    private function normalizarPreferencias(array $prefs): array
    {
        $result = ['necessary' => true];
        foreach (self::CATEGORIAS as $cat) {
            if ($cat === 'necessary') {
                continue;
            }
            $result[$cat] = !empty($prefs[$cat]);
        }
        return $result;
    }

    private function buscarRegistro(?int $userId, ?string $sessionId): ?array
    {
        $pdo = BancoDeDados::pdo();

        if ($userId !== null) {
            $stmt = $pdo->prepare('SELECT id, preferences_json FROM cookie_consents WHERE user_id = :uid ORDER BY updated_at DESC LIMIT 1');
            $stmt->execute([':uid' => $userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return $row;
            }
        }

        if ($sessionId !== null) {
            $stmt = $pdo->prepare('SELECT id, preferences_json FROM cookie_consents WHERE session_id = :sid ORDER BY updated_at DESC LIMIT 1');
            $stmt->execute([':sid' => $sessionId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    private function setCookie(array $prefs): void
    {
        $value = json_encode($prefs, JSON_UNESCAPED_UNICODE);
        $expires = time() + (self::COOKIE_DAYS * 86400);
        setcookie(self::COOKIE_NAME, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'secure'   => true,
            'httponly' => false, // JS precisa ler
            'samesite' => 'Lax',
        ]);
    }
}
