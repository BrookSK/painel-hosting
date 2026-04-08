<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing\Stripe;

use DateTimeImmutable;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class StripeCheckoutService
{
    public function criarCheckoutAssinaturaDoPlano(int $clientId, int $planId, array $addons = [], int $periodo = 1): array
    {
        $secretKey = ConfiguracoesSistema::stripeSecretKey();
        if ($secretKey === '') {
            throw new \RuntimeException('Stripe não configurado (secret key ausente).');
        }

        $appUrl = ConfiguracoesSistema::appUrlBase();

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, name, price_monthly, price_monthly_usd, price_annual_upfront, price_annual_upfront_usd, cpu, ram, storage, stripe_price_id FROM plans WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $planId]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            throw new \RuntimeException('Plano não encontrado.');
        }

        $isAnnualUpfront = $periodo >= 12;
        $taxaUsd = ConfiguracoesSistema::taxaConversaoUsd();

        // Determinar preço do plano em USD cents
        if ($isAnnualUpfront) {
            $upfrontUsd = (float)($plano['price_annual_upfront_usd'] ?? 0);
            $upfrontBrl = (float)($plano['price_annual_upfront'] ?? 0);
            $planUsdCents = $upfrontUsd > 0 ? (int)round($upfrontUsd * 100) : ($upfrontBrl > 0 ? (int)round(($upfrontBrl / $taxaUsd) * 100) : 0);
            if ($planUsdCents <= 0) {
                // Fallback: mensal * 12
                $monthlyUsd = (float)($plano['price_monthly_usd'] ?? 0);
                $monthlyBrl = (float)($plano['price_monthly'] ?? 0);
                $monthlyUsdVal = $monthlyUsd > 0 ? $monthlyUsd : ($monthlyBrl > 0 ? $monthlyBrl / $taxaUsd : 0);
                $planUsdCents = (int)round($monthlyUsdVal * 12 * 100);
            }
        } else {
            $monthlyUsd = (float)($plano['price_monthly_usd'] ?? 0);
            $monthlyBrl = (float)($plano['price_monthly'] ?? 0);
            $planUsdCents = $monthlyUsd > 0 ? (int)round($monthlyUsd * 100) : ($monthlyBrl > 0 ? (int)round(($monthlyBrl / $taxaUsd) * 100) : 0);
        }

        if ($planUsdCents <= 0) {
            throw new \RuntimeException('Preço do plano não configurado.');
        }

        $clienteStmt = $pdo->prepare('SELECT id, name, email, stripe_customer_id FROM clients WHERE id = :id');
        $clienteStmt->execute([':id' => $clientId]);
        $cliente = $clienteStmt->fetch();
        if (!is_array($cliente)) throw new \RuntimeException('Cliente não encontrado.');

        $stripe = new \Stripe\StripeClient($secretKey);

        $customerId = (string)($cliente['stripe_customer_id'] ?? '');
        if ($customerId === '') {
            $c = $stripe->customers->create(['name' => (string)($cliente['name'] ?? ''), 'email' => (string)($cliente['email'] ?? ''), 'metadata' => ['local_client_id' => (string)$clientId]]);
            $customerId = (string)($c['id'] ?? '');
            if ($customerId === '') throw new \RuntimeException('Stripe não retornou customer id.');
            $pdo->prepare('UPDATE clients SET stripe_customer_id = :s WHERE id = :id')->execute([':s' => $customerId, ':id' => $clientId]);
        }

        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO vps (client_id, server_id, container_id, cpu, ram, storage, status, created_at, plan_id) VALUES (:c, NULL, NULL, :cpu, :ram, :st, :s, :cr, :pid)')
                ->execute([':c' => $clientId, ':cpu' => (int)$plano['cpu'], ':ram' => (int)$plano['ram'], ':st' => (int)$plano['storage'], ':s' => 'pending_payment', ':cr' => $agora, ':pid' => (int)$plano['id']]);
            $vpsId = (int)$pdo->lastInsertId();

            $addonsJson = !empty($addons) ? json_encode($addons, JSON_UNESCAPED_UNICODE) : null;
            $due = (new DateTimeImmutable('now'))->modify($isAnnualUpfront ? '+1 year' : '+1 month')->format('Y-m-d');

            $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, addons_json, billing_type, status, next_due_date, created_at) VALUES (:c, :v, :p, :aj, :bt, :s, :n, :cr)')
                ->execute([':c' => $clientId, ':v' => $vpsId, ':p' => (int)$plano['id'], ':aj' => $addonsJson, ':bt' => 'CREDIT_CARD', ':s' => 'PENDING', ':n' => $due, ':cr' => $agora]);
            $localSubId = (int)$pdo->lastInsertId();

            $successUrl = $appUrl . '/cliente/stripe/sucesso?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $appUrl . '/cliente/stripe/cancelado';

            // Criar produto + price no Stripe com o valor correto
            $planProduct = $stripe->products->create(['name' => (string)($plano['name'] ?? 'Plano')]);

            $lineItems = [];
            if ($isAnnualUpfront) {
                // Anual à vista: pagamento único (one-time)
                $planPrice = $stripe->prices->create(['product' => $planProduct->id, 'unit_amount' => $planUsdCents, 'currency' => 'usd']);
                $lineItems[] = ['price' => $planPrice->id, 'quantity' => 1];
            } else {
                // Mensal: subscription recorrente
                $planPrice = $stripe->prices->create(['product' => $planProduct->id, 'unit_amount' => $planUsdCents, 'currency' => 'usd', 'recurring' => ['interval' => 'month']]);
                $lineItems[] = ['price' => $planPrice->id, 'quantity' => 1];
            }

            // Addons
            foreach ($addons as $addon) {
                $addonUsdFixo = (float)($addon['price_usd'] ?? 0);
                $addonBrl = (float)($addon['price'] ?? 0);
                if ($isAnnualUpfront) {
                    // Addon anual: price_annual_usd * 12 ou price_usd * 12
                    $addonAnnualUsd = (float)($addon['price_annual_usd'] ?? 0);
                    $addonAnnualBrl = (float)($addon['price_annual'] ?? 0);
                    if ($addonAnnualUsd > 0) $addonCents = (int)round($addonAnnualUsd * 12 * 100);
                    elseif ($addonAnnualBrl > 0) $addonCents = (int)round(($addonAnnualBrl / $taxaUsd) * 12 * 100);
                    elseif ($addonUsdFixo > 0) $addonCents = (int)round($addonUsdFixo * 12 * 100);
                    elseif ($addonBrl > 0) $addonCents = (int)round(($addonBrl / $taxaUsd) * 12 * 100);
                    else continue;
                } else {
                    $addonCents = $addonUsdFixo > 0 ? (int)round($addonUsdFixo * 100) : ($addonBrl > 0 ? (int)round(($addonBrl / $taxaUsd) * 100) : 0);
                }
                if ($addonCents <= 0) continue;
                try {
                    $ap = $stripe->products->create(['name' => (string)($addon['name'] ?? 'Addon')]);
                    if ($isAnnualUpfront) {
                        $apr = $stripe->prices->create(['product' => $ap->id, 'unit_amount' => $addonCents, 'currency' => 'usd']);
                    } else {
                        $apr = $stripe->prices->create(['product' => $ap->id, 'unit_amount' => $addonCents, 'currency' => 'usd', 'recurring' => ['interval' => 'month']]);
                    }
                    $lineItems[] = ['price' => $apr->id, 'quantity' => 1];
                } catch (\Throwable) {}
            }

            // Criar sessão: payment (one-time) ou subscription
            $sessionParams = [
                'mode' => $isAnnualUpfront ? 'payment' : 'subscription',
                'customer' => $customerId,
                'client_reference_id' => (string)$localSubId,
                'metadata' => ['local_subscription_id' => (string)$localSubId, 'local_client_id' => (string)$clientId, 'local_vps_id' => (string)$vpsId, 'local_plan_id' => (string)$planId, 'periodo' => (string)$periodo],
                'line_items' => $lineItems,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ];
            if (!$isAnnualUpfront) {
                $sessionParams['subscription_data'] = ['metadata' => ['local_subscription_id' => (string)$localSubId, 'local_client_id' => (string)$clientId, 'local_vps_id' => (string)$vpsId, 'local_plan_id' => (string)$planId]];
            }

            $session = $stripe->checkout->sessions->create($sessionParams);

            $sessionId = (string)($session['id'] ?? '');
            $url = (string)($session['url'] ?? '');
            if ($sessionId === '' || $url === '') throw new \RuntimeException('Stripe não retornou a URL da sessão de checkout.');

            $pdo->prepare('UPDATE subscriptions SET stripe_checkout_session_id = :sid WHERE id = :id')
                ->execute([':sid' => $sessionId, ':id' => $localSubId]);

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
     * Cria assinatura Stripe inline (sem redirect) — retorna client_secret para Stripe Elements.
     */
    public function criarAssinaturaInline(int $clientId, int $planId, array $addons = []): array
    {
        $secretKey = ConfiguracoesSistema::stripeSecretKey();
        if ($secretKey === '') {
            throw new \RuntimeException('Stripe não configurado (secret key ausente).');
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("SELECT id, name, price_monthly, price_monthly_usd, cpu, ram, storage, stripe_price_id FROM plans WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $planId]);
        $plano = $stmt->fetch();
        if (!is_array($plano)) throw new \RuntimeException('Plano não encontrado.');

        $stripePriceId = trim((string)($plano['stripe_price_id'] ?? ''));
        if ($stripePriceId === '') {
            $stripePriceId = $this->criarStripePriceParaPlano($secretKey, $plano, $pdo);
            if ($stripePriceId === '') throw new \RuntimeException('Plano não está configurado para Stripe.');
        }

        $clienteStmt = $pdo->prepare('SELECT id, name, email, stripe_customer_id FROM clients WHERE id = :id');
        $clienteStmt->execute([':id' => $clientId]);
        $cliente = $clienteStmt->fetch();
        if (!is_array($cliente)) throw new \RuntimeException('Cliente não encontrado.');

        $stripe = new \Stripe\StripeClient($secretKey);

        // Garantir customer no Stripe
        $customerId = (string)($cliente['stripe_customer_id'] ?? '');
        if ($customerId === '') {
            $c = $stripe->customers->create([
                'name' => (string)($cliente['name'] ?? ''),
                'email' => (string)($cliente['email'] ?? ''),
                'metadata' => ['local_client_id' => (string)$clientId],
            ]);
            $customerId = (string)($c['id'] ?? '');
            $pdo->prepare('UPDATE clients SET stripe_customer_id = :s WHERE id = :id')
                ->execute([':s' => $customerId, ':id' => $clientId]);
        }

        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            // Criar VPS + subscription local
            $pdo->prepare('INSERT INTO vps (client_id, server_id, container_id, cpu, ram, storage, status, created_at, plan_id) VALUES (:c, NULL, NULL, :cpu, :ram, :st, :s, :cr, :pid)')
                ->execute([':c' => $clientId, ':cpu' => (int)$plano['cpu'], ':ram' => (int)$plano['ram'], ':st' => (int)$plano['storage'], ':s' => 'pending_payment', ':cr' => $agora, ':pid' => (int)$plano['id']]);
            $vpsId = (int)$pdo->lastInsertId();

            $addonsJson = !empty($addons) ? json_encode($addons, JSON_UNESCAPED_UNICODE) : null;
            $due = (new DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d');
            $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, addons_json, billing_type, status, next_due_date, created_at) VALUES (:c, :v, :p, :aj, :bt, :s, :n, :cr)')
                ->execute([':c' => $clientId, ':v' => $vpsId, ':p' => (int)$plano['id'], ':aj' => $addonsJson, ':bt' => 'CREDIT_CARD', ':s' => 'PENDING', ':n' => $due, ':cr' => $agora]);
            $localSubId = (int)$pdo->lastInsertId();

            // Montar items: plano + addons
            $items = [['price' => $stripePriceId]];
            $taxaUsd = ConfiguracoesSistema::taxaConversaoUsd();
            foreach ($addons as $addon) {
                $addonUsdFixo = (float)($addon['price_usd'] ?? 0);
                $addonUsdCents = $addonUsdFixo > 0 ? (int)round($addonUsdFixo * 100) : (int)round(((float)($addon['price'] ?? 0) / $taxaUsd) * 100);
                if ($addonUsdCents <= 0) continue;
                try {
                    $ap = $stripe->products->create(['name' => (string)($addon['name'] ?? 'Addon')]);
                    $apr = $stripe->prices->create(['product' => $ap->id, 'unit_amount' => $addonUsdCents, 'currency' => 'usd', 'recurring' => ['interval' => 'month']]);
                    $items[] = ['price' => $apr->id];
                } catch (\Throwable) {}
            }

            // Criar subscription com payment_behavior incomplete — retorna client_secret
            $subscription = $stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => $items,
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'local_subscription_id' => (string)$localSubId,
                    'local_client_id' => (string)$clientId,
                    'local_vps_id' => (string)$vpsId,
                    'local_plan_id' => (string)$planId,
                ],
            ]);

            $stripeSubId = (string)($subscription->id ?? '');
            $clientSecret = (string)($subscription->latest_invoice->payment_intent->client_secret ?? '');

            $pdo->prepare('UPDATE subscriptions SET stripe_subscription_id = :sid WHERE id = :id')
                ->execute([':sid' => $stripeSubId, ':id' => $localSubId]);

            $pdo->commit();

            return [
                'client_secret' => $clientSecret,
                'subscription_id' => $localSubId,
                'stripe_subscription_id' => $stripeSubId,
                'vps_id' => $vpsId,
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cria assinatura Stripe com dados do cartão server-side (sem Stripe.js).
     */
    public function criarAssinaturaComCartao(int $clientId, int $planId, array $addons, string $ccNumero, string $ccValidade, string $ccCvv, string $ccNome): array
    {
        $secretKey = ConfiguracoesSistema::stripeSecretKey();
        if ($secretKey === '') throw new \RuntimeException('Stripe não configurado (secret key ausente).');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT id, name, price_monthly, price_monthly_usd, cpu, ram, storage, stripe_price_id FROM plans WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $planId]);
        $plano = $stmt->fetch();
        if (!is_array($plano)) throw new \RuntimeException('Plano não encontrado.');

        $stripePriceId = trim((string)($plano['stripe_price_id'] ?? ''));
        if ($stripePriceId === '') {
            $stripePriceId = $this->criarStripePriceParaPlano($secretKey, $plano, $pdo);
            if ($stripePriceId === '') throw new \RuntimeException('Plano não configurado para Stripe.');
        }

        $clienteStmt = $pdo->prepare('SELECT id, name, email, stripe_customer_id FROM clients WHERE id = :id');
        $clienteStmt->execute([':id' => $clientId]);
        $cliente = $clienteStmt->fetch();
        if (!is_array($cliente)) throw new \RuntimeException('Cliente não encontrado.');

        $stripe = new \Stripe\StripeClient($secretKey);

        // Garantir customer
        $customerId = (string)($cliente['stripe_customer_id'] ?? '');
        if ($customerId === '') {
            $c = $stripe->customers->create(['name' => (string)($cliente['name'] ?? ''), 'email' => (string)($cliente['email'] ?? ''), 'metadata' => ['local_client_id' => (string)$clientId]]);
            $customerId = (string)($c['id'] ?? '');
            $pdo->prepare('UPDATE clients SET stripe_customer_id = :s WHERE id = :id')->execute([':s' => $customerId, ':id' => $clientId]);
        }

        // Criar PaymentMethod com dados do cartão
        $expParts = explode('/', $ccValidade);
        $expMonth = (int)($expParts[0] ?? 1);
        $expYear = (int)($expParts[1] ?? 30);
        if ($expYear < 100) $expYear += 2000;

        $pm = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'number' => $ccNumero,
                'exp_month' => $expMonth,
                'exp_year' => $expYear,
                'cvc' => $ccCvv,
            ],
            'billing_details' => ['name' => $ccNome !== '' ? $ccNome : (string)($cliente['name'] ?? '')],
        ]);

        // Attach ao customer e definir como default
        $stripe->paymentMethods->attach($pm->id, ['customer' => $customerId]);
        $stripe->customers->update($customerId, ['invoice_settings' => ['default_payment_method' => $pm->id]]);

        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO vps (client_id, server_id, container_id, cpu, ram, storage, status, created_at, plan_id) VALUES (:c, NULL, NULL, :cpu, :ram, :st, :s, :cr, :pid)')
                ->execute([':c' => $clientId, ':cpu' => (int)$plano['cpu'], ':ram' => (int)$plano['ram'], ':st' => (int)$plano['storage'], ':s' => 'pending_payment', ':cr' => $agora, ':pid' => (int)$plano['id']]);
            $vpsId = (int)$pdo->lastInsertId();

            $addonsJson = !empty($addons) ? json_encode($addons, JSON_UNESCAPED_UNICODE) : null;
            $due = (new DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d');
            $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, addons_json, billing_type, status, next_due_date, created_at) VALUES (:c, :v, :p, :aj, :bt, :s, :n, :cr)')
                ->execute([':c' => $clientId, ':v' => $vpsId, ':p' => (int)$plano['id'], ':aj' => $addonsJson, ':bt' => 'CREDIT_CARD', ':s' => 'PENDING', ':n' => $due, ':cr' => $agora]);
            $localSubId = (int)$pdo->lastInsertId();

            // Montar items
            $items = [['price' => $stripePriceId]];
            $taxaUsd = ConfiguracoesSistema::taxaConversaoUsd();
            foreach ($addons as $addon) {
                $addonUsdFixo = (float)($addon['price_usd'] ?? 0);
                $addonUsdCents = $addonUsdFixo > 0 ? (int)round($addonUsdFixo * 100) : (int)round(((float)($addon['price'] ?? 0) / $taxaUsd) * 100);
                if ($addonUsdCents <= 0) continue;
                try {
                    $ap = $stripe->products->create(['name' => (string)($addon['name'] ?? 'Addon')]);
                    $apr = $stripe->prices->create(['product' => $ap->id, 'unit_amount' => $addonUsdCents, 'currency' => 'usd', 'recurring' => ['interval' => 'month']]);
                    $items[] = ['price' => $apr->id];
                } catch (\Throwable) {}
            }

            // Criar subscription — cobra automaticamente com o default payment method
            $subscription = $stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => $items,
                'default_payment_method' => $pm->id,
                'metadata' => [
                    'local_subscription_id' => (string)$localSubId,
                    'local_client_id' => (string)$clientId,
                    'local_vps_id' => (string)$vpsId,
                    'local_plan_id' => (string)$planId,
                ],
            ]);

            $stripeSubId = (string)($subscription->id ?? '');
            $subStatus = (string)($subscription->status ?? '');

            $pdo->prepare('UPDATE subscriptions SET stripe_subscription_id = :sid, status = :st WHERE id = :id')
                ->execute([':sid' => $stripeSubId, ':st' => $subStatus === 'active' ? 'ACTIVE' : 'PENDING', ':id' => $localSubId]);

            if ($subStatus === 'active') {
                $pdo->prepare("UPDATE vps SET status = 'running' WHERE id = :id")->execute([':id' => $vpsId]);
            }

            $pdo->commit();

            return ['subscription_id' => $localSubId, 'stripe_subscription_id' => $stripeSubId, 'status' => $subStatus, 'vps_id' => $vpsId];
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
