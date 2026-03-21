<?php

declare(strict_types=1);

namespace LRV\Core;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class Roteador
{
    private array $rotas = [];

    public function get(string $caminho, callable|array $handler, array $middlewares = []): void
    {
        $this->adicionar('GET', $caminho, $handler, $middlewares);
    }

    public function post(string $caminho, callable|array $handler, array $middlewares = []): void
    {
        $this->adicionar('POST', $caminho, $handler, $middlewares);
    }

    private function adicionar(string $metodo, string $caminho, callable|array $handler, array $middlewares): void
    {
        $caminhoNormalizado = $this->normalizarCaminho($caminho);
        $this->rotas[$metodo][$caminhoNormalizado] = [
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function despachar(): void
    {
        $req = Requisicao::aPartirDoPhp();
        $metodo = $req->metodo;
        $caminho = $this->normalizarCaminho($req->caminho);

        $rota = $this->rotas[$metodo][$caminho] ?? null;

        if ($rota === null) {
            \LRV\App\Services\Errors\ErrorLogService::registrar(
                404,
                'not_found',
                'Rota não encontrada: ' . $metodo . ' ' . $caminho,
            );
            $this->renderizarErro(404)->enviar();
            return;
        }

        $handler = $rota['handler'] ?? null;
        $middlewares = $rota['middlewares'] ?? [];

        try {
            if ($metodo === 'POST' && $this->csrfObrigatorio($caminho)) {
                $token = (string) ($req->post['_csrf'] ?? ($req->headers['x-csrf-token'] ?? ''));
                if (!Csrf::validar($token)) {
                    $this->responderCsrfInvalido($req)->enviar();
                    return;
                }
            }

            foreach ($middlewares as $mw) {
                $resultadoMw = $mw($req);
                if ($resultadoMw instanceof Resposta) {
                    $resultadoMw->enviar();
                    return;
                }
            }

            $resultado = $this->executarHandler($handler, $req);

            if ($resultado instanceof Resposta) {
                $resultado->enviar();
                return;
            }

            if (is_array($resultado)) {
                Resposta::json($resultado)->enviar();
                return;
            }

            Resposta::html((string) $resultado)->enviar();
        } catch (\Throwable $e) {
            $errorId = \LRV\App\Services\Errors\ErrorLogService::registrar(
                500,
                'exception',
                $e->getMessage(),
                $e,
            );
            $this->renderizarErro(500, $errorId)->enviar();
        }
    }

    private function executarHandler(callable|array $handler, Requisicao $req): mixed
    {
        if (is_array($handler)) {
            [$classe, $metodo] = $handler;
            $obj = new $classe();
            return $obj->$metodo($req);
        }

        return $handler($req);
    }

    private function normalizarCaminho(string $caminho): string
    {
        $caminho = '/' . trim($caminho, '/');
        return $caminho === '/' ? '/' : $caminho;
    }

    private function csrfObrigatorio(string $caminho): bool
    {
        if (str_starts_with($caminho, '/webhooks/')) {
            return false;
        }
        if (str_starts_with($caminho, '/api/metrics/')) {
            return false;
        }
        if (str_starts_with($caminho, '/api/worker/')) {
            return false;
        }
        return true;
    }

    private function responderCsrfInvalido(Requisicao $req): Resposta
    {
        $accept = strtolower((string) ($req->headers['accept'] ?? ''));
        if (str_contains($accept, 'application/json')) {
            return Resposta::json(['ok' => false, 'erro' => 'csrf_invalid'], 419);
        }
        \LRV\App\Services\Errors\ErrorLogService::registrar(
            419,
            'csrf',
            'CSRF inválido: ' . $req->metodo . ' ' . $req->caminho,
        );
        return $this->renderizarErro(419);
    }

    private function renderizarErro(int $code, int $errorId = 0): Resposta
    {
        try {
            $html = \LRV\Core\View::renderizar(__DIR__ . '/../app/Views/erros/erro.php', [
                'code'    => $code,
                'errorId' => $errorId > 0 ? $errorId : null,
            ]);
            return Resposta::html($html, $code);
        } catch (\Throwable) {
            return Resposta::texto('Erro ' . $code, $code);
        }
    }
}
