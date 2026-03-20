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

        // Prevenir replay attack: verificar se event_id já foi processado
        if ($eventId !== '') {
            try {
                $pdo = \LRV\Core\BancoDeDados::pdo();
                $ins = $pdo->prepare('INSERT IGNORE INTO stripe_processed_events (event_id, event_type, created_at) VALUES (:eid, :et, :c)');
                $ins->execute([':eid' => $eventId, ':et' => $eventType, ':c' => date('Y-m-d H:i:s')]);
                if ($ins->rowCount() === 0) {
                    // Evento já processado
                    return Resposta::json(['ok' => true, 'duplicado' => true]);
                }
            } catch (\Throwable $e) {
                // Se tabela não existe ainda, continua sem proteção de replay
            }
        }

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
