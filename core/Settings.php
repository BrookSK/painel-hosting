<?php

declare(strict_types=1);

namespace LRV\Core;

use PDO;

final class Settings
{
    private static array $cache = [];
    private static bool $carregado = false;

    public static function obter(string $chave, mixed $padrao = null): mixed
    {
        self::carregar();

        if (!array_key_exists($chave, self::$cache)) {
            return $padrao;
        }

        return self::decodificar(self::$cache[$chave]);
    }

    public static function definir(string $chave, mixed $valor): void
    {
        $valorDb = self::codificar($valor);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        $stmt->execute([
            ':k' => $chave,
            ':v' => $valorDb,
        ]);

        self::$cache[$chave] = $valorDb;
        self::$carregado = true;
    }

    public static function carregar(): void
    {
        if (self::$carregado) {
            return;
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT `key`, `value` FROM settings');
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        self::$cache = [];
        foreach ($linhas as $linha) {
            $k = (string) ($linha['key'] ?? '');
            if ($k === '') {
                continue;
            }
            self::$cache[$k] = (string) ($linha['value'] ?? '');
        }

        self::$carregado = true;
    }

    public static function limparCache(): void
    {
        self::$cache = [];
        self::$carregado = false;
    }

    private static function codificar(mixed $valor): string
    {
        if (is_bool($valor)) {
            return $valor ? 'true' : 'false';
        }

        if (is_int($valor) || is_float($valor)) {
            return (string) $valor;
        }

        if (is_array($valor) || is_object($valor)) {
            return (string) json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($valor === null) {
            return '';
        }

        return (string) $valor;
    }

    private static function decodificar(string $valor): mixed
    {
        $trim = trim($valor);

        if ($trim === 'true') {
            return true;
        }

        if ($trim === 'false') {
            return false;
        }

        if ($trim === '') {
            return '';
        }

        if ($trim[0] === '{' || $trim[0] === '[') {
            $dec = json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $dec;
            }
        }

        if (preg_match('/^-?\d+$/', $trim) === 1) {
            return (int) $trim;
        }

        if (preg_match('/^-?\d+\.\d+$/', $trim) === 1) {
            return (float) $trim;
        }

        return $valor;
    }
}
