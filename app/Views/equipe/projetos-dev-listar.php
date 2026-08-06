<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = I18n::t('dev_workflow.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('dev_workflow.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('dev_workflow.subtitulo')); ?></div>

<?php if (!empty($prsPendentes)): ?>
<div class="card-new" style="margin-bottom:20px;border-left:4px solid #f59e0b;background:#fffbeb;">
  <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M12 9v4M12 17h.01M4.93 19h14.14a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.2 16a2 2 0 001.73 3z"/></svg>
    <div>
      <strong style="color:#92400e;"><?php echo count($prsPendentes); ?> Pull Request(s) aguardando aprovação</strong>
      <div style="font-size:13px;color:#78350f;margin-top:2px;">
        <?php foreach (array_slice($prsPendentes, 0, 3) as $pr): ?>
          <a href="/equipe/dev/demanda?id=<?php echo (int)($pr['id'] ?? 0); ?>" style="color:#b45309;text-decoration:underline;">
            <?php echo View::e((string)($pr['title'] ?? '')); ?> (<?php echo View::e((string)($pr['project_name'] ?? '')); ?>)
          </a><br>
        <?php endforeach; ?>
        <?php if (count($prsPendentes) > 3): ?>
          <span style="color:#92400e;">...e mais <?php echo count($prsPendentes) - 3; ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;"><?php echo View::e(I18n::t('dev_workflow.projetos_desc')); ?></span>
  <a class="botao" href="/equipe/dev/projeto/novo"><?php echo View::e(I18n::t('dev_workflow.novo_projeto')); ?></a>
</div>

<?php if (empty($projetos)): ?>
<div class="card-new" style="padding:40px;text-align:center;">
  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 12px;display:block;"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
  <p style="color:#64748b;margin:0;"><?php echo View::e(I18n::t('dev_workflow.nenhum_projeto')); ?></p>
  <a href="/equipe/dev/projeto/novo" class="botao" style="margin-top:16px;"><?php echo View::e(I18n::t('dev_workflow.criar_primeiro_projeto')); ?></a>
</div>
<?php else: ?>
<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th><?php echo View::e(I18n::t('dev_workflow.projeto')); ?></th>
          <th><?php echo View::e(I18n::t('dev_workflow.repositorio')); ?></th>
          <th><?php echo View::e(I18n::t('dev_workflow.dominio_teste')); ?></th>
          <th><?php echo View::e(I18n::t('dev_workflow.demandas_abertas')); ?></th>
          <th><?php echo View::e(I18n::t('dev_workflow.prs_pendentes')); ?></th>
          <th><?php echo View::e(I18n::t('geral.acoes')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projetos as $p):
          $pid = (int)($p['id'] ?? 0);
          $nome = (string)($p['name'] ?? '');
          $repo = (string)($p['repo_url'] ?? '');
          $repoShort = basename(str_replace('.git', '', $repo));
          $domain = (string)($p['temp_domain'] ?? '');
          $openDemands = (int)($p['open_demands'] ?? 0);
          $pendingPrs = (int)($p['pending_prs'] ?? 0);
        ?>
          <tr>
            <td>
              <strong><?php echo View::e($nome); ?></strong>
              <?php if (!empty($p['description'])): ?>
                <br><span style="font-size:12px;color:#64748b;"><?php echo View::e(mb_substr((string)$p['description'], 0, 60)); ?></span>
              <?php endif; ?>
            </td>
            <td>
              <span style="font-size:13px;font-family:monospace;color:#475569;" title="<?php echo View::e($repo); ?>">
                <?php echo View::e($repoShort); ?>
              </span>
              <br><span class="badge-new badge-gray" style="font-size:10px;"><?php echo View::e((string)($p['default_branch'] ?? 'main')); ?></span>
            </td>
            <td>
              <?php if ($domain !== ''): ?>
                <a href="https://<?php echo View::e($domain); ?>" target="_blank" style="font-size:13px;color:#4F46E5;"><?php echo View::e($domain); ?></a>
              <?php else: ?>
                <span style="color:#94a3b8;font-size:12px;">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($openDemands > 0): ?>
                <span class="badge-new badge-blue"><?php echo $openDemands; ?></span>
              <?php else: ?>
                <span style="color:#94a3b8;">0</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($pendingPrs > 0): ?>
                <span class="badge-new badge-yellow"><?php echo $pendingPrs; ?></span>
              <?php else: ?>
                <span style="color:#94a3b8;">0</span>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <a href="/equipe/dev/demandas?projeto=<?php echo $pid; ?>" class="botao botao-sm" title="Ver demandas">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Demandas
              </a>
              <a href="/equipe/dev/projeto/editar?id=<?php echo $pid; ?>" style="margin-left:6px;font-size:13px;">Editar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
