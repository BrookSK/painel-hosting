<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusMigracao(string $st): string {
    return match($st) {
        'completed' => '<span class="badge-new badge-green">Concluída</span>',
        'failed' => '<span class="badge-new badge-red">Falhou</span>',
        'cancelled' => '<span class="badge-new badge-yellow">Cancelada</span>',
        'pending' => '<span class="badge-new badge-yellow">Pendente</span>',
        default => '<span class="badge-new badge-blue">' . View::e(ucfirst(str_replace('_', ' ', $st))) . '</span>',
    };
}

$pageTitle = I18n::t('migracao_wp.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('migracao_wp.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('migracao_wp.subtitulo')); ?></div>

<div style="margin-bottom:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
  <a href="/equipe/migracoes-wp/novo" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:4px;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    <?php echo View::e(I18n::t('migracao_wp.nova_migracao')); ?>
  </a>
  <select onchange="location.href='/equipe/migracoes-wp'+(this.value?'?status='+this.value:'')" style="padding:6px 12px;border-radius:6px;border:1px solid var(--border);background:var(--bg-card);color:var(--text);">
    <option value="">Todos os status</option>
    <option value="pending" <?php echo ($filtro??'')==='pending'?'selected':''; ?>>Pendente</option>
    <option value="syncing_files" <?php echo ($filtro??'')==='syncing_files'?'selected':''; ?>>Sincronizando</option>
    <option value="importing_db" <?php echo ($filtro??'')==='importing_db'?'selected':''; ?>>Importando DB</option>
    <option value="completed" <?php echo ($filtro??'')==='completed'?'selected':''; ?>>Concluída</option>
    <option value="failed" <?php echo ($filtro??'')==='failed'?'selected':''; ?>>Falhou</option>
  </select>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo View::e(I18n::t('migracao_wp.cliente')); ?></th>
          <th><?php echo View::e(I18n::t('migracao_wp.origem')); ?></th>
          <th><?php echo View::e(I18n::t('migracao_wp.destino')); ?></th>
          <th><?php echo View::e(I18n::t('geral.status')); ?></th>
          <th><?php echo View::e(I18n::t('migracao_wp.progresso')); ?></th>
          <th><?php echo View::e(I18n::t('migracao_wp.criado_em')); ?></th>
          <th><?php echo View::e(I18n::t('geral.acoes')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($migracoes??[]) as $m): ?>
        <tr>
          <td><strong>#<?php echo (int)($m['id']??0); ?></strong></td>
          <td><?php echo View::e((string)($m['client_name']??'—')); ?></td>
          <td>
            <small><?php echo View::e((string)($m['source_user']??'root')); ?>@<?php echo View::e((string)($m['source_host']??'')); ?></small><br>
            <code style="font-size:11px;"><?php echo View::e((string)($m['source_wp_path']??'')); ?></code>
          </td>
          <td><?php echo View::e((string)($m['dest_domain']??'—')); ?></td>
          <td><?php echo badgeStatusMigracao((string)($m['status']??'pending')); ?></td>
          <td>
            <div style="background:var(--border);border-radius:4px;height:8px;width:80px;overflow:hidden;">
              <div style="background:var(--accent);height:100%;width:<?php echo (int)($m['progress_percent']??0); ?>%;transition:width .3s;"></div>
            </div>
            <small><?php echo (int)($m['progress_percent']??0); ?>%</small>
          </td>
          <td><small><?php echo View::e((string)($m['created_at']??'')); ?></small></td>
          <td><a href="/equipe/migracoes-wp/ver?id=<?php echo (int)($m['id']??0); ?>"><?php echo View::e(I18n::t('geral.ver')); ?></a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($migracoes)): ?>
        <tr><td colspan="8" style="text-align:center;padding:24px;"><?php echo View::e(I18n::t('migracao_wp.nenhuma')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
