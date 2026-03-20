<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Webhooks;

use LRV\App\Services\Audit\AuditLogService;
use LRV\App\Services\Billing\WebhookStripeService;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class StripeController
{
    public function receber(Requisicao $req): Resposta
    {
        $segredo = ConfiguracoesSistema::stripeWebhookSecret();
        $sig = (string) ($req->headers['stripe-signature'] ?? '');

        if ($segredo === '' || $sig === '') {
            return Resposta::texto('Não autorizado.', 401);
        }

        $payloadRaw = (string) $req->corpoRaw;
        if (trim($payloadRaw) === '') {
            return Resposta::texto('Payload inválido.', 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payloadRaw, $sig, $segredo);
        } catch (\Throwable $e) {
            return Resposta::texto('Assinatura inválida.', 400);
        }

        $eventId = (string) ($event->id ?? '');
        $eventType = (string) ($event->type ?? '');

        (new AuditLogService())->registrar(
            'stripe',
            null,
            'billing.stripe_webhook_received',
            'stripe_event',
            null,
            [
                'event_id_set' => $eventId !== '',
                'event_type' => $eventType,
            ],
            $req,
        );

        $service = new WebhookStripeService();
        $service->processar($event);

        return Resposta::json(['ok' => true]);
    }
}
