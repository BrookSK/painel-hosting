<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class SaudeController
{
    public function status(Requisicao $req): Resposta
    {
        return Resposta::json([
            'status' => 'ok',
            'sistema' => 'LRV Cloud Manager',
        ]);
    }
}
