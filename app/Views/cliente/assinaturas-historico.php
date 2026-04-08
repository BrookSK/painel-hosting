<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function _badgeStHist(string $st): string {
    $map = [
        'ACTIVE'    => ['Ativa',      '#dcfce7','#166534'],
        'active'    => ['Ativa',      '#dcfce7','#166534'],
        'PENDING'   => ['Pendente',   '#fef3c7','#92400e'],
        'OVERDUE'   => ['Em atraso',  '#fee2e2','#991b1b'],
        'SUSPENDED' => ['Suspensa',   '#fee2e2','#991b1b'],
        'EXPIRED'   => ['Expirada',   '#f1f5f9','#64748b'],
        'CANCELED'  => ['Cancelada',  '#f1f5f9','#334155'],
        'inactive'  => ['Inativa',    '#f1f5f9','#334155'],
    ];
    $d = $map[$st] ?? [$st,'#f1f5f9','#334155'];
    return '<span class="badge-new" style="background:'.$d[1].';color:'.$d[2].';">'.View::e($d[0]).'</span>';
}

function _badgeCob(string $st): string {
    $map = [
        'RECEIVED'           => ['Pago',         '#dcfce7','#166534'],
        'CONFIRMED'          => ['Confirmado',    '#dcfce7','#166534'],
        'PENDING'            => ['Pendente',      '#fef3c7','#92400e'],
        'OVERDUE'            => ['Vencido',       '#fee2e2','#991b1b'],
        'REFUNDED'           => ['Estornado',     '#e0e7ff','#1e3a8a'],
        'PARTIALLY_REFUNDED' => ['Est. parcial',  '#e0e7ff','#1e3a8a'],
        'CANCELED'           => ['Cancelado',     '#f1f5f9','#334155'],
    ];
    $d = $map[$st] ?? [$st,'#f1f5f9','#334155'];
    return '<span class="badge-new" style="background:'.$d[1].';color:'.$d[2].';">'.View::e($d[0]).'</span>';
}

$pageTitle = I18n::t('assinaturas.historico');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::t('assinaturas.historico')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('assinaturas.historico_sub')); ?></div>
  </div>
  <a class="botao ghost sm" href="/cliente/assinaturas">← <?php echo View::e(I18n::t('assinaturas.voltar')); ?></a>
</div>

<!-- Assinaturas -->
<div class="card-new" style="margin-bottom:14px;">
  <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('assinaturas.titulo')); ?></div>
  <?php if (empty($assinaturas)): ?>
    <p style="color:#94a3b8;font-size:13px;"><?php echo View::e(I18n::t('assinaturas.nenhuma')); ?></p>
  <?php else: ?>
    <div style="overflow:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">#</th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.plano')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.valor')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.prox_vencimento')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.status')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.criada_em')); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($assinaturas as $a): ?>
            <tr>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;">#<?php echo (int)($a['id'] ?? 0); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string)($a['plan_name'] ?? '')); ?></strong></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e(I18n::precoPlano($a)); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($a['next_due_date'] ?? '—')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo _badgeStHist((string)($a['status'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($a['created_at'] ?? '')); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Cobranças -->
<?php if (!empty($cobrancas)): ?>
  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('assinaturas.cobrancas')); ?></div>
    <div style="overflow:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.plano')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.valor')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.vencimento')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.tipo')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.status')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.link')); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cobrancas as $c): ?>
            <tr>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;font-size:13px;"><?php echo View::e((string)($c['plan_name'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e(I18n::preco((float)($c['value'] ?? 0))); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($c['dueDate'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($c['billingType'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo _badgeCob((string)($c['status'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
                <?php $url = (string)($c['invoiceUrl'] ?? ($c['bankSlipUrl'] ?? '')); ?>
                <?php if ($url !== ''): ?>
                  <a href="<?php echo View::e($url); ?>" target="_blank" rel="noopener"><?php echo View::e(I18n::t('assinaturas.ver_fatura')); ?></a>
                <?php else: ?>—<?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card-new" style="text-align:center;padding:30px;">
    <p style="color:#94a3b8;font-size:13px;"><?php echo View::e(I18n::t('assinaturas.nenhuma_cobranca')); ?></p>
  </div>
<?php endif; ?>

<!-- Reembolso — discreto no final -->
<?php if (!empty($assinaturas)): ?>
<div style="margin-top:32px;padding-top:16px;border-top:1px solid #f1f5f9;">
  <details>
    <summary style="cursor:pointer;font-size:12px;color:#94a3b8;"><?php echo View::e(I18n::t('assinaturas.solicitar_reembolso')); ?></summary>
    <div style="margin-top:12px;max-width:480px;">
      <form method="post" action="/cliente/assinaturas/reembolso">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;"><?php echo View::e(I18n::t('assinaturas.plano')); ?></label>
          <select class="input" name="subscription_id" required style="font-size:13px;">
            <?php foreach ($assinaturas as $a): ?>
              <option value="<?php echo (int)($a['id'] ?? 0); ?>">#<?php echo (int)($a['id'] ?? 0); ?> — <?php echo View::e((string)($a['plan_name'] ?? '')); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;"><?php echo View::e(I18n::t('assinaturas.motivo')); ?></label>
          <textarea class="input" name="motivo" rows="3" required placeholder="<?php echo View::e(I18n::t('assinaturas.motivo_placeholder')); ?>" style="font-size:13px;"></textarea>
        </div>
        <button class="botao ghost sm" type="submit"><?php echo View::e(I18n::t('assinaturas.enviar_solicitacao')); ?></button>
      </form>
    </div>
  </details>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
