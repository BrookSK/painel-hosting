<?php

declare(strict_types=1);

namespace LRV\App\Services\Billing;

use LRV\App\Services\Email\EmailTemplate;
use LRV\App\Services\Email\SmtpMailer;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

/**
 * Serviço de lembretes de vencimento de assinaturas.
 *
 * Envia notificações por e-mail nos seguintes marcos:
 * - Antes do vencimento: 15, 5, 1, 0 dias
 * - Após o vencimento (atraso): 3, 5, 10 dias
 *
 * Para PIX/BOLETO: gera boleto 15 dias antes.
 * Para CREDIT_CARD: notifica apenas em caso de falha.
 */
final class BillingReminderService
{
    /** Dias antes do vencimento para notificar */
    private const DIAS_ANTES = [15, 5, 1, 0];

    /** Dias após o vencimento (atraso) para notificar */
    private const DIAS_ATRASO = [3, 5, 10];

    public function processar(callable $log): void
    {
        $pdo = BancoDeDados::pdo();
        $hoje = date('Y-m-d');

        // Buscar assinaturas ativas com next_due_date
        $stmt = $pdo->query(
            "SELECT s.id, s.client_id, s.vps_id, s.billing_type, s.next_due_date, s.last_reminder_at, s.status,
                    c.name AS client_name, c.email AS client_email,
                    p.name AS plan_name, p.price_monthly
             FROM subscriptions s
             INNER JOIN clients c ON c.id = s.client_id
             LEFT JOIN plans p ON p.id = s.plan_id
             WHERE s.status IN ('ACTIVE', 'OVERDUE')
               AND s.next_due_date IS NOT NULL
             ORDER BY s.next_due_date ASC
             LIMIT 500"
        );
        $subs = $stmt->fetchAll() ?: [];

        foreach ($subs as $sub) {
            try {
                $this->processarAssinatura($pdo, $sub, $hoje, $log);
            } catch (\Throwable $e) {
                $log('Erro ao processar assinatura #' . ($sub['id'] ?? '?') . ': ' . $e->getMessage());
            }
        }
    }

    private function processarAssinatura(\PDO $pdo, array $sub, string $hoje, callable $log): void
    {
        $subId = (int) ($sub['id'] ?? 0);
        $dueDate = (string) ($sub['next_due_date'] ?? '');
        $billingType = strtoupper(trim((string) ($sub['billing_type'] ?? '')));
        $email = trim((string) ($sub['client_email'] ?? ''));
        $lastReminder = (string) ($sub['last_reminder_at'] ?? '');

        if ($dueDate === '' || $email === '') {
            return;
        }

        $dueDateTs = strtotime($dueDate);
        $hojeTs = strtotime($hoje);
        if ($dueDateTs === false || $hojeTs === false) {
            return;
        }

        $diffDias = (int) round(($dueDateTs - $hojeTs) / 86400);

        // Determinar se devemos enviar notificação
        $marco = null;
        $tipo = null;

        if ($diffDias >= 0) {
            // Antes ou no dia do vencimento
            foreach (self::DIAS_ANTES as $d) {
                if ($diffDias === $d) {
                    $marco = $d;
                    $tipo = 'antes';
                    break;
                }
            }
        } else {
            // Após o vencimento (atraso)
            $diasAtraso = abs($diffDias);
            foreach (self::DIAS_ATRASO as $d) {
                if ($diasAtraso === $d) {
                    $marco = $d;
                    $tipo = 'atraso';
                    break;
                }
            }
        }

        if ($marco === null || $tipo === null) {
            return;
        }

        // Evitar envio duplicado no mesmo dia
        if ($lastReminder !== '' && substr($lastReminder, 0, 10) === $hoje) {
            return;
        }

        // Cartão de crédito: não notificar antes do vencimento (cobrança automática)
        // Só notificar em caso de atraso (falha na cobrança)
        if ($billingType === 'CREDIT_CARD' && $tipo === 'antes') {
            return;
        }

        $nome = trim((string) ($sub['client_name'] ?? ''));
        $plano = trim((string) ($sub['plan_name'] ?? 'Plano'));
        $preco = (float) ($sub['price_monthly'] ?? 0);
        $dueFormatada = date('d/m/Y', $dueDateTs);
        $appUrl = ConfiguracoesSistema::appUrlBase();

        if ($tipo === 'antes') {
            $this->enviarLembreteVencimento($email, $nome, $plano, $preco, $dueFormatada, $marco, $billingType, $appUrl);
            $log("Lembrete enviado: assinatura #{$subId}, {$marco} dias antes do vencimento.");
        } else {
            $this->enviarLembreteAtraso($email, $nome, $plano, $preco, $dueFormatada, $marco, $billingType, $appUrl);
            $log("Lembrete de atraso enviado: assinatura #{$subId}, {$marco} dias de atraso.");
        }

        // Atualizar last_reminder_at
        $pdo->prepare('UPDATE subscriptions SET last_reminder_at = :d WHERE id = :id')
            ->execute([':d' => date('Y-m-d H:i:s'), ':id' => $subId]);
    }

