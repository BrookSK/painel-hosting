<?php
declare(strict_types=1);
use LRV\Core\View;

function fmtPct($v): string {
    if ($v===null||$v==='') return '';
    return number_format((float)$v,2,',','.').'%';
}

$pageTitle = 'Monitoramento';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Monitoramento</div>
<div class="page-subtitle">Ultimas metricas por servidor</div>

<div class="card-new">
  <div class="texto" style="margin-bottom:12px;">Envio de metricas: <code>POST /api/metrics/servers</code> com header <code>x-monitoring-token</code>.</div>
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>Servidor</th><th>IP</th><th>Status</th><th>CPU</th><th>RAM</th><th>Disco</th><th>Coleta</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php foreach (($servidores??[]) as $s): ?>
          <tr>
            <td><strong><?php echo View::e((string)($s['hostname']??'')); ?></strong> <span style="opacity:.7;font-size:12px;">#<?php echo (int)($s['id']??0); ?></span></td>
            <td><?php echo View::e((string)($s['ip_address']??'')); ?></td>
            <td>
              <?php
                $st = (string)($s['status']??'');
                if ($st==='maintenance') echo '<span class="badge-new badge-yellow">Manutencao</span>';
                elseif ($st==='inactive') echo '<span class="badge-new badge-gray">Inativo</span>';
                else echo '<span class="badge-new badge-green">Ativo</span>';
              ?>
            </td>
            <td><code><?php echo View::e(fmtPct($s['cpu_usage']??null)); ?></code></td>
            <td><code><?php echo View::e(fmtPct($s['ram_usage']??null)); ?></code></td>
            <td><code><?php echo View::e(fmtPct($s['disk_usage']??null)); ?></code></td>
            <td><?php echo View::e((string)($s['timestamp']??'')); ?></td>
            <td><a href="/equipe/monitoramento/ver?id=<?php echo (int)($s['id']??0); ?>">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($servidores)): ?>
          <tr><td colspan="8">Nenhum servidor encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
