<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeStatusPublic(string $st): string
{
    if ($st === 'operational') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">' . View::e(I18n::t('status_page.operacional')) . '</span>';
    }
    if ($st === 'degraded') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">' . View::e(I18n::t('status_page.degradado')) . '</span>';
    }
    if ($st === 'major_outage') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">' . View::e(I18n::t('status_page.indisponivel')) . '</span>';
    }
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">' . View::e(I18n::t('status_page.desconhecido')) . '</span>';
}

function badgeIncidentStatusPublic(string $st): string
{
    if ($st === 'resolved') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">' . View::e(I18n::t('status_page.resolvido_badge')) . '</span>';
    }
    if ($st === 'monitoring') {
        return '<span class="badge" style="background:#e0f2fe;color:#075985;">' . View::e(I18n::t('status_page.monitorando')) . '</span>';
    }
    if ($st === 'identified') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">' . View::e(I18n::t('status_page.identificado')) . '</span>';
    }
    return '<span class="badge" style="background:#fee2e2;color:#991b1b;">' . View::e(I18n::t('status_page.investigando')) . '</span>';
}

function badgeImpactPublic(string $impact): string
{
    if ($impact === 'critical') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">' . View::e(I18n::t('status_page.critico')) . '</span>';
    }
    if ($impact === 'major') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">' . View::e(I18n::t('status_page.alto')) . '</span>';
    }
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">' . View::e(I18n::t('status_page.baixo')) . '</span>';
}

function corBar(string $st): string
{
    if ($st === 'operational') {
        return '#22c55e';
    }
    if ($st === 'degraded') {
        return '#f59e0b';
    }
    if ($st === 'major_outage') {
        return '#ef4444';
    }
    return '#cbd5e1';
}

$servicesArr = is_array($services ?? null) ? $services : [];
$incidentServicesMap = is_array($incidentServices ?? null) ? $incidentServices : [];

$geral = 'unknown';
foreach ($servicesArr as $s) {
    if (!is_array($s)) {
        continue;
    }
    $st = (string) ($s['status'] ?? 'unknown');
    if ($st === 'major_outage') {
        $geral = 'major_outage';
        break;
    }
    if ($st === 'degraded' && $geral !== 'major_outage') {
        $geral = 'degraded';
    }
    if ($st === 'operational' && $geral === 'unknown') {
        $geral = 'operational';
    }
}

