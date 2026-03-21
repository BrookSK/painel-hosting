<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusJob(string $st): string {
    if ($st==='completed') return '<span class="badge-new badge-green">' . View::e(I18n::t('eq_jobs.concluido')) . '</span>';
    if ($st==='failed')    return '<span class="badge-new badge-red">' . View::e(I18n::t('eq_jobs.falhou')) . '</span>';
    if ($st==='running')   return '<span class="badge-new badge-blue">' . View::e(I18n::t('eq_jobs.rodando')) . '</span>';
    return '<span class="badge-new badge-yellow">' . View::e(I18n::t('eq_jobs.pendente')) . '</span>';
}

$pageTitle = 'Jobs';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('eq_jobs.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_jobs.subtitulo')); ?></div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th><?php echo View::e(I18n::t('eq_jobs.id')); ?></th><th><?php echo View::e(I18n::t('eq_jobs.tipo')); ?></th><th><?php echo View::e(I18n::t('geral.status')); ?></th><th><?php echo View::e(I18n::t('eq_jobs.criado')); ?></th><th><?php echo View::e(I18n::t('eq_jobs.atualizado')); ?></th><th><?php echo View::e(I18n::t('geral.acoes')); ?></th></tr>
      </thead>
      <tbody>
        <?php foreach (($jobs??[]) as $j): ?>
          <tr>
            <td><strong>#<?php echo (int)($j['id']??0); ?></strong></td>
            <td><?php echo View::e((string)($j['type']??'')); ?></td>
            <td><?php echo badgeStatusJob((string)($j['status']??'pending')); ?></td>
            <td><?php echo View::e((string)($j['created_at']??'')); ?></td>
            <td><?php echo View::e((string)($j['updated_at']??'')); ?></td>
            <td><a href="/equipe/jobs/ver?id=<?php echo (int)($j['id']??0); ?>"><?php echo View::e(I18n::t('geral.ver')); ?></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($jobs)): ?>
          <tr><td colspan="6"><?php echo View::e(I18n::t('eq_jobs.nenhum')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<div style="margin-top:12px;" class="texto"><?php echo I18n::t('eq_jobs.dica'); ?></div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
