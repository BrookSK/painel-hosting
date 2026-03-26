<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Audit\AuditLogService;
use LRV\App\Services\Billing\AssinaturasService;
use LRV\App\Services\Billing\Asaas\AsaasApi;
use LRV\App\Services\Billing\Stripe\StripeCheckoutService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AssinarPlanoController
{
    public function assinar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $in = $req->input();
        $planId = $in->postInt('plan_id', 1, 2147483647, true);
        $gateway = trim((string)($req->post['gateway'] ?? 'PIX'));
        $addonsIds = trim((string)($req->post['addons_ids'] ?? ''));

        // Calcular addons selecionados
        $addonsSelecionados = [];
        $addonsTotal = 0.0;
        if ($addonsIds !== '') {
            $ids = array_filter(array_map('intval', explode(',', $addonsIds)));
            if (!empty($ids)) {
                $pdo = \LRV\Core\BancoDeDados::pdo();
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $st = $pdo->prepare("SELECT id, name, price FROM plan_addons WHERE id IN ({$placeholders}) AND plan_id = ? AND active = 1");
                $params = array_merge($ids, [$planId]);
                $st->execute($params);
                $rows = $st->fetchAll();
                foreach (($rows ?: []) as $r) {
                    $addonsSelecionados[] = ['id' => (int)$r['id'], 'name' => (string)$r['name'], 'price' => (float)$r['price']];
                    $addonsTotal += (float)$r['price'];
                }
            }
        }

        if ($in->temErros() || $planId <= 0) {
            return Resposta::texto('Plano inválido.', 400);
        }

        $isBrl = \LRV\Core\I18n::idioma() === 'pt-BR';

        // BRL → tudo via Asaas (PIX, BOLETO, CREDIT_CARD)
        // USD → só Stripe (cartão)
        if ($isBrl) {
            $billingType = match ($gateway) {
                'BOLETO' => 'BOLETO',
                'CREDIT_CARD', 'stripe' => 'CREDIT_CARD',
                default => 'PIX',
            };

            $service = new AssinaturasService(new AsaasApi(new ClienteHttp()));

            try {
                $resultado = $service->criarAssinaturaDoPlano($clienteId, $planId, $billingType, $addonsSelecionados);
            } catch (\Throwable $e) {
                $erroDetalhe = $e->getMessage();

                (new AuditLogService())->registrar(
                    'client',
                    $clienteId,
                    'billing.subscribe_plan',
                    'plan',
                    $planId,
                    ['plan_id' => $planId, 'billing_type' => $billingType, 'ok' => false, 'erro' => $erroDetalhe],
                    $req,
                );

                $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-criada.php', [
                    'erro' => 'Não foi possível criar a assinatura. Verifique as configurações do Asaas e tente novamente.',
                    'resultado' => null,
                ]);
                return Resposta::html($html, 400);
            }

            $asaasSubId = '';
            $cobrancasCount = 0;
            if (is_array($resultado)) {
                $ass = $resultado['assinatura'] ?? null;
                if (is_array($ass)) {
                    $asaasSubId = (string) ($ass['id'] ?? '');
                }
                $cobr = $resultado['cobrancas'] ?? null;
                if (is_array($cobr)) {
                    $data = $cobr['data'] ?? null;
                    if (is_array($data)) {
                        $cobrancasCount = count($data);
                    }
                }
            }

            (new AuditLogService())->registrar(
                'client',
                $clienteId,
                'billing.subscribe_plan',
                'plan',
                $planId,
                [
                    'plan_id' => $planId,
                    'billing_type' => $billingType,
                    'ok' => true,
                    'asaas_subscription_id_set' => $asaasSubId !== '',
                    'cobrancas_count' => $cobrancasCount,
                ],
                $req,
            );

            $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-criada.php', [
                'erro' => '',
                'resultado' => $resultado,
            ]);

            return Resposta::html($html);
        }

        // USD → Stripe checkout
        $service = new StripeCheckoutService();

        try {
            $resultado = $service->criarCheckoutAssinaturaDoPlano($clienteId, $planId, $addonsSelecionados);
        } catch (\Throwable $e) {
            $erroDetalhe = $e->getMessage();

            (new AuditLogService())->registrar(
                'client',
                $clienteId,
                'billing.subscribe_plan',
                'plan',
                $planId,
                ['plan_id' => $planId, 'gateway' => 'stripe', 'ok' => false, 'erro' => $erroDetalhe],
                $req,
            );

            $mensagemUsuario = match (true) {
                str_contains($erroDetalhe, 'secret key ausente') => 'Stripe is not configured. Please contact support.',
                str_contains($erroDetalhe, 'stripe_price_id ausente') => 'This plan is not yet configured for card payment. Please contact support.',
                str_contains($erroDetalhe, 'Plano não encontrado') => 'Plan not found or inactive.',
                str_contains($erroDetalhe, 'Cliente não encontrado') => 'Could not locate your account. Please try again.',
                default => 'Could not start checkout. Please try again later.',
            };

            $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-criada.php', [
                'erro' => $mensagemUsuario,
                'resultado' => null,
            ]);
            return Resposta::html($html, 400);
        }

        $checkoutUrl = is_array($resultado) ? (string) ($resultado['checkout_url'] ?? '') : '';

        (new AuditLogService())->registrar(
            'client',
            $clienteId,
            'billing.subscribe_plan',
            'plan',
            $planId,
            ['plan_id' => $planId, 'gateway' => 'stripe', 'ok' => true, 'checkout_url_set' => $checkoutUrl !== ''],
            $req,
        );

        if ($checkoutUrl === '') {
            return Resposta::texto('Failed to start checkout.', 500);
        }

        return Resposta::redirecionar($checkoutUrl);
    }
}
