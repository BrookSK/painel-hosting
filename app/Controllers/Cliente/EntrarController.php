<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\LoginBlocker;
use LRV\Core\View;

final class EntrarController
{
    public function formulario(Requisicao $req): Resposta
    {
        if (Auth::clienteId() !== null) {
            return Resposta::redirecionar('/cliente/painel');
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/entrar.php', [
            'erro' => '',
            'email' => '',
        ]);

        return Resposta::html($html);
    }

    public function entrar(Requisicao $req): Resposta
    {
        $ip = LoginBlocker::extrairIp();
        if (LoginBlocker::estaBloqueado($ip)) {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/entrar.php', [
                'erro' => 'Muitas tentativas. Tente novamente em 30 minutos.',
                'email' => '',
            ]);
            return Resposta::html($html, 429);
        }

        $in = $req->input();
        $email = $in->postEmail('email', 190, true);
        $senha = $in->postStringRaw('senha', 255, true);

        if ($in->temErros() || $email === '' || $senha === '') {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/entrar.php', [
                'erro' => $in->temErros() ? $in->primeiroErro() : 'Preencha e-mail e senha.',
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        if (!Auth::entrarCliente($email, $senha)) {
            LoginBlocker::registrarFalha($ip, 'client');
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/entrar.php', [
                'erro' => 'E-mail ou senha inválidos.',
                'email' => $email,
            ]);
            return Resposta::html($html, 401);
        }

        // Verificar se o cliente tem 2FA ativo
        $clienteId = Auth::clienteId();
        if ($clienteId !== null) {
            $pdo = BancoDeDados::pdo();
            $st  = $pdo->prepare('SELECT id FROM client_totp WHERE client_id = :id AND enabled = 1 LIMIT 1');
            $st->execute([':id' => $clienteId]);
            if ($st->fetch()) {
                // Suspender sessão e redirecionar para verificação 2FA
                unset($_SESSION['auth_cliente_id']);
                $_SESSION['2fa_pending_cliente_id'] = $clienteId;
                return Resposta::redirecionar('/cliente/2fa/verificar');
            }
        }

        return Resposta::redirecionar('/cliente/painel');
    }
}
