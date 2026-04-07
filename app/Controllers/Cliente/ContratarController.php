<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;
use LRV\Core\View;

final class ContratarController
{
    public function wizard(Requisicao $req): Resposta
    {
        $planId = (int)($req->query['plan_id'] ?? 0);
        if ($planId <= 0) {
            return Resposta::redirecionar('/');
        }

        $pdo = BancoDeDados::pdo();

        // Plano selecionado — planos privados (client_id) não podem ser contratados pelo wizard público
        try {
            $stmt = $pdo->prepare("SELECT id, name, description, cpu, ram, storage, price_monthly, support_channels, specs_json, is_featured FROM plans WHERE id = :id AND status = 'active' AND client_id IS NULL");
            $stmt->execute([':id' => $planId]);
        } catch (\Throwable) {
            $stmt = $pdo->prepare("SELECT id, name, description, cpu, ram, storage, price_monthly FROM plans WHERE id = :id AND status = 'active' AND client_id IS NULL");
            $stmt->execute([':id' => $planId]);
        }
        $plano = $stmt->fetch();
        if (!is_array($plano)) {
            return Resposta::redirecionar('/');
        }

        // Addons do plano
        $addons = [];
        try {
            $aStmt = $pdo->prepare('SELECT id, name, description, price FROM plan_addons WHERE plan_id = :pid AND active = 1 ORDER BY sort_order');
            $aStmt->execute([':pid' => $planId]);
            $addons = $aStmt->fetchAll() ?: [];
        } catch (\Throwable) {}

        // Plano upsell: próximo plano mais caro
        $upsell = null;
        try {
            $uStmt = $pdo->prepare("SELECT id, name, description, cpu, ram, storage, price_monthly, is_featured FROM plans WHERE status = 'active' AND client_id IS NULL AND price_monthly > :p ORDER BY price_monthly ASC LIMIT 1");
            $uStmt->execute([':p' => (float)$plano['price_monthly']]);
            $upsell = $uStmt->fetch() ?: null;
        } catch (\Throwable) {}

        // Descontos configuráveis
        $desconto6m  = (float) Settings::obter('billing.desconto_6m', 5);
        $desconto12m = (float) Settings::obter('billing.desconto_12m', 10);

        // Addons pré-selecionados da home
        $preSelectedAddons = [];
        $addonsParam = trim((string)($req->query['addons'] ?? ''));
        if ($addonsParam !== '') {
            $preSelectedAddons = array_filter(array_map('intval', explode(',', $addonsParam)));
        }

        $html = View::renderizar(__DIR__ . '/../../Views/contratar/wizard.php', [
            'plano'       => $plano,
            'addons'      => $addons,
            'upsell'      => $upsell,
            'desconto_6m' => $desconto6m,
            'desconto_12m'=> $desconto12m,
            'pre_addons'  => $preSelectedAddons,
        ]);

        return Resposta::html($html);
    }

