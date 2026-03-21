<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_topo_links = [
    ['href' => '/termos',  'label' => 'Termos'],
    ['href' => '/contato', 'label' => 'Contato'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Política de Privacidade — <?php echo View::e($nome_sistema ?? SistemaConfig::nome()); ?></title>
  <?php $seo_titulo = 'Política de Privacidade — ' . ($nome_sistema ?? SistemaConfig::nome()); require __DIR__ . '/_partials/seo.php'; ?>
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
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/topo-publico.php'; ?>

  <div class="pub-page-hero">
    <div class="pub-page-hero-inner">
      <div class="pub-page-label">Legal</div>
      <h1 class="pub-page-title">Política de Privacidade</h1>
      <p class="pub-page-sub">Saiba como coletamos, usamos e protegemos seus dados.</p>
    </div>
  </div>

  <div class="legal-wrap">
    <div class="legal-body">
      <?php if (!empty($conteudo)): ?>
        <?php echo $conteudo; ?>
      <?php else: ?>
        <h1>Política de Privacidade</h1>
        <p>A política de privacidade ainda não foi configurada. Entre em contato com o administrador.</p>
      <?php endif; ?>
    </div>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
