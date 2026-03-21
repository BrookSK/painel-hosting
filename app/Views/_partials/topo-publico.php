<?php
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\View;
$_topo_nome = SistemaConfig::nome();
$_topo_logo = SistemaConfig::logoUrl();
?>
<div class="topo">
  <div class="topo-inner">
    <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;">
      <?php if ($_topo_logo !== ''): ?>
        <img src="<?php echo View::e($_topo_logo); ?>" alt="logo" style="height:32px;width:auto;" />
      <?php endif; ?>
      <span class="topo-titulo"><?php echo View::e($_topo_nome); ?></span>
    </a>
    <nav class="nav">
      <?php require __DIR__ . '/idioma.php'; ?>
      <?php if (!isset($_topo_hide_inicio)): ?>
        <a href="/">Início</a>
      <?php endif; ?>
      <?php if (!empty($_topo_links) && is_array($_topo_links)): ?>
        <?php foreach ($_topo_links as $_l): ?>
          <a href="<?php echo View::e($_l['href']); ?>"><?php echo View::e($_l['label']); ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
    </nav>
  </div>
</div>
