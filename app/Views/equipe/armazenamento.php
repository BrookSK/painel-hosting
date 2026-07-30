<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'Armazenamento';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title">Armazenamento</div>
  <div class="page-subtitle" style="margin-bottom:0;">Visão geral do uso de disco por cliente e VPS. Escaneie e limpe diretamente.</div>
</div>

<!-- Filtros -->
<div class="card-new" style="margin-bottom:20px;">
  <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div>
      <label style="display:block;font-size:12px;margin-bottom:4px;">Servidor</label>
      <select class="input" name="servidor" style="min-width:180px;">
        <option value="">Todos os servidores</option>
        <?php foreach ($servidores as $s): ?>
          <option value="<?php echo (int)$s['id']; ?>" <?php echo $filtroServidor === (int)$s['id'] ? 'selected' : ''; ?>><?php echo View::e((string)$s['hostname']); ?> (<?php echo View::e((string)$s['ip_address']); ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:12px;margin-bottom:4px;">Cliente</label>
      <select class="input" name="cliente" style="min-width:200px;">
        <option value="">Todos os clientes</option>
        <?php foreach ($clientes as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo $filtroCliente === (int)$c['id'] ? 'selected' : ''; ?>><?php echo View::e((string)$c['name']); ?> (<?php echo View::e((string)$c['email']); ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="botao sm" type="submit">Filtrar</button>
    <?php if ($filtroServidor || $filtroCliente): ?>
      <a href="/equipe/armazenamento" class="botao sm ghost">Limpar filtros</a>
    <?php endif; ?>
  </form>
</div>

<?php if (empty($porCliente)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhuma VPS encontrada</div>
  <div style="font-size:13px;color:#64748b;">Nenhuma VPS ativa corresponde aos filtros selecionados.</div>
</div>
<?php else: ?>

<?php foreach ($porCliente as $cid => $clienteData): ?>
<div class="card-new" style="margin-bottom:20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #e2e8f0;">
    <div>
      <div style="font-size:15px;font-weight:700;"><?php echo View::e($clienteData['name']); ?></div>
      <div style="font-size:12px;color:#64748b;"><?php echo View::e($clienteData['email']); ?> — <?php echo count($clienteData['vps']); ?> VPS</div>
    </div>
    <a href="/equipe/clientes/ver?id=<?php echo $cid; ?>" class="botao sm ghost" style="font-size:11px;">Ver cliente</a>
  </div>

  <?php foreach ($clienteData['vps'] as $vpsItem): $vps = $vpsItem['vps']; $vpsId = (int)$vps['id']; ?>
  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:12px;" id="eq-vps-<?php echo $vpsId; ?>">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
      <div>
        <span style="font-weight:600;">VPS #<?php echo $vpsId; ?></span>
        <span style="font-size:12px;color:#64748b;margin-left:8px;"><?php echo (int)$vps['cpu']; ?>vCPU / <?php echo round((int)$vps['ram']/1024); ?>GB RAM / <?php echo round((int)$vps['storage']/1024); ?>GB Disco</span>
        <span style="font-size:12px;color:#94a3b8;margin-left:8px;"><?php echo View::e((string)$vps['hostname']); ?> (<?php echo View::e((string)$vps['ip_address']); ?>)</span>
      </div>
      <button class="botao sm" onclick="escanearVps(<?php echo $vpsId; ?>)" id="eq-btn-<?php echo $vpsId; ?>">Escanear</button>
    </div>

    <!-- Items conhecidos (apps + deploys) -->
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;font-size:12px;">
      <?php foreach ($vpsItem['apps'] as $a): ?>
        <span style="background:#10b98120;color:#10b981;padding:2px 8px;border-radius:99px;">App: <?php echo View::e((string)$a['name']); ?></span>
      <?php endforeach; ?>
      <?php foreach ($vpsItem['deploys'] as $d): ?>
        <span style="background:#4F46E520;color:#4F46E5;padding:2px 8px;border-radius:99px;">Deploy: <?php echo View::e((string)$d['name']); ?></span>
      <?php endforeach; ?>
      <?php foreach ($vpsItem['databases'] as $db): ?>
        <span style="background:#f59e0b20;color:#f59e0b;padding:2px 8px;border-radius:99px;">DB: <?php echo View::e((string)$db['db_name']); ?></span>
      <?php endforeach; ?>
      <?php if (empty($vpsItem['apps']) && empty($vpsItem['deploys']) && empty($vpsItem['databases'])): ?>
        <span style="color:#94a3b8;">Nenhum item registrado</span>
      <?php endif; ?>
    </div>

    <!-- Resultado do scan -->
    <div id="eq-result-<?php echo $vpsId; ?>" style="display:none;">
      <div style="margin-bottom:10px;">
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px;">
          <span><strong id="eq-used-<?php echo $vpsId; ?>">—</strong> usado de <strong id="eq-total-<?php echo $vpsId; ?>">—</strong></span>
          <span id="eq-pct-<?php echo $vpsId; ?>" style="color:#64748b;">—</span>
        </div>
        <div style="background:#e2e8f0;border-radius:4px;height:10px;overflow:hidden;">
          <div id="eq-bar-<?php echo $vpsId; ?>" style="background:#4F46E5;height:100%;width:0%;transition:width .5s;border-radius:4px;"></div>
        </div>
        <div style="font-size:11px;color:#94a3b8;margin-top:3px;" id="eq-livre-<?php echo $vpsId; ?>"></div>
      </div>
      <table style="width:100%;font-size:12px;border-collapse:collapse;" id="eq-items-<?php echo $vpsId; ?>"></table>
      <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;">
        <button class="botao sm ghost" style="font-size:11px;" onclick="eqLimpar(<?php echo $vpsId; ?>,'limpar_tmp')">Limpar /tmp</button>
        <button class="botao sm ghost" style="font-size:11px;" onclick="eqLimpar(<?php echo $vpsId; ?>,'limpar_logs')">Limpar logs</button>
        <button class="botao sm ghost" style="font-size:11px;" onclick="eqLimpar(<?php echo $vpsId; ?>,'limpar_cache')">Limpar caches</button>
      </div>
      <div id="eq-msg-<?php echo $vpsId; ?>" style="margin-top:6px;font-size:12px;"></div>
    </div>
    <div id="eq-loading-<?php echo $vpsId; ?>" style="display:none;font-size:12px;color:#64748b;padding:8px 0;">Escaneando...</div>
  </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<script>
var CSRF = '<?php echo Csrf::token(); ?>';

function formatMb(mb) {
  if (mb >= 1024) return (mb / 1024).toFixed(1) + ' GB';
  return mb + ' MB';
}

function escanearVps(vpsId) {
  var btn = document.getElementById('eq-btn-' + vpsId);
  var loading = document.getElementById('eq-loading-' + vpsId);
  var result = document.getElementById('eq-result-' + vpsId);
  btn.disabled = true; btn.textContent = '...';
  loading.style.display = ''; result.style.display = 'none';

  fetch('/equipe/armazenamento/escanear?vps_id=' + vpsId)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.disabled = false; btn.textContent = 'Escanear';
      loading.style.display = 'none'; result.style.display = '';
      if (!d.ok) { alert(d.erro || 'Erro'); return; }

      var disco = d.disco;
      var pct = disco.total_mb > 0 ? Math.round((disco.usado_mb / disco.total_mb) * 100) : 0;
      document.getElementById('eq-used-' + vpsId).textContent = formatMb(disco.usado_mb);
      document.getElementById('eq-total-' + vpsId).textContent = formatMb(disco.total_mb);
      document.getElementById('eq-livre-' + vpsId).textContent = formatMb(disco.livre_mb) + ' livre';
      document.getElementById('eq-pct-' + vpsId).textContent = pct + '%';
      var bar = document.getElementById('eq-bar-' + vpsId);
      bar.style.width = pct + '%';
      bar.style.background = pct > 90 ? '#ef4444' : (pct > 75 ? '#f59e0b' : '#4F46E5');

      var tbody = document.getElementById('eq-items-' + vpsId);
      var html = '';
      (d.items || []).forEach(function(item) {
        var tipoLabel = item.tipo === 'app' ? 'App' : 'Deploy';
        html += '<tr style="border-bottom:1px solid #f1f5f9;">'
          + '<td style="padding:6px 4px;"><strong>' + item.nome + '</strong> <code style="font-size:10px;color:#94a3b8;">' + item.path + '</code></td>'
          + '<td style="padding:6px 4px;text-align:right;font-weight:600;white-space:nowrap;">' + formatMb(item.size_mb) + '</td>'
          + '<td style="padding:6px 4px;text-align:center;"><button class="botao danger sm" style="font-size:10px;padding:2px 8px;" onclick="eqLimparPath(' + vpsId + ',\'' + item.path.replace(/'/g, "\\'") + '\',this)">Apagar</button></td></tr>';
      });
      if (d.tmp_mb > 10) html += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 4px;">/tmp</td><td style="padding:6px 4px;text-align:right;font-weight:600;">' + formatMb(d.tmp_mb) + '</td><td></td></tr>';
      if (d.logs_mb > 10) html += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:6px 4px;">/var/log</td><td style="padding:6px 4px;text-align:right;font-weight:600;">' + formatMb(d.logs_mb) + '</td><td></td></tr>';
      if (d.outros_mb > 100) html += '<tr><td style="padding:6px 4px;color:#94a3b8;">Outros (sistema, caches)</td><td style="padding:6px 4px;text-align:right;color:#94a3b8;">' + formatMb(d.outros_mb) + '</td><td></td></tr>';
      tbody.innerHTML = html;
    })
    .catch(function(e) { btn.disabled = false; btn.textContent = 'Escanear'; loading.style.display = 'none'; alert('Erro: ' + e); });
}

function eqLimpar(vpsId, acao) {
  var msg = document.getElementById('eq-msg-' + vpsId);
  msg.innerHTML = '<span style="color:#64748b;">Limpando...</span>';
  fetch('/equipe/armazenamento/limpar', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf='+encodeURIComponent(CSRF)+'&vps_id='+vpsId+'&acao='+encodeURIComponent(acao)})
    .then(function(r){return r.json();}).then(function(d){
      msg.innerHTML = d.ok ? '<span style="color:#10b981;">✓ Feito</span>' : '<span style="color:#ef4444;">✘ '+(d.erro||'Erro')+'</span>';
      if (d.ok) setTimeout(function(){ escanearVps(vpsId); }, 800);
    }).catch(function(e){ msg.innerHTML = '<span style="color:#ef4444;">Erro: '+e+'</span>'; });
}

function eqLimparPath(vpsId, path, btn) {
  if (!confirm('Apagar permanentemente:\n' + path + '\n\nIrreversível. Continuar?')) return;
  btn.disabled = true;
  fetch('/equipe/armazenamento/limpar', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf='+encodeURIComponent(CSRF)+'&vps_id='+vpsId+'&acao=limpar_path&path='+encodeURIComponent(path)})
    .then(function(r){return r.json();}).then(function(d){
      btn.disabled = false;
      if (d.ok) { setTimeout(function(){ escanearVps(vpsId); }, 800); }
      else { alert(d.erro || 'Erro'); }
    }).catch(function(e){ btn.disabled = false; alert('Erro: '+e); });
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