    private function enviarLembreteVencimento(
        string $email, string $nome, string $plano, float $preco,
        string $dueFormatada, int $diasAntes, string $billingType, string $appUrl,
    ): void {
        $saudacao = $nome !== '' ? 'Olá ' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ',' : 'Olá,';

        if ($diasAntes === 0) {
            $titulo = 'Sua fatura vence hoje';
            $msg = 'Sua assinatura do plano <strong>' . htmlspecialchars($plano, ENT_QUOTES, 'UTF-8')
                 . '</strong> vence <strong>hoje (' . $dueFormatada . ')</strong>.';
        } else {
            $titulo = 'Fatura com vencimento em ' . $diasAntes . ' dia' . ($diasAntes > 1 ? 's' : '');
            $msg = 'Sua assinatura do plano <strong>' . htmlspecialchars($plano, ENT_QUOTES, 'UTF-8')
                 . '</strong> vence em <strong>' . $dueFormatada . '</strong> (' . $diasAntes . ' dia' . ($diasAntes > 1 ? 's' : '') . ').';
        }

        $metodo = match ($billingType) {
            'BOLETO' => 'Boleto Bancário',
            'PIX' => 'PIX',
            default => $billingType,
        };

        $corpo = '<p style="margin:0 0 12px;">' . $saudacao . '</p>'
               . '<p style="margin:0 0 12px;">' . $msg . '</p>'
               . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
               . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">Plano</td>'
               . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;">' . htmlspecialchars($plano, ENT_QUOTES, 'UTF-8') . '</td></tr>'
               . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">Valor</td>'
               . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;">R$ ' . number_format($preco, 2, ',', '.') . '</td></tr>'
               . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">Vencimento</td>'
               . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;">' . $dueFormatada . '</td></tr>'
               . '<tr><td style="padding:8px 12px;font-weight:600;color:#64748b;">Pagamento</td>'
               . '<td style="padding:8px 12px;">' . htmlspecialchars($metodo, ENT_QUOTES, 'UTF-8') . '</td></tr>'
               . '</table>'
               . '<p style="margin:0 0 12px;">Acesse o painel para efetuar o pagamento e evitar a suspensão do serviço.</p>';

        $html = EmailTemplate::renderizar(
            $titulo,
            $corpo,
            'Acessar Painel',
            $appUrl . '/cliente/assinaturas',
        );

        try {
            (new SmtpMailer())->enviar($email, $titulo, $html, true);
        } catch (\Throwable) {}
    }

    private function enviarLembreteAtraso(
        string $email, string $nome, string $plano, float $preco,
        string $dueFormatada, int $diasAtraso, string $billingType, string $appUrl,
    ): void {
        $saudacao = $nome !== '' ? 'Olá ' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ',' : 'Olá,';

        $titulo = 'Fatura em atraso — ' . $diasAtraso . ' dia' . ($diasAtraso > 1 ? 's' : '');

        $metodoMsg = '';
        if ($billingType === 'CREDIT_CARD') {
            $metodoMsg = '<p style="margin:0 0 12px;color:#dc2626;">Houve uma falha na cobrança automática do seu cartão. Por favor, atualize seus dados de pagamento ou escolha outro método.</p>';
        }

        $corpo = '<p style="margin:0 0 12px;">' . $saudacao . '</p>'
               . '<p style="margin:0 0 12px;">Sua fatura do plano <strong>' . htmlspecialchars($plano, ENT_QUOTES, 'UTF-8')
               . '</strong> venceu em <strong>' . $dueFormatada . '</strong> e está com <strong>' . $diasAtraso . ' dia' . ($diasAtraso > 1 ? 's' : '') . ' de atraso</strong>.</p>'
               . $metodoMsg
               . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
               . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">Plano</td>'
               . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;">' . htmlspecialchars($plano, ENT_QUOTES, 'UTF-8') . '</td></tr>'
               . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">Valor</td>'
               . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;">R$ ' . number_format($preco, 2, ',', '.') . '</td></tr>'
               . '<tr><td style="padding:8px 12px;font-weight:600;color:#64748b;">Vencimento</td>'
               . '<td style="padding:8px 12px;">' . $dueFormatada . '</td></tr>'
               . '</table>'
               . '<p style="margin:0 0 12px;color:#dc2626;font-weight:600;">Regularize o pagamento para evitar a suspensão do seu serviço.</p>';

        $html = EmailTemplate::renderizar(
            $titulo,
            $corpo,
            'Regularizar Pagamento',
            $appUrl . '/cliente/assinaturas',
        );

        try {
            (new SmtpMailer())->enviar($email, $titulo, $html, true);
        } catch (\Throwable) {}
    }
}
