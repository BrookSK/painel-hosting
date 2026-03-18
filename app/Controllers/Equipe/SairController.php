<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class SairController
{
    public function sair(Requisicao $req): Resposta
    {
        Auth::sairEquipe();
        return Resposta::redirecionar('/equipe/entrar');
    }
}
