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
        Auth::sairCliente();
        return Resposta::redirecionar('/cliente/entrar');
    }
}
