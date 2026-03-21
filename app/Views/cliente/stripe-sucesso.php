<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;

$assinatura = (array) ($assinatura ?? []);
$vps        = is_array($vps ?? null) ? $vps : null;
$erro       = (string) ($erro ?? '');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo View::e(I18n::t('stripe.sucesso_titulo')); ?> — <?php echo View::e(SistemaConfig::nome()); ?></title>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
body{background:#060d1f}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<div class="pub-page-hero">
  <div class="pub-page-hero-inner">
    <div style="font-size:56px;margin-bottom:12px">✅</div>
    <h1 class="pub-page-title"><?php echo View::e(I18n::t('stripe.sucesso_titulo')); ?></h1>
    <p class="pub-page-sub"><?php echo View::e(I18n::t('stripe.sucesso_sub')); ?></p>
  </div>
</div>

<style>
.pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden}
.pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.pub-page-hero-inner{position:relative;max-width:600px;margin:0 auto}
.pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:10px}
.pub-page-sub{font-size:15px;color:rgba(255,255,255,.6);line-height:1.7}
.checkout-wrap{max-width:640px;margin:0 auto;padding:40px 24px 72px}
.checkout-card{background:#fff;border-radius:20px;padding:36px;box-shadow:0 4px 32px rgba(0,0,0,.25)}
.detail-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:18px;margin-bottom:16px}
.detail-box-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:12px}
.detail-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:14px;color:#475569}
.detail-row+.detail-row{border-top:1px solid #f1f5f9}
.detail-row strong{color:#0f172a;font-weight:600}
.steps-box{background:#eef2ff;border:1px solid #c7d2fe;border-radius:14px;padding:18px;margin-bottom:24px}
.steps-box strong{font-size:14px;color:#1e40af;display:block;margin-bottom:8px}
.steps-box ul{margin:0;padding:0 0 0 18px;font-size:13px;color:#3730a3;line-height:1.9}
.checkout-actions{display:flex;gap:10px;flex-wrap:wrap}
@media(max-width:480px){.checkout-card{padding:24px 18px}.checkout-actions{flex-direction:column}.checkout-actions .botao{width:100%;justify-content:center;text-align:center}}
</style>

<div class="checkout-wrap">
  <div class="checkout-card">

    <?php if ($erro !== ''): ?>
      <div class="erro"><?php echo View::e($erro); ?></div>
      <div style="margin-top:14px">
        <a class="botao sec" href="/cliente/planos"><?php echo View::e(I18n::t('stripe.erro_tentar')); ?></a>
      </div>
    <?php else: ?>

      <?php if (!empty($assinatura)): ?>
      <div class="detail-box">
        <div class="detail-box-title"><?php echo View::e(I18n::t('stripe.detalhes')); ?></div>
        <div class="detail-row">
          <span><?php echo View::e(I18n::t('stripe.plano')); ?></span>
          <strong><?php echo View::e((string) ($assinatura['plan_name'] ?? '')); ?></strong>
        </div>
        <div class="detail-row">
          <span><?php echo View::e(I18n::t('stripe.valor')); ?></span>
          <strong><?php echo View::e(I18n::preco((float)($assinatura['price_monthly'] ?? 0))); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></strong>
        </div>
        <?php if (!empty($assinatura['next_due_date'])): ?>
        <div class="detail-row">
          <span><?php echo View::e(I18n::t('stripe.prox_cobranca')); ?></span>
          <strong><?php echo View::e((string) $assinatura['next_due_date']); ?></strong>
        </div>
        <?php endif; ?>
        <div class="detail-row">
          <span><?php echo View::e(I18n::t('stripe.status')); ?></span>
          <span class="badge" style="background:#dcfce7;color:#166534"><?php echo View::e(I18n::t('stripe.ativa')); ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if (is_array($vps)): ?>
      <div class="detail-box">
        <div class="detail-box-title"><?php echo View::e(I18n::t('stripe.vps_vinculada')); ?></div>
        <div class="detail-row">
          <span>ID</span>
          <strong>#<?php echo (int) ($vps['id'] ?? 0); ?></strong>
        </div>
        <?php if (!empty($vps['label'])): ?>
        <div class="detail-row">
          <span>Label</span>
          <strong><?php echo View::e((string) $vps['label']); ?></strong>
        </div>
        <?php endif; ?>
        <div class="detail-row">
          <span><?php echo View::e(I18n::t('stripe.status')); ?></span>
          <strong><?php echo View::e((string) ($vps['status'] ?? '')); ?></strong>
        </div>
      </div>
      <?php endif; ?>

      <div class="steps-box">
        <strong><?php echo View::e(I18n::t('stripe.proximos_passos')); ?></strong>
        <ul>
          <li><?php echo View::e(I18n::t('stripe.passo_1')); ?></li>
          <li><?php echo View::e(I18n::t('stripe.passo_2')); ?></li>
          <li><?php echo View::e(I18n::t('stripe.passo_3')); ?></li>
        </ul>
      </div>

      <div class="checkout-actions">
        <a class="botao" href="/cliente/vps"><?php echo View::e(I18n::t('stripe.ver_vps')); ?></a>
        <a class="botao sec" href="/cliente/assinaturas"><?php echo View::e(I18n::t('stripe.ver_assinaturas')); ?></a>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
