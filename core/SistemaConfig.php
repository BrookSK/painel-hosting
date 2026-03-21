<?php

declare(strict_types=1);

namespace LRV\Core;

use LRV\Core\ConfiguracoesSistema;
use LRV\Core\I18n;

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
        return $v !== '' ? $v : '© ' . date('Y') . ' LRV Web';
    }

    public static function termsHtml(): string
    {
        $key = I18n::settingsKey('legal.terms_html');
        $val = (string) Settings::obter($key, '');
        if ($val === '' && $key !== 'legal.terms_html') {
            $val = (string) Settings::obter('legal.terms_html', '');
        }
        return $val;
    }

    public static function privacyHtml(): string
    {
        $key = I18n::settingsKey('legal.privacy_html');
        $val = (string) Settings::obter($key, '');
        if ($val === '' && $key !== 'legal.privacy_html') {
            $val = (string) Settings::obter('legal.privacy_html', '');
        }
        return $val;
    }

    public static function licenseHtml(): string
    {
        $key = I18n::settingsKey('legal.license_html');
        $val = (string) Settings::obter($key, '');
        if ($val === '' && $key !== 'legal.license_html') {
            $val = (string) Settings::obter('legal.license_html', '');
        }
        return $val;
    }

    // ── SEO ─────────────────────────────────────────────────

    public static function seoTitulo(): string
    {
        return trim((string) Settings::obter('seo.titulo', ''));
    }

    public static function seoDescricao(): string
    {
        return trim((string) Settings::obter('seo.descricao', ''));
    }

    public static function seoPalavrasChave(): string
    {
        return trim((string) Settings::obter('seo.palavras_chave', ''));
    }

    public static function seoOgImage(): string
    {
        return trim((string) Settings::obter('seo.og_image', ''));
    }

    public static function seoRobots(): string
    {
        $v = trim((string) Settings::obter('seo.robots', 'index, follow'));
        return $v !== '' ? $v : 'index, follow';
    }

    public static function seoGoogleAnalyticsId(): string
    {
        return trim((string) Settings::obter('seo.google_analytics_id', ''));
    }

    public static function seoCanonicalBase(): string
    {
        $v = trim((string) Settings::obter('seo.canonical_base', ''));
        if ($v !== '') return rtrim($v, '/');
        return ConfiguracoesSistema::appUrlBase();
    }

    public static function seoSchemaType(): string
    {
        $v = trim((string) Settings::obter('seo.schema_type', 'Organization'));
        return $v !== '' ? $v : 'Organization';
    }

    public static function versao(): string
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        $arquivo = \defined('BASE_PATH')
            ? \BASE_PATH . '/CHANGELOG.md'
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
