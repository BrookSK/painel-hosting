<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Email\MailcowService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class EmailController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $svc    = new MailcowService(new ClienteHttp());
        $emails = $svc->listar($clienteId);

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/emails-listar.php', [
            'emails'      => $emails,
            'webmail_url' => $svc->webmailUrl(),
            'erro'        => '',
            'sucesso'     => '',
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $in         = $req->input();
        $localPart  = $in->postString('local_part', 64, true);
        $domain     = $in->postString('domain', 191, true);
        $senha      = trim((string) ($req->post['senha'] ?? ''));

        if ($in->temErros() || $localPart === '' || $domain === '' || strlen($senha) < 8) {
            return $this->renderizarErro($clienteId, 'Preencha todos os campos. Senha mínima: 8 caracteres.');
        }

        try {
            $svc = new MailcowService(new ClienteHttp());
            $svc->criarEmail($clienteId, $localPart, $domain, $senha);
        } catch (\Throwable $e) {
            return $this->renderizarErro($clienteId, $e->getMessage());
        }

        return Resposta::redirecionar('/cliente/emails');
    }

    public function remover(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $emailId = (int) ($req->post['email_id'] ?? 0);
        if ($emailId <= 0) {
            return Resposta::texto('E-mail inválido.', 400);
        }

        try {
            (new MailcowService(new ClienteHttp()))->removerEmail($clienteId, $emailId);
        } catch (\Throwable $e) {
            return $this->renderizarErro($clienteId, $e->getMessage());
        }

        return Resposta::redirecionar('/cliente/emails');
    }

    private function renderizarErro(int $clienteId, string $erro): Resposta
    {
        $svc    = new MailcowService(new ClienteHttp());
        $emails = $svc->listar($clienteId);

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/emails-listar.php', [
            'emails'      => $emails,
            'webmail_url' => $svc->webmailUrl(),
            'erro'        => $erro,
            'sucesso'     => '',
        ]);

        return Resposta::html($html, 422);
    }
}
