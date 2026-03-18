<?php

declare(strict_types=1);

namespace LRV\Core\Http;

final class Resposta
{
    private function __construct(
        private readonly string $corpo,
        private readonly int $status,
        private readonly array $headers,
    ) {
    }

    public static function json(array $dados, int $status = 200): self
    {
        return new self(
            json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function texto(string $texto, int $status = 200): self
    {
        return new self($texto, $status, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public static function redirecionar(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public function enviar(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
        echo $this->corpo;
    }
}