    public function finalizar(Requisicao $req): Resposta
    {
        $in = $req->input();

        $planId     = $in->postInt('plan_id', 1, 2147483647, true);
        $nome       = $in->postString('nome', 190, true);
        $email      = $in->postEmail('email', 190, true);
        $senha      = $in->postStringRaw('senha', 255, true);
        $cpfCnpj    = $in->postString('cpf_cnpj', 20, true);
        $ddi        = $in->postString('ddi', 5, false);
        $celular    = $in->postString('celular', 20, false);
        $gateway    = trim((string)($req->post['gateway'] ?? 'PIX'));
        $periodo    = (int)($req->post['periodo'] ?? 1);
        $quantidade = max(1, (int)($req->post['quantidade'] ?? 1));
        $addonsIds  = trim((string)($req->post['addons_ids'] ?? ''));
        $country    = trim((string)($req->post['country'] ?? ''));
        $prefLang   = trim((string)($req->post['preferred_lang'] ?? ''));
        $currency   = in_array((string)($req->post['currency'] ?? 'BRL'), ['BRL', 'USD']) ? (string)$req->post['currency'] : 'BRL';

        if ($in->temErros() || $nome === '' || $email === '' || $senha === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Preencha todos os campos obrigatórios.'], 422);
        }

        // CPF/CNPJ obrigatório apenas para BRL
        if ($currency === 'BRL' && $cpfCnpj === '') {
            return Resposta::json(['ok' => false, 'erro' => 'CPF/CNPJ é obrigatório para pagamento em Real.'], 422);
        }

        // USD: forçar Stripe
        if ($currency === 'USD') {
            $gateway = 'stripe';
        }

        if (strlen($senha) < 8) {
            return Resposta::json(['ok' => false, 'erro' => 'Senha mínima: 8 caracteres.'], 422);
        }

        $pdo = BancoDeDados::pdo();

        // Verificar se email já existe
        $check = $pdo->prepare('SELECT id FROM clients WHERE email = :e LIMIT 1');
        $check->execute([':e' => $email]);
        if ($check->fetch()) {
            return Resposta::json(['ok' => false, 'erro' => 'Este e-mail já está cadastrado. Faça login para contratar.'], 422);
        }

        // Criar conta
        $hash  = password_hash($senha, PASSWORD_BCRYPT);
        $agora = date('Y-m-d H:i:s');
        $mobilePhone = '';
        if ($celular !== '') {
            $ddi = $ddi !== '' ? $ddi : '+55';
            $mobilePhone = $ddi . preg_replace('/\D/', '', $celular);
        }

        try {
            $ins = $pdo->prepare('INSERT INTO clients (name, email, cpf_cnpj, mobile_phone, country, preferred_lang, password, created_at) VALUES (:n, :e, :cpf, :mph, :co, :pl, :p, :c)');
            $ins->execute([
                ':n'   => $nome,
                ':e'   => $email,
                ':cpf' => preg_replace('/\D/', '', $cpfCnpj),
                ':mph' => $mobilePhone !== '' ? $mobilePhone : null,
                ':co'  => $country !== '' ? strtoupper(substr($country, 0, 2)) : null,
                ':pl'  => in_array($prefLang, ['pt-BR', 'en-US', 'es-ES'], true) ? $prefLang : null,
                ':p'   => $hash,
                ':c'   => $agora,
            ]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => 'Não foi possível criar a conta. Verifique os dados.'], 400);
        }

        $clienteId = (int)$pdo->lastInsertId();

        // Login automático
        \LRV\Core\Auth::entrarCliente($email, $senha);

        // Criar trial se ativo
        try {
            if ((int) Settings::obter('trial.enabled', 0) === 1) {
                $dias    = max(1, (int) Settings::obter('trial.dias', 7));
                $vcpu    = max(1, (int) Settings::obter('trial.vcpu', 1));
                $ramMb   = max(128, (int) Settings::obter('trial.ram_mb', 1024));
                $discoGb = max(1, (int) Settings::obter('trial.disco_gb', 20));
                $expires = date('Y-m-d H:i:s', strtotime("+{$dias} days"));
                $pdo->prepare('INSERT IGNORE INTO client_trials (client_id, expires_at, vcpu, ram_mb, disco_gb, status) VALUES (:cid, :exp, :vcpu, :ram, :disco, \'active\')')
                    ->execute([':cid' => $clienteId, ':exp' => $expires, ':vcpu' => $vcpu, ':ram' => $ramMb, ':disco' => $discoGb]);
            }
        } catch (\Throwable) {}

        // Calcular addons
        $addonsSelecionados = [];
        if ($addonsIds !== '') {
            $ids = array_filter(array_map('intval', explode(',', $addonsIds)));
            if (!empty($ids)) {
                $ph = implode(',', array_fill(0, count($ids), '?'));
                $st = $pdo->prepare("SELECT id, name, price FROM plan_addons WHERE id IN ({$ph}) AND plan_id = ? AND active = 1");
                $st->execute(array_merge($ids, [$planId]));
                foreach (($st->fetchAll() ?: []) as $r) {
                    $addonsSelecionados[] = ['id' => (int)$r['id'], 'name' => (string)$r['name'], 'price' => (float)$r['price']];
                }
            }
        }

        // Dados do cartão (se CREDIT_CARD)
        $ccNome    = trim((string)($req->post['cc_nome'] ?? ''));
        $ccNumero  = trim((string)($req->post['cc_numero'] ?? ''));
        $ccValidade= trim((string)($req->post['cc_validade'] ?? ''));
        $ccCvv     = trim((string)($req->post['cc_cvv'] ?? ''));

        // Criar assinaturas (uma por servidor)
        $isBrl = \LRV\Core\I18n::moedaCodigo() === 'BRL';
        $redirectUrl = '/cliente/painel';

