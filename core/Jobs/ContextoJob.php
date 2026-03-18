<?php

declare(strict_types=1);

namespace LRV\Core\Jobs;

final class ContextoJob
{
    public function __construct(
        public readonly int $jobId,
        private readonly RepositorioJobs $repo,
    ) {
    }

    public function log(string $mensagem): void
    {
        $this->repo->adicionarLog($this->jobId, "\n" . $mensagem);
    }
}
