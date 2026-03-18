<?php

declare(strict_types=1);

namespace LRV\App\Services\Http;

final class ClienteHttp
{
    public function requestJson(string $metodo, string $url, array $headers = [], ?array $body = null): array
    {
        $headersNorm = [];
        foreach ($headers as $k => $v) {
            $headersNorm[] = $k . ': ' . $v;
        }

        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $headersNorm[] = 'Content-Type: application/json; charset=utf-8';
        }

        if (function_exists('curl_init')) {
            return $this->requestComCurl($metodo, $url, $headersNorm, $payload);
        }

        return $this->requestComStream($metodo, $url, $headersNorm, $payload);
    }

    private function requestComCurl(string $metodo, string $url, array $headers, ?string $payload): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Falha ao iniciar requisição HTTP.');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $resp = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            throw new \RuntimeException('Falha HTTP: ' . $err);
        }

        $json = json_decode((string) $resp, true);

        return [
            'status' => $status,
            'body' => (string) $resp,
            'json' => is_array($json) ? $json : null,
        ];
    }

    private function requestComStream(string $metodo, string $url, array $headers, ?string $payload): array
    {
        $opts = [
            'http' => [
                'method' => $metodo,
                'header' => implode("\r\n", $headers),
                'timeout' => 30,
            ],
        ];

        if ($payload !== null) {
            $opts['http']['content'] = $payload;
        }

        $ctx = stream_context_create($opts);
        $resp = @file_get_contents($url, false, $ctx);

        $status = 0;
        $headersResp = $http_response_header ?? [];
        foreach ($headersResp as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m) === 1) {
                $status = (int) $m[1];
                break;
            }
        }

        if ($resp === false) {
            throw new \RuntimeException('Falha HTTP.');
        }

        $json = json_decode((string) $resp, true);

        return [
            'status' => $status,
            'body' => (string) $resp,
            'json' => is_array($json) ? $json : null,
        ];
    }
}
