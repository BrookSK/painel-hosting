<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function badgeIncidentStatusHist(string $st): string
{
    $map = [
        'resolved'     => ['Resolvido', '#dcfce7', '#166534'],
        'monitoring'   => ['Monitorando', '#e0f2fe', '#075985'],
        'identified'   => ['Identificado', '#fef3c7', '#92400e'],
        'investigating'=> ['Investigando', '#fee2e2', '#991b1b'],
    ];
    $d = $map[$st] ?? [$st, '#f1f5f9', '#334155'];
    return '<span class="badge" style="background:' . $d[1] . ';color:' . $d[2] . ';">' . View::e($d[0]) . '</span>';
}

function badgeImpactHist(string $impact): string
{
    if ($impact === 'critical') return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Crítico</span>';
    if ($impact === 'major')    return '<span class="badge" style="background:#fef3c7;color:#92400e;">Alto</span>';
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">Baixo</span>';
}

$incidentes = is_array($incidentes ?? ($incidents ?? null)) ? ($incidentes ?? $incidents) : [];
$pagina     = (int) ($pagina ?? 1);
$totalPags  = (int) ($totalPaginas ?? 1);

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Histórico de incidentes</title>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Histórico de incidentes</div>
        <div style="opacity:.9; font-size:13px;">Todos os incidentes registrados</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/_partials/idioma.php'; ?>
        <a href="/status">Status atual</a>
        <a href="/">Início</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <?php if (empty($incidentes)): ?>
      <div class="card">
        <div class="texto" style="margin:0;">Nenhum incidente registrado.</div>
      </div>
    <?php else: ?>
      <div style="display:flex; flex-direction:column; gap:12px;">
        <?php foreach ($incidentes as $inc): ?>
          <?php if (!is_array($inc)) continue; ?>
          <div class="card">
            <div class="linha" style="justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
              <div>
                <div style="font-weight:700; font-size:15px;"><?php echo View::e((string) ($inc['title'] ?? '')); ?></div>
                <div style="font-size:12px; opacity:.8; margin-top:2px;">
                  Início: <?php echo View::e((string) ($inc['started_at'] ?? '')); ?>
                  <?php if (trim((string) ($inc['resolved_at'] ?? '')) !== ''): ?>
                    &nbsp;|&nbsp; Resolvido: <?php echo View::e((string) ($inc['resolved_at'] ?? '')); ?>
                  <?php endif; ?>
                </div>
              </div>
              <div class="linha" style="gap:6px;">
                <?php echo badgeImpactHist((string) ($inc['impact'] ?? 'minor')); ?>
                <?php echo badgeIncidentStatusHist((string) ($inc['status'] ?? 'investigating')); ?>
              </div>
            </div>

            <?php if (trim((string) ($inc['message'] ?? '')) !== ''): ?>
              <div class="texto" style="margin:0;"><?php echo nl2br(View::e((string) ($inc['message'] ?? ''))); ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPags > 1): ?>
        <div class="linha" style="justify-content:center; margin-top:20px; gap:8px;">
          <?php if ($pagina > 1): ?>
            <a class="botao sec" href="/status/incidentes?pagina=<?php echo $pagina - 1; ?>" style="padding:8px 14px;">← Anterior</a>
          <?php endif; ?>
          <span style="padding:8px 14px; font-size:13px; opacity:.8;">Página <?php echo $pagina; ?> de <?php echo $totalPags; ?></span>
          <?php if ($pagina < $totalPags): ?>
            <a class="botao" href="/status/incidentes?pagina=<?php echo $pagina + 1; ?>" style="padding:8px 14px;">Próxima →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>
</html>
