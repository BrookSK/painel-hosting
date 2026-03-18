<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;

final class JobsTesteController
{
    public function enfileirar(Requisicao $req): Resposta
    {
        $repo = new RepositorioJobs();
        $id = $repo->criar('noop', [
            'criado_em' => date('Y-m-d H:i:s'),
        ]);

        return Resposta::json([
            'ok' => true,
            'job_id' => $id,
        ]);
    }
}
