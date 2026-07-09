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
                  <a href="/cliente/arquivos?app_id=<?php echo $appId; ?>&path=<?php echo urlencode($appRootPath); ?>" class="botao ghost sm" style="font-size:11px;padding:3px 8px;" title="Arquivos"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></a>
                  <?php if (!empty($a['db_id'])): ?>
                    <a href="/cliente/banco-dados/ver?id=<?php echo (int)$a['db_id']; ?>" class="botao ghost sm" style="font-size:11px;padding:3px 8px;" title="Banco de dados"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></a>
                  <?php endif; ?>
                  <?php if (!empty($a['domain'])): ?>
                    <a href="https://<?php echo View::e((string)$a['domain']); ?>" target="_blank" class="botao ghost sm" style="font-size:11px;padding:3px 8px;" title="Abrir site"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></a>
                  <?php endif; ?>
                  <button class="botao ghost sm" style="font-size:11px;padding:3px 8px;" onclick="toggleAppLogs(<?php echo $appId; ?>)" title="Ver logs do servidor"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></button>
                <?php endif; ?>
                <?php if ($appSt === 'error'): ?>
                  <form method="post" action="/cliente/aplicacoes/reinstalar" style="display:inline;">
                    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>"/>
                    <input type="hidden" name="app_id" value="<?php echo $appId; ?>"/>
                    <button class="botao sm" type="submit" style="font-size:11px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Reinstalar</button>
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
          <!-- Logs panel -->
          <tr id="app-logs-row-<?php echo $appId; ?>" style="display:none;">
            <td colspan="7" style="padding:0;border-bottom:1px solid #f1f5f9;">
              <div style="background:#0b1020;border-radius:0 0 8px 8px;padding:12px;font-family:monospace;font-size:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                  <div style="display:flex;gap:6px;">
                    <button onclick="carregarAppLogs(<?php echo $appId; ?>,'all')" style="background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">Todos</button>
                    <button onclick="carregarAppLogs(<?php echo $appId; ?>,'nginx')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">Nginx</button>
                    <button onclick="carregarAppLogs(<?php echo $appId; ?>,'php')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">PHP</button>
                    <button onclick="carregarAppLogs(<?php echo $appId; ?>,'app')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">App</button>
                  </div>
                  <button onclick="carregarAppLogs(<?php echo $appId; ?>,'all')" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:11px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Atualizar</button>
                </div>
                <pre id="app-logs-output-<?php echo $appId; ?>" style="color:#e2e8f0;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">Carregando...</pre>
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

<script>
function toggleAppLogs(appId) {
  var row = document.getElementById('app-logs-row-' + appId);
  if (row.style.display === 'none') {
    row.style.display = '';
    carregarAppLogs(appId, 'all');
  } else {
    row.style.display = 'none';
  }
}

function carregarAppLogs(appId, tipo) {
  var output = document.getElementById('app-logs-output-' + appId);
  output.textContent = '⏳ Carregando logs...';

  fetch('/cliente/aplicacoes/logs?app_id=' + appId + '&tipo=' + tipo + '&linhas=100')
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) {
        output.textContent = d.logs || '(sem logs)';
        output.scrollTop = output.scrollHeight;
      } else {
        output.textContent = '✘ ' + (d.erro || 'Erro ao carregar logs');
      }
    })
    .catch(function() { output.textContent = '✘ Erro de rede'; });
}

// Poll automático para apps em "installing" — atualiza a cada 5s
(function() {
  var appsInstalando = <?php
    $idsInstalando = [];
    foreach ($aplicacoes as $a) {
      if (($a['status'] ?? '') === 'installing') $idsInstalando[] = (int)$a['id'];
    }
    echo json_encode($idsInstalando);
  ?>;

  if (appsInstalando.length === 0) return;

  var pollInterval = setInterval(function() {
    var pending = 0;
    appsInstalando.forEach(function(appId) {
      fetch('/cliente/aplicacoes/status?id=' + appId)
        .then(function(r) { return r.json(); })
        .then(function(d) {
          if (!d.ok) return;
          if (d.status !== 'installing') {
            window.location.reload();
          } else {
            pending++;
          }
        })
        .catch(function() {});
    });
  }, 5000);
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
