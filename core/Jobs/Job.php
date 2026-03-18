<?php

declare(strict_types=1);

namespace LRV\Core\Jobs;

final class Job
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly array $payload,
        public readonly string $status,
        public readonly string $log,
    ) {
    }
}
