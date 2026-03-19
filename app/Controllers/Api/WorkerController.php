<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\App\Jobs\RegistroHandlers;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\ProcessadorJobs;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\Jobs\WorkerJobs;
use LRV\Core\Settings;

final class WorkerController
{
    public function runOnce(Requisicao $req): Resposta
    {
        $token = (string) ($req->headers['x-worker-token'] ?? '');
        if ($token === '') {
            $token = (string) ($req->query['token'] ?? '');
        }

        $esperado = (string) Settings::obter('worker.http_token', '');

        if ($esperado === '' || $token === '' || !hash_equals($esperado, $token)) {
            return Resposta::json(['ok' => false, 'erro' => 'unauthorized'], 401);
        }

        try {
            $repo = new RepositorioJobs();
            $proc = new ProcessadorJobs();
            RegistroHandlers::registrar($proc);
            $worker = new WorkerJobs($repo, $proc);

            $executou = $worker->executarUmaVez();

            return Resposta::json([
                'ok' => true,
                'executou' => $executou,
            ]);
        } catch (\Throwable $e) {
            return Resposta::json([
                'ok' => false,
                'erro' => 'internal_error',
                'mensagem' => $e->getMessage(),
            ], 500);
        }
    }
}
