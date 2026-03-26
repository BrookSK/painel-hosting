<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Email\MailcowService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\App\Services\Infra\SubdomainVerificationService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class DominiosController
{
    public function index(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();

        // Domínios raiz (para email)
        $rootStmt = $pdo->prepare("SELECT * FROM client_domains WHERE client_id = :c ORDER BY domain");
        $rootStmt->execute([':c' => $clienteId]);
        $dominiosRaiz = $rootStmt->fetchAll() ?: [];

        // Subdomínios
        $svc = new SubdomainVerificationService();
        $subdomains = $svc->listar($clienteId);

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/dominios.php', [
            'dominios_raiz' => $dominiosRaiz,
            'subdomains'    => $subdomains,
            'erro'          => '',
            'sucesso'       => (string)($req->query['ok'] ?? ''),
        ]));
    }

    public function adicionarRaiz(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $domain = strtolower(trim((string)($req->post['domain'] ?? '')));
        if ($domain === '') return $this->redir('Informe o domínio.');

        try {
            (new MailcowService(new ClienteHttp()))->adicionarDominio($clienteId, $domain);
        } catch (\Throwable $e) {
            return $this->redir($e->getMessage());
        }

        return Resposta::redirecionar('/cliente/dominios?ok=raiz_adicionado');
    }

    public function adicionarSub(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $subdomain = strtolower(trim((string)($req->post['subdomain'] ?? '')));
        if ($subdomain === '') return $this->redir('Informe o subdomínio.');

        try {
            (new SubdomainVerificationService())->adicionarSubdominio($clienteId, $subdomain);
        } catch (\Throwable $e) {
            return $this->redir($e->getMessage());
        }

        return Resposta::redirecionar('/cliente/dominios?ok=sub_adicionado');
    }

    public function verificarTxt(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $subId = (int)($req->post['sub_id'] ?? 0);
        if ($subId <= 0) return $this->redir('ID inválido.');

        try {
            $res = (new SubdomainVerificationService())->verificarTxt($clienteId, $subId);
        } catch (\Throwable $e) {
            return $this->redir($e->getMessage());
        }

        if ($res['ok']) return Resposta::redirecionar('/cliente/dominios?ok=txt_verificado');
        return $this->redir($res['erro'] ?? 'TXT não encontrado.');
    }

    public function verificarCname(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $subId = (int)($req->post['sub_id'] ?? 0);
        if ($subId <= 0) return $this->redir('ID inválido.');

        try {
            $res = (new SubdomainVerificationService())->verificarCname($clienteId, $subId);
        } catch (\Throwable $e) {
            return $this->redir($e->getMessage());
        }

        if ($res['ok']) return Resposta::redirecionar('/cliente/dominios?ok=cname_verificado');
        return $this->redir($res['erro'] ?? 'CNAME não encontrado.');
    }

    public function removerRaiz(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $dominioId = (int)($req->post['dominio_id'] ?? 0);
        if ($dominioId <= 0) return $this->redir('ID inválido.');

        try {
            (new MailcowService(new ClienteHttp()))->removerDominio($clienteId, $dominioId);
        } catch (\Throwable $e) {
            return $this->redir($e->getMessage());
        }

        return Resposta::redirecionar('/cliente/dominios?ok=raiz_removido');
    }

    public function removerSub(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $subId = (int)($req->post['sub_id'] ?? 0);
        if ($subId <= 0) return $this->redir('ID inválido.');

        try {
            (new SubdomainVerificationService())->removerSubdominio($clienteId, $subId);
        } catch (\Throwable $e) {
            return $this->redir($e->getMessage());
        }

        return Resposta::redirecionar('/cliente/dominios?ok=sub_removido');
    }

    private function redir(string $erro): Resposta
    {
        // Store error in session and redirect
        $_SESSION['_dominios_erro'] = $erro;
        return Resposta::redirecionar('/cliente/dominios');
    }
}