        for ($i = 0; $i < $quantidade; $i++) {
            try {
                if ($isBrl) {
                    $billingType = match ($gateway) {
                        'BOLETO' => 'BOLETO',
                        'CREDIT_CARD' => 'CREDIT_CARD',
                        default => 'PIX',
                    };
                    $asaasApi = new \LRV\App\Services\Billing\Asaas\AsaasApi(new \LRV\App\Services\Http\ClienteHttp());
                    $service = new \LRV\App\Services\Billing\AssinaturasService($asaasApi);
                    $resultado = $service->criarAssinaturaDoPlano($clienteId, $planId, $billingType, $addonsSelecionados);
                    $localSubId = (int)($resultado['local_subscription_id'] ?? 0);

                    // Pagar com cartão inline se CREDIT_CARD
                    if ($gateway === 'CREDIT_CARD' && $ccNumero !== '') {
                        $cobrancas = $resultado['cobrancas'] ?? [];
                        $payments = $cobrancas['data'] ?? [];
                        $firstPaymentId = '';
                        if (is_array($payments)) {
                            foreach ($payments as $pay) {
                                if (is_array($pay) && isset($pay['id'])) {
                                    $firstPaymentId = (string)$pay['id'];
                                    break;
                                }
                            }
                        }
                        if ($firstPaymentId !== '') {
                            $valParts = explode('/', $ccValidade);
                            $ccMes = (string)($valParts[0] ?? '');
                            $ccAno = (string)($valParts[1] ?? '');
                            if (strlen($ccAno) === 2) $ccAno = '20' . $ccAno;

                            try {
                                $asaasApi->pagarComCartao($firstPaymentId, [
                                    'holderName' => $ccNome,
                                    'number' => $ccNumero,
                                    'expiryMonth' => $ccMes,
                                    'expiryYear' => $ccAno,
                                    'ccv' => $ccCvv,
                                ], [
                                    'name' => $nome,
                                    'email' => $email,
                                    'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
                                    'phone' => $mobilePhone !== '' ? $mobilePhone : null,
                                ]);
                            } catch (\Throwable $cardErr) {
                                if ($i === 0) {
                                    return Resposta::json(['ok' => false, 'erro' => 'Assinatura criada, mas erro no pagamento: ' . $cardErr->getMessage()], 400);
                                }
                            }
                        }
                        if ($i === 0) {
                            // Cartão pago inline — enfileirar provisionamento direto
                            $vpsIdLocal = (int)($resultado['local_subscription_id'] ?? 0);
                            try {
                                $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE client_id = :c AND status = 'pending_payment' ORDER BY id DESC LIMIT 1")
                                    ->execute([':c' => $clienteId]);
                                $vpsRow = $pdo->prepare("SELECT id FROM vps WHERE client_id = :c ORDER BY id DESC LIMIT 1");
                                $vpsRow->execute([':c' => $clienteId]);
                                $vr = $vpsRow->fetch();
                                if (is_array($vr)) {
                                    (new \LRV\Core\Jobs\RepositorioJobs())->criar('provisionar_vps', ['vps_id' => (int)$vr['id']]);
                                }
                            } catch (\Throwable) {}
                            $redirectUrl = '/cliente/assinaturas';
                        }
                    } elseif ($gateway === 'PIX' && $i === 0) {
                        // Buscar QR code PIX inline
                        $cobrancas = $resultado['cobrancas'] ?? [];
                        $payments = $cobrancas['data'] ?? [];
                        $firstPaymentId = '';
                        if (is_array($payments)) {
                            foreach ($payments as $pay) {
                                if (is_array($pay) && isset($pay['id'])) {
                                    $firstPaymentId = (string)$pay['id'];
                                    break;
                                }
                            }
                        }
                        if ($firstPaymentId !== '') {
                            try {
                                $pixData = $asaasApi->buscarPixQrCode($firstPaymentId);
                                $pixPayload = (string)($pixData['payload'] ?? '');
                                $pixImage = (string)($pixData['encodedImage'] ?? '');
                                if ($pixPayload !== '') {
                                    return Resposta::json([
                                        'ok' => true,
                                        'payment_type' => 'pix',
                                        'pix_payload' => $pixPayload,
                                        'pix_image' => $pixImage,
                                        'sub_id' => $localSubId,
                                        'redirect' => '/cliente/assinaturas',
                                    ]);
                                }
                            } catch (\Throwable) {}
                        }
                        $redirectUrl = '/cliente/pagamento?sub=' . $localSubId;
                    } elseif ($gateway === 'BOLETO' && $i === 0) {
                        // Buscar linha digitável inline
                        $cobrancas = $resultado['cobrancas'] ?? [];
                        $payments = $cobrancas['data'] ?? [];
                        $firstPayment = null;
                        if (is_array($payments)) {
                            foreach ($payments as $pay) {
                                if (is_array($pay) && isset($pay['id'])) {
                                    $firstPayment = $pay;
                                    break;
                                }
                            }
                        }
                        if ($firstPayment) {
                            try {
                                $boletoData = $asaasApi->buscarLinhaDigitavel((string)$firstPayment['id']);
                                $linhaDigitavel = (string)($boletoData['identificationField'] ?? '');
                                $bankSlipUrl = (string)($firstPayment['bankSlipUrl'] ?? '');
                                if ($linhaDigitavel !== '') {
                                    return Resposta::json([
                                        'ok' => true,
                                        'payment_type' => 'boleto',
                                        'boleto_linha' => $linhaDigitavel,
                                        'boleto_url' => $bankSlipUrl,
                                        'redirect' => '/cliente/assinaturas',
                                    ]);
                                }
                            } catch (\Throwable) {}
                        }
                        $redirectUrl = '/cliente/pagamento?sub=' . $localSubId;
                    } elseif ($i === 0 && $localSubId > 0) {
                        $redirectUrl = '/cliente/pagamento?sub=' . $localSubId;
                    }
                } else {
                    $service = new \LRV\App\Services\Billing\Stripe\StripeCheckoutService();
                    $resultado = $service->criarCheckoutAssinaturaDoPlano($clienteId, $planId, $addonsSelecionados);
                    $checkoutUrl = is_array($resultado) ? (string)($resultado['checkout_url'] ?? '') : '';
                    if ($i === 0 && $checkoutUrl !== '') {
                        $redirectUrl = $checkoutUrl;
                    }
                }
            } catch (\Throwable $e) {
                if ($i === 0) {
                    return Resposta::json(['ok' => false, 'erro' => 'Conta criada, mas erro ao criar assinatura: ' . $e->getMessage()], 400);
                }
            }
        }

