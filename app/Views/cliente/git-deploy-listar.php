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
    $appType = (string)($d['app_type'] ?? 'php');
    $appTypeIcon = match($appType) { 'nodejs' => '🟢', 'python' => '🐍', 'static' => '📄', default => '🐘' };
    $appTypeLabel = match($appType) { 'nodejs' => 'Node.js', 'python' => 'Python', 'static' => 'Estático', default => 'PHP' };
  ?>
  <div class="card-new" id="dep-<?php echo $did; ?>">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;margin-bottom:2px;"><?php echo $appTypeIcon; ?> <?php echo View::e((string)($d['name'] ?? '')); ?> <span style="font-size:11px;font-weight:500;color:#64748b;"><?php echo $appTypeLabel; ?></span></div>
        <div style="font-size:12px;color:#64748b;font-family:monospace;"><?php echo View::e((string)($d['repo_url'] ?? '')); ?> <span style="color:#4F46E5;">@<?php echo View::e((string)($d['branch'] ?? 'main')); ?></span>
          <?php if (in_array($appType, ['nodejs', 'python']) && !empty($d['app_port'])): ?>
            · <span style="color:#f59e0b;">:<?php echo (int)$d['app_port']; ?></span>
          <?php endif; ?>
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
      <button class="botao sm ghost" onclick="toggleConsole(<?php echo $did; ?>)" title="Executar comandos na pasta do projeto">💻 Console</button>
      <?php if ($appType === 'nodejs'): ?>
      <button class="botao sm ghost" onclick="runQuickCmd(<?php echo $did; ?>,'pm2 restart deploy-<?php echo $did; ?> 2>&1 && pm2 status deploy-<?php echo $did; ?> 2>&1')" title="Reiniciar processo Node.js">🔄 Reiniciar</button>
      <button class="botao sm ghost" onclick="runQuickCmd(<?php echo $did; ?>,'pm2 logs deploy-<?php echo $did; ?> --lines 30 --nostream 2>&1')" title="Ver logs PM2">📜 Logs PM2</button>
      <?php endif; ?>
      <a href="/cliente/git-deploy/editar?id=<?php echo $did; ?>" class="botao sm ghost">✏️ Editar</a>
      <button class="botao sm ghost" onclick="toggleServerLogs(<?php echo $did; ?>)" title="Ver logs do servidor">📋 Logs servidor</button>
      <form method="post" action="/cliente/git-deploy/excluir" style="display:inline;" onsubmit="return confirm('Remover esta integração?')">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo $did; ?>" />
        <button class="botao danger sm" type="submit">🗑 Remover</button>
      </form>
      <span id="deploy-status-<?php echo $did; ?>" style="font-size:12px;color:#64748b;"></span>
    </div>

    <!-- Console inline -->
    <div id="console-<?php echo $did; ?>" style="display:none;margin-top:12px;background:#0b1020;border-radius:8px;padding:12px;font-family:monospace;font-size:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <span style="color:#64748b;">📂 <?php echo View::e((string)($d['deploy_path'] ?? '/var/www/html')); ?></span>
        <div style="display:flex;gap:4px;">
          <button onclick="runQuickCmd(<?php echo $did; ?>,'curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs 2>&1 && node -v && npm -v && (test -f package.json && npm install 2>&1 || true)')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;" title="Instalar Node.js + npm install">📦 Node.js</button>
          <button onclick="runQuickCmd(<?php echo $did; ?>,'(which composer >/dev/null 2>&1 || (curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 2>&1)) && composer install --no-interaction --no-dev 2>&1')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;" title="Instalar Composer + dependências">📦 Composer</button>
          <button onclick="runQuickCmd(<?php echo $did; ?>,'apt-get update -qq && apt-get install -y -qq python3 python3-pip 2>&1 && python3 --version && (test -f requirements.txt && pip3 install -r requirements.txt 2>&1 || true)')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;" title="Instalar Python + dependências">📦 Python</button>
        </div>
      </div>
      <div id="console-output-<?php echo $did; ?>" style="color:#e2e8f0;white-space:pre-wrap;max-height:300px;overflow-y:auto;margin-bottom:8px;"></div>
      <div style="display:flex;gap:6px;">
        <span style="color:#10b981;flex-shrink:0;">$</span>
        <input type="text" id="console-input-<?php echo $did; ?>" style="flex:1;background:transparent;border:none;color:#e2e8f0;font-family:monospace;font-size:12px;outline:none;" placeholder="npm install, npm run build, ls -la..." onkeydown="if(event.key==='Enter')runConsoleCmd(<?php echo $did; ?>)" />
        <button onclick="runConsoleCmd(<?php echo $did; ?>)" style="background:#4F46E5;color:#fff;border:none;border-radius:4px;padding:2px 10px;font-size:11px;cursor:pointer;">▶</button>
      </div>
    </div>

    <!-- Logs accordion -->
    <div id="logs-<?php echo $did; ?>" style="display:none;margin-top:12px;"></div>

    <!-- Server logs viewer -->
    <div id="server-logs-<?php echo $did; ?>" style="display:none;margin-top:12px;background:#0b1020;border-radius:8px;padding:12px;font-family:monospace;font-size:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <div style="display:flex;gap:6px;">
          <span style="color:#64748b;font-size:11px;">📋 Logs do servidor</span>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'all')" style="background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">Todos</button>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'nginx')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">Nginx</button>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'php')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">PHP</button>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'app')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">App</button>
        </div>
        <button onclick="carregarServerLogs(<?php echo $did; ?>,'all')" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:11px;">🔄</button>
      </div>
      <pre id="server-logs-output-<?php echo $did; ?>" style="color:#e2e8f0;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">Carregando...</pre>
    </div>
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

