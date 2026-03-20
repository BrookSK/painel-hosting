<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Webhooks;

use LRV\App\Services\Audit\AuditLogService;
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

        $eventId = (string) ($payload['id'] ?? '');
        $eventType = (string) ($payload['event'] ?? '');
        $subscriptionId = (string) ($payload['subscriptionId'] ?? '');
        if ($subscriptionId === '') {
            $sub = $payload['subscription'] ?? null;
            if (is_string($sub)) {
                $subscriptionId = $sub;
            } elseif (is_array($sub)) {
                $subscriptionId = (string) ($sub['id'] ?? '');
            }
        }
        if ($subscriptionId === '') {
            $payment = $payload['payment'] ?? null;
            if (is_array($payment)) {
                $subscriptionId = (string) ($payment['subscription'] ?? '');
                if ($subscriptionId === '') {
                    $subscriptionId = (string) ($payment['subscriptionId'] ?? '');
                }
            }
        }

        (new AuditLogService())->registrar(
            'asaas',
            null,
            'billing.asaas_webhook_received',
            'asaas_event',
            null,
            [
                'event_id_set' => $eventId !== '',
                'event_type' => $eventType,
                'subscription_id_set' => $subscriptionId !== '',
            ],
            $req,
        );

        $service = new WebhookAsaasService();
        $service->processar($payload);

        return Resposta::json(['ok' => true]);
    }
}