        // Enviar e-mail de boas-vindas (traduzido)
        try {
            // Usar idioma preferido do cliente para o e-mail
            $emailLang = in_array($prefLang, ['pt-BR', 'en-US', 'es-ES'], true) ? $prefLang : \LRV\Core\I18n::idioma();
            $origLang = \LRV\Core\I18n::idioma();
            \LRV\Core\I18n::definirIdioma($emailLang);

            $mailer = new \LRV\App\Services\Email\SmtpMailer();
            $appUrl = \LRV\Core\ConfiguracoesSistema::appUrlBase();
            $assunto = \LRV\Core\I18n::t('email.bemvindo_assunto');
            $corpo = '<p style="margin:0 0 12px;">' . htmlspecialchars(\LRV\Core\I18n::tf('email.bemvindo_corpo', $nome), ENT_QUOTES, 'UTF-8') . '</p>'
                   . '<p style="margin:0 0 6px;"><strong>' . htmlspecialchars(\LRV\Core\I18n::t('email.seu_login'), ENT_QUOTES, 'UTF-8') . '</strong></p>'
                   . '<p style="margin:0 0 4px;">E-mail: <code>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</code></p>'
                   . '<p style="margin:0 0 12px;">' . htmlspecialchars(\LRV\Core\I18n::t('email.senha_definida_cadastro'), ENT_QUOTES, 'UTF-8') . '</p>';
            $html = \LRV\App\Services\Email\EmailTemplate::renderizar(
                $assunto,
                $corpo,
                \LRV\Core\I18n::t('email.bemvindo_btn'),
                $appUrl . '/cliente/entrar',
            );
            $mailer->enviar($email, $assunto, $html, true);

            // E-mail de pagamento pendente (se não pagou com cartão inline)
            if ($gateway !== 'CREDIT_CARD') {
                $assuntoPag = \LRV\Core\I18n::t('email.pag_pendente_assunto');
                $corpoPag = '<p style="margin:0 0 12px;">' . htmlspecialchars(\LRV\Core\I18n::t('email.pag_pendente_corpo'), ENT_QUOTES, 'UTF-8') . '</p>';
                $htmlPag = \LRV\App\Services\Email\EmailTemplate::renderizar(
                    $assuntoPag,
                    $corpoPag,
                    \LRV\Core\I18n::t('email.ver_vps_btn'),
                    $appUrl . '/cliente/vps',
                );
                $mailer->enviar($email, $assuntoPag, $htmlPag, true);
            }

            \LRV\Core\I18n::definirIdioma($origLang);
        } catch (\Throwable) {}

        return Resposta::json(['ok' => true, 'redirect' => $redirectUrl]);
    }
}
