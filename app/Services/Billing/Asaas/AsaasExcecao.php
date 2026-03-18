<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing\Asaas;

final class AsaasExcecao extends \RuntimeException
{
    public function __construct(
        string $mensagem,
        public readonly int $status,
        public readonly ?array $respostaJson,
    ) {
        parent::__construct($mensagem);
    }
}
