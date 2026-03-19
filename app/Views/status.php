<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeStatusPublic(string $st): string
{
    if ($st === 'operational') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Operacional</span>';
    }
    if ($st === 'degraded') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Degradado</span>';
    }
    if ($st === 'major_outage') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Indisponível</span>';
    }
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">Desconhecido</span>';
}

function badgeIncidentStatusPublic(string $st): string
{
    if ($st === 'resolved') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Resolvido</span>';
    }
    if ($st === 'monitoring') {
        return '<span class="badge" style="background:#e0f2fe;color:#075985;">Monitorando</span>';
    }
    if ($st === 'identified') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Identificado</span>';
    }
    return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Investigando</span>';
}

function badgeImpactPublic(string $impact): string
{
    if ($impact === 'critical') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Crítico</span>';
    }
    if ($impact === 'major') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Alto</span>';
    }
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">Baixo</span>';
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

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Status</title>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    .bar{display:flex; gap:4px; flex-wrap:nowrap;}
    .bar span{display:inline-block; width:10px; height:10px; border-radius:3px; background:#cbd5e1;}
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Status</div>
        <div style="opacity:.9; font-size:13px;">Página pública de disponibilidade</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/_partials/idioma.php'; ?>
        <a href="/cliente/entrar">Cliente</a>
        <a href="/equipe/entrar">Equipe</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="margin-bottom:14px;">
      <div class="linha" style="justify-content:space-between;">
        <div>
          <div class="titulo" style="font-size:16px;margin-bottom:6px;">Status geral</div>
          <div class="texto" style="margin:0;">Atualização automática a cada 30s.</div>
        </div>
        <div>
          <?php echo badgeStatusPublic($geral); ?>
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Serviços</h2>

      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Serviço</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Uptime (24h)</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Histórico</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Última checagem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($servicesArr as $s): ?>
              <?php if (!is_array($s)) continue; ?>
              <?php $sid = (int) ($s['id'] ?? 0); ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div><strong><?php echo View::e((string) ($s['name'] ?? '')); ?></strong></div>
                  <?php if (trim((string) ($s['description'] ?? '')) !== ''): ?>
                    <div style="font-size:12px; opacity:.8; margin-top:2px;"><?php echo View::e((string) ($s['description'] ?? '')); ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo badgeStatusPublic((string) ($s['status'] ?? 'unknown')); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php
                    $u = $uptime24[$sid] ?? null;
                    $uTxt = $u === null ? '' : number_format((float) $u, 2, ',', '.') . '%';
                  ?>
                  <code><?php echo View::e($uTxt); ?></code>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div class="bar">
                    <?php foreach (($bars[$sid] ?? []) as $b): ?>
                      <span title="<?php echo View::e((string) $b); ?>" style="background:<?php echo View::e(corBar((string) $b)); ?>"></span>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($s['last_check_at'] ?? '')); ?></code></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($servicesArr)): ?>
              <tr>
                <td colspan="5" style="padding:12px;">Ainda não há serviços públicos configurados/coletados.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Incidentes</h2>

      <?php if (empty($incidents)): ?>
        <div class="texto" style="margin:0;">Nenhum incidente recente.</div>
      <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:10px;">
          <?php foreach (($incidents ?? []) as $inc): ?>
            <?php if (!is_array($inc)) continue; ?>
            <?php $iid = (int) ($inc['id'] ?? 0); ?>
            <?php $svc = $incidentServicesMap[$iid] ?? []; ?>
            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px;">
              <div class="linha" style="justify-content:space-between;">
                <div>
                  <div><strong><?php echo View::e((string) ($inc['title'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8; margin-top:2px;">Início: <?php echo View::e((string) ($inc['started_at'] ?? '')); ?><?php if (trim((string) ($inc['resolved_at'] ?? '')) !== ''): ?> | Resolvido: <?php echo View::e((string) ($inc['resolved_at'] ?? '')); ?><?php endif; ?></div>
                </div>
                <div class="linha">
                  <?php echo badgeImpactPublic((string) ($inc['impact'] ?? 'minor')); ?>
                  <?php echo badgeIncidentStatusPublic((string) ($inc['status'] ?? 'investigating')); ?>
                </div>
              </div>

              <?php if (trim((string) ($inc['message'] ?? '')) !== ''): ?>
                <div class="texto" style="margin:10px 0 0 0;"><?php echo nl2br(View::e((string) ($inc['message'] ?? ''))); ?></div>
              <?php endif; ?>

              <?php if (!empty($svc)): ?>
                <div style="margin-top:10px;">
                  <div class="texto" style="margin:0 0 6px 0;"><strong>Serviços afetados</strong></div>
                  <div class="linha">
                    <?php foreach ($svc as $sv): ?>
                      <?php if (!is_array($sv)) continue; ?>
                      <span class="badge" style="background:#f1f5f9;color:#334155;"><?php echo View::e((string) ($sv['name'] ?? '')); ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <?php $up = $updates[$iid] ?? []; ?>
              <?php if (!empty($up)): ?>
                <div style="margin-top:10px;">
                  <div class="texto" style="margin:0 0 6px 0;"><strong>Atualizações</strong></div>
                  <div style="display:flex; flex-direction:column; gap:6px;">
                    <?php foreach ($up as $u): ?>
                      <?php if (!is_array($u)) continue; ?>
                      <div style="border-left:3px solid #e5e7eb; padding-left:10px;">
                        <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($u['created_at'] ?? '')); ?> - <?php echo View::e((string) ($u['status'] ?? '')); ?></div>
                        <div class="texto" style="margin:2px 0 0 0;"><?php echo nl2br(View::e((string) ($u['message'] ?? ''))); ?></div>
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
  </div>

  <script>
    setTimeout(function(){
      try { window.location.reload(); } catch(e) {}
    }, 30000);
  </script>
</body>
</html>
