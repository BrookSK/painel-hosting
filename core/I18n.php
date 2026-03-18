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
}
