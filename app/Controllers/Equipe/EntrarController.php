<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class EntrarController
{
    public function formulario(Requisicao $req): Resposta
    {
        if (!Auth::equipeExiste()) {
            return Resposta::redirecionar('/equipe/primeiro-acesso');
        }

        if (Auth::equipeId() !== null) {
            return Resposta::redirecionar('/equipe/painel');
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/entrar.php', [
            'erro' => '',
            'email' => '',
        ]);

        return Resposta::html($html);
    }

    public function entrar(Requisicao $req): Resposta
    {
        if (!Auth::equipeExiste()) {
            return Resposta::redirecionar('/equipe/primeiro-acesso');
        }

        $in = $req->input();
        $email = $in->postEmail('email', 190, true);
        $senha = $in->postStringRaw('senha', 255, true);

        if ($in->temErros() || $email === '' || $senha === '') {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/entrar.php', [
                'erro' => $in->temErros() ? $in->primeiroErro() : 'Preencha e-mail e senha.',
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        if (!Auth::entrarEquipe($email, $senha)) {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/entrar.php', [
                'erro' => 'E-mail ou senha inválidos.',
                'email' => $email,
            ]);
            return Resposta::html($html, 401);
        }

        return Resposta::redirecionar('/equipe/painel');
    }
}
