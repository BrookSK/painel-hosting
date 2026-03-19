<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Jobs\RegistroHandlers;
use LRV\App\Services\Setup\InicializacaoService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\ProcessadorJobs;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\Jobs\WorkerJobs;
use LRV\Core\View;

final class InicializacaoController
{
    public function index(Requisicao $req): Resposta
    {
        return $this->renderizar('', [], false);
    }

    public function aplicarSchema(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        $svc = new InicializacaoService();

        try {
            $svc->aplicarSchema(function (string $m) use (&$logs): void {
                $logs[] = $m;
            });
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    public function aplicarMigrations(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        $svc = new InicializacaoService();

        try {
            $svc->aplicarMigrations(function (string $m) use (&$logs): void {
                $logs[] = $m;
            });
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    public function criarDiretorios(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        $svc = new InicializacaoService();

        try {
            $svc->criarDiretorios(function (string $m) use (&$logs): void {
                $logs[] = $m;
            });
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    public function gerarTokensEDefaults(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        $svc = new InicializacaoService();

        try {
            $svc->garantirDefaultsESecrets(function (string $m) use (&$logs): void {
                $logs[] = $m;
            });
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    public function processarUmJob(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        try {
            $repo = new RepositorioJobs();
            $proc = new ProcessadorJobs();
            RegistroHandlers::registrar($proc);
            $worker = new WorkerJobs($repo, $proc);

            $executou = $worker->executarUmaVez();
            if ($executou) {
                $logs[] = 'Processado 1 job.';
            } else {
                $logs[] = 'Nenhum job pendente.';
            }
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    private function renderizar(string $erro, array $logs, bool $ok): Resposta
    {
        $svc = new InicializacaoService();
        $st = $svc->status();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/inicializacao.php', [
            'erro' => $erro,
            'ok' => $ok,
            'logs' => $logs,
            'status' => $st['itens'] ?? [],
            'pendentes' => $st['pendentes'] ?? [],
        ]);

        return Resposta::html($html);
    }
}
