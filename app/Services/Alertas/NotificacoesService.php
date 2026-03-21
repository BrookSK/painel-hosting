<?php

declare(strict_types=1);

namespace LRV\App\Services\Alertas;

use LRV\App\Services\Email\SmtpMailer;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\ConfiguracoesSistema;

final class NotificacoesService
{
    public function __construct(
        private readonly ClienteHttp $http,
    ) {
    }

    public function alertarAdmin(string $titulo, string $mensagem): void
    {
        $this->enviarEmailAdmin($titulo, $mensagem);
        $this->enviarWhatsAppAdmin($mensagem);
    }

    public function enviarEmailAdmin(string $titulo, string $mensagem): bool
    {
        $email = trim(ConfiguracoesSistema::emailAdmin());
        if ($email === '') {
            return false;
        }

        try {
            $assunto = '[LRV] ' . $titulo;
            (new SmtpMailer())->enviar($email, $assunto, $mensagem);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function enviarWhatsAppAdmin(string $mensagem): bool
    {
        $urlBase = trim(ConfiguracoesSistema::evolutionUrlBase());
        $token = trim(ConfiguracoesSistema::evolutionToken());

        $numero = trim(ConfiguracoesSistema::whatsappAdminNumero());
        $instancia = trim(ConfiguracoesSistema::evolutionInstance());

        if ($urlBase === '' || $token === '' || $numero === '' || $instancia === '') {
            return false;
        }

        $url = rtrim($urlBase, '/') . '/message/sendText/' . rawurlencode($instancia);

        $resp = $this->http->requestJson('POST', $url, [
            'apikey' => $token,
            'User-Agent' => 'LRVCloudManager/1.0 (PHP)',
        ], [
            'number' => $numero,
            'text' => $mensagem,
        ]);

        $status = (int) ($resp['status'] ?? 0);
        return $status >= 200 && $status < 300;
    }
}
