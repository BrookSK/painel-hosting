<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo View::e(I18n::t('stripe.cancelado_titulo')); ?> — <?php echo View::e(SistemaConfig::nome()); ?></title>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
body{background:#060d1f}
.pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden}
.pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.pub-page-hero-inner{position:relative;max-width:600px;margin:0 auto}
.pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:10px}
.pub-page-sub{font-size:15px;color:rgba(255,255,255,.6);line-height:1.7}
.cancel-wrap{max-width:560px;margin:0 auto;padding:40px 24px 72px}
.cancel-card{background:#fff;border-radius:20px;padding:36px;box-shadow:0 4px 32px rgba(0,0,0,.25);text-align:center}
.cancel-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:24px}
@media(max-width:480px){.cancel-card{padding:24px 18px}.cancel-actions{flex-direction:column}}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<div class="pub-page-hero">
  <div class="pub-page-hero-inner">
    <div style="font-size:56px;margin-bottom:12px">⚠️</div>
    <h1 class="pub-page-title"><?php echo View::e(I18n::t('stripe.cancelado_titulo')); ?></h1>
    <p class="pub-page-sub"><?php echo View::e(I18n::t('stripe.cancelado_sub')); ?></p>
  </div>
</div>

<div class="cancel-wrap">
  <div class="cancel-card">
    <p style="font-size:15px;color:#475569;line-height:1.7;margin:0 0 8px"><?php echo View::e(I18n::t('stripe.cancelado_sub')); ?></p>
    <div class="cancel-actions">
      <a class="botao" href="/cliente/planos"><?php echo View::e(I18n::t('stripe.escolher_plano')); ?></a>
      <a class="botao sec" href="/"><?php echo View::e(I18n::t('stripe.voltar_planos')); ?></a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
