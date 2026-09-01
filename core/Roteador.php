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

    public function put(string $caminho, callable|array $handler, array $middlewares = []): void
    {
        $this->adicionar('PUT', $caminho, $handler, $middlewares);
    }

    public function patch(string $caminho, callable|array $handler, array $middlewares = []): void
    {
        $this->adicionar('PATCH', $caminho, $handler, $middlewares);
    }

    public function delete(string $caminho, callable|array $handler, array $middlewares = []): void
    {
        $this->adicionar('DELETE', $caminho, $handler, $middlewares);
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
        $parametros = [];

        // Fallback: rotas com parâmetros dinâmicos (ex: /webhooks/git-deploy/{secret})
        if ($rota === null) {
            [$rota, $parametros] = $this->casarRotaDinamica($metodo, $caminho);
        }

        if ($rota === null) {
            // Ignorar rotas automáticas de browsers/OS que geram ruído
            if (str_starts_with($caminho, '/.well-known/') || $caminho === '/favicon.ico' || $caminho === '/robots.txt') {
                Resposta::texto('', 404)->enviar();
                return;
            }
            \LRV\App\Services\Errors\ErrorLogService::registrar(
                404,
                'not_found',
                'Rota não encontrada: ' . $metodo . ' ' . $caminho,
            );
            $this->renderizarErro(404)->enviar();
            return;
        }

        // Injetar parâmetros dinâmicos na requisição
        if ($parametros !== []) {
            $req->params = $parametros;
        }

        $handler = $rota['handler'] ?? null;
        $middlewares = $rota['middlewares'] ?? [];

        try {
            if (in_array($metodo, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $this->csrfObrigatorio($caminho)) {
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
                $this->logApiPublica($req, $resultado);
                $resultado->enviar();
                return;
            }

            if (is_array($resultado)) {
                $resp = Resposta::json($resultado);
                $this->logApiPublica($req, $resp);
                $resp->enviar();
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

    /**
     * Tenta casar o caminho contra rotas com parâmetros dinâmicos ({param}).
     *
     * @return array{0: array|null, 1: array<string,string>}
     */
    private function casarRotaDinamica(string $metodo, string $caminho): array
    {
        $rotasDoMetodo = $this->rotas[$metodo] ?? [];

        foreach ($rotasDoMetodo as $padrao => $rota) {
            // Só interessa padrões que contenham parâmetros dinâmicos
            if (!str_contains($padrao, '{')) {
                continue;
            }

            // Converter /webhooks/git-deploy/{secret} em regex nomeada.
            // preg_quote escapa tudo; desfazemos as chaves para o callback substituir.
            $regex = str_replace(['\{', '\}'], ['{', '}'], preg_quote($padrao, '#'));
            $regex = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                static fn(array $m): string => '(?P<' . $m[1] . '>[^/]+)',
                $regex
            );

            if (preg_match('#^' . $regex . '$#', $caminho, $matches)) {
                $parametros = [];
                foreach ($matches as $chave => $valor) {
                    if (is_string($chave)) {
                        $parametros[$chave] = $valor;
                    }
                }
                return [$rota, $parametros];
            }
        }

        return [null, []];
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
        if (str_starts_with($caminho, '/api/v1/')) {
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

        // Para formulários de login/autenticação, redirecionar de volta
        // em vez de mostrar erro genérico (sessão expirada é comum em mobile)
        $caminho = $this->normalizarCaminho($req->caminho);
        $rotasRedirect = [
            '/cliente/entrar'       => '/cliente/entrar',
            '/equipe/entrar'        => '/equipe/entrar',
            '/cliente/reset-senha'  => '/cliente/reset-senha',
            '/equipe/reset-senha'   => '/equipe/reset-senha',
        ];
        if (isset($rotasRedirect[$caminho])) {
            return Resposta::redirecionar($rotasRedirect[$caminho]);
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

    /**
     * Registra requisição no log da API Pública (apenas /api/v1/).
     */
    private function logApiPublica(Requisicao $req, Resposta $resposta): void
    {
        if (!str_starts_with($req->caminho, '/api/v1/')) {
            return;
        }

        try {
            \LRV\App\Services\PublicApi\ApiRequestLogger::registrar($req, $resposta->statusCode());
        } catch (\Throwable) {
            // Nunca bloquear a resposta por falha de logging
        }
    }
}
