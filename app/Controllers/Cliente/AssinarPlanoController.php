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
                $st = $pdo->prepare("SELECT id, name, price, price_usd, price_annual, price_annual_usd FROM plan_addons WHERE id IN ({$placeholders}) AND plan_id = ? AND active = 1");
                $params = array_merge($ids, [$planId]);
                $st->execute($params);
                $rows = $st->fetchAll();
                foreach (($rows ?: []) as $r) {
                    $addonsSelecionados[] = ['id' => (int)$r['id'], 'name' => (string)$r['name'], 'price' => (float)$r['price'], 'price_usd' => (float)($r['price_usd'] ?? 0), 'price_annual' => (float)($r['price_annual'] ?? 0), 'price_annual_usd' => (float)($r['price_annual_usd'] ?? 0)];
                    $addonsTotal += (float)$r['price'];
                }
            }
        }

        if ($in->temErros() || $planId <= 0) {
            return Resposta::texto('Plano inválido.', 400);
        }

        // Validar acesso ao plano: clientes gerenciados só podem assinar planos exclusivos deles
        $pdo = \LRV\Core\BancoDeDados::pdo();
        $planCheck = $pdo->prepare("SELECT client_id FROM plans WHERE id = :id AND status = 'active' LIMIT 1");
        $planCheck->execute([':id' => $planId]);
        $planRow = $planCheck->fetch();
        if (!is_array($planRow)) {
            return Resposta::texto('Plano não encontrado.', 404);
        }
        $planClientId = $planRow['client_id'] ?? null;
        if ($planClientId !== null && (int)$planClientId !== $clienteId) {
            return Resposta::texto('Acesso negado a este plano.', 403);
        }
        if ($planClientId === null && Auth::clienteGerenciado() && !Auth::estaImpersonando()) {
            return Resposta::texto('Plano não disponível.', 403);
        }

        // Salvar CPF/CNPJ se enviado e cliente não tem
        $cpfEnviado = preg_replace('/\D/', '', trim((string)($req->post['cpf_cnpj'] ?? '')));
        if ($cpfEnviado !== '') {
            $pdo = \LRV\Core\BancoDeDados::pdo();
            $pdo->prepare('UPDATE clients SET cpf_cnpj = :c WHERE id = :id AND (cpf_cnpj IS NULL OR cpf_cnpj = \'\')')
                ->execute([':c' => $cpfEnviado, ':id' => $clienteId]);
        }

        // Usar moeda selecionada pelo cliente no checkout (não o idioma do browser)
        $selectedCurrency = trim(strtoupper((string)($req->post['currency'] ?? '')));
        $isBrl = $selectedCurrency !== 'USD';

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
                $erroApi = [];

                if ($e instanceof \LRV\App\Services\Billing\Asaas\AsaasExcecao && is_array($e->respostaJson)) {
                    $erroApi = $e->respostaJson;
                }

                (new AuditLogService())->registrar(
                    'client',
                    $clienteId,
                    'billing.subscribe_plan',
                    'plan',
                    $planId,
                    ['plan_id' => $planId, 'billing_type' => $billingType, 'ok' => false, 'erro' => $erroDetalhe, 'asaas_response' => $erroApi],
                    $req,
                );

                // Extrair mensagem legível do Asaas
                $mensagemUsuario = $this->extrairErroAsaas($erroApi, $erroDetalhe);

                $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-criada.php', [
                    'erro' => $mensagemUsuario,
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

            $localSubId = (int) ($resultado['local_subscription_id'] ?? 0);
            return Resposta::json(['ok' => true, 'redirect' => '/cliente/pagamento?sub=' . $localSubId]);
        }

        // USD → Stripe Checkout (redirect)
        $service = new StripeCheckoutService();
        $periodo = (int)($req->post['periodo'] ?? 1);

        try {
            $resultado = $service->criarCheckoutAssinaturaDoPlano($clienteId, $planId, $addonsSelecionados, $periodo);
        } catch (\Throwable $e) {
            $erroDetalhe = $e->getMessage();
            (new AuditLogService())->registrar('client', $clienteId, 'billing.subscribe_plan', 'plan', $planId,
                ['plan_id' => $planId, 'gateway' => 'stripe', 'ok' => false, 'erro' => $erroDetalhe], $req);

            $mensagemUsuario = match (true) {
                str_contains($erroDetalhe, 'secret key ausente') => 'Stripe não configurado. Contate o suporte.',
                str_contains($erroDetalhe, 'stripe_price_id ausente'),
                str_contains($erroDetalhe, 'configurado para Stripe') => 'Plano não configurado para pagamento com cartão.',
                str_contains($erroDetalhe, 'Plano não encontrado') => 'Plano não encontrado ou inativo.',
                str_contains($erroDetalhe, 'Cliente não encontrado') => 'Erro ao localizar sua conta.',
                default => 'Erro ao processar pagamento: ' . $erroDetalhe,
            };
            return Resposta::json(['ok' => false, 'erro' => $mensagemUsuario], 400);
        }

        $checkoutUrl = is_array($resultado) ? (string)($resultado['checkout_url'] ?? '') : '';

        (new AuditLogService())->registrar('client', $clienteId, 'billing.subscribe_plan', 'plan', $planId,
            ['plan_id' => $planId, 'gateway' => 'stripe', 'ok' => true, 'periodo' => $periodo], $req);

        if ($checkoutUrl === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Falha ao iniciar checkout.'], 500);
        }

        return Resposta::json(['ok' => true, 'redirect' => $checkoutUrl]);
    }

    private function extrairErroAsaas(array $respostaJson, string $fallback): string
    {
        // Formato Asaas: {"errors": [{"code": "...", "description": "..."}]}
        $errors = $respostaJson['errors'] ?? null;
        if (is_array($errors) && !empty($errors)) {
            $msgs = [];
            foreach ($errors as $err) {
                $desc = trim((string) ($err['description'] ?? ''));
                if ($desc !== '') {
                    $msgs[] = $desc;
                }
            }
            if (!empty($msgs)) {
                return implode(' ', $msgs);
            }
        }

        // Formato alternativo: {"message": "..."}
        $msg = trim((string) ($respostaJson['message'] ?? ''));
        if ($msg !== '') {
            return $msg;
        }

        // Mensagens internas conhecidas
        if (str_contains($fallback, 'Token do Asaas não configurado')) {
            return 'O gateway Asaas não está configurado. Entre em contato com o suporte.';
        }
        if (str_contains($fallback, 'Plano não encontrado')) {
            return 'Plano não encontrado ou inativo.';
        }
        if (str_contains($fallback, 'Cliente não encontrado')) {
            return 'Erro ao localizar sua conta. Tente novamente.';
        }

        return 'Não foi possível criar a assinatura: ' . $fallback;
    }
}
