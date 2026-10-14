<?php

declare(strict_types=1);

namespace LRV\App\Services\Seguranca;

use LRV\Core\Settings;

/**
 * Captcha do cadastro (Cloudflare Turnstile).
 * Configurado em Equipe → Configurações → Proteção do cadastro.
 * Sem as chaves configuradas o captcha fica desligado (não bloqueia o cadastro).
 */
final class Captcha
{
    public const CAMPO = 'cf-turnstile-response';

    public static function ativo(): bool
    {
        return self::siteKey() !== '' && self::secret() !== '';
    }

    public static function siteKey(): string
    {
        return trim((string) Settings::obter('captcha.turnstile_site_key', ''));
    }

    private static function secret(): string
    {
        return trim((string) Settings::obter('captcha.turnstile_secret', ''));
    }

    /** HTML do widget (vazio se desligado). */
    public static function widget(): string
    {
        if (!self::ativo()) {
            return '';
        }
        $k = htmlspecialchars(self::siteKey(), ENT_QUOTES);
        return '<div class="cf-turnstile" data-sitekey="' . $k . '" data-language="auto" style="margin:12px 0;"></div>'
            . '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }

    /**
     * Confere o token com a Cloudflare. Desligado = true.
     * Ligado e a Cloudflare não respondeu = false (não deixa passar sem verificação).
     */
    public static function validar(string $token, string $ip = ''): bool
    {
        if (!self::ativo()) {
            return true;
        }
        $token = trim($token);
        if ($token === '' || strlen($token) > 4096) {
            return false;
        }

        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array_filter([
                'secret' => self::secret(),
                'response' => $token,
                'remoteip' => $ip,
            ])),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        if (!is_string($resp)) {
            return false;
        }
        $json = json_decode($resp, true);
        return is_array($json) && ($json['success'] ?? false) === true;
    }
}
