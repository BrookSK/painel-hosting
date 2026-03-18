<?php

declare(strict_types=1);

namespace LRV\Core\Jobs;

final class ProcessadorJobs
{
    private array $handlers = [];

    public function registrar(string $type, callable $handler): void
    {
        $this->handlers[$type] = $handler;
    }

    public function processar(Job $job, ContextoJob $ctx): void
    {
        $handler = $this->handlers[$job->type] ?? null;
        if ($handler === null) {
            throw new \RuntimeException('Tipo de job não registrado: ' . $job->type);
        }

        $handler($job->payload, $ctx);
    }
}
