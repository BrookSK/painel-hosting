<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use LRV\Core\Bootstrap;
use LRV\Core\Jobs\ProcessadorJobs;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\Jobs\WorkerJobs;
use LRV\App\Jobs\RegistroHandlers;

Bootstrap::iniciar();

$once = in_array('--once', $argv ?? [], true);

$repo = new RepositorioJobs();
$proc = new ProcessadorJobs();
RegistroHandlers::registrar($proc);
$worker = new WorkerJobs($repo, $proc);

if ($once) {
    $executou = $worker->executarUmaVez();
    echo $executou ? "Processado 1 job.\n" : "Nenhum job pendente.\n";
    exit(0);
}

echo "Worker iniciado.\n";

while (true) {
    $executou = $worker->executarUmaVez();
    if (!$executou) {
        sleep(2);
    }
}
