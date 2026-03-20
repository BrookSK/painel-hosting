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

        $planId = (int) ($req->post['plan_id'] ?? 0);
        $gateway = strtolower(trim((string) ($req->post['gateway'] ?? '')));

        if ($gateway === 'stripe') {
            if ($planId <= 0) {
                return Resposta::texto('Plano inválido.', 400);
            }

            $service = new StripeCheckoutService();

            try {
                $resultado = $service->criarCheckoutAssinaturaDoPlano($clienteId, $planId);
            } catch (\Throwable $e) {
                (new AuditLogService())->registrar(
                    'client',
                    $clienteId,
                    'billing.subscribe_plan',
                    'plan',
                    $planId,
                    ['plan_id' => $planId, 'gateway' => 'stripe', 'ok' => false],
                    $req,
                );

                $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-criada.php', [
                    'erro' => 'Não foi possível iniciar o checkout. Verifique as configurações do Stripe e tente novamente.',
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
                return Resposta::texto('Falha ao iniciar checkout.', 500);
            }

            return Resposta::redirecionar($checkoutUrl);
        }

        $billingType = (string) ($req->post['billing_type'] ?? 'PIX');
        if (!in_array($billingType, ['PIX', 'BOLETO'], true)) {
            $billingType = 'PIX';
        }

        if ($planId <= 0) {
            return Resposta::texto('Plano inválido.', 400);
        }

        $service = new AssinaturasService(new AsaasApi(new ClienteHttp()));

        try {
            $resultado = $service->criarAssinaturaDoPlano($clienteId, $planId, $billingType);
        } catch (\Throwable $e) {
            (new AuditLogService())->registrar(
                'client',
                $clienteId,
                'billing.subscribe_plan',
                'plan',
                $planId,
                ['plan_id' => $planId, 'billing_type' => $billingType, 'ok' => false],
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
}
