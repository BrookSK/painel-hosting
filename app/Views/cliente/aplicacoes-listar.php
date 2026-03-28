<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusAppCliente(string $st): string {
    if ($st === 'inactive')    return '<span class="badge-new" style="background:#f1f5f9;color:#334155;">Inativa</span>';
    if ($st === 'deploying')   return '<span class="badge-new" style="background:#e0e7ff;color:#1e3a8a;">Deploy</span>';
    if ($st === 'installing')  return '<span class="badge-new" style="background:#fef3c7;color:#92400e;">Instalando</span>';
    if ($st === 'running')     return '<span class="badge-new badge-green">Rodando</span>';
    if ($st === 'stopped')     return '<span class="badge-new" style="background:#f1f5f9;color:#334155;">Parada</span>';
    if ($st === 'error')       return '<span class="badge-new badge-red">Erro</span>';
    return '<span class="badge-new badge-green">Ativa</span>';
}

$pageTitle = I18n::t('apps.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::t('apps.titulo')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('apps.subtitulo')); ?></div>
  </div>
  <a href="/cliente/aplicacoes/catalogo" class="botao sm"><?php echo I18n::t('apps.catalogo_btn'); ?></a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('apps.aplicacao')); ?></th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">VPS</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('apps.tipo')); ?></th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('apps.dominio')); ?></th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('apps.porta')); ?></th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.status')); ?></th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.acoes')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($aplicacoes ?? []) as $a):
          $appId = (int)($a['id'] ?? 0);
          $appSt = (string)($a['status'] ?? '');
          $appType = (string)($a['type'] ?? '');
          // Determinar pasta raiz dos arquivos baseado no tipo da aplicação
          $appRootPath = match($appType) {
              'nodejs' => '/app',
              'static-site', 'nginx' => '/usr/share/nginx/html',
              default => '/var/www/html',
          };
        ?>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>#<?php echo $appId; ?></strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;">#<?php echo (int)($a['vps_id'] ?? 0); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($a['type'] ?? '')); ?></code></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($a['domain'] ?? '')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($a['port'] ?? '')); ?></code></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusAppCliente($appSt); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
              <div style="display:flex;gap:4px;flex-wrap:wrap;">
                <?php if ($appSt === 'running' || $appSt === 'active'): ?>
                  <a href="/cliente/arquivos?app_id=<?php echo $appId; ?>&path=<?php echo urlencode($appRootPath); ?>" class="botao ghost sm" style="font-size:11px;padding:3px 8px;" title="Arquivos">📁</a>
                  <?php if (!empty($a['db_id'])): ?>
                    <a href="/cliente/banco-dados/ver?id=<?php echo (int)$a['db_id']; ?>" class="botao ghost sm" style="font-size:11px;padding:3px 8px;" title="Banco de dados">🗄️</a>
                  <?php endif; ?>
                  <?php if (!empty($a['domain'])): ?>
                    <a href="https://<?php echo View::e((string)$a['domain']); ?>" target="_blank" class="botao ghost sm" style="font-size:11px;padding:3px 8px;" title="Abrir site">🌐</a>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($appSt === 'error'): ?>
                  <form method="post" action="/cliente/aplicacoes/reinstalar" style="display:inline;">
                    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>"/>
                    <input type="hidden" name="app_id" value="<?php echo $appId; ?>"/>
                    <button class="botao sm" type="submit" style="font-size:11px;">🔄 Reinstalar</button>
                  </form>
                <?php endif; ?>
                <form method="post" action="/cliente/aplicacoes/deletar" style="display:inline;" onsubmit="return confirm('Deletar aplicação #<?php echo $appId; ?>?')">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>"/>
                  <input type="hidden" name="app_id" value="<?php echo $appId; ?>"/>
                  <button class="botao danger sm" type="submit" style="font-size:11px;">✕</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($aplicacoes)): ?>
          <tr><td colspan="7" style="padding:12px;color:#94a3b8;"><?php echo View::e(I18n::t('apps.nenhuma')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
