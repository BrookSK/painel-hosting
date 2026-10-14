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

            // Conta inativada/em exclusão: derruba sessões já abertas.
            // A equipe impersonando continua podendo ver a conta.
            if (!Auth::estaImpersonando() && Auth::clienteBloqueado((int) Auth::clienteId())) {
                Auth::sairCliente();
                return Resposta::redirecionar('/cliente/entrar?conta=inativa');
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

    /**
     * Bloqueia acesso para clientes gerenciados (is_managed),
     * exceto quando a equipe está impersonando ou managed_show_all_tabs está ativo.
     */
    public static function bloquearClienteGerenciado(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            if (Auth::estaImpersonando()) {
                return null; // equipe impersonando → acesso total
            }
            if (Auth::clienteGerenciado()) {
                if (Auth::clienteGerenciadoMostrarTodasAbas()) {
                    // Mesmo com todas as abas, Terminal e Arquivos ficam bloqueados pra gerenciados
                    $uri = strtok((string)($req->uri ?? $_SERVER['REQUEST_URI'] ?? ''), '?');
                    if (str_starts_with($uri, '/cliente/vps/terminal') || str_starts_with($uri, '/cliente/arquivos')) {
                        return Resposta::redirecionar('/cliente/painel');
                    }
                    return null; // demais rotas liberadas
                }
                return Resposta::redirecionar('/cliente/painel');
            }
            return null;
        };
    }

    /**
     * Recursos que executam comandos direto no servidor compartilhado (Git Deploy etc.).
     * Só para contas liberadas pela equipe — senão um cliente vira porta de entrada
     * para o servidor inteiro (outros clientes e sites hospedados manualmente).
     * A equipe impersonando continua com acesso.
     */
    public static function exigirAcessoServidor(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            $id = Auth::clienteId();
            if ($id === null) {
                return null; // exigirLoginCliente cuida
            }
            if (Auth::estaImpersonando() || Auth::clienteTemAcessoServidor($id)) {
                return null;
            }
            $msg = 'Este recurso roda direto no servidor e precisa ser liberado pela nossa equipe. Abra um chamado para solicitar.';
            $accept = strtolower((string) ($req->headers['accept'] ?? ''));
            $ajax = strtolower((string) ($req->headers['x-requested-with'] ?? '')) === 'xmlhttprequest';
            $fetchJs = $req->metodo !== 'GET' && !str_contains($accept, 'text/html');
            if ($ajax || str_contains($accept, 'application/json') || $fetchJs) {
                return Resposta::json(['ok' => false, 'erro' => $msg], 403);
            }
            $_SESSION['flash_warning'] = $msg;
            return Resposta::redirecionar('/cliente/painel');
        };
    }

    /**
     * Verifica se o cliente tem acesso à rota com base no tipo de plano.
     * Redireciona para o painel se a feature não é permitida.
     */
    public static function verificarFeaturePlano(): callable
    {
        return static function (Requisicao $req): ?Resposta {
            $clienteId = Auth::clienteId();
            if ($clienteId === null) {
                return null; // Sem login, outro middleware cuida
            }

            // Equipe impersonando tem acesso total
            if (Auth::estaImpersonando()) {
                return null;
            }

            try {
                $permitida = \LRV\App\Services\Plans\PlanFeatureService::rotaPermitida($clienteId, $req->caminho);
            } catch (\Throwable) {
                return null; // Em caso de erro, não bloquear
            }

            if (!$permitida) {
                $_SESSION['flash_warning'] = 'Seu plano não inclui acesso a este recurso. Faça upgrade para desbloquear.';
                return Resposta::redirecionar('/cliente/painel');
            }

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
        // Só confia em X-Forwarded-For vindo de proxy confiável (senão o rate limit é burlável)
        return IpCliente::obter($req->headers);
    }
}
