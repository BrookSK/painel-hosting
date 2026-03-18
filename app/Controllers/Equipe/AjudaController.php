<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AjudaController
{
    public function index(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/ajuda.php');
        return Resposta::html($html);
    }
}
