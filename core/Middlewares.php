<?php

declare(strict_types=1);

namespace LRV\Core;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class Middlewares
{
    public static function exigirLoginEquipe(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            if (Auth::equipeId() === null) {
                $_SESSION['redirect_after_login'] = $req->caminho . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
                return Resposta::redirecionar('/equipe/entrar');
            }

            // Verificar expiração de sessão por inatividade (24h)
            $lastActivity = (int) ($_SESSION['equipe_last_activity'] ?? 0);
            $timeout = 86400; // 24 horas
            if ($lastActivity > 0 && (time() - $lastActivity) > $timeout) {
                $caminho = $req->caminho . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['redirect_after_login'] = $caminho;
                return Resposta::redirecionar('/equipe/entrar?sessao=expirada');
            }
            $_SESSION['equipe_last_activity'] = time();

            return null;
        };
    }

    public static function exigirLoginCliente(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            if (Auth::clienteId() === null) {
                $_SESSION['redirect_after_login'] = $req->caminho . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
                return Resposta::redirecionar('/cliente/entrar');
            }

            // Verificar expiração de sessão por inatividade (24h)
            $lastActivity = (int) ($_SESSION['cliente_last_activity'] ?? 0);
            $timeout = 86400; // 24 horas
            if ($lastActivity > 0 && (time() - $lastActivity) > $timeout) {
                $caminho = $req->caminho . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['redirect_after_login'] = $caminho;
                return Resposta::redirecionar('/cliente/entrar?sessao=expirada');
            }
            $_SESSION['cliente_last_activity'] = time();

            return null;
        };
    }

    public static function exigirPermissao(string $permissao): callable
    {
        return static function (Requisicao $req) use ($permissao): ?Resposta {
            $id = Auth::equipeId();
            if ($id === null) {
                return Resposta::redirecionar('/equipe/entrar');
            }

            try {
                if (!Rbac::temPermissao($id, $permissao)) {
                    return Resposta::texto('Acesso negado.', 403);
                }
            } catch (\Throwable $e) {
                return Resposta::texto('Acesso negado.', 403);
            }

            return null;
        };
    }

    public static function rateLimitIp(string $nome, int $limite, int $janelaSegundos): callable
    {
        return static function (Requisicao $req) use ($nome, $limite, $janelaSegundos): ?Resposta {
            $ip = self::ipDoRequest($req);
            $key = 'ip:' . $nome . ':' . $ip;
            return self::aplicarRateLimit($req, $key, $limite, $janelaSegundos);
        };
    }

    public static function rateLimitCliente(string $nome, int $limite, int $janelaSegundos): callable
    {
        return static function (Requisicao $req) use ($nome, $limite, $janelaSegundos): ?Resposta {
            $clienteId = Auth::clienteId();
            if ($clienteId !== null && $clienteId > 0) {
                $key = 'client:' . $nome . ':' . $clienteId;
            } else {
                $key = 'ip:' . $nome . ':' . self::ipDoRequest($req);
            }
            return self::aplicarRateLimit($req, $key, $limite, $janelaSegundos);
        };
    }

    public static function rateLimitEquipe(string $nome, int $limite, int $janelaSegundos): callable
    {
        return static function (Requisicao $req) use ($nome, $limite, $janelaSegundos): ?Resposta {
            $equipeId = Auth::equipeId();
            if ($equipeId !== null && $equipeId > 0) {
                $key = 'team:' . $nome . ':' . $equipeId;
            } else {
                $key = 'ip:' . $nome . ':' . self::ipDoRequest($req);
            }
            return self::aplicarRateLimit($req, $key, $limite, $janelaSegundos);
        };
    }

    private static function aplicarRateLimit(Requisicao $req, string $key, int $limite, int $janelaSegundos): ?Resposta
    {
        $r = RateLimiter::consumir($key, $limite, $janelaSegundos);
        if (!empty($r['ok'])) {
            return null;
        }

        $retryAfter = (int) ($r['retry_after'] ?? 0);
        $resetAt = (int) ($r['reset_at'] ?? 0);

        $headers = [
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Limit' => (string) $limite,
            'X-RateLimit-Remaining' => '0',
        ];
        if ($resetAt > 0) {
            $headers['X-RateLimit-Reset'] = (string) $resetAt;
        }

        $accept = strtolower((string) ($req->headers['accept'] ?? ''));
        $isApi = str_starts_with($req->caminho, '/api/');
        if ($isApi || str_contains($accept, 'application/json')) {
            return Resposta::json(['ok' => false, 'erro' => 'rate_limited'], 429)->comHeaders($headers);
        }

        return Resposta::texto('Muitas requisições. Tente novamente em alguns instantes.', 429)->comHeaders($headers);
    }

    private static function ipDoRequest(Requisicao $req): string
    {
        $xff = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
        if ($xff !== '') {
            $partes = array_map('trim', explode(',', $xff));
            $ip = trim((string) ($partes[0] ?? ''));
            if ($ip !== '') {
                return $ip;
            }
        }

        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        return $ip !== '' ? $ip : '0.0.0.0';
    }
}
