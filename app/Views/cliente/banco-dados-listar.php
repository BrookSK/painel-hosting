<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'Bancos de Dados';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Bancos de Dados</div>
    <div class="page-subtitle" style="margin-bottom:0;">Crie e gerencie bancos MySQL nas suas VPS</div>
  </div>
  <div style="display:flex;gap:8px;">
    <button class="botao ghost sm" onclick="document.getElementById('pmaConfigPanel').style.display=document.getElementById('pmaConfigPanel').style.display==='none'?'block':'none'">⚙️ Config phpMyAdmin</button>
    <a href="/cliente/banco-dados/criar" class="botao">+ Novo banco</a>
  </div>
</div>

<!-- Painel de configuração phpMyAdmin -->
<div id="pmaConfigPanel" class="card-new" style="display:none;margin-bottom:20px;">
  <div style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:4px;">⚙️ Configurações do phpMyAdmin</div>
  <p style="font-size:12px;color:#64748b;margin-bottom:14px;">Ajuste os limites para importar arquivos SQL grandes. As alterações são aplicadas no servidor imediatamente.</p>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:14px;">
    <div>
      <label style="display:block;font-size:12px;color:#475569;margin-bottom:4px;">upload_max_filesize</label>
      <select class="input" id="pma-upload-max" style="font-size:13px;">
        <option value="64M">64 MB</option>
        <option value="128M">128 MB</option>
        <option value="256M" selected>256 MB</option>
        <option value="512M">512 MB</option>
        <option value="1G">1 GB</option>
        <option value="2G">2 GB</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:12px;color:#475569;margin-bottom:4px;">post_max_size</label>
      <select class="input" id="pma-post-max" style="font-size:13px;">
        <option value="64M">64 MB</option>
        <option value="128M">128 MB</option>
        <option value="256M" selected>256 MB</option>
        <option value="512M">512 MB</option>
        <option value="1G">1 GB</option>
        <option value="2G">2 GB</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:12px;color:#475569;margin-bottom:4px;">max_execution_time</label>
      <select class="input" id="pma-max-exec" style="font-size:13px;">
        <option value="300">5 min (300s)</option>
        <option value="600">10 min (600s)</option>
        <option value="1800" selected>30 min (1800s)</option>
        <option value="3600">1 hora (3600s)</option>
        <option value="7200">2 horas (7200s)</option>
        <option value="0">Sem limite</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:12px;color:#475569;margin-bottom:4px;">max_input_time</label>
      <select class="input" id="pma-max-input" style="font-size:13px;">
        <option value="300">5 min (300s)</option>
        <option value="600">10 min (600s)</option>
        <option value="1800" selected>30 min (1800s)</option>
        <option value="3600">1 hora (3600s)</option>
        <option value="0">Sem limite</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:12px;color:#475569;margin-bottom:4px;">memory_limit</label>
      <select class="input" id="pma-memory" style="font-size:13px;">
        <option value="256M">256 MB</option>
        <option value="512M" selected>512 MB</option>
        <option value="1G">1 GB</option>
        <option value="2G">2 GB</option>
      </select>
    </div>
  </div>

  <div style="display:flex;align-items:center;gap:8px;">
    <label style="font-size:12px;color:#475569;">VPS:</label>
    <select class="input" id="pma-vps-id" style="font-size:13px;max-width:250px;">
      <?php
        $vpsListPma = \LRV\Core\BancoDeDados::pdo()->prepare("SELECT v.id, v.cpu, v.ram FROM vps v WHERE v.client_id = :c AND v.status = 'running' ORDER BY v.id");
        $vpsListPma->execute([':c' => \LRV\Core\Auth::clienteId()]);
        foreach ($vpsListPma->fetchAll() ?: [] as $vp):
      ?>
        <option value="<?php echo (int)$vp['id']; ?>">VPS #<?php echo (int)$vp['id']; ?> — <?php echo (int)$vp['cpu']; ?>vCPU / <?php echo round((int)$vp['ram']/1024); ?>GB</option>
      <?php endforeach; ?>
    </select>
    <button class="botao sm" id="pmaApplyBtn" onclick="aplicarConfigPma()">Aplicar</button>
    <span id="pmaStatus" style="font-size:12px;color:#64748b;"></span>
  </div>
</div>