function toggleConsole(id) {
  var el = document.getElementById('console-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
  if (el.style.display !== 'none') document.getElementById('console-input-' + id).focus();
}

function runQuickCmd(id, cmd) {
  var output = document.getElementById('console-output-' + id);
  var el = document.getElementById('console-' + id);
  if (el.style.display === 'none') el.style.display = 'block';
  output.textContent += '$ ' + cmd.substring(0, 80) + '...\n⏳ Instalando (pode demorar)...\n';
  output.scrollTop = output.scrollHeight;

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);
  fd.append('command', cmd);

  fetch('/cliente/git-deploy/console', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) {
        output.textContent += (d.output || '✓ Concluído') + '\n';
      } else {
        output.textContent += '✘ ' + (d.erro || 'Erro') + '\n';
      }
      output.scrollTop = output.scrollHeight;
    })
    .catch(function() { output.textContent += '✘ Erro de rede\n'; });
}

function runConsoleCmd(id) {
  var input = document.getElementById('console-input-' + id);
  var output = document.getElementById('console-output-' + id);
  var cmd = input.value.trim();
  if (!cmd) return;

  output.textContent += '$ ' + cmd + '\n';
  input.value = '';
  input.disabled = true;

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);
  fd.append('command', cmd);

  fetch('/cliente/git-deploy/console', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      input.disabled = false;
      input.focus();
      if (d.ok) {
        if (d.output) output.textContent += d.output + '\n';
      } else {
        output.textContent += '✘ ' + (d.erro || 'Erro') + '\n';
      }
      output.scrollTop = output.scrollHeight;
    })
    .catch(function() {
      input.disabled = false;
      output.textContent += '✘ Erro de rede\n';
    });
}
function toggleServerLogs(id) {
  var el = document.getElementById('server-logs-' + id);
  if (el.style.display === 'none') {
    el.style.display = 'block';
    carregarServerLogs(id, 'all');
  } else {
    el.style.display = 'none';
  }
}

function carregarServerLogs(id, tipo) {
  var output = document.getElementById('server-logs-output-' + id);
  output.textContent = '⏳ Carregando logs...';

  fetch('/cliente/git-deploy/server-logs?id=' + id + '&tipo=' + tipo + '&linhas=100')
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
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
