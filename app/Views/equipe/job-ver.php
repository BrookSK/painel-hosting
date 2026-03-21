<?php
declare(strict_types=1);
use LRV\Core\View;

$payloadJson = json_encode($payload??[], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

$pageTitle = 'Job #'.(int)($job['id']??0);
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Job #<?php echo (int)($job['id']??0); ?></div>
<div class="page-subtitle"><?php echo View::e((string)($job['type']??'')); ?></div>

<div class="grid">
  <div class="card-new">
    <div class="card-new-title">Dados</div>
    <p class="texto"><strong>Status:</strong> <?php echo View::e((string)($job['status']??'')); ?></p>
    <p class="texto"><strong>Criado:</strong> <?php echo View::e((string)($job['created_at']??'')); ?></p>
    <p class="texto"><strong>Atualizado:</strong> <?php echo View::e((string)($job['updated_at']??'')); ?></p>
    <div style="margin-top:10px;">
      <div style="font-weight:700;font-size:13px;margin-bottom:6px;">Payload</div>
      <pre style="white-space:pre-wrap;background:#0b1220;color:#e2e8f0;padding:12px;border-radius:12px;overflow:auto;"><?php echo View::e((string)$payloadJson); ?></pre>
    </div>
  </div>
  <div class="card-new">
    <div class="card-new-title">Log</div>
    <pre style="white-space:pre-wrap;background:#0b1220;color:#e2e8f0;padding:12px;border-radius:12px;overflow:auto;min-height:240px;"><?php echo View::e((string)($job['log']??'')); ?></pre>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
