<?php

declare(strict_types=1);

namespace LRV\Core;

/**
 * Helper central para CPF e CNPJ (documento fiscal brasileiro).
 *
 * O Asaas aceita os dois no mesmo campo `cpfCnpj` (11 dígitos = CPF, 14 = CNPJ).
 * Esta classe normaliza (só dígitos), detecta o tipo, valida o dígito verificador
 * real e formata para exibição — usada em TODOS os fluxos de pagamento.
 */
final class Documento
{
    /** Remove tudo que não for dígito. */
    public static function normalizar(string $valor): string
    {
        return preg_replace('/\D/', '', $valor) ?? '';
    }

    /** Retorna 'cpf', 'cnpj' ou '' (indefinido) conforme a quantidade de dígitos. */
    public static function tipo(string $valor): string
    {
        $d = self::normalizar($valor);
        return match (strlen($d)) {
            11 => 'cpf',
            14 => 'cnpj',
            default => '',
        };
    }

    /**
     * Valida CPF ou CNPJ (incluindo dígito verificador).
     * Aceita com ou sem máscara.
     */
    public static function valido(string $valor): bool
    {
        $d = self::normalizar($valor);
        return match (strlen($d)) {
            11 => self::cpfValido($d),
            14 => self::cnpjValido($d),
            default => false,
        };
    }

    /** Formata para exibição: 000.000.000-00 ou 00.000.000/0000-00. */
    public static function formatar(string $valor): string
    {
        $d = self::normalizar($valor);
        if (strlen($d) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d) ?? $d;
        }
        if (strlen($d) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d) ?? $d;
        }
        return $d;
    }

    private static function cpfValido(string $cpf): bool
    {
        // Rejeita sequências repetidas (00000000000, 11111111111, ...)
        if (preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }
        return true;
    }

    private static function cnpjValido(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        $calc = static function (string $base, array $pesos): int {
            $soma = 0;
            foreach ($pesos as $i => $peso) {
                $soma += (int) $base[$i] * $peso;
            }
            $resto = $soma % 11;
            return $resto < 2 ? 0 : 11 - $resto;
        };

        $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $d1 = $calc(substr($cnpj, 0, 12), $pesos1);
        if ((int) $cnpj[12] !== $d1) {
            return false;
        }

        $d2 = $calc(substr($cnpj, 0, 13), $pesos2);
        return (int) $cnpj[13] === $d2;
    }
}
