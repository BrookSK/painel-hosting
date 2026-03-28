<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$pageTitle = 'Git Deploy';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Git Deploy</div>
    <div class="page-subtitle" style="margin-bottom:0;">Conecte repositórios GitHub/GitLab e faça deploy com 1 clique</div>
  </div>
  <a href="/cliente/git-deploy/novo" class="botao">+ Novo repositório</a>
</div>

<?php if (empty($deployments)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:40px;margin-bottom:12px;">🚀</div>
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhum repositório conectado</div>
  <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Conecte um repositório Git para fazer deploy automático na sua VPS.</div>
  <a href="/cliente/git-deploy/novo" class="botao">Conectar repositório</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:14px;">
  <?php foreach ($deployments as $d):
    $did = (int)($d['id'] ?? 0);
    $status = (string)($d['status'] ?? 'active');
    $statusColor = $status === 'active' ? '#10b981' : ($status === 'error' ? '#ef4444' : '#94a3b8');
    $statusLabel = $status === 'active' ? 'Ativo' : ($status === 'error' ? 'Erro' : 'Inativo');
    $lastHash = (string)($d['last_commit_hash'] ?? '');
    $lastMsg = (string)($d['last_commit_message'] ?? '');
    $lastAt = (string)($d['last_deployed_at'] ?? '');
    $lastAuthor = (string)($d['last_commit_author'] ?? '');
  ?>
  <div class="card-new" id="dep-<?php echo $did; ?>">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;margin-bottom:2px;"><?php echo View::e((string)($d['name'] ?? '')); ?></div>
        <div style="font-size:12px;color:#64748b;font-family:monospace;"><?php echo View::e((string)($d['repo_url'] ?? '')); ?> <span style="color:#4F46E5;">@<?php echo View::e((string)($d['branch'] ?? 'main')); ?></span>
          <?php if (!empty($d['subdomain'])): ?>
            · <a href="https://<?php echo View::e((string)$d['subdomain']); ?>" target="_blank" rel="noopener" style="color:#10b981;font-family:system-ui;">🌐 <?php echo View::e((string)$d['subdomain']); ?></a>
          <?php endif; ?>
        </div>
      </div>
      <span style="font-size:11px;padding:3px 10px;border-radius:99px;background:<?php echo $statusColor; ?>20;color:<?php echo $statusColor; ?>;font-weight:600;"><?php echo $statusLabel; ?></span>
    </div>

    <?php if ($lastHash !== ''): ?>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:12px;">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <code style="color:#4F46E5;"><?php echo View::e(substr($lastHash, 0, 8)); ?></code>
        <span style="color:#475569;flex:1;"><?php echo View::e($lastMsg); ?></span>
        <?php if ($lastAuthor !== ''): ?><span style="color:#94a3b8;">— <?php echo View::e($lastAuthor); ?></span><?php endif; ?>
        <?php if ($lastAt !== ''): ?><span style="color:#94a3b8;"><?php echo View::e($lastAt); ?></span><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($d['error_message'])): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:12px;color:#dc2626;"><?php echo View::e((string)$d['error_message']); ?></div>
    <?php endif; ?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
      <button class="botao sm" id="btn-deploy-<?php echo $did; ?>" onclick="executarDeploy(<?php echo $did; ?>)">▶ Deploy agora</button>
      <button class="botao sm ghost" onclick="verLogs(<?php echo $did; ?>)">📋 Histórico</button>
      <a href="/cliente/arquivos?vps_id=<?php echo (int)($d['vps_id'] ?? 0); ?>&path=<?php echo urlencode((string)($d['deploy_path'] ?? '/var/www/html')); ?>&direct=1" class="botao sm ghost" title="Ver arquivos">📁 Arquivos</a>
      <a href="/cliente/git-deploy/editar?id=<?php echo $did; ?>" class="botao sm ghost">✏️ Editar</a>
      <form method="post" action="/cliente/git-deploy/excluir" style="display:inline;" onsubmit="return confirm('Remover esta integração?')">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo $did; ?>" />
        <button class="botao danger sm" type="submit">🗑 Remover</button>
      </form>
      <span id="deploy-status-<?php echo $did; ?>" style="font-size:12px;color:#64748b;"></span>
    </div>

    <!-- Logs accordion -->
    <div id="logs-<?php echo $did; ?>" style="display:none;margin-top:12px;"></div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
var _csrf = '<?php echo View::e(Csrf::token()); ?>';

function executarDeploy(id) {
  var btn = document.getElementById('btn-deploy-' + id);
  var st = document.getElementById('deploy-status-' + id);
  btn.disabled = true; btn.textContent = '⏳ Fazendo deploy...';
  st.textContent = ''; st.style.color = '#64748b';

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);

  fetch('/cliente/git-deploy/deploy', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) {
        st.textContent = '✓ Deploy concluído — ' + (d.commit ? d.commit.substring(0, 8) : '') + ' ' + (d.mensagem || '');
        st.style.color = '#10b981';
        setTimeout(function() { location.reload(); }, 2000);
      } else {
        st.textContent = '✘ ' + (d.erro || 'Erro');
        st.style.color = '#ef4444';
        btn.disabled = false; btn.textContent = '▶ Deploy agora';
      }
    })
    .catch(function() {
      st.textContent = '✘ Erro de rede';
      st.style.color = '#ef4444';
      btn.disabled = false; btn.textContent = '▶ Deploy agora';
    });
}

function verLogs(id) {
  var el = document.getElementById('logs-' + id);
  if (el.style.display !== 'none') { el.style.display = 'none'; return; }
  el.innerHTML = '<div style="font-size:12px;color:#64748b;padding:8px;">Carregando...</div>';
  el.style.display = 'block';

  fetch('/cliente/git-deploy/logs?id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (!d.ok || !d.logs.length) { el.innerHTML = '<div style="font-size:12px;color:#94a3b8;padding:8px;">Nenhum log encontrado.</div>'; return; }
      var html = '<div style="font-size:12px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">';
      d.logs.forEach(function(l) {
        var ok = l.status === 'success';
        html += '<div style="display:flex;gap:10px;padding:8px 12px;border-bottom:1px solid #f1f5f9;align-items:flex-start;">';
        html += '<span style="color:' + (ok ? '#10b981' : '#ef4444') + ';flex-shrink:0;">' + (ok ? '✓' : '✘') + '</span>';
        html += '<div style="flex:1;min-width:0;">';
        if (l.commit_hash) html += '<code style="color:#4F46E5;">' + l.commit_hash.substring(0, 8) + '</code> ';
        if (l.commit_message) html += '<span style="color:#475569;">' + escHtml(l.commit_message) + '</span>';
        if (l.commit_author) html += ' <span style="color:#94a3b8;">— ' + escHtml(l.commit_author) + '</span>';
        html += '<div style="color:#94a3b8;margin-top:2px;">' + escHtml(l.deployed_at) + '</div>';
        if (!ok && l.output) html += '<pre style="margin-top:4px;background:#fef2f2;color:#dc2626;padding:6px;border-radius:6px;font-size:11px;white-space:pre-wrap;max-height:120px;overflow:auto;">' + escHtml(l.output) + '</pre>';
        html += '</div></div>';
      });
      html += '</div>';
      el.innerHTML = html;
    });
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
