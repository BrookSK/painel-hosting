<?php

declare(strict_types=1);

namespace LRV\Core\Http;

final class Input
{
    private array $erros = [];

    public function __construct(
        private readonly Requisicao $req,
    ) {
    }

    public function temErros(): bool
    {
        return !empty($this->erros);
    }

    public function erros(): array
    {
        return $this->erros;
    }

    public function primeiroErro(): string
    {
        $primeiro = (string) ($this->erros[0] ?? '');
        return $primeiro !== '' ? $primeiro : 'Requisição inválida.';
    }

    public function postString(string $chave, int $maxLen, bool $obrigatorio = false): string
    {
        return $this->lerString($this->req->post, $chave, $maxLen, $obrigatorio);
    }

    public function postStringRaw(string $chave, int $maxLen, bool $obrigatorio = false): string
    {
        return $this->lerStringRaw($this->req->post, $chave, $maxLen, $obrigatorio);
    }

    public function queryString(string $chave, int $maxLen, bool $obrigatorio = false): string
    {
        return $this->lerString($this->req->query, $chave, $maxLen, $obrigatorio);
    }

    public function queryStringRaw(string $chave, int $maxLen, bool $obrigatorio = false): string
    {
        return $this->lerStringRaw($this->req->query, $chave, $maxLen, $obrigatorio);
    }

    public function postEmail(string $chave, int $maxLen, bool $obrigatorio = false): string
    {
        $v = $this->postString($chave, $maxLen, $obrigatorio);
        if ($v === '') {
            return '';
        }
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->erros[] = 'E-mail inválido.';
            return '';
        }
        return $v;
    }

    public function queryRegex(string $chave, int $maxLen, string $pattern, bool $obrigatorio = false, string $erro = 'Valor inválido.'): string
    {
        $v = $this->queryString($chave, $maxLen, $obrigatorio);
        if ($v === '') {
            return '';
        }
        if (@preg_match($pattern, $v) !== 1) {
            $this->erros[] = $erro;
            return '';
        }
        return $v;
    }

    private function lerStringRaw(array $fonte, string $chave, int $maxLen, bool $obrigatorio): string
    {
        $raw = $fonte[$chave] ?? '';
        $v = (string) $raw;
        $v = str_replace("\0", '', $v);

        if ($v === '') {
            if ($obrigatorio) {
                $this->erros[] = 'Campo obrigatório.';
            }
            return '';
        }

        if ($maxLen > 0 && strlen($v) > $maxLen) {
            $this->erros[] = 'Campo muito longo.';
            return substr($v, 0, $maxLen);
        }

        return $v;
    }

    public function postUrl(string $chave, int $maxLen, bool $obrigatorio = false): string
    {
        $v = $this->postString($chave, $maxLen, $obrigatorio);
        if ($v === '') {
            return '';
        }
        if (!filter_var($v, FILTER_VALIDATE_URL)) {
            $this->erros[] = 'URL inválida.';
            return '';
        }
        return $v;
    }

    public function postRegex(string $chave, int $maxLen, string $pattern, bool $obrigatorio = false, string $erro = 'Valor inválido.'): string
    {
        $v = $this->postString($chave, $maxLen, $obrigatorio);
        if ($v === '') {
            return '';
        }
        if (@preg_match($pattern, $v) !== 1) {
            $this->erros[] = $erro;
            return '';
        }
        return $v;
    }

    public function postInt(string $chave, int $min, int $max, bool $obrigatorio = false): int
    {
        return $this->lerInt($this->req->post, $chave, $min, $max, $obrigatorio);
    }

    public function queryInt(string $chave, int $min, int $max, bool $obrigatorio = false): int
    {
        return $this->lerInt($this->req->query, $chave, $min, $max, $obrigatorio);
    }

    public function postEnum(string $chave, array $permitidos, string $padrao = ''): string
    {
        $v = $this->postString($chave, 60, false);
        if ($v === '') {
            return $padrao;
        }
        foreach ($permitidos as $p) {
            if ($v === (string) $p) {
                return $v;
            }
        }
        $this->erros[] = 'Valor inválido.';
        return $padrao;
    }

    private function lerString(array $fonte, string $chave, int $maxLen, bool $obrigatorio): string
    {
        $raw = $fonte[$chave] ?? '';
        $v = trim((string) $raw);
        $v = str_replace("\0", '', $v);

        if ($v === '') {
            if ($obrigatorio) {
                $this->erros[] = 'Campo obrigatório.';
            }
            return '';
        }

        if ($maxLen > 0 && strlen($v) > $maxLen) {
            $this->erros[] = 'Campo muito longo.';
            return substr($v, 0, $maxLen);
        }

        return $v;
    }

    private function lerInt(array $fonte, string $chave, int $min, int $max, bool $obrigatorio): int
    {
        $raw = $fonte[$chave] ?? null;

        if ($raw === null || $raw === '') {
            if ($obrigatorio) {
                $this->erros[] = 'Campo obrigatório.';
            }
            return 0;
        }

        if (is_string($raw)) {
            $raw = trim($raw);
            $raw = str_replace("\0", '', $raw);
        }

        if (!is_numeric($raw)) {
            $this->erros[] = 'Número inválido.';
            return 0;
        }

        $v = (int) $raw;

        if ($v < $min || $v > $max) {
            $this->erros[] = 'Número fora do intervalo.';
            return 0;
        }

        return $v;
    }
}
