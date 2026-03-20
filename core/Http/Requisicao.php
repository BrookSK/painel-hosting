<?php

declare(strict_types=1);

namespace LRV\Core\Http;

final class Requisicao
{
    public function __construct(
        public readonly string $metodo,
        public readonly string $caminho,
        public readonly array $query,
        public readonly array $post,
        public readonly array $headers,
        public readonly string $corpoRaw,
    ) {
    }

    public static function aPartirDoPhp(): self
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $caminho = (string) parse_url($uri, PHP_URL_PATH);

        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $nome = strtolower(str_replace('_', '-', substr($k, 5)));
                $headers[$nome] = $v;
            }
        }

        $corpoRaw = (string) file_get_contents('php://input');

        return new self(
            $metodo,
            $caminho,
            $_GET ?? [],
            $_POST ?? [],
            $headers,
            $corpoRaw,
        );
    }

    public function json(): array
    {
        $decodificado = json_decode($this->corpoRaw, true);
        return is_array($decodificado) ? $decodificado : [];
    }

    public function input(): Input
    {
        return new Input($this);
    }
}
