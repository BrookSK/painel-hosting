<?php

declare(strict_types=1);

namespace LRV\App\Services\Email;

use LRV\Core\Settings;

/**
 * Serviço de envio de e-mail via SMTP (socket nativo, sem dependências externas).
 * Configuração via tabela settings (chaves smtp.*).
 */
final class SmtpMailer
{
    private string $host;
    private int    $port;
    private string $user;
    private string $pass;
    private string $from;
    private string $fromName;
    private string $encryption; // tls | ssl | none
    private int    $timeout;

    public function __construct()
    {
        $this->host       = trim((string) Settings::obter('smtp.host', ''));
        $this->port       = (int) Settings::obter('smtp.port', 587);
        $this->user       = trim((string) Settings::obter('smtp.user', ''));
        $this->pass       = trim((string) Settings::obter('smtp.pass', ''));
        $this->from       = trim((string) Settings::obter('smtp.from_email', ''));
        $this->fromName   = trim((string) Settings::obter('smtp.from_name', ''));
        $this->encryption = strtolower(trim((string) Settings::obter('smtp.encryption', 'tls')));
        $this->timeout    = max(5, (int) Settings::obter('smtp.timeout', 15));
    }

    public static function configurado(): bool
    {
        $host = trim((string) Settings::obter('smtp.host', ''));
        return $host !== '';
    }

    /**
     * Envia e-mail. Lança \RuntimeException em caso de falha.
     */
    public function enviar(string $para, string $assunto, string $corpo, bool $html = false): void
    {
        if ($this->host === '') {
            // Fallback para mail() nativo se SMTP não configurado
            $headers = 'Content-Type: text/plain; charset=utf-8';
            if (!@mail($para, $assunto, $corpo, $headers)) {
                throw new \RuntimeException('SMTP não configurado e mail() falhou.');
            }
            return;
        }

        $socket = $this->conectar();

        try {
            $this->conversa($socket, $para, $assunto, $corpo, $html);
        } finally {
            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);
        }
    }

    private function conectar(): mixed
    {
        $prefix = match ($this->encryption) {
            'ssl'  => 'ssl://',
            default => '',
        };

        $errno = 0; $errstr = '';
        $socket = @fsockopen($prefix . $this->host, $this->port, $errno, $errstr, $this->timeout);

        if ($socket === false) {
            throw new \RuntimeException("SMTP: não foi possível conectar em {$this->host}:{$this->port} — {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, $this->timeout);
        $this->lerResposta($socket, 220);

        return $socket;
    }

    private function conversa(mixed $socket, string $para, string $assunto, string $corpo, bool $html): void
    {
        $domain = gethostname() ?: 'localhost';

        $this->cmd($socket, "EHLO {$domain}", 250);

        if ($this->encryption === 'tls') {
            $this->cmd($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('SMTP: falha ao iniciar TLS.');
            }
            $this->cmd($socket, "EHLO {$domain}", 250);
        }

        if ($this->user !== '') {
            $this->cmd($socket, 'AUTH LOGIN', 334);
            $this->cmd($socket, base64_encode($this->user), 334);
            $this->cmd($socket, base64_encode($this->pass), 235);
        }

        $from = $this->from !== '' ? $this->from : $this->user;
        $this->cmd($socket, "MAIL FROM:<{$from}>", 250);
        $this->cmd($socket, "RCPT TO:<{$para}>", [250, 251]);
        $this->cmd($socket, 'DATA', 354);

        $contentType = $html
            ? 'text/html; charset=utf-8'
            : 'text/plain; charset=utf-8';

        $fromHeader = $this->fromName !== ''
            ? "=?utf-8?B?" . base64_encode($this->fromName) . "?= <{$from}>"
            : $from;

        $assuntoCod = '=?utf-8?B?' . base64_encode($assunto) . '?=';

        $msg  = "From: {$fromHeader}\r\n";
        $msg .= "To: {$para}\r\n";
        $msg .= "Subject: {$assuntoCod}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: {$contentType}\r\n";
        $msg .= "Date: " . date('r') . "\r\n";
        $msg .= "\r\n";
        $msg .= $corpo . "\r\n";
        $msg .= ".";

        $this->cmd($socket, $msg, 250);
    }

    /** @param int|int[] $esperado */
    private function cmd(mixed $socket, string $cmd, int|array $esperado): string
    {
        fwrite($socket, $cmd . "\r\n");
        return $this->lerResposta($socket, $esperado);
    }

    /** @param int|int[] $esperado */
    private function lerResposta(mixed $socket, int|array $esperado): string
    {
        $resposta = '';
        while ($linha = fgets($socket, 512)) {
            $resposta .= $linha;
            if (isset($linha[3]) && $linha[3] === ' ') break;
        }

        $codigo = (int) substr($resposta, 0, 3);
        $esperados = is_array($esperado) ? $esperado : [$esperado];

        if (!in_array($codigo, $esperados, true)) {
            throw new \RuntimeException("SMTP: esperado " . implode('/', $esperados) . ", recebido {$codigo}: " . trim($resposta));
        }

        return $resposta;
    }
}
