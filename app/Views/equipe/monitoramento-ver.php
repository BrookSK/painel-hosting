<?php
declare(strict_types=1);
use LRV\Core\View;

function fmtPctMon($v): string {
    if ($v===null||$v==='') return '';
    return number_format((float)$v,2,',','.').'%';
}

$pageTitle = 'Monitoramento — '.(string)($servidor['hostname']??'');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Monitoramento</div>
<div class="page-subtitle">Servidor <?php echo View::e((string)($servidor['hostname']??'')); ?></div>

<div class="card-new" style="margin-bottom:14px;">
  <div class="grid">
    <div>
      <div class="texto" style="margin:0;"><strong>ID:</strong> #<?php echo (int)($servidor['id']??0); ?></div>
      <div class="texto" style="margin:0;"><strong>IP:</strong> <?php echo View::e((string)($servidor['ip_address']??'')); ?></div>
    </div>
    <div>
      <div class="texto" style="margin:0;"><strong>Status:</strong> <?php echo View::e((string)($servidor['status']??'')); ?></div>
      <div class="texto" style="margin:0;"><strong>Coletas:</strong> <?php echo (int)count($metricas??[]); ?></div>
    </div>
  </div>
</div>

<div class="card-new">
  <div class="card-new-title">Ultimas metricas</div>
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>Data</th><th>CPU</th><th>RAM</th><th>Disco</th></tr>
      </thead>
      <tbody>
        <?php foreach (($metricas??[]) as $m): ?>
          <tr>
            <td><?php echo View::e((string)($m['timestamp']??'')); ?></td>
            <td><code><?php echo View::e(fmtPctMon($m['cpu_usage']??null)); ?></code></td>
            <td><code><?php echo View::e(fmtPctMon($m['ram_usage']??null)); ?></code></td>
            <td><code><?php echo View::e(fmtPctMon($m['disk_usage']??null)); ?></code></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($metricas)): ?>
          <tr><td colspan="4">Ainda nao ha metricas para este servidor.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
