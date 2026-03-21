<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_topo_links = [
    ['href' => '/status',  'label' => 'Status'],
    ['href' => '/contato', 'label' => 'Contato'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Changelog — <?php echo View::e($nome_sistema ?? SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    .changelog-body{max-width:780px;margin:0 auto;padding:32px 18px 48px;}
    .changelog-body h1{font-size:26px;font-weight:700;margin-bottom:4px;color:#0B1C3D;}
    .changelog-body h2{font-size:18px;font-weight:700;margin:32px 0 8px;padding:10px 14px;background:linear-gradient(90deg,#eef2ff,#f5f3ff);border-left:4px solid #4F46E5;border-radius:0 8px 8px 0;color:#1e3a8a;}
    .changelog-body h3{font-size:14px;font-weight:600;margin:16px 0 6px;color:#7C3AED;text-transform:uppercase;letter-spacing:.04em;}
    .changelog-body p{color:#475569;line-height:1.75;margin:0 0 10px;}
    .changelog-body ul{color:#475569;line-height:1.75;padding-left:20px;margin:0 0 12px;}
    .changelog-body li{margin-bottom:4px;}
    .changelog-body code{background:#f1f5f9;padding:1px 5px;border-radius:4px;font-size:13px;color:#0f172a;}
    .changelog-body strong{color:#0f172a;}
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/topo-publico.php'; ?>

  <div class="changelog-body">
    <?php echo $conteudo_html ?? ''; ?>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
