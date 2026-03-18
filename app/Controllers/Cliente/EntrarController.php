<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
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
        $email = trim((string) ($req->post['email'] ?? ''));
        $senha = (string) ($req->post['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/entrar.php', [
                'erro' => 'Preencha e-mail e senha.',
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        if (!Auth::entrarCliente($email, $senha)) {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/entrar.php', [
                'erro' => 'E-mail ou senha inválidos.',
                'email' => $email,
            ]);
            return Resposta::html($html, 401);
        }

        return Resposta::redirecionar('/cliente/painel');
    }
}
