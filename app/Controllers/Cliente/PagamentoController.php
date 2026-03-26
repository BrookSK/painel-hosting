<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Billing\Asaas\AsaasApi;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PagamentoController
{
    public function aguardando(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $in = $req->input();
        $subId = $in->queryInt('sub', 1, 2147483647, true);
        if ($subId <= 0) {
            return Resposta::redirecionar('/cliente/assinaturas');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT s.id, s.asaas_subscription_id, s.status, s.next_due_date,
                    p.name AS plan_name, p.price_monthly
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.id = :id AND s.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $subId, ':c' => $clienteId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return Resposta::redirecionar('/cliente/assinaturas');
        }

        $asaasSubId = (string) ($sub['asaas_subscription_id'] ?? '');
        $cobranca = null;
        $pixData = null;
        $boletoData = null;
        $billingType = '';

        if ($asaasSubId !== '') {
            $api = new AsaasApi(new ClienteHttp());

            try {
                $cobrancas = $api->listarCobrancasDaAssinatura($asaasSubId);
                $lista = $cobrancas['data'] ?? [];

                // Pegar a cobrança pendente mais recente
                foreach (($lista ?: []) as $c) {
                    $st = strtoupper((string) ($c['status'] ?? ''));
                    if (in_array($st, ['PENDING', 'AWAITING_RISK_ANALYSIS'], true)) {
                        $cobranca = $c;
                        break;
                    }
                }

                // Se não tem pendente, pegar a primeira (pode já estar paga)
                if ($cobranca === null && !empty($lista)) {
                    $cobranca = $lista[0];
                }
            } catch (\Throwable) {}

            if (is_array($cobranca)) {
                $billingType = strtoupper((string) ($cobranca['billingType'] ?? ''));
                $paymentId = (string) ($cobranca['id'] ?? '');
                $paymentStatus = strtoupper((string) ($cobranca['status'] ?? ''));

                if ($paymentId !== '' && in_array($paymentStatus, ['PENDING', 'AWAITING_RISK_ANALYSIS'], true)) {
                    if ($billingType === 'PIX') {
                        try {
                            $pixData = $api->buscarPixQrCode($paymentId);
                        } catch (\Throwable) {}
                    } elseif ($billingType === 'BOLETO') {
                        try {
                            $boletoData = $api->buscarLinhaDigitavel($paymentId);
                            $boletoData['bankSlipUrl'] = (string) ($cobranca['bankSlipUrl'] ?? '');
                        } catch (\Throwable) {}
                    }
                }
            }
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/pagamento-aguardando.php', [
            'sub' => $sub,
            'cobranca' => $cobranca,
            'pixData' => $pixData,
            'boletoData' => $boletoData,
            'billingType' => $billingType,
        ]);

        return Resposta::html($html);
    }

    public function statusApi(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'auth'], 401);
        }

        $in = $req->input();
        $subId = $in->queryInt('sub', 1, 2147483647, true);
        if ($subId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'invalid'], 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, asaas_subscription_id, status FROM subscriptions WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subId, ':c' => $clienteId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return Resposta::json(['ok' => false, 'erro' => 'not_found'], 404);
        }

        $asaasSubId = (string) ($sub['asaas_subscription_id'] ?? '');
        $subStatus = strtoupper((string) ($sub['status'] ?? ''));
        $paymentStatus = '';
        $pixData = null;

        if ($asaasSubId !== '') {
            $api = new AsaasApi(new ClienteHttp());
            try {
                $cobrancas = $api->listarCobrancasDaAssinatura($asaasSubId);
                $lista = $cobrancas['data'] ?? [];
                foreach (($lista ?: []) as $c) {
                    $st = strtoupper((string) ($c['status'] ?? ''));
                    if (in_array($st, ['PENDING', 'AWAITING_RISK_ANALYSIS', 'CONFIRMED', 'RECEIVED'], true)) {
                        $paymentStatus = $st;
                        $paymentId = (string) ($c['id'] ?? '');
                        $billingType = strtoupper((string) ($c['billingType'] ?? ''));

                        // Se PIX expirou, buscar novo QR code
                        if ($billingType === 'PIX' && $st === 'PENDING' && $paymentId !== '') {
                            try {
                                $pixData = $api->buscarPixQrCode($paymentId);
                            } catch (\Throwable) {}
                        }
                        break;
                    }
                }
                // Se não achou pendente/confirmado, checar se tem algum pago
                if ($paymentStatus === '') {
                    foreach (($lista ?: []) as $c) {
                        $st = strtoupper((string) ($c['status'] ?? ''));
                        if (in_array($st, ['CONFIRMED', 'RECEIVED'], true)) {
                            $paymentStatus = $st;
                            break;
                        }
                    }
                }
            } catch (\Throwable) {}
        }

        $pago = in_array($paymentStatus, ['CONFIRMED', 'RECEIVED'], true) || $subStatus === 'ACTIVE';

        return Resposta::json([
            'ok' => true,
            'pago' => $pago,
            'sub_status' => $subStatus,
            'payment_status' => $paymentStatus,
            'pix' => $pixData,
        ]);
    }

    public function pagarCartao(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'auth'], 401);
        }

        $in = $req->input();
        $subId = $in->postInt('sub_id', 1, 2147483647, true);
        if ($subId <= 0 || $in->temErros()) {
            return Resposta::json(['ok' => false, 'erro' => 'Dados inválidos.'], 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, asaas_subscription_id FROM subscriptions WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subId, ':c' => $clienteId]);
        $sub = $stmt->fetch();

        if (!is_array($sub)) {
            return Resposta::json(['ok' => false, 'erro' => 'Assinatura não encontrada.'], 404);
        }

        $asaasSubId = (string) ($sub['asaas_subscription_id'] ?? '');
        if ($asaasSubId === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Assinatura sem vínculo com gateway.'], 400);
        }

        // Dados do cartão
        $holderName = trim((string) ($req->post['holder_name'] ?? ''));
        $number = preg_replace('/\D/', '', (string) ($req->post['number'] ?? ''));
        $expMonth = trim((string) ($req->post['exp_month'] ?? ''));
        $expYear = trim((string) ($req->post['exp_year'] ?? ''));
        $ccv = trim((string) ($req->post['ccv'] ?? ''));

        // Dados do titular
        $holderCpf = preg_replace('/\D/', '', (string) ($req->post['holder_cpf'] ?? ''));
        $holderEmail = trim((string) ($req->post['holder_email'] ?? ''));
        $holderPhone = preg_replace('/\D/', '', (string) ($req->post['holder_phone'] ?? ''));
        $holderCep = preg_replace('/\D/', '', (string) ($req->post['holder_cep'] ?? ''));
        $holderNumber = trim((string) ($req->post['holder_address_number'] ?? ''));

        if ($holderName === '' || $number === '' || $expMonth === '' || $expYear === '' || $ccv === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Preencha todos os dados do cartão.'], 400);
        }
        if ($holderCpf === '' || $holderEmail === '' || $holderPhone === '' || $holderCep === '' || $holderNumber === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Preencha todos os dados do titular.'], 400);
        }

        $api = new AsaasApi(new ClienteHttp());

        // Buscar cobrança pendente
        try {
            $cobrancas = $api->listarCobrancasDaAssinatura($asaasSubId);
            $lista = $cobrancas['data'] ?? [];
            $paymentId = '';
            foreach (($lista ?: []) as $c) {
                $st = strtoupper((string) ($c['status'] ?? ''));
                if (in_array($st, ['PENDING', 'AWAITING_RISK_ANALYSIS'], true)) {
                    $paymentId = (string) ($c['id'] ?? '');
                    break;
                }
            }
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => 'Erro ao buscar cobrança.'], 500);
        }

        if ($paymentId === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Nenhuma cobrança pendente encontrada.'], 400);
        }

        try {
            $resultado = $api->pagarComCartao($paymentId, [
                'holderName' => $holderName,
                'number' => $number,
                'expiryMonth' => $expMonth,
                'expiryYear' => $expYear,
                'ccv' => $ccv,
            ], [
                'name' => $holderName,
                'email' => $holderEmail,
                'cpfCnpj' => $holderCpf,
                'phone' => $holderPhone,
                'postalCode' => $holderCep,
                'addressNumber' => $holderNumber,
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            return Resposta::json(['ok' => false, 'erro' => 'Falha no pagamento: ' . $msg], 400);
        }

        $status = strtoupper((string) ($resultado['status'] ?? ''));
        $pago = in_array($status, ['CONFIRMED', 'RECEIVED'], true);

        return Resposta::json([
            'ok' => true,
            'pago' => $pago,
            'status' => $status,
        ]);
    }
}
