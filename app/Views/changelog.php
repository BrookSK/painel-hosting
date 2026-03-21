<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php $seo_titulo = I18n::t('changelog.titulo') . ' — ' . ($nome_sistema ?? SistemaConfig::nome()); require __DIR__ . '/_partials/seo.php'; ?>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;}
    .pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden;}
    .pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .pub-page-hero-inner{position:relative;max-width:600px;margin:0 auto;}
    .pub-page-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#a78bfa;margin-bottom:12px;}
    .pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:10px;}
    .pub-page-sub{font-size:15px;color:rgba(255,255,255,.6);line-height:1.7;}
    .changelog-wrap{max-width:800px;margin:0 auto;padding:48px 24px 72px;}
    .changelog-body{background:#fff;border-radius:20px;padding:40px 44px;box-shadow:0 4px 32px rgba(0,0,0,.25);}
    .changelog-body h1{font-size:26px;font-weight:800;margin-bottom:4px;color:#0B1C3D;letter-spacing:-.02em;}
    .changelog-body h2{font-size:17px;font-weight:700;margin:32px 0 8px;padding:10px 16px;background:linear-gradient(90deg,#eef2ff,#f5f3ff);border-left:4px solid #4F46E5;border-radius:0 10px 10px 0;color:#1e3a8a;}
    .changelog-body h3{font-size:13px;font-weight:700;margin:16px 0 6px;color:#7C3AED;text-transform:uppercase;letter-spacing:.06em;}
    .changelog-body p{color:#475569;line-height:1.75;margin:0 0 10px;font-size:14px;}
    .changelog-body ul{color:#475569;line-height:1.75;padding-left:20px;margin:0 0 12px;font-size:14px;}
    .changelog-body li{margin-bottom:4px;}
    .changelog-body code{background:#f1f5f9;padding:2px 6px;border-radius:5px;font-size:12px;color:#0f172a;}
    .changelog-body strong{color:#0f172a;}
    @media(max-width:600px){.changelog-body{padding:24px 18px}.changelog-wrap{padding:32px 16px 48px}}
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/navbar-publica.php'; ?>

  <div class="pub-page-hero">
    <div class="pub-page-hero-inner">
      <div class="pub-page-label"><?php echo View::e(I18n::t('changelog.label')); ?></div>
      <h1 class="pub-page-title"><?php echo View::e(I18n::t('changelog.titulo')); ?></h1>
      <p class="pub-page-sub"><?php echo View::e(I18n::t('changelog.sub')); ?></p>
    </div>
  </div>

  <div class="changelog-wrap">
    <div class="changelog-body">
      <?php echo $conteudo_html ?? ''; ?>
    </div>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
