<?php
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\View;
$_topo_nome = SistemaConfig::nome();
$_topo_logo = SistemaConfig::logoUrl();
?>
<nav class="pub-navbar">
  <div class="pub-navbar-inner">
    <a href="/" class="pub-navbar-brand">
      <?php if ($_topo_logo !== ''): ?>
        <img src="<?php echo View::e($_topo_logo); ?>" alt="logo" />
      <?php else: ?>
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none"><rect width="26" height="26" rx="7" fill="#4F46E5"/><path d="M6 13h14M13 6v14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
      <?php endif; ?>
      <span><?php echo View::e($_topo_nome); ?></span>
    </a>
    <div class="pub-navbar-links">
      <?php if (!isset($_topo_hide_inicio)): ?>
        <a href="/">Início</a>
      <?php endif; ?>
      <?php if (!empty($_topo_links) && is_array($_topo_links)): ?>
        <?php foreach ($_topo_links as $_l): ?>
          <a href="<?php echo View::e($_l['href']); ?>"><?php echo View::e($_l['label']); ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="pub-navbar-actions">
      <?php require __DIR__ . '/idioma.php'; ?>
    </div>
  </div>
</nav>
<style>
.pub-navbar{position:sticky;top:0;z-index:100;background:rgba(11,28,61,.95);backdrop-filter:blur(16px) saturate(180%);-webkit-backdrop-filter:blur(16px) saturate(180%);border-bottom:1px solid rgba(255,255,255,.08);padding:0 24px;}
.pub-navbar-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:58px;gap:16px;}
.pub-navbar-brand{display:flex;align-items:center;gap:9px;text-decoration:none;color:#fff;flex-shrink:0;}
.pub-navbar-brand img{height:28px;width:auto;}
.pub-navbar-brand span{font-size:15px;font-weight:700;letter-spacing:-.01em;}
.pub-navbar-links{display:flex;align-items:center;gap:2px;flex:1;justify-content:center;}
.pub-navbar-links a{color:rgba(255,255,255,.72);text-decoration:none;font-size:13px;font-weight:500;padding:6px 11px;border-radius:8px;transition:color .15s,background .15s;}
.pub-navbar-links a:hover{color:#fff;background:rgba(255,255,255,.1);}
.pub-navbar-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
@media(max-width:768px){.pub-navbar-links{display:none;}}
</style>
