<?php

declare(strict_types=1);

namespace LRV\Core;

final class I18n
{
    private static string $idioma = 'pt-BR';
    private static array $cache = [];

    public static function definirIdioma(string $idioma): void
    {
        self::$idioma = $idioma;
    }

    public static function idioma(): string
    {
        return self::$idioma;
    }

    public static function t(string $chave): string
    {
        $idioma = self::$idioma;
        if (!isset(self::$cache[$idioma])) {
            $arquivo = __DIR__ . '/../app/Idiomas/' . $idioma . '.php';
            if (is_file($arquivo)) {
                $dados = require $arquivo;
                self::$cache[$idioma] = is_array($dados) ? $dados : [];
            } else {
                self::$cache[$idioma] = [];
            }
        }

        return (string) (self::$cache[$idioma][$chave] ?? $chave);
    }

    /**
     * Traduz com substituição de placeholders (%s, %d).
     */
    public static function tf(string $chave, mixed ...$args): string
    {
        $tpl = self::t($chave);
        if (count($args) === 0) return $tpl;
        return sprintf($tpl, ...$args);
    }

    /**
     * Retorna a chave de settings com sufixo do idioma atual.
     * Ex: legal.terms_html → legal.terms_html.en-US (se idioma != pt-BR)
     * Fallback para a chave sem sufixo (pt-BR é o padrão).
     */
    public static function settingsKey(string $baseKey): string
    {
        $idioma = self::$idioma;
        if ($idioma === 'pt-BR') {
            return $baseKey;
        }
        return $baseKey . '.' . $idioma;
    }
}
