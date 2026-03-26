<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$vps = $vps ?? [];
$logs = $logs ?? [];
$auditLogs = $audit_logs ?? [];
$vid = (int)($vps['id'] ?? 0);

$pageTitle = 'Logs VPS #' . $vid;
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
  <div>
    <div class="page-title">Logs — VPS #<?php echo $vid; ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">
      <?php echo View::e((string)($vps['client_name'] ?? '')); ?> · Status: <?php echo View::e((string)($vps['status'] ?? '')); ?>
      · <?php echo (int)($vps['cpu'] ?? 0); ?> vCPU / <?php echo round((int)($vps['ram'] ?? 0) / 1024); ?>GB RAM
    </div>
  </div>
  <a href="/equipe/vps" class="botao ghost sm">← Voltar</a>
</div>

<!-- Job logs -->
<div class="card-new" style="margin-bottom:16px;">
  <div class="card-new-title" style="margin-bottom:12px;">📋 Jobs de provisionamento</div>
  <?php if (empty($logs)): ?>
    <p style="color:#94a3b8;font-size:13px;">Nenhum job encontrado para esta VPS. O worker pode não estar rodando (<code>php worker.php</code>).</p>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($logs as $l): ?>
        <div style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:6px;">
            <div>
              <span class="badge-new badge-blue" style="font-size:11px;"><?php echo View::e((string)($l['type'] ?? '')); ?></span>
              <span class="badge-new <?php echo ($l['status'] ?? '') === 'completed' ? 'badge-green' : (($l['status'] ?? '') === 'failed' ? 'badge-red' : 'badge-yellow'); ?>" style="font-size:11px;">
                <?php echo View::e((string)($l['status'] ?? '')); ?>
              </span>
            </div>
            <div style="font-size:11px;color:#94a3b8;">
              <?php echo View::e((string)($l['created_at'] ?? '')); ?>
              <?php if (!empty($l['started_at'])): ?> → <?php echo View::e((string)$l['started_at']); ?><?php endif; ?>
              <?php if (!empty($l['finished_at'])): ?> → <?php echo View::e((string)$l['finished_at']); ?><?php endif; ?>
            </div>
          </div>
          <?php $output = trim((string)($l['output'] ?? '')); ?>
          <?php if ($output !== ''): ?>
            <pre style="background:#0b1220;color:#e2e8f0;padding:10px 12px;border-radius:8px;font-size:12px;line-height:1.6;overflow:auto;max-height:300px;white-space:pre-wrap;"><?php echo View::e($output); ?></pre>
          <?php else: ?>
            <p style="font-size:12px;color:#94a3b8;font-style:italic;">Sem output registrado.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Audit logs -->
<?php if (!empty($auditLogs)): ?>
<div class="card-new">
  <div class="card-new-title" style="margin-bottom:12px;">🔍 Audit log</div>
  <div style="overflow:auto;">
    <table style="font-size:13px;">
      <thead>
        <tr>
          <th>Ação</th><th>Ator</th><th>Detalhes</th><th>Data</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($auditLogs as $a): ?>
          <tr>
            <td><code><?php echo View::e((string)($a['action'] ?? '')); ?></code></td>
            <td><?php echo View::e((string)($a['actor_type'] ?? '')); ?> #<?php echo (int)($a['actor_id'] ?? 0); ?></td>
            <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;font-size:11px;color:#64748b;">
              <?php echo View::e(substr((string)($a['details'] ?? ''), 0, 200)); ?>
            </td>
            <td style="white-space:nowrap;"><?php echo View::e((string)($a['created_at'] ?? '')); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
