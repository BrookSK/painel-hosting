<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusAssinatura(string $st): string {
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
    return '<span class="badge-new" style="background:' . $d[1] . ';color:' . $d[2] . ';">' . View::e($d[0]) . '</span>';
}

function badgeStatusCobranca(string $st): string {
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
    return '<span class="badge-new" style="background:' . $d[1] . ';color:' . $d[2] . ';">' . View::e($d[0]) . '</span>';
}

$pageTitle    = I18n::t('assinaturas.titulo');
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title"><?php echo View::e(I18n::t('assinaturas.titulo')); ?></div>
  <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('assinaturas.planos_cobrancas')); ?></div>
</div>

<?php if (empty($assinaturas)): ?>
  <div class="card-new" style="text-align:center;padding:40px 24px;">
    <div style="font-size:36px;margin-bottom:12px;">💳</div>
    <div style="font-size:15px;font-weight:600;margin-bottom:8px;"><?php echo View::e(I18n::t('assinaturas.nenhuma_ainda')); ?></div>
    <div style="font-size:13px;color:#64748b;margin-bottom:16px;"><?php echo View::e(I18n::t('assinaturas.escolha_plano')); ?></div>
    <a class="botao" href="/cliente/planos"><?php echo View::e(I18n::t('assinaturas.ver_planos')); ?></a>
  </div>
<?php else: ?>
  <div class="card-new" style="margin-bottom:14px;">
    <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('assinaturas.titulo')); ?></div>
    <div style="overflow:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">#</th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.plano')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.valor')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.prox_vencimento')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.status')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.acoes')); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($assinaturas as $a): ?>
            <tr>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;">#<?php echo (int)($a['id'] ?? 0); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string)($a['plan_name'] ?? '')); ?></strong></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e(I18n::preco((float)($a['price_monthly'] ?? 0))); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($a['next_due_date'] ?? '—')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusAssinatura((string)($a['status'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
                <?php $aStatus = strtoupper((string)($a['status'] ?? '')); ?>
                <?php if ($aStatus === 'PENDING'): ?>
                  <a class="botao sm" href="/cliente/pagamento?sub=<?php echo (int)($a['id'] ?? 0); ?>">
                    <?php echo View::e(I18n::t('pagamento.pagar')); ?>
                  </a>
                <?php else: ?>
                  <button class="botao ghost sm" onclick="document.getElementById('modal-reembolso-<?php echo (int)($a['id'] ?? 0); ?>').style.display='flex'">
                    <?php echo View::e(I18n::t('assinaturas.solicitar_reembolso')); ?>
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (!empty($cobrancas)): ?>
    <div class="card-new">
      <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('assinaturas.cobrancas')); ?></div>
      <div style="overflow:auto;">
        <table style="width:100%;border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.id')); ?></th>
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
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;font-size:12px;"><?php echo View::e((string)($c['id'] ?? '')); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e(I18n::preco((float)($c['value'] ?? 0))); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($c['dueDate'] ?? '')); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($c['billingType'] ?? '')); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusCobranca((string)($c['status'] ?? '')); ?></td>
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
  <?php endif; ?>
<?php endif; ?>

<!-- Modais de reembolso -->
<?php foreach (($assinaturas ?? []) as $a): ?>
  <div id="modal-reembolso-<?php echo (int)($a['id'] ?? 0); ?>"
       style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;">
    <div class="card-new" style="max-width:480px;width:90%;position:relative;">
      <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('assinaturas.solicitar_reembolso')); ?></div>
      <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Assinatura #<?php echo (int)($a['id'] ?? 0); ?> — <?php echo View::e((string)($a['plan_name'] ?? '')); ?></p>
      <form method="post" action="/cliente/assinaturas/reembolso">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="subscription_id" value="<?php echo (int)($a['id'] ?? 0); ?>" />
        <div style="margin-bottom:12px;">
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('assinaturas.motivo')); ?></label>
          <textarea class="input" name="motivo" rows="4" required placeholder="<?php echo View::e(I18n::t('assinaturas.motivo_placeholder')); ?>"></textarea>
        </div>
        <div style="display:flex;gap:8px;">
          <button class="botao" type="submit"><?php echo View::e(I18n::t('assinaturas.enviar_solicitacao')); ?></button>
          <button type="button" class="botao ghost"
            onclick="document.getElementById('modal-reembolso-<?php echo (int)($a['id'] ?? 0); ?>').style.display='none'">
            <?php echo View::e(I18n::t('geral.cancelar')); ?>
          </button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
