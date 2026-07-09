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
            <div class="tpl-icon"><?php
              $iconMap = [
                'icon-edit'     => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
                'icon-hexagon'  => '<svg width="28" height="28" viewBox="0 0 24 24" fill="#16a34a" stroke="none"><path d="M12 1.5l9.5 5.5v11L12 23.5 2.5 18V7z"/></svg>',
                'icon-code'     => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
                'icon-database' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
                'icon-zap'      => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
                'icon-globe'    => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
                'icon-file'     => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
                'icon-settings' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
                'icon-mail'     => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg>',
              ];
              $iconKey = trim((string)($t['icon'] ?? ''));
              echo $iconMap[$iconKey] ?? '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>';
            ?></div>
            <div class="tpl-name"><?php echo View::e((string)($t['name']??'')); ?></div>
            <div class="tpl-desc"><?php echo View::e((string)($t['description']??'')); ?></div>
            <div class="tpl-meta">
              <span class="tpl-tag"><?php echo View::e((string)($t['docker_image']??'')); ?></span>
              <span class="tpl-tag"><?php echo View::e(I18n::t('eq_apps.porta')); ?>: <?php echo View::e((string)($t['default_port']??'')); ?></span>
              <?php if (!empty($t['requires_domain'])): ?><span class="tpl-tag"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> <?php echo View::e(I18n::t('eq_apps.requer_dominio')); ?></span><?php endif; ?>
              <?php if (!empty($t['requires_repo'])): ?><span class="tpl-tag"><svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> <?php echo View::e(I18n::t('eq_apps.requer_repo')); ?></span><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
