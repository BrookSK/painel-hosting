<?php

declare(strict_types=1);

namespace LRV\Core;

final class AppLogger
{
    private static function logDir(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    }

    private static function escrever(string $nivel, string $mensagem, array $contexto = []): void
    {
        try {
            $dir = self::logDir();
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $arquivo = $dir . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log';
            $ts = date('Y-m-d H:i:s');
            $ctx = $contexto !== [] ? ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $linha = "[{$ts}] [{$nivel}] {$mensagem}{$ctx}" . PHP_EOL;

            $fp = @fopen($arquivo, 'a');
            if ($fp !== false) {
                @flock($fp, LOCK_EX);
                @fwrite($fp, $linha);
                @flock($fp, LOCK_UN);
                @fclose($fp);
            }
        } catch (\Throwable $e) {
        }
    }

    public static function info(string $mensagem, array $contexto = []): void
    {
        self::escrever('INFO', $mensagem, $contexto);
    }

    public static function aviso(string $mensagem, array $contexto = []): void
    {
        self::escrever('WARNING', $mensagem, $contexto);
    }

    public static function erro(string $mensagem, array $contexto = []): void
    {
        self::escrever('ERROR', $mensagem, $contexto);
    }

    public static function debug(string $mensagem, array $contexto = []): void
    {
        self::escrever('DEBUG', $mensagem, $contexto);
    }
}
