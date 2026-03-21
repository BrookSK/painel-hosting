<?php

declare(strict_types=1);

namespace LRV\Core;

final class SistemaConfig
{
    public static function nome(): string
    {
        $v = trim((string) Settings::obter('system.name', ''));
        return $v !== '' ? $v : 'LRV Cloud Manager';
    }

    public static function logoUrl(): string
    {
        return trim((string) Settings::obter('system.logo_url', ''));
    }

    public static function faviconUrl(): string
    {
        return trim((string) Settings::obter('system.favicon_url', ''));
    }

    public static function empresaNome(): string
    {
        $v = trim((string) Settings::obter('system.company_name', ''));
        return $v !== '' ? $v : 'LRV Cloud';
    }

    public static function copyrightText(): string
    {
        $v = trim((string) Settings::obter('system.copyright_text', ''));
        return $v !== '' ? $v : '© ' . date('Y') . ' LRV Cloud';
    }

    public static function termsHtml(): string
    {
        return (string) Settings::obter('legal.terms_html', '');
    }

    public static function privacyHtml(): string
    {
        return (string) Settings::obter('legal.privacy_html', '');
    }

    public static function versao(): string
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        $arquivo = defined('BASE_PATH')
            ? BASE_PATH . '/CHANGELOG.md'
            : dirname(__DIR__) . '/CHANGELOG.md';

        if (is_file($arquivo)) {
            $conteudo = file_get_contents($arquivo);
            if ($conteudo !== false && preg_match('/##\s+\[(\d+\.\d+\.\d+)\]/', $conteudo, $m)) {
                $cache = $m[1];
                return $cache;
            }
        }

        $cache = '1.0.0';
        return $cache;
    }
}
