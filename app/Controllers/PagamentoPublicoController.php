<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\App\Services\Billing\AssinaturasService;
use LRV\App\Services\Billing\Asaas\AsaasApi;
use LRV\App\Services\Billing\PaymentLinkService;
use LRV\App\Services\Billing\Stripe\StripeCheckoutService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

/**
 * Checkout público por link, SEM login. O admin gera o link (PaymentLinkService),
 * o cliente abre /pagar/{token} e paga. A identidade vem do token, não da sessão.
 * Reaproveita AssinaturasService (Asaas BRL) e StripeCheckoutService (USD).
 * A confirmação do pagamento e o provisionamento seguem pelos webhooks existentes.
 */
final class PagamentoPublicoController
{
    /**
     * GET /pagar/{token}
     * Mostra a tela de pagamento. Se ainda não há cobrança criada, mostra a
     * escolha de forma de pagamento; se já há (Asaas), mostra PIX/boleto/cartão.
     */
    public function mostrar(Requisicao $req): Resposta
    {
        $token = (string) ($req->params['token'] ?? '');
        $service = new PaymentLinkService();
        $link = $service->resolverPorToken($token);

        if ($link === null) {
            return $this->renderErro('Link inválido', 'Este link de pagamento não existe ou foi digitado errado.');
        }

        $status = (string) ($link['status'] ?? 'pending');
        if ($status === 'expired') {
            return $this->renderErro('Link expirado', 'Este link de pagamento expirou. Solicite um novo ao suporte.');
        }

        // Já pago?
        if ($status === 'paid') {
            return $this->renderConfirmado($link);
        }

        $currency = strtoupper((string) ($link['currency'] ?? 'BRL'));
        $subscriptionId = (int) ($link['subscription_id'] ?? 0);

        // Já tem assinatura (Asaas) → carregar cobrança e mostrar PIX/boleto/cartão
        if ($subscriptionId > 0) {
            return $this->renderPagamentoAsaas($token, $link, $subscriptionId);
        }

        // Em processamento sem assinatura ainda (ex.: Stripe redirect em andamento):
        // mostrar a escolha novamente — o claim é liberado em caso de falha.
        return $this->renderEscolha($token, $link, $currency);
    }

