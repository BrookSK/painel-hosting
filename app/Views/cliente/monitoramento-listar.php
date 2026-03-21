<?php
declare(strict_types=1);
use LRV\Core\View;

function fmtPctCli($v): string {
    if ($v === null || $v === '') return '';
    return number_format((float)$v, 2, ',', '.') . '%';
}

$pageTitle    = 'Monitoramento';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title">Monitoramento</div>
  <div class="page-subtitle" style="margin-bottom:0;">Última métrica por VPS</div>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">VPS</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Servidor</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">CPU</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">RAM</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Disco</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Coleta</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($linhas ?? []) as $l): ?>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int)($l['vps_id'] ?? 0); ?></strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($l['hostname'] ?? '')); ?> <span style="opacity:.7;font-size:12px;">(<?php echo View::e((string)($l['ip_address'] ?? '')); ?>)</span></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli($l['cpu_usage'] ?? null)); ?></code></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli($l['ram_usage'] ?? null)); ?></code></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli($l['disk_usage'] ?? null)); ?></code></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($l['timestamp'] ?? '')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><a href="/cliente/monitoramento/ver?vps_id=<?php echo (int)($l['vps_id'] ?? 0); ?>">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($linhas)): ?>
          <tr><td colspan="7" style="padding:12px;color:#94a3b8;">Nenhuma VPS encontrada.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
