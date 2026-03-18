<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Webhooks;

use LRV\App\Services\Billing\WebhookAsaasService;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class AsaasController
{
    public function receber(Requisicao $req): Resposta
    {
        $segredo = ConfiguracoesSistema::webhookSegredoAsaas();
        $token = (string) ($req->headers['asaas-access-token'] ?? '');

        if ($segredo === '' || $token !== $segredo) {
            return Resposta::texto('Não autorizado.', 401);
        }

        $payload = $req->json();
        if ($payload === []) {
            return Resposta::texto('Payload inválido.', 400);
        }

        $service = new WebhookAsaasService();
        $service->processar($payload);

        return Resposta::json(['ok' => true]);
    }
}
