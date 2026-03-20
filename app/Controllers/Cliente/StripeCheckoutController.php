<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\View;

final class StripeCheckoutController
{
    public function sucesso(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $in = $req->input();
        $sessionId = $in->queryString('session_id', 255, false);
        if ($sessionId !== '') {
            $sessionId = $in->queryRegex('session_id', 255, '/^cs_(?:test_|live_)?[A-Za-z0-9_]+$/', false, 'Checkout inválido.');
            if ($sessionId === '') {
                $html = View::renderizar(__DIR__ . '/../../Views/cliente/stripe-sucesso.php', [
                    'erro' => $in->primeiroErro(),
                ]);
                return Resposta::html($html, 400);
            }
        }

        $erro = '';
        $stripeSubId = '';

        if ($sessionId !== '') {
            try {
                $secretKey = ConfiguracoesSistema::stripeSecretKey();
                if ($secretKey !== '') {
                    $stripe = new \Stripe\StripeClient($secretKey);
                    $session = $stripe->checkout->sessions->retrieve($sessionId, []);

                    $stripeSubId = (string) ($session['subscription'] ?? '');
                    if ($stripeSubId !== '') {
                        $pdo = BancoDeDados::pdo();
                        $pdo->beginTransaction();
                        try {
                            $stmt = $pdo->prepare('SELECT id, vps_id, status FROM subscriptions WHERE client_id = :c AND stripe_checkout_session_id = :sid LIMIT 1');
                            $stmt->execute([':c' => $clienteId, ':sid' => $sessionId]);
                            $sub = $stmt->fetch();

                            if (is_array($sub)) {
                                $subId = (int) ($sub['id'] ?? 0);
                                $vpsId = (int) ($sub['vps_id'] ?? 0);
                                $statusAnterior = strtoupper(trim((string) ($sub['status'] ?? '')));

                                $upSub = $pdo->prepare('UPDATE subscriptions SET stripe_subscription_id = :s WHERE id = :id');
                                $upSub->execute([':s' => $stripeSubId, ':id' => $subId]);

                                $paymentStatus = strtolower(trim((string) ($session['payment_status'] ?? '')));
                                if ($paymentStatus === 'paid' && $statusAnterior !== 'ACTIVE') {
                                    $upSt = $pdo->prepare("UPDATE subscriptions SET status = 'ACTIVE' WHERE id = :id");
                                    $upSt->execute([':id' => $subId]);

                                    if ($vpsId > 0) {
                                        $upVps = $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id AND status IN ('pending_payment','suspended_payment')");
                                        $upVps->execute([':id' => $vpsId]);

                                        $repoJobs = new RepositorioJobs();
                                        $repoJobs->criar('alerta_billing', [
                                            'titulo' => 'Pagamento confirmado (Stripe)',
                                            'mensagem' => "Checkout concluído e pago.\n\nCliente: #{$clienteId}\nAssinatura: #{$subId}\nVPS: #{$vpsId}\nCheckout session: {$sessionId}",
                                        ]);
                                        $repoJobs->criar('reativar_vps', ['vps_id' => $vpsId]);
                                        $repoJobs->criar('provisionar_vps', ['vps_id' => $vpsId]);
                                    }
                                }
                            }

                            $pdo->commit();
                        } catch (\Throwable $e) {
                            $pdo->rollBack();
                            throw $e;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $erro = 'Não foi possível validar o checkout.';
            }
        }

        (new AuditLogService())->registrar(
            'client',
            $clienteId,
            'billing.stripe_checkout_return',
            'stripe_checkout',
            null,
            [
                'session_id_set' => $sessionId !== '',
                'stripe_subscription_id_set' => $stripeSubId !== '',
                'ok' => $erro === '',
            ],
            $req,
        );

        // Buscar dados da assinatura para exibir na view de sucesso
        $assinatura = null;
        $vps = null;
        if ($erro === '' && $sessionId !== '') {
            try {
                $pdo = BancoDeDados::pdo();
                $stmt = $pdo->prepare(
                    'SELECT s.id, s.status, s.next_due_date, s.vps_id, p.name AS plan_name, p.price_monthly
                     FROM subscriptions s
                     INNER JOIN plans p ON p.id = s.plan_id
                     WHERE s.client_id = :c AND s.stripe_checkout_session_id = :sid
                     LIMIT 1'
                );
                $stmt->execute([':c' => $clienteId, ':sid' => $sessionId]);
                $assinatura = $stmt->fetch() ?: null;

                if (is_array($assinatura) && (int) ($assinatura['vps_id'] ?? 0) > 0) {
                    $stmtV = $pdo->prepare('SELECT id, label, status FROM vps WHERE id = :id AND client_id = :c LIMIT 1');
                    $stmtV->execute([':id' => (int) $assinatura['vps_id'], ':c' => $clienteId]);
                    $vps = $stmtV->fetch() ?: null;
                }
            } catch (\Throwable $e) {
            }
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/stripe-sucesso.php', [
            'erro'       => $erro,
            'assinatura' => $assinatura,
            'vps'        => $vps,
        ]);

        return Resposta::html($html, $erro === '' ? 200 : 400);
    }

    public function cancelado(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        (new AuditLogService())->registrar(
            'client',
            $clienteId,
            'billing.stripe_checkout_cancel',
            'stripe_checkout',
            null,
            [],
            $req,
        );

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/stripe-cancelado.php', []);
        return Resposta::html($html);
    }
}
