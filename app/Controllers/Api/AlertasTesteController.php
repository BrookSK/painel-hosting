<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;

final class AlertasTesteController
{
    public function enfileirar(Requisicao $req): Resposta
    {
        $repo = new RepositorioJobs();
        $id = $repo->criar('alerta_ticket', [
            'titulo' => 'Teste de alerta',
            'mensagem' => 'Teste de alerta via Jobs. ' . date('Y-m-d H:i:s'),
        ]);

        return Resposta::json([
            'ok' => true,
            'job_id' => $id,
        ]);
    }
}
