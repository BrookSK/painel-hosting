<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class InicialController
{
    public function index(Requisicao $req): Resposta
    {
        $html = file_get_contents(__DIR__ . '/../Views/inicial.php');
        return Resposta::html((string) $html);
    }
}
