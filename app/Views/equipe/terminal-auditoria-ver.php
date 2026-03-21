<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Auditoria — Sessao #'.(int)($sessao['id']??0);
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Auditoria — Sessao #<?php echo (int)($sessao['id']??0); ?></div>
<div class="page-subtitle">Comandos executados</div>

<div class="card-new">
  <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:16px;">
    <div><div class="texto" style="margin:0 0 4px 0;"><strong>Usuario</strong></div><div><?php echo View::e((string)($sessao['user_name']??'')); ?></div></div>
    <div><div class="texto" style="margin:0 0 4px 0;"><strong>Node</strong></div><div><?php echo View::e((string)($sessao['server_hostname']??'')); ?></div></div>
    <div><div class="texto" style="margin:0 0 4px 0;"><strong>Inicio</strong></div><div><?php echo View::e((string)($sessao['started_at']??'')); ?></div></div>
    <div><div class="texto" style="margin:0 0 4px 0;"><strong>Fim</strong></div><div><?php echo View::e((string)($sessao['ended_at']??'')); ?></div></div>
    <div><div class="texto" style="margin:0 0 4px 0;"><strong>IP</strong></div><div><code><?php echo View::e((string)($sessao['ip']??'')); ?></code></div></div>
  </div>

  <div style="border:1px solid #0b1220;border-radius:14px;overflow:hidden;background:#020617;">
    <div style="padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.18);color:#e2e8f0;font-size:13px;">Comandos</div>
    <pre style="margin:0;padding:12px;color:#e2e8f0;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;max-height:520px;overflow:auto;">
<?php foreach (((array)($comandos??[])) as $c): ?><?php echo View::e((string)($c['created_at']??'')); ?>  $ <?php echo View::e((string)($c['command']??'')); ?>
<?php endforeach; ?><?php if (empty($comandos)): ?>Nenhum comando registrado.
<?php endif; ?></pre>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
