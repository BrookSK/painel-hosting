<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$assinatura = (array)($assinatura ?? []);
$vps = is_array($vps ?? null) ? $vps : null;
$erro = (string)($erro ?? '');

// Determinar valor correto
$priceMonthlyUsd = (float)($assinatura['price_monthly_usd'] ?? 0);
$priceMonthly = (float)($assinatura['price_monthly'] ?? 0);
$priceUpfront = (float)($assinatura['price_annual_upfront_usd'] ?? 0);
$priceUpfrontBrl = (float)($assinatura['price_annual_upfront'] ?? 0);

$pageTitle = I18n::t('stripe.sucesso_titulo');
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="max-width:640px;margin:0 auto;">
  <div style="text-align:center;margin-bottom:24px;">
    <div style="font-size:48px;margin-bottom:8px;">✅</div>
    <div class="page-title"><?php echo View::e(I18n::t('stripe.sucesso_titulo')); ?></div>
    <div class="page-subtitle"><?php echo View::e(I18n::t('stripe.sucesso_sub')); ?></div>
  </div>

  <?php if ($erro !== ''): ?>
    <div class="card-new"><div class="erro"><?php echo View::e($erro); ?></div>
      <a class="botao" href="/cliente/planos" style="margin-top:12px;"><?php echo View::e(I18n::t('stripe.erro_tentar')); ?></a>
    </div>
  <?php else: ?>

    <?php if (!empty($assinatura)): ?>
    <div class="card-new" style="margin-bottom:14px;">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:12px;"><?php echo View::e(I18n::t('stripe.detalhes')); ?></div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
        <span style="color:#475569;"><?php echo View::e(I18n::t('stripe.plano')); ?></span>
        <strong><?php echo View::e((string)($assinatura['plan_name'] ?? '')); ?></strong>
      </div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
        <span style="color:#475569;"><?php echo View::e(I18n::t('stripe.valor')); ?></span>
        <strong><?php
          if ($priceMonthlyUsd > 0) {
              echo 'US$ ' . number_format($priceMonthlyUsd, 2, '.', ',') . '/mês';
          } elseif ($priceMonthly > 0) {
              echo View::e(I18n::preco($priceMonthly)) . '/mês';
          } else {
              echo 'Pagamento único';
          }
        ?></strong>
      </div>
      <?php if (!empty($assinatura['next_due_date'])): ?>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
        <span style="color:#475569;"><?php echo View::e(I18n::t('stripe.prox_cobranca')); ?></span>
        <strong><?php echo View::e((string)$assinatura['next_due_date']); ?></strong>
      </div>
      <?php endif; ?>
      <div style="display:flex;justify-content:space-between;padding:8px 0;">
        <span style="color:#475569;"><?php echo View::e(I18n::t('stripe.status')); ?></span>
        <span class="badge-new badge-green"><?php echo View::e(I18n::t('stripe.ativa')); ?></span>
      </div>
    </div>
    <?php endif; ?>

    <div class="card-new" style="background:#eef2ff;border-color:#c7d2fe;margin-bottom:14px;">
      <strong style="color:#1e40af;"><?php echo View::e(I18n::t('stripe.proximos_passos')); ?></strong>
      <ul style="margin:8px 0 0;padding-left:18px;font-size:13px;color:#3730a3;line-height:2;">
        <li><?php echo View::e(I18n::t('stripe.passo_1')); ?></li>
        <li><?php echo View::e(I18n::t('stripe.passo_2')); ?></li>
        <li><?php echo View::e(I18n::t('stripe.passo_3')); ?></li>
      </ul>
    </div>

    <div style="display:flex;gap:10px;">
      <a class="botao" href="/cliente/vps"><?php echo View::e(I18n::t('stripe.ver_vps')); ?></a>
      <a class="botao ghost" href="/cliente/assinaturas"><?php echo View::e(I18n::t('stripe.ver_assinaturas')); ?></a>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
