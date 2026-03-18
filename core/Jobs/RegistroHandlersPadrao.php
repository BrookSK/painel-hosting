<?php

declare(strict_types=1);

namespace LRV\Core\Jobs;

final class RegistroHandlersPadrao
{
    public static function registrar(ProcessadorJobs $p): void
    {
        $p->registrar('noop', static function (array $payload, ContextoJob $ctx): void {
            $ctx->log('Job de teste executado.');
        });
    }
}
