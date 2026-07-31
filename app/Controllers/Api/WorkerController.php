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

        // Verificar se há jobs pendentes antes de iniciar processamento longo
        $repo = new RepositorioJobs();
        $temPendente = $repo->temPendente();

        if (!$temPendente) {
            return Resposta::json(['ok' => true, 'executou' => false]);
        }

        // Enviar resposta imediatamente ao Plesk/cron e continuar processando em background
        ignore_user_abort(true);
        set_time_limit(0);

        // Retornar resposta HTTP imediatamente
        $response = json_encode(['ok' => true, 'executou' => true, 'modo' => 'background']);
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($response));
        header('Connection: close');
        echo $response;

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ob_end_flush();
            flush();
        }

        // Agora processa o job em background (a conexão HTTP já foi fechada)
        try {
            $proc = new ProcessadorJobs();
            RegistroHandlers::registrar($proc);
            $worker = new WorkerJobs($repo, $proc);
            $worker->executarUmaVez();
        } catch (\Throwable) {
            // Silencioso — o erro fica no log do job
        }

        exit(0);
    }
}
