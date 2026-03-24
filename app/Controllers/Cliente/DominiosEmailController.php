<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Email\MailcowService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class DominiosEmailController
{
    public function index(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $svc      = new MailcowService(new ClienteHttp());
        $dominios = $svc->listarDominios($clienteId);

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/emails-dominios.php', [
            'dominios'    => $dominios,
            'webmail_url' => $svc->webmailUrl(),
            'dns_template' => $svc->dnsInstructionsTemplate(),
            'erro'        => '',
            'sucesso'     => '',
        ]);

        return Resposta::html($html);
    }

    public function adicionar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $domain = strtolower(trim((string) ($req->post['domain'] ?? '')));

        if ($domain === '') {
            return $this->renderizarErro($clienteId, 'Informe o domínio.');
        }

        try {
            (new MailcowService(new ClienteHttp()))->adicionarDominio($clienteId, $domain);
        } catch (\Throwable $e) {
            return $this->renderizarErro($clienteId, $e->getMessage());
        }

        return Resposta::redirecionar('/cliente/emails/dominios');
    }

    public function verificar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $dominioId = (int) ($req->post['dominio_id'] ?? 0);
        if ($dominioId <= 0) {
            return Resposta::texto('Domínio inválido.', 400);
        }

        try {
            $res = (new MailcowService(new ClienteHttp()))->verificarDNSDominio($clienteId, $dominioId);
        } catch (\Throwable $e) {
            return $this->renderizarErro($clienteId, $e->getMessage());
        }

        if ($res['ok']) {
            return Resposta::redirecionar('/cliente/emails/dominios?verificado=1');
        }

        return $this->renderizarErro($clienteId, $res['erro'] ?? 'DNS ainda não propagado. Aguarde e tente novamente.');
    }

    public function remover(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $dominioId = (int) ($req->post['dominio_id'] ?? 0);
        if ($dominioId <= 0) {
            return Resposta::texto('Domínio inválido.', 400);
        }

        try {
            (new MailcowService(new ClienteHttp()))->removerDominio($clienteId, $dominioId);
        } catch (\Throwable $e) {
            return $this->renderizarErro($clienteId, $e->getMessage());
        }

        return Resposta::redirecionar('/cliente/emails/dominios');
    }

    public function instrucoes(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $dominioId = (int) ($req->query['id'] ?? 0);
        if ($dominioId <= 0) {
            return Resposta::texto('Domínio inválido.', 400);
        }

        $svc  = new MailcowService(new ClienteHttp());
        $pdo  = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, domain, status FROM client_domains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $dominioId, ':c' => $clienteId]);
        $dominio = $stmt->fetch();

        if (!is_array($dominio)) {
            return Resposta::texto('Domínio não encontrado.', 404);
        }

        // Tentar obter DKIM
        $dkim = '';
        try {
            $dkim = $svc->obterDKIM((string) $dominio['domain']);
        } catch (\Throwable) {
            // silencioso — DKIM pode não estar disponível ainda
        }

        $template = $svc->dnsInstructionsTemplate();
        $template = str_replace('{domain}', (string) $dominio['domain'], $template);

        $mailcowHost = parse_url($svc->webmailUrl(), PHP_URL_HOST)
            ?: parse_url((string)\LRV\Core\Settings::obter('email.mailcow_url', ''), PHP_URL_HOST)
            ?: '';

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/emails-dominios-instrucoes.php', [
            'dominio'       => $dominio,
            'dkim'          => $dkim,
            'dns_template'  => $template,
            'webmail_url'   => $svc->webmailUrl(),
            'mailcow_host'  => $mailcowHost,
        ]);

        return Resposta::html($html);
    }

    private function renderizarErro(int $clienteId, string $erro): Resposta
    {
        $svc      = new MailcowService(new ClienteHttp());
        $dominios = $svc->listarDominios($clienteId);

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/emails-dominios.php', [
            'dominios'    => $dominios,
            'webmail_url' => $svc->webmailUrl(),
            'dns_template' => $svc->dnsInstructionsTemplate(),
            'erro'        => $erro,
            'sucesso'     => '',
        ]);

        return Resposta::html($html, 422);
    }
}