    /**
     * POST /pagar/{token}/iniciar
     * Cria a assinatura/cobrança no gateway e vincula ao link.
     * Body: metodo (PIX|BOLETO|CREDIT_CARD para BRL; para USD vai direto ao Stripe).
     */
    public function iniciar(Requisicao $req): Resposta
    {
        $token = (string) ($req->params['token'] ?? '');
        $service = new PaymentLinkService();
        $link = $service->resolverPorToken($token);

        if ($link === null) {
            return $this->renderErro('Link indisponível', 'Este link de pagamento não está mais disponível.');
        }

        // Se já iniciou (ou está sendo processado), volta para a tela de pagamento
        if ((int) ($link['subscription_id'] ?? 0) > 0 || (string) ($link['status'] ?? '') === 'processing') {
            return Resposta::redirecionar('/pagar/' . $token);
        }

        if ((string) ($link['status'] ?? '') !== 'pending') {
            return $this->renderErro('Link indisponível', 'Este link de pagamento não está mais disponível.');
        }

        $linkId = (int) $link['id'];

        // Reivindicação atômica: impede que dois cliques/abas criem cobranças duplicadas.
        if (!$service->reivindicarParaProcessamento($linkId)) {
            // Outra requisição já pegou — redireciona para a tela de pagamento.
            return Resposta::redirecionar('/pagar/' . $token);
        }
        $clienteId = (int) $link['client_id'];
        $planoId = (int) $link['plan_id'];
        $periodo = (int) ($link['periodo'] ?? 1);
        $currency = strtoupper((string) ($link['currency'] ?? 'BRL'));
        $addons = [];
        if (!empty($link['addons_json'])) {
            $addons = is_string($link['addons_json']) ? (json_decode($link['addons_json'], true) ?: []) : (array) $link['addons_json'];
        }

        // CPF/CNPJ: o Asaas exige para emitir cobrança. Aceita CPF ou CNPJ, com
        // validação de dígito verificador. Atualiza o cliente (sobrescreve) se válido.
        $cpfEnviado = \LRV\Core\Documento::normalizar((string) ($req->post['cpf_cnpj'] ?? ''));
        if ($cpfEnviado !== '' && \LRV\Core\Documento::valido($cpfEnviado)) {
            BancoDeDados::pdo()
                ->prepare('UPDATE clients SET cpf_cnpj = :c WHERE id = :id')
                ->execute([':c' => $cpfEnviado, ':id' => $clienteId]);
        }

        // Para BRL (Asaas), CPF/CNPJ é obrigatório e deve ser válido.
        if ($currency !== 'USD') {
            $docAtual = $cpfEnviado;
            if ($docAtual === '') {
                $st = BancoDeDados::pdo()->prepare('SELECT cpf_cnpj FROM clients WHERE id = :id');
                $st->execute([':id' => $clienteId]);
                $docAtual = \LRV\Core\Documento::normalizar((string) ($st->fetch()['cpf_cnpj'] ?? ''));
            }
            if (!\LRV\Core\Documento::valido($docAtual)) {
                $service->liberarReivindicacao($linkId);
                return $this->renderErro('CPF/CNPJ necessário', 'Para gerar a cobrança, informe um CPF ou CNPJ válido na tela anterior.');
            }
        }

        // USD → Stripe Checkout (redirect externo)
        if ($currency === 'USD') {
            try {
                $resultado = (new StripeCheckoutService())->criarCheckoutAssinaturaDoPlano($clienteId, $planoId, $addons, $periodo);
            } catch (\Throwable $e) {
                $service->liberarReivindicacao($linkId);
                return $this->renderErro('Erro no pagamento', 'Não foi possível iniciar o pagamento: ' . $e->getMessage());
            }
            $subId = (int) ($resultado['subscription_id'] ?? 0);
            if ($subId > 0) {
                $service->vincularAssinatura($linkId, $subId);
            }
            $checkoutUrl = (string) ($resultado['checkout_url'] ?? '');
            if ($checkoutUrl === '') {
                return $this->renderErro('Erro no pagamento', 'Falha ao iniciar o checkout.');
            }
            return Resposta::redirecionar($checkoutUrl);
        }

        // BRL → Asaas (PIX / BOLETO / CREDIT_CARD)
        $metodo = strtoupper(trim((string) ($req->post['metodo'] ?? 'PIX')));
        $billingType = in_array($metodo, ['PIX', 'BOLETO', 'CREDIT_CARD'], true) ? $metodo : 'PIX';

        try {
            $resultado = (new AssinaturasService(new AsaasApi(new ClienteHttp())))
                ->criarAssinaturaDoPlano($clienteId, $planoId, $billingType, $addons, $periodo);
        } catch (\Throwable $e) {
            $service->liberarReivindicacao($linkId);
            $msg = $e instanceof \LRV\App\Services\Billing\Asaas\AsaasExcecao && is_array($e->respostaJson)
                ? $this->extrairErroAsaas($e->respostaJson)
                : $e->getMessage();
            return $this->renderErro('Erro no pagamento', 'Não foi possível iniciar o pagamento: ' . $msg);
        }

        $subId = (int) ($resultado['local_subscription_id'] ?? 0);
        if ($subId > 0) {
            $service->vincularAssinatura($linkId, $subId);
        }

        return Resposta::redirecionar('/pagar/' . $token);
    }

    /**
     * GET /pagar/{token}/status — polling (JSON), sem login.
     */
    public function status(Requisicao $req): Resposta
    {
        $token = (string) ($req->params['token'] ?? '');
        $service = new PaymentLinkService();
        $link = $service->resolverPorToken($token);
        if ($link === null) {
            return Resposta::json(['ok' => false, 'erro' => 'not_found'], 404);
        }

        $subscriptionId = (int) ($link['subscription_id'] ?? 0);
        if ($subscriptionId <= 0) {
            return Resposta::json(['ok' => true, 'pago' => false, 'sub_status' => '', 'payment_status' => '']);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT asaas_subscription_id, status FROM subscriptions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $subscriptionId]);
        $sub = $stmt->fetch();
        if (!is_array($sub)) {
            return Resposta::json(['ok' => false, 'erro' => 'not_found'], 404);
        }

