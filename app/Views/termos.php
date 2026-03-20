<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Termos de Uso — <?php echo View::e($nome_sistema ?? 'LRV Cloud Manager'); ?></title>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    .legal-body{max-width:780px;margin:0 auto;padding:32px 18px 48px;}
    .legal-body h1{font-size:26px;font-weight:700;margin-bottom:8px;color:#0B1C3D;}
    .legal-body h2{font-size:18px;font-weight:700;margin:28px 0 8px;color:#1e3a8a;}
    .legal-body h3{font-size:15px;font-weight:600;margin:20px 0 6px;color:#334155;}
    .legal-body p{color:#475569;line-height:1.75;margin:0 0 12px;}
    .legal-body ul{color:#475569;line-height:1.75;padding-left:20px;margin:0 0 12px;}
    .legal-body li{margin-bottom:4px;}
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div style="font-size:18px;font-weight:700;"><?php echo View::e($nome_sistema ?? 'LRV Cloud Manager'); ?></div>
      <div class="linha">
        <a href="/">Início</a>
        <a href="/status">Status</a>
        <a href="/contato">Contato</a>
      </div>
    </div>
  </div>

  <div class="legal-body">
    <?php if (!empty($conteudo)): ?>
      <?php echo $conteudo; ?>
    <?php else: ?>
      <h1>Termos de Uso</h1>
      <p>Os termos de uso ainda não foram configurados. Entre em contato com o administrador.</p>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
