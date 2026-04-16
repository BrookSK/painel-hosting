<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class SolucoesController
{
    public function vps(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/vps.php'));
    }

    public function aplicacoes(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/aplicacoes.php'));
    }

    public function devops(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/devops.php'));
    }

    public function email(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/email.php'));
    }

    public function seguranca(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/seguranca.php'));
    }

    public function wordpress(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/wordpress.php'));
    }

    public function webhosting(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/webhosting.php'));
    }

    public function nodejs(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/nodejs.php'));
    }

    public function cpp(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/cpp.php'));
    }
}
