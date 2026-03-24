<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusAplicacao(string $st): string {
    $map = [
        'inactive'  => ['badge-gray',  I18n::t('eq_apps.inativa')],
        'deploying' => ['badge-blue',  I18n::t('eq_apps.deploy')],
        'error'     => ['badge-red',   I18n::t('eq_apps.erro')],
        'running'   => ['badge-green', I18n::t('eq_apps.ativa')],
        'active'    => ['badge-green', I18n::t('eq_apps.ativa')],
    ];
    $d = $map[$st] ?? ['badge-gray', View::e($st)];
    return '<span class="badge-new ' . $d[0] . '">' . $d[1] . '</span>';
}

$pageTitle = I18n::t('eq_apps.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('eq_apps.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_apps.subtitulo')); ?></div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;"><?php echo View::e(I18n::t('eq_apps.desc')); ?></span>
  <a class="botao" href="/equipe/aplicacoes/novo"><?php echo View::e(I18n::t('eq_apps.nova')); ?></a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>VPS</th><th><?php echo View::e(I18n::t('eq_apps.cliente')); ?></th>
          <th><?php echo View::e(I18n::t('eq_apps.tipo')); ?></th>
          <th><?php echo View::e(I18n::t('eq_apps.dominio')); ?></th>
          <th><?php echo View::e(I18n::t('eq_apps.porta')); ?></th>
          <th><?php echo View::e(I18n::t('eq_apps.status')); ?></th>
          <th><?php echo View::e(I18n::t('eq_apps.acoes')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($aplicacoes??[]) as $a): ?>
          <tr>
            <td><strong>#<?php echo (int)($a['id']??0); ?></strong></td>
            <td>#<?php echo (int)($a['vps_id']??0); ?></td>
            <td><?php echo View::e((string)($a['client_email']??'')); ?></td>
            <td><code><?php echo View::e((string)($a['type']??'')); ?></code></td>
            <td><?php echo View::e((string)($a['domain']??'')); ?></td>
            <td><code><?php echo View::e((string)($a['port']??'')); ?></code></td>
            <td><?php echo badgeStatusAplicacao((string)($a['status']??'active')); ?></td>
            <td>
              <a href="/equipe/aplicacoes/editar?id=<?php echo (int)($a['id']??0); ?>"><?php echo View::e(I18n::t('geral.editar')); ?></a> |
              <form method="post" action="/equipe/aplicacoes/deploy" style="display:inline;" onsubmit="return confirm('<?php echo View::e(I18n::t('eq_apps.confirmar_deploy')); ?>');">
                <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                <input type="hidden" name="id" value="<?php echo (int)($a['id']??0); ?>" />
                <button class="botao sec" type="submit" style="padding:4px 8px;font-size:12px;">Deploy</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($aplicacoes)): ?>
          <tr><td colspan="8"><?php echo View::e(I18n::t('eq_apps.nenhuma')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Catálogo de templates -->
<?php if (!empty($templates)): ?>
<div style="margin-top:28px;">
  <div class="page-title" style="font-size:18px;"><?php echo View::e(I18n::t('eq_apps.catalogo')); ?></div>
  <p class="texto" style="margin:4px 0 16px;"><?php echo View::e(I18n::t('eq_apps.catalogo_desc')); ?></p>

  <style>
  .tpl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;}
  .tpl-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;display:flex;flex-direction:column;transition:border-color .15s,box-shadow .15s;}
  .tpl-card:hover{border-color:#7C3AED;box-shadow:0 4px 20px rgba(124,58,237,.08);}
  .tpl-icon{font-size:28px;margin-bottom:6px;}
  .tpl-name{font-size:15px;font-weight:700;color:#1e293b;margin-bottom:4px;}
  .tpl-desc{font-size:13px;color:#64748b;line-height:1.5;flex:1;margin-bottom:8px;}
  .tpl-meta{display:flex;gap:6px;flex-wrap:wrap;align-items:center;}
  .tpl-tag{display:inline-block;font-size:11px;padding:2px 8px;border-radius:999px;background:#f1f5f9;color:#475569;font-weight:600;}
  </style>

  <?php
    $catLabels = ['cms'=>'CMS','backend'=>'Backend','database'=>'Database','webserver'=>'Web Server','dev'=>'Dev Tools','other'=>I18n::t('cat.other')];
    $porCat = [];
    foreach ($templates as $t) {
        $cat = (string)($t['category'] ?? 'other');
        $porCat[$cat][] = $t;
    }
  ?>

  <?php foreach ($porCat as $cat => $items): ?>
    <div style="margin-bottom:18px;">
      <div style="font-size:14px;font-weight:700;color:#475569;margin-bottom:8px;"><?php echo View::e($catLabels[$cat] ?? ucfirst($cat)); ?></div>
      <div class="tpl-grid">
        <?php foreach ($items as $t): ?>
          <div class="tpl-card">
            <div class="tpl-icon"><?php echo (string)($t['icon'] ?? '📦'); ?></div>
            <div class="tpl-name"><?php echo View::e((string)($t['name']??'')); ?></div>
            <div class="tpl-desc"><?php echo View::e((string)($t['description']??'')); ?></div>
            <div class="tpl-meta">
              <span class="tpl-tag"><?php echo View::e((string)($t['docker_image']??'')); ?></span>
              <span class="tpl-tag"><?php echo View::e(I18n::t('eq_apps.porta')); ?>: <?php echo View::e((string)($t['default_port']??'')); ?></span>
              <?php if (!empty($t['requires_domain'])): ?><span class="tpl-tag">🌐 <?php echo View::e(I18n::t('eq_apps.requer_dominio')); ?></span><?php endif; ?>
              <?php if (!empty($t['requires_repo'])): ?><span class="tpl-tag">📂 <?php echo View::e(I18n::t('eq_apps.requer_repo')); ?></span><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
