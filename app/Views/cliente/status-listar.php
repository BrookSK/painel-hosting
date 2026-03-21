<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeSvcStatusCliente(string $st): string {
    if ($st === 'operational') return '<span class="badge-new badge-green">Operacional</span>';
    if ($st === 'degraded')    return '<span class="badge-new badge-yellow">Degradado</span>';
    if ($st === 'major_outage') return '<span class="badge-new badge-red">Indisponível</span>';
    return '<span class="badge-new" style="background:#f1f5f9;color:#334155;">Desconhecido</span>';
}

function badgeIncidentStatusCliente(string $st): string {
    if ($st === 'resolved')   return '<span class="badge-new badge-green">Resolvido</span>';
    if ($st === 'monitoring') return '<span class="badge-new" style="background:#e0f2fe;color:#075985;">Monitorando</span>';
    if ($st === 'identified') return '<span class="badge-new badge-yellow">Identificado</span>';
    return '<span class="badge-new badge-red">Investigando</span>';
}

function badgeImpactCliente(string $impact): string {
    if ($impact === 'critical') return '<span class="badge-new badge-red">Crítico</span>';
    if ($impact === 'major')    return '<span class="badge-new badge-yellow">Alto</span>';
    return '<span class="badge-new" style="background:#f1f5f9;color:#334155;">Baixo</span>';
}

function corBarCli(string $st): string {
    if ($st === 'operational') return '#22c55e';
    if ($st === 'degraded')    return '#f59e0b';
    if ($st === 'major_outage') return '#ef4444';
    return '#cbd5e1';
}

$servicesArr        = is_array($services ?? null) ? $services : [];
$incidentsArr       = is_array($incidents ?? null) ? $incidents : [];
$incidentServicesMap = is_array($incidentServices ?? null) ? $incidentServices : [];

$pageTitle    = 'Status';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<style>
.bar{display:flex;gap:4px;flex-wrap:nowrap;}
.bar span{display:inline-block;width:10px;height:10px;border-radius:3px;background:#cbd5e1;}
</style>

<div style="margin-bottom:24px;">
  <div class="page-title">Status</div>
  <div class="page-subtitle" style="margin-bottom:0;">Disponibilidade das suas VPS</div>
</div>

<div class="card-new" style="margin-bottom:14px;">
  <div class="card-new-title" style="margin-bottom:12px;">Suas VPS</div>
  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">VPS</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Status</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Uptime (24h)</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Histórico</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Última checagem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($servicesArr as $s): ?>
          <?php if (!is_array($s)) continue; ?>
          <?php $sid = (int)($s['id'] ?? 0); ?>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int)($s['vps_id'] ?? 0); ?></strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeSvcStatusCliente((string)($s['status'] ?? 'unknown')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
              <?php $u = $uptime24[$sid] ?? null; ?>
              <code><?php echo $u === null ? '' : number_format((float)$u, 2, ',', '.') . '%'; ?></code>
            </td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
              <div class="bar">
                <?php foreach (($bars[$sid] ?? []) as $b): ?>
                  <span title="<?php echo View::e((string)$b); ?>" style="background:<?php echo View::e(corBarCli((string)$b)); ?>"></span>
                <?php endforeach; ?>
              </div>
            </td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($s['last_check_at'] ?? '')); ?></code></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($servicesArr)): ?>
          <tr><td colspan="5" style="padding:12px;color:#94a3b8;">Sem dados ainda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card-new">
  <div class="card-new-title" style="margin-bottom:12px;">Incidentes</div>
  <?php if (empty($incidentsArr)): ?>
    <div style="font-size:13px;color:#94a3b8;">Nenhum incidente recente.</div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($incidentsArr as $inc): ?>
        <?php if (!is_array($inc)) continue; ?>
        <?php $iid = (int)($inc['id'] ?? 0); ?>
        <?php $svc = $incidentServicesMap[$iid] ?? []; ?>
        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
            <div>
              <div><strong><?php echo View::e((string)($inc['title'] ?? '')); ?></strong></div>
              <div style="font-size:12px;color:#94a3b8;margin-top:2px;">
                Início: <?php echo View::e((string)($inc['started_at'] ?? '')); ?>
                <?php if (trim((string)($inc['resolved_at'] ?? '')) !== ''): ?> | Resolvido: <?php echo View::e((string)($inc['resolved_at'] ?? '')); ?><?php endif; ?>
              </div>
            </div>
            <div style="display:flex;gap:6px;">
              <?php echo badgeImpactCliente((string)($inc['impact'] ?? 'minor')); ?>
              <?php echo badgeIncidentStatusCliente((string)($inc['status'] ?? 'investigating')); ?>
            </div>
          </div>
          <?php if (trim((string)($inc['message'] ?? '')) !== ''): ?>
            <div style="font-size:13px;color:#475569;margin-bottom:8px;"><?php echo nl2br(View::e((string)($inc['message'] ?? ''))); ?></div>
          <?php endif; ?>
          <?php if (!empty($svc)): ?>
            <div style="margin-bottom:8px;">
              <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Serviços afetados</div>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php foreach ($svc as $sv): ?>
                  <?php if (!is_array($sv)) continue; ?>
                  <span class="badge-new" style="background:#f1f5f9;color:#334155;"><code><?php echo (int)($sv['service_id'] ?? 0); ?></code> <?php echo View::e((string)($sv['name'] ?? '')); ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <?php $up = $updates[$iid] ?? []; ?>
          <?php if (!empty($up)): ?>
            <div>
              <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Atualizações</div>
              <div style="display:flex;flex-direction:column;gap:6px;">
                <?php foreach ($up as $u): ?>
                  <?php if (!is_array($u)) continue; ?>
                  <div style="border-left:3px solid #e5e7eb;padding-left:10px;">
                    <div style="font-size:12px;color:#94a3b8;"><?php echo View::e((string)($u['created_at'] ?? '')); ?> — <?php echo View::e((string)($u['status'] ?? '')); ?></div>
                    <div style="font-size:13px;color:#475569;"><?php echo nl2br(View::e((string)($u['message'] ?? ''))); ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>setTimeout(function(){ try{ window.location.reload(); }catch(e){} }, 30000);</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
