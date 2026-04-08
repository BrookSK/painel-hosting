<?php

declare(strict_types=1);

namespace LRV\Core;

final class I18n
{
    private static string $idioma = 'pt-BR';
    private static string $moedaCodigo = 'BRL';
    private static array $cache = [];

    public static function definirIdioma(string $idioma): void
    {
        self::$idioma = $idioma;
    }

    public static function definirMoeda(string $moeda): void
    {
        $moeda = strtoupper(trim($moeda));
        if (in_array($moeda, ['BRL', 'USD'], true)) {
            self::$moedaCodigo = $moeda;
        }
    }

    public static function idioma(): string
    {
        return self::$idioma;
    }

    public static function moedaCodigo(): string
    {
        return self::$moedaCodigo;
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
        if (self::$moedaCodigo === 'BRL') {
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
        if (self::$moedaCodigo === 'BRL') {
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
        return self::$moedaCodigo === 'BRL' ? 'R$' : '$';
    }

    /**
     * Retorna o preço de exibição de um plano, considerando moeda e preço USD fixo.
     * Usa price_monthly_usd se disponível, senão converte price_monthly.
     */
    public static function precoPlano(array $plano): string
    {
        $priceBrl = (float)($plano['price_monthly'] ?? 0);
        $priceUsd = (float)($plano['price_monthly_usd'] ?? 0);
        $currency = (string)($plano['currency'] ?? 'BRL');

        // Se o plano é em USD e tem preço USD definido
        if ($currency === 'USD' && $priceUsd > 0) {
            return 'US$ ' . number_format($priceUsd, 2, '.', ',');
        }
        if ($priceUsd > 0 && self::$moedaCodigo === 'USD') {
            return 'US$ ' . number_format($priceUsd, 2, '.', ',');
        }
        if ($priceBrl > 0) {
            return self::preco($priceBrl);
        }
        // Fallback: converter USD pra exibição
        if ($priceUsd > 0) {
            return 'US$ ' . number_format($priceUsd, 2, '.', ',');
        }
        return self::preco(0);
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
