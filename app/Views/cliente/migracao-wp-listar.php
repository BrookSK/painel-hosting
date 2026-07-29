<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusMigCli(string $st): string {
    return match($st) {
        'completed' => '<span class="badge-new badge-green">' . View::e(I18n::t('migracao_wp_cli.concluida')) . '</span>',
        'failed' => '<span class="badge-new badge-red">' . View::e(I18n::t('migracao_wp_cli.falhou')) . '</span>',
        'cancelled' => '<span class="badge-new badge-yellow">' . View::e(I18n::t('migracao_wp_cli.cancelada')) . '</span>',
        'pending' => '<span class="badge-new badge-yellow">' . View::e(I18n::t('migracao_wp_cli.pendente')) . '</span>',
        default => '<span class="badge-new badge-blue">' . View::e(ucfirst(str_replace('_', ' ', $st))) . '</span>',
    };
}

$pageTitle = I18n::t('migracao_wp_cli.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('migracao_wp_cli.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('migracao_wp_cli.subtitulo')); ?></div>

<div style="margin-bottom:16px;">
  <a href="/cliente/migracoes-wp/novo" class="botao">
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:4px;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    <?php echo View::e(I18n::t('migracao_wp_cli.nova_migracao')); ?>
  </a>
</div>

<?php if (empty($migracoes)): ?>
<div class="card-new" style="text-align:center;padding:40px;">
  <svg width="48" height="48" viewBox="0 0 20 20" fill="none" style="opacity:.4;margin-bottom:12px;"><path d="M3 10h4l2-6 2 12 2-6h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
  <p style="color:var(--text-muted);margin:0;"><?php echo View::e(I18n::t('migracao_wp_cli.nenhuma')); ?></p>
  <p style="color:var(--text-muted);margin:8px 0 0;font-size:13px;"><?php echo View::e(I18n::t('migracao_wp_cli.nenhuma_desc')); ?></p>
</div>
<?php else: ?>
<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo View::e(I18n::t('migracao_wp_cli.origem')); ?></th>
          <th><?php echo View::e(I18n::t('migracao_wp_cli.destino')); ?></th>
          <th><?php echo View::e(I18n::t('geral.status')); ?></th>
          <th><?php echo View::e(I18n::t('migracao_wp_cli.progresso')); ?></th>
          <th><?php echo View::e(I18n::t('migracao_wp_cli.criado_em')); ?></th>
          <th><?php echo View::e(I18n::t('geral.acoes')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($migracoes as $m): ?>
        <tr>
          <td><strong>#<?php echo (int)$m['id']; ?></strong></td>
          <td>
            <small><?php echo View::e((string)($m['source_user']??'root')); ?>@<?php echo View::e((string)$m['source_host']); ?></small><br>
            <code style="font-size:11px;"><?php echo View::e((string)$m['source_wp_path']); ?></code>
          </td>
          <td><?php echo View::e((string)($m['dest_domain']??'—')); ?></td>
          <td><?php echo badgeStatusMigCli((string)$m['status']); ?></td>
          <td>
            <div style="background:var(--border);border-radius:4px;height:8px;width:80px;overflow:hidden;">
              <div style="background:var(--accent);height:100%;width:<?php echo (int)$m['progress_percent']; ?>%;transition:width .3s;"></div>
            </div>
            <small><?php echo (int)$m['progress_percent']; ?>%</small>
          </td>
          <td><small><?php echo View::e((string)$m['created_at']); ?></small></td>
          <td><a href="/cliente/migracoes-wp/ver?id=<?php echo (int)$m['id']; ?>"><?php echo View::e(I18n::t('geral.ver')); ?></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
