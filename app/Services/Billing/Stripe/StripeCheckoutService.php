<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing\Stripe;

use DateTimeImmutable;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class StripeCheckoutService
{
    public function criarCheckoutAssinaturaDoPlano(int $clientId, int $planId, array $addons = []): array
    {
        $secretKey = ConfiguracoesSistema::stripeSecretKey();
        if ($secretKey === '') {
            throw new \RuntimeException('Stripe não configurado (secret key ausente).');
        }

        $appUrl = ConfiguracoesSistema::appUrlBase();

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, name, price_monthly, price_monthly_usd, cpu, ram, storage, stripe_price_id FROM plans WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $planId]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            throw new \RuntimeException('Plano não encontrado.');
        }

        $stripePriceId = trim((string) ($plano['stripe_price_id'] ?? ''));

        // Auto-criar Stripe Price se ausente
        if ($stripePriceId === '') {
            $stripePriceId = $this->criarStripePriceParaPlano($secretKey, $plano, $pdo);
            if ($stripePriceId === '') {
                throw new \RuntimeException('Plano não está configurado para Stripe (stripe_price_id ausente).');
            }
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

            $addonsJson = !empty($addons) ? json_encode($addons, JSON_UNESCAPED_UNICODE) : null;

            $insSub = $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, addons_json, asaas_subscription_id, stripe_subscription_id, stripe_checkout_session_id, billing_type, status, next_due_date, created_at) VALUES (:c, :v, :p, :aj, NULL, NULL, NULL, :bt, :s, :n, :cr)');
            $insSub->execute([
                ':c' => $clientId,
                ':v' => $vpsId,
                ':p' => (int) $plano['id'],
                ':aj' => $addonsJson,
                ':bt' => 'CREDIT_CARD',
                ':s' => 'PENDING',
                ':n' => $due,
                ':cr' => $agora,
            ]);

            $localSubId = (int) $pdo->lastInsertId();

            $successUrl = $appUrl . '/cliente/stripe/sucesso?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $appUrl . '/cliente/stripe/cancelado';

            // Build line items: plan + addons
            $lineItems = [
                ['price' => $stripePriceId, 'quantity' => 1],
            ];

            // Create Stripe prices for addons on-the-fly
            $taxaUsd = ConfiguracoesSistema::taxaConversaoUsd();
            foreach ($addons as $addon) {
                $addonUsdFixo = (float)($addon['price_usd'] ?? 0);
                if ($addonUsdFixo > 0) {
                    $addonUsdCents = (int)round($addonUsdFixo * 100);
                } else {
                    $addonPriceBrl = (float)($addon['price'] ?? 0);
                    if ($addonPriceBrl <= 0) continue;
                    $addonUsdCents = (int)round(($addonPriceBrl / $taxaUsd) * 100);
                }
                if ($addonUsdCents <= 0) continue;
                try {
                    $addonProduct = $stripe->products->create([
                        'name' => (string)($addon['name'] ?? 'Addon'),
                    ]);
                    $addonPrice = $stripe->prices->create([
                        'product' => $addonProduct->id,
                        'unit_amount' => $addonUsdCents,
                        'currency' => 'usd',
                        'recurring' => ['interval' => 'month'],
                    ]);
                    $lineItems[] = ['price' => $addonPrice->id, 'quantity' => 1];
                } catch (\Throwable) {}
            }

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
                'line_items' => $lineItems,
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

    /**
     * Cria produto + price recorrente no Stripe e salva o stripe_price_id no banco.
     */
    private function criarStripePriceParaPlano(string $secretKey, array $plano, \PDO $pdo): string
    {
        // Usar preço USD fixo se definido, senão converter BRL
        $precoUsdFixo = (float)($plano['price_monthly_usd'] ?? 0);
        if ($precoUsdFixo > 0) {
            $precoUsdCents = (int)round($precoUsdFixo * 100);
        } else {
            $taxaUsd = ConfiguracoesSistema::taxaConversaoUsd();
            $precoBrl = (float)($plano['price_monthly'] ?? 0);
            if ($precoBrl <= 0) {
                return '';
            }
            $precoUsdCents = (int)round(($precoBrl / $taxaUsd) * 100);
        }

        if ($precoUsdCents <= 0) {
            return '';
        }

        $stripe = new \Stripe\StripeClient($secretKey);

        $product = $stripe->products->create([
            'name' => (string) ($plano['name'] ?? 'Plano'),
        ]);

        $price = $stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => $precoUsdCents,
            'currency' => 'usd',
            'recurring' => ['interval' => 'month'],
        ]);

        $priceId = (string) ($price->id ?? '');
        if ($priceId !== '') {
            $up = $pdo->prepare('UPDATE plans SET stripe_price_id = :sp WHERE id = :id');
            $up->execute([':sp' => $priceId, ':id' => (int) $plano['id']]);
        }

        return $priceId;
    }
}
