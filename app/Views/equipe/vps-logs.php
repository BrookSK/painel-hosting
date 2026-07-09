<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$vps = $vps ?? [];
$logs = $logs ?? [];
$auditLogs = $audit_logs ?? [];
$vid = (int)($vps['id'] ?? 0);

$pendingJobs = (int)($pending_jobs ?? 0);

$pageTitle = 'Logs VPS #' . $vid;
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
  <div>
    <div class="page-title">Logs — VPS #<?php echo $vid; ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">
      <?php echo View::e((string)($vps['client_name'] ?? '')); ?> · Status: <?php echo View::e((string)($vps['status'] ?? '')); ?>
      · <?php echo (int)($vps['cpu'] ?? 0); ?> vCPU / <?php echo round((int)($vps['ram'] ?? 0) / 1024); ?>GB RAM
      · Node: <?php echo View::e((string)($vps['server_id'] ?? 'nenhum')); ?>
    </div>
  </div>
  <div style="display:flex;gap:8px;">
    <form method="post" action="/equipe/vps/provisionar" style="display:inline;">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>"/>
      <input type="hidden" name="vps_id" value="<?php echo $vid; ?>"/>
      <button class="botao sm" type="submit"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Reprovisionar</button>
    </form>
    <a href="/equipe/vps" class="botao ghost sm">← Voltar</a>
  </div>
</div>

<?php if ($pendingJobs > 0): ?>
<div class="aviso" style="margin-bottom:16px;">
  Existem <strong><?php echo $pendingJobs; ?></strong> job(s) pendente(s) na fila. O worker (<code>php worker.php</code>) precisa estar rodando para processá-los.
  <a href="/equipe/jobs">Ver fila de jobs</a>
</div>
<?php endif; ?>

<!-- Job logs -->
<div class="card-new" style="margin-bottom:16px;">
  <div class="card-new-title" style="margin-bottom:12px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Jobs de provisionamento</div>
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
  <div class="card-new-title" style="margin-bottom:12px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Audit log</div>
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
            <td style="max-width:400px;overflow:hidden;font-size:11px;color:#64748b;">
              <?php
                $details = (string)($a['details'] ?? '');
                $decoded = json_decode($details, true);
                if (is_array($decoded) && isset($decoded['direct_log'])) {
                    echo '<pre style="background:#0b1220;color:#e2e8f0;padding:8px;border-radius:6px;font-size:11px;white-space:pre-wrap;max-height:200px;overflow:auto;margin:0;">' . View::e((string)$decoded['direct_log']) . '</pre>';
                } else {
                    echo View::e(substr($details, 0, 300));
                }
              ?>
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
