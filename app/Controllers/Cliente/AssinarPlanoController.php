<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Billing\AssinaturasService;
use LRV\App\Services\Billing\Asaas\AsaasApi;
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
        $billingType = (string) ($req->post['billing_type'] ?? 'PIX');
        if (!in_array($billingType, ['PIX', 'BOLETO', 'CREDIT_CARD'], true)) {
            $billingType = 'PIX';
        }

        if ($planId <= 0) {
            return Resposta::texto('Plano inválido.', 400);
        }

        $service = new AssinaturasService(new AsaasApi(new ClienteHttp()));

        try {
            $resultado = $service->criarAssinaturaDoPlano($clienteId, $planId, $billingType);
        } catch (\Throwable $e) {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-criada.php', [
                'erro' => 'Não foi possível criar a assinatura. Verifique as configurações do Asaas e tente novamente.',
                'resultado' => null,
            ]);
            return Resposta::html($html, 400);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/assinatura-criada.php', [
            'erro' => '',
            'resultado' => $resultado,
        ]);

        return Resposta::html($html);
    }
}
