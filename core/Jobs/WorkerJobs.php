<?php

declare(strict_types=1);

namespace LRV\Core\Jobs;

final class WorkerJobs
{
    public function __construct(
        private readonly RepositorioJobs $repo,
        private readonly ProcessadorJobs $processador,
    ) {
    }

    public function executarUmaVez(): bool
    {
        $job = $this->repo->pegarProximoEMarcarRunning();
        if ($job === null) {
            return false;
        }

        $ctx = new ContextoJob($job->id, $this->repo);
        $ctx->log('[INÍCIO] ' . $job->type);

        try {
            $this->processador->processar($job, $ctx);
            $ctx->log('[FIM] concluído');
            $this->repo->marcarConcluido($job->id);
        } catch (\Throwable $e) {
            $this->repo->marcarFalha($job->id, $e->getMessage());
        }

        return true;
    }
}
