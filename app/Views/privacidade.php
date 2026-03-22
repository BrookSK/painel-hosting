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
  <?php $seo_titulo = I18n::t('privacidade.titulo') . ' — ' . ($nome_sistema ?? SistemaConfig::nome()); require __DIR__ . '/_partials/seo.php'; ?>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;}
    .pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden;}
    .pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .pub-page-hero-inner{position:relative;max-width:600px;margin:0 auto;}
    .pub-page-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#a78bfa;margin-bottom:12px;}
    .pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:10px;}
    .pub-page-sub{font-size:15px;color:rgba(255,255,255,.6);line-height:1.7;}
    .legal-wrap{max-width:800px;margin:0 auto;padding:48px 24px 72px;}
    .legal-body{background:#fff;border-radius:20px;padding:40px 44px;box-shadow:0 4px 32px rgba(0,0,0,.25);}
    .legal-body h1{font-size:26px;font-weight:800;margin-bottom:8px;color:#0B1C3D;letter-spacing:-.02em;}
    .legal-body h2{font-size:17px;font-weight:700;margin:28px 0 8px;color:#1e3a8a;}
    .legal-body h3{font-size:14px;font-weight:600;margin:20px 0 6px;color:#334155;}
    .legal-body p{color:#475569;line-height:1.75;margin:0 0 12px;font-size:14px;}
    .legal-body ul{color:#475569;line-height:1.75;padding-left:20px;margin:0 0 12px;font-size:14px;}
    .legal-body li{margin-bottom:4px;}
    .legal-body .legal-meta{font-size:13px;color:#94a3b8;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;}
    .legal-body .aviso{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin:20px 0;font-size:13px;color:#991b1b;font-weight:500;}
    .legal-body .destaque{background:#f0f4ff;border:1px solid #c7d2fe;border-radius:10px;padding:14px 16px;margin:20px 0;font-size:13px;color:#1e40af;}
    @media(max-width:600px){.legal-body{padding:24px 18px}.legal-wrap{padding:32px 16px 48px}}
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/navbar-publica.php'; ?>

  <div class="pub-page-hero">
    <div class="pub-page-hero-inner">
      <div class="pub-page-label"><?php echo View::e(I18n::t('privacidade.label')); ?></div>
      <h1 class="pub-page-title"><?php echo View::e(I18n::t('privacidade.titulo')); ?></h1>
      <p class="pub-page-sub"><?php echo View::e(I18n::t('privacidade.sub')); ?></p>
    </div>
  </div>

  <div class="legal-wrap">
    <div class="legal-body">
      <?php if (!empty($conteudo)): ?>
        <?php echo $conteudo; ?>
      <?php else: ?>
        <h1><?php echo View::e(I18n::t('privacidade.titulo')); ?></h1>
        <p><?php echo View::e(I18n::t('privacidade.vazio')); ?></p>
      <?php endif; ?>

      <h2 id="cookies"><?php echo View::e(I18n::t('cookies.titulo')); ?></h2>
      <p><?php echo View::e(I18n::t('cookies.privacidade_texto')); ?></p>
      <ul>
        <li><strong><?php echo View::e(I18n::t('cookies.cat_necessarios')); ?></strong> — <?php echo View::e(I18n::t('cookies.cat_necessarios_desc')); ?></li>
        <li><strong><?php echo View::e(I18n::t('cookies.cat_analytics')); ?></strong> — <?php echo View::e(I18n::t('cookies.cat_analytics_desc')); ?></li>
        <li><strong><?php echo View::e(I18n::t('cookies.cat_marketing')); ?></strong> — <?php echo View::e(I18n::t('cookies.cat_marketing_desc')); ?></li>
        <li><strong><?php echo View::e(I18n::t('cookies.cat_preferencias')); ?></strong> — <?php echo View::e(I18n::t('cookies.cat_preferencias_desc')); ?></li>
      </ul>
      <p><a href="#" onclick="ckAbrirModal();return false" style="color:#4F46E5;font-weight:600"><?php echo View::e(I18n::t('cookies.configurar')); ?></a></p>
    </div>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
