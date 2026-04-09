<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class FaturasController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $cStmt = $pdo->prepare('SELECT name, email, stripe_customer_id, asaas_customer_id FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        $faturas = [];

        // Buscar invoices e pagamentos do Stripe
        $stripeCustomerId = trim((string)($cliente['stripe_customer_id'] ?? ''));
        $stripeKey = ConfiguracoesSistema::stripeSecretKey();
        if ($stripeCustomerId !== '' && $stripeKey !== '') {
            try {
                $stripe = new \Stripe\StripeClient($stripeKey);

                // 1. Invoices (de subscriptions)
                $invoices = $stripe->invoices->all(['customer' => $stripeCustomerId, 'limit' => 50]);
                foreach (($invoices->data ?? []) as $inv) {
                    $faturas[] = [
                        'id' => (string)($inv->id ?? ''),
                        'gateway' => 'Stripe',
                        'plano' => (string)($inv->lines->data[0]->description ?? 'Plano'),
                        'valor' => number_format(((int)($inv->amount_paid ?? 0)) / 100, 2, '.', ','),
                        'moeda' => strtoupper((string)($inv->currency ?? 'usd')),
                        'status' => (string)($inv->status ?? ''),
                        'data' => date('Y-m-d', (int)($inv->created ?? time())),
                        'pdf_url' => (string)($inv->invoice_pdf ?? ''),
                        'hosted_url' => (string)($inv->hosted_invoice_url ?? ''),
                    ];
                }

                // 2. Checkout Sessions pagas (pagamentos únicos)
                $sessions = $stripe->checkout->sessions->all(['customer' => $stripeCustomerId, 'limit' => 50]);
                foreach (($sessions->data ?? []) as $sess) {
                    $sessStatus = (string)($sess->payment_status ?? '');
                    $sessMode = (string)($sess->mode ?? '');
                    if ($sessMode !== 'payment' || $sessStatus !== 'paid') continue;
                    $sessId = (string)($sess->id ?? '');
                    $amountTotal = (int)($sess->amount_total ?? 0);
                    // Buscar receipt_url do payment_intent
                    $receiptUrl = '';
                    $piId = (string)($sess->payment_intent ?? '');
                    if ($piId !== '') {
                        try {
                            $pi = $stripe->paymentIntents->retrieve($piId, ['expand' => ['latest_charge']]);
                            $receiptUrl = (string)($pi->latest_charge->receipt_url ?? '');
                        } catch (\Throwable) {}
                    }
                    $faturas[] = [
                        'id' => $sessId,
                        'gateway' => 'Stripe',
                        'plano' => 'Pagamento único',
                        'valor' => number_format($amountTotal / 100, 2, '.', ','),
                        'moeda' => strtoupper((string)($sess->currency ?? 'usd')),
                        'status' => 'paid',
                        'data' => date('Y-m-d', (int)($sess->created ?? time())),
                        'pdf_url' => $receiptUrl,
                        'hosted_url' => $receiptUrl,
                    ];
                }

                // 3. Charges (todos os pagamentos)
                $charges = $stripe->charges->all(['customer' => $stripeCustomerId, 'limit' => 50]);
                $invoiceIds = array_column($faturas, 'id');
                foreach (($charges->data ?? []) as $ch) {
                    $chId = (string)($ch->id ?? '');
                    $chInvoice = (string)($ch->invoice ?? '');
                    // Pular se já tem via invoice
                    if ($chInvoice !== '' && in_array($chInvoice, $invoiceIds, true)) continue;
                    $chStatus = (string)($ch->status ?? '');
                    if ($chStatus !== 'succeeded') continue;
                    $faturas[] = [
                        'id' => $chId,
                        'gateway' => 'Stripe',
                        'plano' => (string)($ch->description ?? 'Pagamento'),
                        'valor' => number_format(((int)($ch->amount ?? 0)) / 100, 2, '.', ','),
                        'moeda' => strtoupper((string)($ch->currency ?? 'usd')),
                        'status' => 'paid',
                        'data' => date('Y-m-d', (int)($ch->created ?? time())),
                        'pdf_url' => (string)($ch->receipt_url ?? ''),
                        'hosted_url' => (string)($ch->receipt_url ?? ''),
                    ];
                }
            } catch (\Throwable) {}
        }

        // Buscar cobranças do Asaas
        $asaasCustomerId = trim((string)($cliente['asaas_customer_id'] ?? ''));
        if ($asaasCustomerId !== '') {
            try {
                $api = new \LRV\App\Services\Billing\Asaas\AsaasApi(new \LRV\App\Services\Http\ClienteHttp());
                // Buscar assinaturas do cliente
                $subs = $pdo->prepare('SELECT asaas_subscription_id, plan_id FROM subscriptions WHERE client_id = :c AND asaas_subscription_id IS NOT NULL');
                $subs->execute([':c' => $clienteId]);
                $subRows = $subs->fetchAll() ?: [];

                foreach ($subRows as $sr) {
                    $asaasSubId = trim((string)($sr['asaas_subscription_id'] ?? ''));
                    if ($asaasSubId === '') continue;
                    try {
                        $cobrancas = $api->listarCobrancasDaAssinatura($asaasSubId);
                        foreach (($cobrancas['data'] ?? []) as $c) {
                            $st = strtoupper((string)($c['status'] ?? ''));
                            if (!in_array($st, ['CONFIRMED', 'RECEIVED', 'PENDING', 'OVERDUE'], true)) continue;
                            $faturas[] = [
                                'id' => (string)($c['id'] ?? ''),
                                'gateway' => 'Asaas',
                                'plano' => (string)($c['description'] ?? 'Assinatura'),
                                'valor' => number_format((float)($c['value'] ?? 0), 2, ',', '.'),
                                'moeda' => 'BRL',
                                'status' => $st,
                                'data' => (string)($c['dateCreated'] ?? ($c['dueDate'] ?? '')),
                                'pdf_url' => (string)($c['invoiceUrl'] ?? ''),
                                'hosted_url' => (string)($c['invoiceUrl'] ?? ''),
                            ];
                        }
                    } catch (\Throwable) {}
                }
            } catch (\Throwable) {}
        }

        // Ordenar por data desc e remover duplicatas
        $seen = [];
        $faturas = array_filter($faturas, function($f) use (&$seen) {
            $key = ($f['gateway'] ?? '') . '_' . ($f['valor'] ?? '') . '_' . ($f['data'] ?? '');
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        });
        usort($faturas, fn($a, $b) => strcmp((string)($b['data'] ?? ''), (string)($a['data'] ?? '')));

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/faturas.php', [
            'faturas' => $faturas,
            'cliente' => $cliente,
        ]));
    }
}
