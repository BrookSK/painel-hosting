<?php

declare(strict_types=1);

namespace LRV\Core;

use PDO;

final class BancoDeDados
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = Configuracao::obter();
        $banco = $cfg['banco'] ?? null;
        if (!is_array($banco)) {
            throw new \RuntimeException('Configuração do banco não encontrada.');
        }

        $driver = (string) ($banco['driver'] ?? 'mysql');
        $host = (string) ($banco['host'] ?? '127.0.0.1');
        $porta = (int) ($banco['porta'] ?? 3306);
        $database = (string) ($banco['database'] ?? '');
        $usuario = (string) ($banco['usuario'] ?? '');
        $senha = (string) ($banco['senha'] ?? '');
        $charset = (string) ($banco['charset'] ?? 'utf8mb4');

        $dsn = $driver . ':host=' . $host . ';port=' . $porta . ';dbname=' . $database . ';charset=' . $charset;

        self::$pdo = new PDO($dsn, $usuario, $senha, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }
}
