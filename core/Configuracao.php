<?php

declare(strict_types=1);

namespace LRV\Core;

final class Configuracao
{
    private static ?array $cache = null;

    public static function obter(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $arquivo = __DIR__ . '/../config/instalacao.php';
        if (!is_file($arquivo)) {
            throw new \RuntimeException('Arquivo de instalação não encontrado. Crie config/instalacao.php a partir do exemplo.');
        }

        $config = require $arquivo;
        if (!is_array($config)) {
            throw new \RuntimeException('Configuração inválida.');
        }

        self::$cache = $config;
        return $config;
    }
}
