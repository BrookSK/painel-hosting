<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Jobs\RegistroHandlers;
use LRV\App\Services\Audit\AuditLogService;
use LRV\App\Services\Infra\NodeHealthService;
use LRV\App\Services\Setup\InicializacaoService;
use LRV\App\Services\Terminal\TerminalWsSetupService;
use LRV\Core\BancoDeDados;
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

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'setup.apply_schema',
            'setup',
            null,
            ['ok' => $ok, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

        return $this->renderizar($erro, $logs, $ok);
    }

    public function enfileirarColetaStatus(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        try {
            $pdo = BancoDeDados::pdo();
            try {
                $st = $pdo->prepare("SELECT id, status, COALESCE(run_at,'') AS run_at FROM jobs WHERE type = 'coletar_status' AND status IN ('pending','running') ORDER BY id DESC LIMIT 1");
                $st->execute();
                $ja = $st->fetch();
            } catch (\Throwable $e) {
                $st = $pdo->prepare("SELECT id, status FROM jobs WHERE type = 'coletar_status' AND status IN ('pending','running') ORDER BY id DESC LIMIT 1");
                $st->execute();
                $ja = $st->fetch();
            }
            if (is_array($ja)) {
                $logs[] = 'Já existe um coletar_status em fila/rodando: #' . (int) ($ja['id'] ?? 0) . ' (status=' . (string) ($ja['status'] ?? '') . (array_key_exists('run_at', $ja) ? (' run_at=' . (string) ($ja['run_at'] ?? '')) : '') . ')';
                $ok = true;

                (new AuditLogService())->registrar(
                    'team',
                    \LRV\Core\Auth::equipeId(),
                    'status.enqueue_collect_once',
                    'job',
                    (int) ($ja['id'] ?? 0) ?: null,
                    [
                        'ok' => true,
                        'ja_existia' => true,
                        'job_id' => (int) ($ja['id'] ?? 0),
                        'job_status' => (string) ($ja['status'] ?? ''),
                        'run_at' => array_key_exists('run_at', $ja) ? (string) ($ja['run_at'] ?? '') : '',
                    ],
                    $req,
                );
                return $this->renderizar($erro, $logs, $ok);
            }

            $repo = new RepositorioJobs();
            $id = $repo->criar('coletar_status', ['reagendar' => false]);
            $logs[] = 'Job coletar_status enfileirado: #' . $id;
            $ok = true;

            (new AuditLogService())->registrar(
                'team',
                \LRV\Core\Auth::equipeId(),
                'status.enqueue_collect_once',
                'job',
                $id,
                ['ok' => true, 'ja_existia' => false, 'job_id' => $id, 'reagendar' => false],
                $req,
            );
        } catch (\Throwable $e) {
            $erro = $e->getMessage();

            (new AuditLogService())->registrar(
                'team',
                \LRV\Core\Auth::equipeId(),
                'status.enqueue_collect_once',
                'job',
                null,
                ['ok' => false, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
                $req,
            );
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    public function iniciarColetaStatusContinua(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        try {
            $pdo = BancoDeDados::pdo();
            try {
                $st = $pdo->prepare("SELECT id, status, COALESCE(run_at,'') AS run_at FROM jobs WHERE type = 'coletar_status' AND status IN ('pending','running') ORDER BY id DESC LIMIT 1");
                $st->execute();
                $ja = $st->fetch();
            } catch (\Throwable $e) {
                $st = $pdo->prepare("SELECT id, status FROM jobs WHERE type = 'coletar_status' AND status IN ('pending','running') ORDER BY id DESC LIMIT 1");
                $st->execute();
                $ja = $st->fetch();
            }
            if (is_array($ja)) {
                $logs[] = 'Já existe um coletar_status em fila/rodando: #' . (int) ($ja['id'] ?? 0) . ' (status=' . (string) ($ja['status'] ?? '') . (array_key_exists('run_at', $ja) ? (' run_at=' . (string) ($ja['run_at'] ?? '')) : '') . ')';
                $ok = true;

                (new AuditLogService())->registrar(
                    'team',
                    \LRV\Core\Auth::equipeId(),
                    'status.enqueue_collect_continuous',
                    'job',
                    (int) ($ja['id'] ?? 0) ?: null,
                    [
                        'ok' => true,
                        'ja_existia' => true,
                        'job_id' => (int) ($ja['id'] ?? 0),
                        'job_status' => (string) ($ja['status'] ?? ''),
                        'run_at' => array_key_exists('run_at', $ja) ? (string) ($ja['run_at'] ?? '') : '',
                    ],
                    $req,
                );
                return $this->renderizar($erro, $logs, $ok);
            }

            $repo = new RepositorioJobs();
            $id = $repo->criar('coletar_status', ['reagendar' => true]);
            $logs[] = 'Coleta contínua iniciada. Job coletar_status enfileirado: #' . $id;
            $ok = true;

            (new AuditLogService())->registrar(
                'team',
                \LRV\Core\Auth::equipeId(),
                'status.enqueue_collect_continuous',
                'job',
                $id,
                ['ok' => true, 'ja_existia' => false, 'job_id' => $id, 'reagendar' => true],
                $req,
            );
        } catch (\Throwable $e) {
            $erro = $e->getMessage();

            (new AuditLogService())->registrar(
                'team',
                \LRV\Core\Auth::equipeId(),
                'status.enqueue_collect_continuous',
                'job',
                null,
                ['ok' => false, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
                $req,
            );
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    public function iniciarBackupAutomatico(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        try {
            $pdo = BancoDeDados::pdo();
            $st = $pdo->prepare("SELECT id, status FROM jobs WHERE type = 'backup_automatico' AND status IN ('pending','running') ORDER BY id DESC LIMIT 1");
            $st->execute();
            $ja = $st->fetch();

            if (is_array($ja)) {
                $logs[] = 'Já existe um backup_automatico em fila/rodando: #' . (int)($ja['id'] ?? 0);
                $ok = true;
                return $this->renderizar($erro, $logs, $ok);
            }

            $repo = new RepositorioJobs();
            $id = $repo->criar('backup_automatico', ['reagendar' => true]);
            $logs[] = 'Backup automático iniciado. Job enfileirado: #' . $id . '. Reagenda a cada 24h.';
            $ok = true;

            (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
                'backup.start_auto', 'job', $id, ['job_id' => $id], $req);
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        return $this->renderizar($erro, $logs, $ok);
    }

    public function terminalInstalarDependencias(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        try {
            $svc = new TerminalWsSetupService();
            $svc->instalarDependencias(function (string $m) use (&$logs): void {
                $logs[] = $m;
            });
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'terminal_ws.install_deps',
            'terminal_ws',
            null,
            ['ok' => $ok, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

        return $this->renderizar($erro, $logs, $ok);
    }

    public function terminalIniciarDaemon(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        try {
            $svc = new TerminalWsSetupService();
            $svc->iniciarDaemon(function (string $m) use (&$logs): void {
                $logs[] = $m;
            });
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'terminal_ws.start_daemon',
            'terminal_ws',
            null,
            ['ok' => $ok, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

        return $this->renderizar($erro, $logs, $ok);
    }

    public function terminalPararDaemon(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';

        try {
            $svc = new TerminalWsSetupService();
            $svc->pararDaemon(function (string $m) use (&$logs): void {
                $logs[] = $m;
            });
            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'terminal_ws.stop_daemon',
            'terminal_ws',
            null,
            ['ok' => $ok, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

        return $this->renderizar($erro, $logs, $ok);
    }

    public function testarNodes(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';
        $testados = 0;

        try {
            $pdo = BancoDeDados::pdo();

            try {
                $stmt = $pdo->query("SELECT id, hostname, ip_address, status, is_online, last_check_at, last_error FROM servers ORDER BY id DESC");
                $nodes = $stmt->fetchAll();
            } catch (\Throwable $e) {
                $stmt = $pdo->query("SELECT id, hostname, ip_address, status FROM servers ORDER BY id DESC");
                $nodes = $stmt->fetchAll();
            }

            $svc = new NodeHealthService();

            foreach (($nodes ?: []) as $n) {
                if (!is_array($n)) {
                    continue;
                }

                $nodeId = (int) ($n['id'] ?? 0);
                if ($nodeId <= 0) {
                    continue;
                }

                $st = (string) ($n['status'] ?? '');
                if ($st !== 'active') {
                    $logs[] = 'Node #' . $nodeId . ' ignorado (status=' . ($st !== '' ? $st : 'desconhecido') . ').';
                    continue;
                }

                $host = trim((string) ($n['ip_address'] ?? ''));
                if ($host === '') {
                    $host = trim((string) ($n['hostname'] ?? ''));
                }

                $logs[] = 'Node #' . $nodeId . ' (' . ($host !== '' ? $host : 'sem host') . '):';

                $svc->verificarNode($nodeId, function (string $m) use (&$logs, $nodeId): void {
                    $logs[] = '[' . $nodeId . '] ' . $m;
                });

                $testados++;
            }

            $ok = true;
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'servers.test_nodes',
            'server',
            null,
            ['ok' => $ok, 'testados' => $testados, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

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

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'setup.apply_migrations',
            'setup',
            null,
            ['ok' => $ok, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

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

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'setup.create_directories',
            'setup',
            null,
            ['ok' => $ok, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

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

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'setup.generate_tokens_defaults',
            'setup',
            null,
            ['ok' => $ok, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

        return $this->renderizar($erro, $logs, $ok);
    }

    public function processarUmJob(Requisicao $req): Resposta
    {
        $logs = [];
        $ok = false;
        $erro = '';
        $executou = false;

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

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'jobs.run_once',
            'job',
            null,
            ['ok' => $ok, 'executou' => $executou, 'erro' => $erro !== '' ? substr($erro, 0, 200) : ''],
            $req,
        );

        return $this->renderizar($erro, $logs, $ok);
    }

    private function renderizar(string $erro, array $logs, bool $ok): Resposta
    {
        $svc = new InicializacaoService();
        $st = $svc->status();

        $terminalWs = [];
        try {
            $terminalWs = (new TerminalWsSetupService())->status();
        } catch (\Throwable $e) {
            $terminalWs = [];
        }

        $nodes = [];
        try {
            $pdo = BancoDeDados::pdo();
            try {
                $stmt = $pdo->query("SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status, is_online, last_check_at, last_error FROM servers ORDER BY id DESC");
                $nodes = $stmt->fetchAll();
            } catch (\Throwable $e) {
                $stmt = $pdo->query("SELECT id, hostname, ip_address, ssh_port, status FROM servers ORDER BY id DESC");
                $nodes = $stmt->fetchAll();
            }
        } catch (\Throwable $e) {
            $nodes = [];
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/inicializacao.php', [
            'erro' => $erro,
            'ok' => $ok,
            'logs' => $logs,
            'status' => $st['itens'] ?? [],
            'pendentes' => $st['pendentes'] ?? [],
            'nodes' => is_array($nodes) ? $nodes : [],
            'terminal_ws' => $terminalWs,
        ]);

        return Resposta::html($html);
    }
}