<?php if (empty($bancos)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:40px;margin-bottom:12px;">🗄️</div>
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhum banco de dados</div>
  <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Crie um banco MySQL para usar nas suas aplicações.</div>
  <a href="/cliente/banco-dados/criar" class="botao">Criar banco de dados</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px;">
  <?php foreach ($bancos as $b):
    $bid = (int)($b['id'] ?? 0);
    $status = (string)($b['status'] ?? 'active');
    $statusColor = $status === 'active' ? '#10b981' : ($status === 'error' ? '#ef4444' : '#f59e0b');
    $statusLabel = $status === 'active' ? 'Ativo' : ($status === 'error' ? 'Erro' : 'Criando...');
    $dbName = (string)($b['db_name'] ?? '');
    $dbUser = (string)($b['db_user'] ?? '');
    $dbPassEnc = (string)($b['db_password_enc_raw'] ?? '');
    $dbHost = (string)($b['db_host'] ?? '127.0.0.1');
    $dbPort = (string)($b['db_port'] ?? '3306');
  ?>
  <div class="card-new">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;"><?php echo View::e((string)($b['name'] ?? '')); ?></div>
        <div style="font-size:12px;color:#64748b;font-family:monospace;margin-top:2px;"><?php echo View::e($dbName); ?></div>
      </div>
      <span style="font-size:11px;padding:3px 10px;border-radius:99px;background:<?php echo $statusColor; ?>20;color:<?php echo $statusColor; ?>;font-weight:600;"><?php echo $statusLabel; ?></span>
    </div>

    <!-- Associação com domínio -->
    <div style="margin-bottom:10px;display:flex;align-items:center;gap:6px;">
      <span style="font-size:11px;color:#94a3b8;">🌐</span>
      <select id="db-note-<?php echo $bid; ?>" onchange="salvarNota(<?php echo $bid; ?>)" style="border:none;border-bottom:1px dashed #e2e8f0;background:transparent;font-size:12px;color:#475569;padding:2px 0;outline:none;cursor:pointer;">
        <option value="">Nenhum domínio associado</option>
        <?php foreach (($dominiosCliente ?? []) as $dc): ?>
          <option value="<?php echo View::e((string)($dc['subdomain'] ?? '')); ?>" <?php echo ((string)($b['notes'] ?? '')) === (string)($dc['subdomain'] ?? '') ? 'selected' : ''; ?>><?php echo View::e((string)($dc['subdomain'] ?? '')); ?></option>
        <?php endforeach; ?>
        <?php if (!empty($b['notes']) && !in_array((string)($b['notes'] ?? ''), array_column($dominiosCliente ?? [], 'subdomain'))): ?>
          <option value="<?php echo View::e((string)$b['notes']); ?>" selected><?php echo View::e((string)$b['notes']); ?></option>
        <?php endif; ?>
      </select>
    </div>

    <!-- Credenciais inline -->
    <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px;">
      <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
        <span style="font-size:11px;color:#64748b;width:55px;flex-shrink:0;">Host</span>
        <code id="db-host-<?php echo $bid; ?>" style="flex:1;font-size:12px;color:#1e293b;"><?php echo View::e($dbHost); ?></code>
        <button type="button" onclick="copiarDb('db-host-<?php echo $bid; ?>')" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;">Copiar</button>
      </div>
      <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
        <span style="font-size:11px;color:#64748b;width:55px;flex-shrink:0;">Banco</span>
        <code id="db-name-<?php echo $bid; ?>" style="flex:1;font-size:12px;color:#1e293b;"><?php echo View::e($dbName); ?></code>
        <button type="button" onclick="copiarDb('db-name-<?php echo $bid; ?>')" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;">Copiar</button>
      </div>
      <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
        <span style="font-size:11px;color:#64748b;width:55px;flex-shrink:0;">Usuário</span>
        <code id="db-user-<?php echo $bid; ?>" style="flex:1;font-size:12px;color:#1e293b;"><?php echo View::e($dbUser); ?></code>
        <button type="button" onclick="copiarDb('db-user-<?php echo $bid; ?>')" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;">Copiar</button>
      </div>
      <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
        <span style="font-size:11px;color:#64748b;width:55px;flex-shrink:0;">Senha</span>
        <code id="db-pass-<?php echo $bid; ?>" style="flex:1;font-size:12px;color:#1e293b;" data-enc="<?php echo View::e($dbPassEnc); ?>" data-revealed="0">••••••••••••</code>
        <button type="button" onclick="toggleSenha(<?php echo $bid; ?>)" id="btn-eye-<?php echo $bid; ?>" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;" title="Mostrar/esconder">👁</button>
        <button type="button" onclick="copiarSenha(<?php echo $bid; ?>)" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;">Copiar</button>
      </div>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="/cliente/banco-dados/ver?id=<?php echo $bid; ?>" class="botao sm">Ver detalhes / SQL</a>
      <?php
        // Verificar se o servidor desta VPS tem phpMyAdmin
        $pmaCheck = \LRV\Core\BancoDeDados::pdo()->prepare('SELECT s.phpmyadmin_url FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :v LIMIT 1');
        $pmaCheck->execute([':v' => (int)($b['vps_id'] ?? 0)]);
        $pmaRow = $pmaCheck->fetch();
        $hasPma = !empty($pmaRow['phpmyadmin_url']) || trim((string)\LRV\Core\Settings::obter('infra.phpmyadmin_url', '')) !== '';
      ?>
      <?php if ($hasPma): ?>
        <a href="/cliente/banco-dados/phpmyadmin?id=<?php echo $bid; ?>" target="_blank" class="botao ghost sm" title="Abrir phpMyAdmin">🐬 phpMyAdmin</a>
      <?php endif; ?>
      <form method="post" action="/cliente/banco-dados/excluir" style="display:inline;" onsubmit="return confirm('Remover banco <?php echo View::e((string)($b['name'] ?? '')); ?>? Os dados serão perdidos.')">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo $bid; ?>" />
        <button class="botao danger sm" type="submit">Remover</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<script>
function copiarDb(id) {
  var el = document.getElementById(id);
  if (!el) return;
  var text = el.getAttribute('data-plain') || el.textContent.trim();
  if (text === '••••••••••••') return;
  navigator.clipboard.writeText(text).then(function() {
    var btn = el.parentElement.querySelector('button:last-child');
    if (btn) { var t = btn.textContent; btn.textContent = '✓'; setTimeout(function(){ btn.textContent = t; }, 1500); }
  });
}

function toggleSenha(bid) {
  var el = document.getElementById('db-pass-' + bid);
  if (!el) return;
  var revealed = el.getAttribute('data-revealed') === '1';
  if (revealed) {
    el.textContent = '••••••••••••';
    el.setAttribute('data-revealed', '0');
    return;
  }
  // Fetch decrypted password via AJAX
  var plain = el.getAttribute('data-plain');
  if (plain) {
    el.textContent = plain;
    el.setAttribute('data-revealed', '1');
    return;
  }
  el.textContent = '...';
  fetch('/cliente/banco-dados/senha?id=' + bid)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok && d.senha) {
        el.setAttribute('data-plain', d.senha);
        el.textContent = d.senha;
        el.setAttribute('data-revealed', '1');
      } else {
        el.textContent = '(erro)';
      }
    })
    .catch(function() { el.textContent = '(erro)'; });
}

