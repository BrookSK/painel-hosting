<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;

function badgeIncidentStatusHist(string $st): string
{
    $map = [
        'resolved'     => [I18n::t('status_page.resolvido_badge'), '#dcfce7', '#166534'],
        'monitoring'   => [I18n::t('status_page.monitorando'), '#e0f2fe', '#075985'],
        'identified'   => [I18n::t('status_page.identificado'), '#fef3c7', '#92400e'],
        'investigating'=> [I18n::t('status_page.investigando'), '#fee2e2', '#991b1b'],
    ];
    $d = $map[$st] ?? [$st, '#f1f5f9', '#334155'];
    return '<span class="badge" style="background:' . $d[1] . ';color:' . $d[2] . ';">' . View::e($d[0]) . '</span>';
}
function badgeImpactHist(string $impact): string
{
    if ($impact === 'critical') return '<span class="badge" style="background:#fee2e2;color:#991b1b;">' . View::e(I18n::t('status_page.critico')) . '</span>';
    if ($impact === 'major')    return '<span class="badge" style="background:#fef3c7;color:#92400e;">' . View::e(I18n::t('status_page.alto')) . '</span>';
    return '<span class="badge" style="background:#f1f5f9;color:#334155;">' . View::e(I18n::t('status_page.baixo')) . '</span>';
}

$incidentes = is_array($incidentes ?? ($incidents ?? null)) ? ($incidentes ?? $incidents) : [];
$pagina     = (int) ($pagina ?? 1);
$totalPags  = (int) ($totalPaginas ?? 1);
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo View::e(I18n::t('status_inc.titulo')); ?> — <?php echo View::e(SistemaConfig::nome()); ?></title>
<?php require __DIR__ . '/_partials/estilo.php'; ?>
<style>
body{background:#060d1f}
</style>
</head>
<body>
<?php require __DIR__ . '/_partials/navbar-publica.php'; ?>

<style>
.pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden}
.pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.pub-page-hero-inner{position:relative;max-width:600px;margin:0 auto}
.pub-page-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#a78bfa;margin-bottom:12px}
.pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:10px}
.pub-page-sub{font-size:15px;color:rgba(255,255,255,.6);line-height:1.7}
.inc-wrap{max-width:900px;margin:0 auto;padding:40px 24px 72px}
.inc-card{background:#fff;border-radius:18px;padding:24px;margin-bottom:14px;box-shadow:0 4px 24px rgba(0,0,0,.2)}
.inc-header{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;margin-bottom:8px}
.inc-title{font-weight:700;font-size:15px;color:#0f172a}
.inc-meta{font-size:12px;color:#94a3b8;margin-top:2px}
.inc-badges{display:flex;gap:6px;flex-wrap:wrap}
.inc-msg{font-size:13px;color:#475569;line-height:1.65;margin:0}
.inc-empty{background:#fff;border-radius:18px;padding:48px 24px;box-shadow:0 4px 24px rgba(0,0,0,.2);text-align:center;color:#94a3b8;font-size:14px}
.inc-empty-icon{font-size:32px;margin-bottom:10px}
.inc-pag{display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;flex-wrap:wrap}
.inc-pag-info{font-size:13px;color:rgba(255,255,255,.5);padding:8px 14px}
@media(max-width:640px){.inc-wrap{padding:24px 16px 48px}.inc-card{padding:18px}}
</style>

<div class="pub-page-hero">
  <div class="pub-page-hero-inner">
    <div class="pub-page-label"><?php echo View::e(I18n::t('status_page.label')); ?></div>
    <h1 class="pub-page-title"><?php echo View::e(I18n::t('status_inc.titulo')); ?></h1>
    <p class="pub-page-sub"><?php echo View::e(I18n::t('status_inc.sub')); ?></p>
  </div>
</div>

<div class="inc-wrap">
  <?php if (empty($incidentes)): ?>
    <div class="inc-empty">
      <div class="inc-empty-icon">✅</div>
      <?php echo View::e(I18n::t('status_inc.nenhum')); ?>
    </div>
  <?php else: ?>
    <?php foreach ($incidentes as $inc): ?>
      <?php if (!is_array($inc)) continue; ?>
      <div class="inc-card">
        <div class="inc-header">
          <div>
            <div class="inc-title"><?php echo View::e((string) ($inc['title'] ?? '')); ?></div>
            <div class="inc-meta">
              <?php echo View::e(I18n::t('status_page.inicio')); ?> <?php echo View::e((string) ($inc['started_at'] ?? '')); ?>
              <?php if (trim((string) ($inc['resolved_at'] ?? '')) !== ''): ?>
                &nbsp;·&nbsp; <?php echo View::e(I18n::t('status_page.resolvido')); ?> <?php echo View::e((string) ($inc['resolved_at'] ?? '')); ?>
              <?php endif; ?>
            </div>
          </div>
          <div class="inc-badges">
            <?php echo badgeImpactHist((string) ($inc['impact'] ?? 'minor')); ?>
            <?php echo badgeIncidentStatusHist((string) ($inc['status'] ?? 'investigating')); ?>
          </div>
        </div>
        <?php if (trim((string) ($inc['message'] ?? '')) !== ''): ?>
          <p class="inc-msg"><?php echo nl2br(View::e((string) ($inc['message'] ?? ''))); ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <?php if ($totalPags > 1): ?>
    <div class="inc-pag">
      <?php if ($pagina > 1): ?>
        <a class="botao sec" href="/status/incidentes?pagina=<?php echo $pagina - 1; ?>" style="padding:8px 14px"><?php echo View::e(I18n::t('status_inc.anterior')); ?></a>
      <?php endif; ?>
      <span class="inc-pag-info"><?php echo View::e(I18n::tf('status_inc.pagina', $pagina, $totalPags)); ?></span>
      <?php if ($pagina < $totalPags): ?>
        <a class="botao" href="/status/incidentes?pagina=<?php echo $pagina + 1; ?>" style="padding:8px 14px"><?php echo View::e(I18n::t('status_inc.proxima')); ?></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
