<?php

declare(strict_types=1);

namespace LRV\Core\Http;

final class Resposta
{
    private function __construct(
        private readonly string $corpo,
        private readonly int $status,
        private readonly array $headers,
        private readonly ?string $arquivoPath = null,
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

    public static function arquivo(string $path, string $nomeArquivo, string $contentType = 'application/octet-stream'): self
    {
        $nomeArquivo = trim($nomeArquivo);
        if ($nomeArquivo === '') {
            $nomeArquivo = basename($path);
        }

        $size = 0;
        if (is_file($path)) {
            $size = (int) (@filesize($path) ?: 0);
        }

        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . str_replace('"', '', $nomeArquivo) . '"',
        ];

        if ($size > 0) {
            $headers['Content-Length'] = (string) $size;
        }

        return new self('', 200, $headers, $path);
    }

    public function comHeaders(array $headers): self
    {
        if (empty($headers)) {
            return $this;
        }

        $novos = $this->headers;
        foreach ($headers as $k => $v) {
            $k = trim((string) $k);
            if ($k === '') {
                continue;
            }
            $novos[$k] = (string) $v;
        }

        return new self($this->corpo, $this->status, $novos, $this->arquivoPath);
    }

    public function enviar(): void
    {
        http_response_code($this->status);

        $headersLower = [];
        foreach ($this->headers as $k => $_) {
            $headersLower[strtolower((string) $k)] = true;
        }

        if (PHP_SAPI !== 'cli') {
            if (!isset($headersLower['x-frame-options'])) {
                header('X-Frame-Options: DENY');
            }
            if (!isset($headersLower['x-content-type-options'])) {
                header('X-Content-Type-Options: nosniff');
            }

            $ct = '';
            foreach ($this->headers as $k => $v) {
                if (strtolower((string) $k) === 'content-type') {
                    $ct = strtolower((string) $v);
                    break;
                }
            }

            if ($ct !== '' && str_starts_with($ct, 'text/html')) {
                if (!isset($headersLower['content-security-policy'])) {
                    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; object-src 'none'; form-action 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; connect-src 'self' ws: wss:");
                }
            }
        }

        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }

        if ($this->arquivoPath !== null) {
            $fp = @fopen($this->arquivoPath, 'rb');
            if ($fp === false) {
                echo '';
                return;
            }
            @fpassthru($fp);
            @fclose($fp);
            return;
        }

        echo $this->corpo;
    }
}
