<?php

declare(strict_types=1);

namespace LRV\Core;

final class RateLimiter
{
    private static function dir(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'rate_limit';
    }

    private static function arquivo(string $key): string
    {
        $hash = sha1($key);
        return self::dir() . DIRECTORY_SEPARATOR . $hash . '.json';
    }

    public static function consumir(string $key, int $limite, int $janelaSegundos): array
    {
        $key = trim($key);
        if ($key === '' || $limite <= 0 || $janelaSegundos <= 0) {
            return [
                'ok' => true,
                'remaining' => null,
                'reset_at' => null,
                'retry_after' => null,
            ];
        }

        $dir = self::dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $path = self::arquivo($key);

        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            return [
                'ok' => true,
                'remaining' => null,
                'reset_at' => null,
                'retry_after' => null,
            ];
        }

        $agora = time();

        try {
            @flock($fp, LOCK_EX);

            $conteudo = '';
            try {
                @rewind($fp);
                $conteudo = (string) @stream_get_contents($fp);
            } catch (\Throwable $e) {
            }

            $dados = [];
            if (trim($conteudo) !== '') {
                $dec = json_decode($conteudo, true);
                if (is_array($dec)) {
                    $dados = $dec;
                }
            }

            $resetAt = (int) ($dados['reset_at'] ?? 0);
            $count = (int) ($dados['count'] ?? 0);

            if ($resetAt <= 0 || $resetAt <= $agora) {
                $resetAt = $agora + $janelaSegundos;
                $count = 0;
            }

            if ($count >= $limite) {
                $retryAfter = $resetAt - $agora;
                if ($retryAfter < 0) {
                    $retryAfter = 0;
                }

                return [
                    'ok' => false,
                    'remaining' => 0,
                    'reset_at' => $resetAt,
                    'retry_after' => $retryAfter,
                ];
            }

            $count++;

            $novo = json_encode([
                'reset_at' => $resetAt,
                'count' => $count,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            try {
                @ftruncate($fp, 0);
                @rewind($fp);
                @fwrite($fp, (string) $novo);
                @fflush($fp);
            } catch (\Throwable $e) {
            }

            $remaining = $limite - $count;
            if ($remaining < 0) {
                $remaining = 0;
            }

            return [
                'ok' => true,
                'remaining' => $remaining,
                'reset_at' => $resetAt,
                'retry_after' => null,
            ];
        } finally {
            try {
                @flock($fp, LOCK_UN);
            } catch (\Throwable $e) {
            }
            try {
                @fclose($fp);
            } catch (\Throwable $e) {
            }
        }
    }
}
