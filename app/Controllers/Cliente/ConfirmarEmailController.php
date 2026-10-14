<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Clientes\ConfirmacaoEmailService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ConfirmarEmailController
{
    /** GET /cliente/confirmar-email/pendente — aviso para confirmar o e-mail. */
    public function pendente(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }
        if (!ConfirmacaoEmailService::pendente($id)) {
            return Resposta::redirecionar('/cliente/painel');
        }
        return $this->render('pendente', (string) ($req->query['ok'] ?? '') === '1' ? 'Enviamos um novo link para o seu e-mail.' : '', '');
    }

    /** POST /cliente/confirmar-email/reenviar */
    public function reenviar(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }
        if (!(new ConfirmacaoEmailService())->enviar($id)) {
            return $this->render('pendente', '', 'Aguarde um minuto antes de pedir outro link.');
        }
        return Resposta::redirecionar('/cliente/confirmar-email/pendente?ok=1');
    }

    /** GET /cliente/confirmar-email?token= — link do e-mail (funciona sem estar logado). */
    public function confirmar(Requisicao $req): Resposta
    {
        $token = trim((string) ($req->query['token'] ?? ''));
        $id = (new ConfirmacaoEmailService())->confirmar($token);
        if ($id === null) {
            return $this->render('erro', '', 'Link inválido ou expirado. Entre na sua conta para receber um novo.');
        }
        if (Auth::clienteId() === $id) {
            $_SESSION['flash_success'] = 'E-mail confirmado. Sua conta está ativa.';
            return Resposta::redirecionar('/cliente/painel');
        }
        return $this->render('ok', 'E-mail confirmado! Agora é só entrar na sua conta.', '');
    }

    private function render(string $etapa, string $ok, string $erro): Resposta
    {
        $email = '';
        $id = Auth::clienteId();
        if ($id !== null) {
            $s = BancoDeDados::pdo()->prepare('SELECT email FROM clients WHERE id = :id');
            $s->execute([':id' => $id]);
            $email = (string) ($s->fetchColumn() ?: '');
        }
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/confirmar-email.php', [
            'etapa' => $etapa, 'ok' => $ok, 'erro' => $erro, 'email' => $email,
        ]));
    }
}
