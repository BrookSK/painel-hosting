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
            Resposta::texto(I18n::t('geral.rota_nao_encontrada'), 404)->enviar();
            return;
        }

        $handler = $rota['handler'] ?? null;
        $middlewares = $rota['middlewares'] ?? [];

        try {
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
            Resposta::texto(I18n::t('geral.erro_interno'), 500)->enviar();
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
}