function copiarSenha(bid) {
  var el = document.getElementById('db-pass-' + bid);
  var plain = el ? el.getAttribute('data-plain') : null;
  if (plain) {
    navigator.clipboard.writeText(plain);
    return;
  }
  fetch('/cliente/banco-dados/senha?id=' + bid)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok && d.senha) {
        el.setAttribute('data-plain', d.senha);
        navigator.clipboard.writeText(d.senha);
      }
    });
}

function salvarNota(bid) {
  var el = document.getElementById('db-note-' + bid);
  if (!el) return;
  var fd = new FormData();
  fd.append('_csrf', '<?php echo View::e(Csrf::token()); ?>');
  fd.append('id', bid);
  fd.append('notes', el.value);
  fetch('/cliente/banco-dados/nota', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) { el.style.borderBottomColor = '#10b981'; setTimeout(function(){ el.style.borderBottomColor = '#e2e8f0'; }, 1500); }
    });
}
</script>
<?php endif; ?>

<script>
function aplicarConfigPma() {
  var btn = document.getElementById('pmaApplyBtn');
  var st = document.getElementById('pmaStatus');
  btn.disabled = true; btn.textContent = '⏳ Aplicando...';
  st.textContent = ''; st.style.color = '#64748b';

  var fd = new FormData();
  fd.append('_csrf', '<?php echo View::e(Csrf::token()); ?>');
  fd.append('vps_id', document.getElementById('pma-vps-id').value);
  fd.append('upload_max_filesize', document.getElementById('pma-upload-max').value);
  fd.append('post_max_size', document.getElementById('pma-post-max').value);
  fd.append('max_execution_time', document.getElementById('pma-max-exec').value);
  fd.append('max_input_time', document.getElementById('pma-max-input').value);
  fd.append('memory_limit', document.getElementById('pma-memory').value);

  fetch('/cliente/banco-dados/config-phpmyadmin', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.disabled = false; btn.textContent = 'Aplicar';
      if (d.ok) {
        st.textContent = '✓ Configurações aplicadas com sucesso';
        st.style.color = '#10b981';
      } else {
        st.textContent = '✘ ' + (d.erro || 'Erro ao aplicar');
        st.style.color = '#ef4444';
      }
    })
    .catch(function() {
      btn.disabled = false; btn.textContent = 'Aplicar';
      st.textContent = '✘ Erro de rede';
      st.style.color = '#ef4444';
    });
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
