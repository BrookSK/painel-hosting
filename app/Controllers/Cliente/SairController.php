<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class SairController
{
    public function sair(Requisicao $req): Resposta
    {
        $estaImpersonando = Auth::estaImpersonando();
        Auth::sairCliente();
        if ($estaImpersonando) {
            return Resposta::redirecionar('/equipe/clientes');
        }
        return Resposta::redirecionar('/cliente/entrar');
    }
}