        $subStatus = strtoupper((string) ($sub['status'] ?? ''));
        $asaasSubId = (string) ($sub['asaas_subscription_id'] ?? '');
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
                        if ($billingType === 'PIX' && $st === 'PENDING' && $paymentId !== '') {
                            try {
                                $pixData = $api->buscarPixQrCode($paymentId);
                            } catch (\Throwable) {}
                        }
                        break;
                    }
                }
            } catch (\Throwable) {}
        }

        $pago = in_array($paymentStatus, ['CONFIRMED', 'RECEIVED'], true) || $subStatus === 'ACTIVE';
        if ($pago) {
            $service->marcarPago((int) $link['id']);
        }

        return Resposta::json([
            'ok' => true,
            'pago' => $pago,
            'sub_status' => $subStatus,
            'payment_status' => $paymentStatus,
            'pix' => $pixData,
        ]);
    }

    /**
     * POST /pagar/{token}/cartao — paga com cartão de crédito (Asaas), sem login.
     */
    public function cartao(Requisicao $req): Resposta
    {
        $token = (string) ($req->params['token'] ?? '');
        $service = new PaymentLinkService();
        $link = $service->resolverPorToken($token);
        if ($link === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Link inválido.'], 404);
        }

        $subscriptionId = (int) ($link['subscription_id'] ?? 0);
        if ($subscriptionId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Pagamento não iniciado.'], 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT asaas_subscription_id FROM subscriptions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $subscriptionId]);
        $sub = $stmt->fetch();
        $asaasSubId = is_array($sub) ? (string) ($sub['asaas_subscription_id'] ?? '') : '';
        if ($asaasSubId === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Assinatura sem vínculo com gateway.'], 400);
        }

        $holderName = trim((string) ($req->post['holder_name'] ?? ''));
        $number = preg_replace('/\D/', '', (string) ($req->post['number'] ?? ''));
        $expMonth = trim((string) ($req->post['exp_month'] ?? ''));
        $expYear = trim((string) ($req->post['exp_year'] ?? ''));
        $ccv = trim((string) ($req->post['ccv'] ?? ''));
        $holderCpf = \LRV\Core\Documento::normalizar((string) ($req->post['holder_cpf'] ?? ''));
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
        if (!\LRV\Core\Documento::valido($holderCpf)) {
            return Resposta::json(['ok' => false, 'erro' => 'CPF ou CNPJ do titular inválido.'], 400);
        }

        $api = new AsaasApi(new ClienteHttp());
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
        } catch (\Throwable) {
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
            return Resposta::json(['ok' => false, 'erro' => 'Falha no pagamento: ' . $e->getMessage()], 400);
        }

        $status = strtoupper((string) ($resultado['status'] ?? ''));
        $pago = in_array($status, ['CONFIRMED', 'RECEIVED'], true);
        if ($pago) {
            $service->marcarPago((int) $link['id']);
        }

        return Resposta::json(['ok' => true, 'pago' => $pago, 'status' => $status]);
    }

    // ── render helpers ───────────────────────────────────────────────

    /**
     * Renderiza a view pública com Referrer-Policy: no-referrer para evitar que o
     * token (presente na URL) vaze via header Referer para hosts de terceiros.
     */
    private function render(array $dados, int $status = 200): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/publico/pagar.php', $dados), $status)
            ->comHeaders(['Referrer-Policy' => 'no-referrer']);
    }

    private function renderEscolha(string $token, array $link, string $currency): Resposta
    {
        return $this->render([
            'tela' => 'escolha',
            'token' => $token,
            'link' => $link,
            'currency' => $currency,
            'sub' => null,
            'cobranca' => null,
            'pixData' => null,
            'boletoData' => null,
            'billingType' => '',
            'tituloErro' => '',
            'msgErro' => '',
        ]);
    }

    private function renderPagamentoAsaas(string $token, array $link, int $subscriptionId): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, asaas_subscription_id, status FROM subscriptions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $subscriptionId]);
        $sub = $stmt->fetch() ?: [];
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
                foreach (($lista ?: []) as $c) {
                    $st = strtoupper((string) ($c['status'] ?? ''));
                    if (in_array($st, ['PENDING', 'AWAITING_RISK_ANALYSIS'], true)) {
                        $cobranca = $c;
                        break;
                    }
                }
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

        // Montar o "sub" no formato que a view espera
        $subView = [
            'id' => $subscriptionId,
            'status' => (string) ($sub['status'] ?? ''),
            'plan_name' => (string) ($link['plan_name'] ?? ''),
            'price_monthly' => (float) ($link['price_monthly'] ?? 0),
        ];

        return $this->render([
            'tela' => 'pagamento',
            'token' => $token,
            'link' => $link,
            'currency' => strtoupper((string) ($link['currency'] ?? 'BRL')),
            'sub' => $subView,
            'cobranca' => $cobranca,
            'pixData' => $pixData,
            'boletoData' => $boletoData,
            'billingType' => $billingType,
            'tituloErro' => '',
            'msgErro' => '',
        ]);
    }

    private function renderConfirmado(array $link): Resposta
    {
        return $this->render([
            'tela' => 'confirmado',
            'token' => '',
            'link' => $link,
            'currency' => strtoupper((string) ($link['currency'] ?? 'BRL')),
            'sub' => null,
            'cobranca' => null,
            'pixData' => null,
            'boletoData' => null,
            'billingType' => '',
            'tituloErro' => '',
            'msgErro' => '',
        ]);
    }

    private function renderErro(string $titulo, string $msg): Resposta
    {
        return $this->render([
            'tela' => 'erro',
            'token' => '',
            'link' => [],
            'currency' => 'BRL',
            'sub' => null,
            'cobranca' => null,
            'pixData' => null,
            'boletoData' => null,
            'billingType' => '',
            'tituloErro' => $titulo,
            'msgErro' => $msg,
        ], 404);
    }

    private function extrairErroAsaas(array $respostaJson): string
    {
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
        $msg = trim((string) ($respostaJson['message'] ?? ''));
        return $msg !== '' ? $msg : 'erro desconhecido';
    }
}
