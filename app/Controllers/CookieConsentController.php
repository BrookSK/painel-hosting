<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\App\Services\Cookies\CookieConsentService;
use LRV\Core\Csrf;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class CookieConsentController
{
    public function salvar(Requisicao $req): Resposta
    {
        if (!Csrf::validar($req->post['_csrf'] ?? '')) {
            return Resposta::json(['erro' => 'Token inválido.'], 403);
        }

        $body = $req->post;
        $prefs = [
            'necessary'   => true,
            'analytics'   => !empty($body['analytics']),
            'marketing'   => !empty($body['marketing']),
            'preferences' => !empty($body['preferences']),
        ];

        $userId = $_SESSION['cliente_id'] ?? $_SESSION['equipe_id'] ?? null;
        $sessionId = session_id() ?: null;
        $ip = $this->extrairIp($req);
        $ua = substr(trim((string) ($req->headers['user-agent'] ?? '')), 0, 255) ?: null;

        $service = new CookieConsentService();
        $service->salvarConsentimento($prefs, $userId ? (int) $userId : null, $sessionId, $ip, $ua);

        return Resposta::json(['ok' => true, 'preferences' => $prefs]);
    }

    public function obter(Requisicao $req): Resposta
    {
        $userId = $_SESSION['cliente_id'] ?? $_SESSION['equipe_id'] ?? null;
        $sessionId = session_id() ?: null;

        $service = new CookieConsentService();
        $prefs = $service->obterConsentimento($userId ? (int) $userId : null, $sessionId);

        return Resposta::json(['preferences' => $prefs]);
    }

    private function extrairIp(Requisicao $req): ?string
    {
        $xff = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
        if ($xff !== '') {
            $parts = array_map('trim', explode(',', $xff));
            return $parts[0] !== '' ? $parts[0] : null;
        }
        return isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : null;
    }
}
