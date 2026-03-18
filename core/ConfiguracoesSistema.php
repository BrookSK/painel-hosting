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
}
