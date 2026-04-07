<?php

declare(strict_types=1);

namespace LRV\Core;

final class ConfiguracoesSistema
{
    public static function toleranciaPagamentoDias(): int
    {
        $v = Settings::obter('cobranca.tolerancia_dias', 3);
        $dias = is_int($v) ? $v : (int) $v;
        return $dias > 0 ? $dias : 3;
    }

    public static function asaasToken(): string
    {
        return (string) Settings::obter('asaas.token', '');
    }

    public static function asaasUrlBase(): string
    {
        $url = (string) Settings::obter('asaas.url_base', 'https://api.asaas.com/v3');
        return $url !== '' ? $url : 'https://api.asaas.com/v3';
    }

    public static function webhookSegredoAsaas(): string
    {
        return (string) Settings::obter('asaas.webhook_segredo', '');
    }

    public static function stripeSecretKey(): string
    {
        return (string) Settings::obter('stripe.secret_key', '');
    }

    public static function stripePublishableKey(): string
    {
        return (string) Settings::obter('stripe.publishable_key', '');
    }

    public static function stripeWebhookSecret(): string
    {
        return (string) Settings::obter('stripe.webhook_secret', '');
    }

    public static function appUrlBase(): string
    {
        $url = trim((string) Settings::obter('app.url_base', ''));
        if ($url !== '') {
            return rtrim($url, '/');
        }

        // Fallback automático: deriva da requisição atual
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        return $scheme . '://' . $host;
    }

    public static function evolutionUrlBase(): string
    {
        return (string) Settings::obter('whatsapp.evolution.url_base', '');
    }

    public static function evolutionToken(): string
    {
        return (string) Settings::obter('whatsapp.evolution.token', '');
    }

    public static function emailAdmin(): string
    {
        return (string) Settings::obter('alertas.email_admin', '');
    }

    public static function whatsappAdminNumero(): string
    {
        return (string) Settings::obter('alertas.whatsapp_admin_numero', '');
    }

    public static function evolutionInstance(): string
    {
        return (string) Settings::obter('whatsapp.evolution.instance', '');
    }

    public static function sshKeyDir(): string
    {
        $v = trim((string) Settings::obter('infra.ssh_key_dir', ''));
        if ($v !== '') return $v;
        // Default: storage/ssh-keys relativo à raiz do projeto
        return defined('BASE_PATH')
            ? BASE_PATH . '/storage/ssh-keys'
            : dirname(__DIR__) . '/storage/ssh-keys';
    }

    public static function monitoringToken(): string
    {
        return (string) Settings::obter('monitoring.token', '');
    }

    public static function terminalWsInternalPort(): int
    {
        $v = Settings::obter('terminal.ws_internal_port', 8081);
        $porta = is_int($v) ? $v : (int) $v;
        if ($porta <= 0 || $porta > 65535) {
            return 8081;
        }
        return $porta;
    }

    public static function terminalTokenTtlSegundos(): int
    {
        $v = Settings::obter('terminal.token_ttl_seconds', 60);
        $ttl = is_int($v) ? $v : (int) $v;
        if ($ttl < 10) {
            return 60;
        }
        return $ttl;
    }

    public static function terminalIdleTimeoutSegundos(): int
    {
        $v = Settings::obter('terminal.idle_timeout_seconds', 900);
        $ttl = is_int($v) ? $v : (int) $v;
        if ($ttl < 60) {
            return 900;
        }
        return $ttl;
    }

    public static function terminalSafeModeHabilitado(): bool
    {
        $v = Settings::obter('terminal.safe_mode', 1);
        if (is_bool($v)) {
            return $v;
        }
        if (is_int($v)) {
            return $v === 1;
        }
        $s = strtolower(trim((string) $v));
        if ($s === '1' || $s === 'true' || $s === 'on' || $s === 'yes') {
            return true;
        }
        if ($s === '0' || $s === 'false' || $s === 'off' || $s === 'no') {
            return false;
        }
        return true;
    }

    public static function infraNodeMaxUtilPercent(): int
    {
        $v = Settings::obter('infra.node_max_util_percent', 85);
        $p = is_int($v) ? $v : (int) $v;
        if ($p < 50) {
            return 85;
        }
        if ($p > 100) {
            return 100;
        }
        return $p;
    }

    public static function taxaConversaoUsd(): float
    {
        $v = Settings::obter('billing.taxa_conversao_usd', 5.0);
        $taxa = is_float($v) ? $v : (float) $v;
        return $taxa > 0 ? $taxa : 5.0;
    }
}
