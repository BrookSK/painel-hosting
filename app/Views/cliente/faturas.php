<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = 'Faturas';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

function _badgeFatura(string $st): string {
    $map = [
        'paid' => ['Pago', '#dcfce7', '#166534'],
        'CONFIRMED' => ['Pago', '#dcfce7', '#166534'],
        'RECEIVED' => ['Pago', '#dcfce7', '#166534'],
        'open' => ['Aberta', '#fef3c7', '#92400e'],
        'PENDING' => ['Pendente', '#fef3c7', '#92400e'],
        'OVERDUE' => ['Vencida', '#fee2e2', '#991b1b'],
        'void' => ['Cancelada', '#f1f5f9', '#334155'],
        'draft' => ['Rascunho', '#f1f5f9', '#64748b'],
        'uncollectible' => ['Incobr.', '#f1f5f9', '#64748b'],
    ];
    $d = $map[$st] ?? [$st, '#f1f5f9', '#334155'];
    return '<span class="badge-new" style="background:'.$d[1].';color:'.$d[2].';">'.View::e($d[0]).'</span>';
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Faturas</div>
    <div class="page-subtitle" style="margin-bottom:0;">Histórico de pagamentos e invoices</div>
  </div>
  <a class="botao ghost sm" href="/cliente/assinaturas">← Assinaturas</a>
</div>

<?php if (empty($faturas)): ?>
<div class="card-new" style="text-align:center;padding:40px 24px;">
  <div style="font-size:36px;margin-bottom:12px;">🧾</div>
  <div style="font-size:15px;font-weight:600;margin-bottom:8px;">Nenhuma fatura encontrada</div>
  <div style="font-size:13px;color:#64748b;">As faturas aparecerão aqui após o primeiro pagamento.</div>
</div>
<?php else: ?>
<div class="card-new">
  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Data</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Descrição</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Valor</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Gateway</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Status</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($faturas as $f): ?>
        <tr>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;font-size:13px;"><?php echo View::e((string)($f['data'] ?? '')); ?></td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($f['plano'] ?? '')); ?></td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;font-weight:600;">
            <?php echo (string)($f['moeda'] ?? '') === 'BRL' ? 'R$' : 'US$'; ?> <?php echo View::e((string)($f['valor'] ?? '0')); ?>
          </td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
            <span class="badge-new" style="background:<?php echo (string)($f['gateway'] ?? '') === 'Stripe' ? '#e0e7ff' : '#fef3c7'; ?>;color:<?php echo (string)($f['gateway'] ?? '') === 'Stripe' ? '#1e3a8a' : '#92400e'; ?>;">
              <?php echo View::e((string)($f['gateway'] ?? '')); ?>
            </span>
          </td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo _badgeFatura((string)($f['status'] ?? '')); ?></td>
          <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;gap:6px;">
              <?php if (!empty($f['pdf_url'])): ?>
                <a href="<?php echo View::e((string)$f['pdf_url']); ?>" target="_blank" rel="noopener" class="botao ghost sm" style="font-size:11px;padding:3px 8px;">📄 PDF</a>
              <?php endif; ?>
              <?php if (!empty($f['hosted_url'])): ?>
                <a href="<?php echo View::e((string)$f['hosted_url']); ?>" target="_blank" rel="noopener" class="botao ghost sm" style="font-size:11px;padding:3px 8px;">🔗 Ver</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
