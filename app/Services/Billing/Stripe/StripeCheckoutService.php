<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing\Stripe;

use DateTimeImmutable;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class StripeCheckoutService
{
    public function criarCheckoutAssinaturaDoPlano(int $clientId, int $planId): array
    {
        $secretKey = ConfiguracoesSistema::stripeSecretKey();
        if ($secretKey === '') {
            throw new \RuntimeException('Stripe não configurado (secret key ausente).');
        }

        $appUrl = ConfiguracoesSistema::appUrlBase();

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, name, price_monthly, cpu, ram, storage, stripe_price_id FROM plans WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $planId]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            throw new \RuntimeException('Plano não encontrado.');
        }

        $stripePriceId = trim((string) ($plano['stripe_price_id'] ?? ''));
        if ($stripePriceId === '') {
            throw new \RuntimeException('Plano não está configurado para Stripe (stripe_price_id ausente).');
        }

        $clienteStmt = $pdo->prepare('SELECT id, name, email, stripe_customer_id FROM clients WHERE id = :id');
        $clienteStmt->execute([':id' => $clientId]);
        $cliente = $clienteStmt->fetch();

        if (!is_array($cliente)) {
            throw new \RuntimeException('Cliente não encontrado.');
        }

        $stripe = new \Stripe\StripeClient($secretKey);

        $customerId = (string) ($cliente['stripe_customer_id'] ?? '');
        if ($customerId === '') {
            $c = $stripe->customers->create([
                'name' => (string) ($cliente['name'] ?? ''),
                'email' => (string) ($cliente['email'] ?? ''),
                'metadata' => [
                    'local_client_id' => (string) $clientId,
                ],
            ]);
            $customerId = (string) ($c['id'] ?? '');
            if ($customerId === '') {
                throw new \RuntimeException('Stripe não retornou customer id.');
            }

            $up = $pdo->prepare('UPDATE clients SET stripe_customer_id = :s WHERE id = :id');
            $up->execute([':s' => $customerId, ':id' => $clientId]);
        }

        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            $insVps = $pdo->prepare('INSERT INTO vps (client_id, server_id, container_id, cpu, ram, storage, status, created_at, plan_id) VALUES (:c, NULL, NULL, :cpu, :ram, :st, :s, :cr, :pid)');
            $insVps->execute([
                ':c' => $clientId,
                ':cpu' => (int) $plano['cpu'],
                ':ram' => (int) $plano['ram'],
                ':st' => (int) $plano['storage'],
                ':s' => 'pending_payment',
                ':cr' => $agora,
                ':pid' => (int) $plano['id'],
            ]);

            $vpsId = (int) $pdo->lastInsertId();

            $due = (new DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d');

            $insSub = $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, asaas_subscription_id, stripe_subscription_id, stripe_checkout_session_id, status, next_due_date, created_at) VALUES (:c, :v, :p, NULL, NULL, NULL, :s, :n, :cr)');
            $insSub->execute([
                ':c' => $clientId,
                ':v' => $vpsId,
                ':p' => (int) $plano['id'],
                ':s' => 'PENDING',
                ':n' => $due,
                ':cr' => $agora,
            ]);

            $localSubId = (int) $pdo->lastInsertId();

            $successUrl = $appUrl . '/cliente/stripe/sucesso?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $appUrl . '/cliente/stripe/cancelado';

            $session = $stripe->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $customerId,
                'client_reference_id' => (string) $localSubId,
                'metadata' => [
                    'local_subscription_id' => (string) $localSubId,
                    'local_client_id' => (string) $clientId,
                    'local_vps_id' => (string) $vpsId,
                    'local_plan_id' => (string) $planId,
                ],
                'subscription_data' => [
                    'metadata' => [
                        'local_subscription_id' => (string) $localSubId,
                        'local_client_id' => (string) $clientId,
                        'local_vps_id' => (string) $vpsId,
                        'local_plan_id' => (string) $planId,
                    ],
                ],
                'line_items' => [
                    [
                        'price' => $stripePriceId,
                        'quantity' => 1,
                    ],
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            $sessionId = (string) ($session['id'] ?? '');
            $url = (string) ($session['url'] ?? '');
            if ($sessionId === '' || $url === '') {
                throw new \RuntimeException('Stripe não retornou a URL da sessão de checkout.');
            }

            $upSub = $pdo->prepare('UPDATE subscriptions SET stripe_checkout_session_id = :sid WHERE id = :id');
            $upSub->execute([
                ':sid' => $sessionId,
                ':id' => $localSubId,
            ]);

            $pdo->commit();

            return [
                'checkout_session_id' => $sessionId,
                'checkout_url' => $url,
                'subscription_id' => $localSubId,
                'vps_id' => $vpsId,
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