$_geral_color = match($geral) {
    'operational' => '#22c55e',
    'degraded'    => '#f59e0b',
    'major_outage'=> '#ef4444',
    default       => '#94a3b8',
};
$_geral_label = match($geral) {
    'operational' => I18n::t('status.todos_operacionais'),
    'degraded'    => I18n::t('status.degradacao'),
    'major_outage'=> I18n::t('status.indisponivel'),
    default       => I18n::t('status.desconhecido'),
};
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php $seo_titulo = 'Status — ' . \LRV\Core\SistemaConfig::nome(); require __DIR__ . '/_partials/seo.php'; ?>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;}
    .pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden;}
    .pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .pub-page-hero-inner{position:relative;max-width:700px;margin:0 auto;}
    .pub-page-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#a78bfa;margin-bottom:12px;}
    .pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:16px;}
    .status-overall{display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);padding:10px 20px;border-radius:999px;font-size:14px;font-weight:600;color:#fff;backdrop-filter:blur(8px);}
    .status-overall-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
    .status-content{max-width:900px;margin:0 auto;padding:40px 24px 72px;}
    .status-card{background:#fff;border-radius:18px;padding:28px;margin-bottom:20px;box-shadow:0 4px 24px rgba(0,0,0,.2);}
    .status-card-title{font-size:15px;font-weight:800;color:#0f172a;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
    .bar{display:flex;gap:3px;flex-wrap:nowrap;}
    .bar span{display:inline-block;width:10px;height:10px;border-radius:3px;background:#cbd5e1;}
    .incident-item{border:1px solid #e2e8f0;border-radius:14px;padding:18px;margin-bottom:12px;}
    .incident-item:last-child{margin-bottom:0;}
    .incident-update{border-left:3px solid #e2e8f0;padding-left:12px;margin-top:8px;}
    @media(max-width:640px){.status-content{padding:24px 16px 48px}.status-card{padding:18px}.status-card-title{flex-wrap:wrap;gap:6px}.status-card-title span{margin-left:0;width:100%}}
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/navbar-publica.php'; ?>

  <div class="pub-page-hero">
    <div class="pub-page-hero-inner">
      <div class="pub-page-label"><?php echo View::e(I18n::t('status_page.label')); ?></div>
      <h1 class="pub-page-title"><?php echo View::e(I18n::t('status_page.titulo')); ?></h1>
      <div class="status-overall">
        <span class="status-overall-dot" style="background:<?php echo View::e($_geral_color); ?>;box-shadow:0 0 0 3px <?php echo View::e($_geral_color); ?>33;"></span>
        <?php echo View::e($_geral_label); ?>
      </div>
    </div>
  </div>

  <div class="status-content">
    <div class="status-card">
      <div class="status-card-title">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="4" width="14" height="4" rx="1.5" stroke="#4F46E5" stroke-width="1.4"/><rect x="1" y="10" width="14" height="4" rx="1.5" stroke="#4F46E5" stroke-width="1.4"/><circle cx="12" cy="6" r="1" fill="#4F46E5"/><circle cx="12" cy="12" r="1" fill="#4F46E5"/></svg>
        <?php echo View::e(I18n::t('status_page.servicos')); ?>
        <span style="margin-left:auto;font-size:12px;font-weight:400;color:#94a3b8;"><?php echo View::e(I18n::t('status_page.auto_refresh')); ?></span>
      </div>
      <div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th><?php echo View::e(I18n::t('status_page.th_servico')); ?></th>
              <th><?php echo View::e(I18n::t('status_page.th_status')); ?></th>
              <th><?php echo View::e(I18n::t('status_page.th_uptime')); ?></th>
              <th><?php echo View::e(I18n::t('status_page.th_historico')); ?></th>
              <th><?php echo View::e(I18n::t('status_page.th_checagem')); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($servicesArr as $s): ?>
              <?php if (!is_array($s)) continue; ?>
              <?php $sid = (int) ($s['id'] ?? 0); ?>
              <tr>
                <td>
                  <div style="font-weight:600;font-size:14px;"><?php echo View::e((string) ($s['name'] ?? '')); ?></div>
                  <?php if (trim((string) ($s['description'] ?? '')) !== ''): ?>
                    <div style="font-size:12px;color:#94a3b8;margin-top:2px;"><?php echo View::e((string) ($s['description'] ?? '')); ?></div>
                  <?php endif; ?>
                </td>
                <td><?php echo badgeStatusPublic((string) ($s['status'] ?? 'unknown')); ?></td>
                <td>
                  <?php $u = $uptime24[$sid] ?? null; ?>
                  <code style="font-size:13px;"><?php echo $u === null ? '—' : View::e(number_format((float) $u, 2, ',', '.') . '%'); ?></code>
                </td>
                <td>
                  <div class="bar">
                    <?php foreach (($bars[$sid] ?? []) as $b): ?>
                      <span title="<?php echo View::e((string) $b); ?>" style="background:<?php echo View::e(corBar((string) $b)); ?>"></span>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td><code style="font-size:12px;color:#64748b;"><?php echo View::e((string) ($s['last_check_at'] ?? '')); ?></code></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($servicesArr)): ?>
              <tr><td colspan="5" style="color:#94a3b8;text-align:center;padding:24px;"><?php echo View::e(I18n::t('status_page.nenhum_servico')); ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="status-card">
      <div class="status-card-title">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="#4F46E5" stroke-width="1.4"/><path d="M8 5v4" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round"/><circle cx="8" cy="11.5" r=".8" fill="#4F46E5"/></svg>
        <?php echo View::e(I18n::t('status_page.incidentes')); ?>
      </div>
      <?php if (empty($incidents)): ?>
        <div style="text-align:center;padding:24px;color:#94a3b8;font-size:14px;">
          <div style="font-size:28px;margin-bottom:8px;">✅</div>
          <?php echo View::e(I18n::t('status_page.nenhum_incidente')); ?>
        </div>
      <?php else: ?>
        <?php foreach (($incidents ?? []) as $inc): ?>
          <?php if (!is_array($inc)) continue; ?>
          <?php $iid = (int) ($inc['id'] ?? 0); ?>
          <?php $svc = $incidentServicesMap[$iid] ?? []; ?>
          <div class="incident-item">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
              <div>
                <div style="font-weight:700;font-size:14px;color:#0f172a;"><?php echo View::e((string) ($inc['title'] ?? '')); ?></div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">
                  <?php echo View::e(I18n::t('status_page.inicio')); ?> <?php echo View::e((string) ($inc['started_at'] ?? '')); ?>
                  <?php if (trim((string) ($inc['resolved_at'] ?? '')) !== ''): ?> · <?php echo View::e(I18n::t('status_page.resolvido')); ?> <?php echo View::e((string) ($inc['resolved_at'] ?? '')); ?><?php endif; ?>
                </div>
              </div>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php echo badgeImpactPublic((string) ($inc['impact'] ?? 'minor')); ?>
                <?php echo badgeIncidentStatusPublic((string) ($inc['status'] ?? 'investigating')); ?>
              </div>
            </div>
            <?php if (trim((string) ($inc['message'] ?? '')) !== ''): ?>
              <p style="font-size:13px;color:#475569;line-height:1.65;margin-bottom:8px;"><?php echo nl2br(View::e((string) ($inc['message'] ?? ''))); ?></p>
            <?php endif; ?>
            <?php if (!empty($svc)): ?>
              <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
                <?php foreach ($svc as $sv): ?>
                  <?php if (!is_array($sv)) continue; ?>
                  <span class="badge badge-cinza"><?php echo View::e((string) ($sv['name'] ?? '')); ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <?php $up = $updates[$iid] ?? []; ?>
            <?php if (!empty($up)): ?>
              <div style="display:flex;flex-direction:column;gap:8px;margin-top:10px;">
                <?php foreach ($up as $u): ?>
                  <?php if (!is_array($u)) continue; ?>
                  <div class="incident-update">
                    <div style="font-size:11px;color:#94a3b8;margin-bottom:3px;"><?php echo View::e((string) ($u['created_at'] ?? '')); ?> · <?php echo View::e((string) ($u['status'] ?? '')); ?></div>
                    <div style="font-size:13px;color:#475569;"><?php echo nl2br(View::e((string) ($u['message'] ?? ''))); ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <script>setTimeout(function(){try{window.location.reload();}catch(e){}},30000);</script>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
