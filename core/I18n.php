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

    /**
     * Formata um preço em BRL convertendo para USD quando o idioma não é pt-BR.
     * Usa a taxa de conversão configurada pelo admin.
     */
    public static function preco(float $valorBrl): string
    {
        if (self::$idioma === 'pt-BR') {
            return 'R$ ' . number_format($valorBrl, 2, ',', '.');
        }
        $taxa = \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd();
        $valorUsd = $valorBrl / $taxa;
        return '$ ' . number_format($valorUsd, 2, '.', ',');
    }

    /**
     * Retorna apenas o valor numérico convertido (sem símbolo de moeda).
     */
    public static function precoValor(float $valorBrl): float
    {
        if (self::$idioma === 'pt-BR') {
            return $valorBrl;
        }
        $taxa = \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd();
        return round($valorBrl / $taxa, 2);
    }

    /**
     * Retorna o símbolo da moeda atual.
     */
    public static function moeda(): string
    {
        return self::$idioma === 'pt-BR' ? 'R$' : '$';
    }

    /**
     * Formata um número no padrão do idioma atual.
     */
    public static function numero(float $valor, int $decimais = 2): string
    {
        if (self::$idioma === 'pt-BR') {
            return number_format($valor, $decimais, ',', '.');
        }
        return number_format($valor, $decimais, '.', ',');
    }
}
