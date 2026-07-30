<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'Armazenamento';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title">Armazenamento</div>
  <div class="page-subtitle" style="margin-bottom:0;">Veja quanto espaço cada item ocupa no servidor e libere espaço quando precisar.</div>
</div>

<?php if (empty($vpsData)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhuma VPS ativa</div>
  <div style="font-size:13px;color:#64748b;">Você precisa de uma VPS em execução para ver o armazenamento.</div>
</div>
<?php else: ?>
<?php foreach ($vpsData as $i => $data): $vps = $data['vps']; $vpsId = (int)$vps['id']; ?>
<div class="card-new" style="max-width:800px;margin-bottom:20px;" id="vps-card-<?php echo $vpsId; ?>">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div>
      <div style="font-size:15px;font-weight:700;">VPS #<?php echo $vpsId; ?> — <?php echo (int)$vps['cpu']; ?> vCPU / <?php echo round((int)$vps['ram']/1024); ?> GB RAM / <?php echo round((int)$vps['storage']/1024); ?> GB Disco</div>
      <div style="font-size:12px;color:#64748b;"><?php echo View::e((string)$vps['ip_address']); ?></div>
    </div>
    <button class="botao sm" onclick="escanearVps(<?php echo $vpsId; ?>)" id="btn-scan-<?php echo $vpsId; ?>">Escanear disco</button>
  </div>

  <!-- Resultado do scan -->
  <div id="scan-result-<?php echo $vpsId; ?>" style="display:none;">
    <!-- Barra de uso geral -->
    <div style="margin-bottom:16px;">
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
        <span><strong id="used-<?php echo $vpsId; ?>">—</strong> usado de <strong id="total-<?php echo $vpsId; ?>">—</strong></span>
        <span id="pct-<?php echo $vpsId; ?>" style="color:#64748b;">—</span>
      </div>
      <div style="background:#e2e8f0;border-radius:6px;height:14px;overflow:hidden;">
        <div id="bar-<?php echo $vpsId; ?>" style="background:#4F46E5;height:100%;width:0%;transition:width .5s;border-radius:6px;"></div>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:11px;color:#94a3b8;margin-top:4px;">
        <span id="livre-<?php echo $vpsId; ?>">—</span>
        <span>Atualizado agora</span>
      </div>
    </div>

    <!-- Tabela de itens -->
    <div style="margin-bottom:12px;">
      <table style="width:100%;font-size:13px;border-collapse:collapse;">
        <thead>
          <tr style="border-bottom:2px solid #e2e8f0;">
            <th style="text-align:left;padding:8px 4px;">Item</th>
            <th style="text-align:left;padding:8px 4px;">Tipo</th>
            <th style="text-align:right;padding:8px 4px;">Tamanho</th>
            <th style="text-align:center;padding:8px 4px;">Ação</th>
          </tr>
        </thead>
        <tbody id="items-<?php echo $vpsId; ?>">
          <tr><td colspan="4" style="padding:12px;color:#94a3b8;text-align:center;">Clique em "Escanear disco" para ver o uso.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Ações de limpeza global -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;padding-top:12px;border-top:1px solid #e2e8f0;">
      <button class="botao sm ghost" onclick="limpar(<?php echo $vpsId; ?>,'limpar_tmp')">Limpar /tmp</button>
      <button class="botao sm ghost" onclick="limpar(<?php echo $vpsId; ?>,'limpar_logs')">Limpar logs antigos</button>
      <button class="botao sm ghost" onclick="limpar(<?php echo $vpsId; ?>,'limpar_cache')">Limpar caches (npm/composer/pip)</button>
    </div>
    <div id="limpar-msg-<?php echo $vpsId; ?>" style="margin-top:8px;font-size:12px;"></div>
  </div>

  <!-- Loading -->
  <div id="scan-loading-<?php echo $vpsId; ?>" style="display:none;text-align:center;padding:24px;color:#64748b;font-size:13px;">
    Escaneando disco... isso pode levar alguns segundos.
  </div>
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
  var btn = document.getElementById('btn-scan-' + vpsId);
  var loading = document.getElementById('scan-loading-' + vpsId);
  var result = document.getElementById('scan-result-' + vpsId);
  btn.disabled = true; btn.textContent = 'Escaneando...';
  loading.style.display = ''; result.style.display = 'none';

  fetch('/cliente/armazenamento/escanear?vps_id=' + vpsId)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.disabled = false; btn.textContent = 'Escanear disco';
      loading.style.display = 'none'; result.style.display = '';
      if (!d.ok) { alert(d.erro || 'Erro'); return; }

      var disco = d.disco;
      var pct = disco.total_mb > 0 ? Math.round((disco.usado_mb / disco.total_mb) * 100) : 0;
      document.getElementById('used-' + vpsId).textContent = formatMb(disco.usado_mb);
      document.getElementById('total-' + vpsId).textContent = formatMb(disco.total_mb);
      document.getElementById('livre-' + vpsId).textContent = formatMb(disco.livre_mb) + ' livre';
      document.getElementById('pct-' + vpsId).textContent = pct + '%';
      var bar = document.getElementById('bar-' + vpsId);
      bar.style.width = pct + '%';
      bar.style.background = pct > 90 ? '#ef4444' : (pct > 75 ? '#f59e0b' : '#4F46E5');

      // Preencher tabela
      var tbody = document.getElementById('items-' + vpsId);
      var html = '';
      (d.items || []).forEach(function(item) {
        var tipoLabel = item.tipo === 'app' ? 'Aplicação' : 'Git Deploy';
        var tipoColor = item.tipo === 'app' ? '#10b981' : '#4F46E5';
        html += '<tr style="border-bottom:1px solid #f1f5f9;">'
          + '<td style="padding:8px 4px;"><strong>' + item.nome + '</strong><br><code style="font-size:11px;color:#94a3b8;">' + item.path + '</code></td>'
          + '<td style="padding:8px 4px;"><span style="background:' + tipoColor + '20;color:' + tipoColor + ';padding:2px 8px;border-radius:99px;font-size:11px;">' + tipoLabel + '</span></td>'
          + '<td style="padding:8px 4px;text-align:right;font-weight:600;">' + formatMb(item.size_mb) + '</td>'
          + '<td style="padding:8px 4px;text-align:center;"><button class="botao danger sm" style="font-size:11px;" onclick="limparPath(' + vpsId + ',\'' + item.path.replace(/'/g, "\\'") + '\',this)">Apagar</button></td>'
          + '</tr>';
      });
      // Tmp
      if (d.tmp_mb > 0) {
        html += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:8px 4px;">Arquivos temporários<br><code style="font-size:11px;color:#94a3b8;">/tmp</code></td><td style="padding:8px 4px;"><span style="background:#f59e0b20;color:#f59e0b;padding:2px 8px;border-radius:99px;font-size:11px;">Sistema</span></td><td style="padding:8px 4px;text-align:right;font-weight:600;">' + formatMb(d.tmp_mb) + '</td><td style="padding:8px 4px;text-align:center;"><button class="botao sm ghost" style="font-size:11px;" onclick="limpar(' + vpsId + ',\'limpar_tmp\')">Limpar</button></td></tr>';
      }
      // Logs
      if (d.logs_mb > 0) {
        html += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:8px 4px;">Logs do servidor<br><code style="font-size:11px;color:#94a3b8;">/var/log</code></td><td style="padding:8px 4px;"><span style="background:#f59e0b20;color:#f59e0b;padding:2px 8px;border-radius:99px;font-size:11px;">Sistema</span></td><td style="padding:8px 4px;text-align:right;font-weight:600;">' + formatMb(d.logs_mb) + '</td><td style="padding:8px 4px;text-align:center;"><button class="botao sm ghost" style="font-size:11px;" onclick="limpar(' + vpsId + ',\'limpar_logs\')">Limpar</button></td></tr>';
      }
      // Outros
      if (d.outros_mb > 100) {
        html += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:8px 4px;">Outros arquivos<br><code style="font-size:11px;color:#94a3b8;">Sistema, caches, pacotes</code></td><td style="padding:8px 4px;"><span style="background:#94a3b820;color:#94a3b8;padding:2px 8px;border-radius:99px;font-size:11px;">Outros</span></td><td style="padding:8px 4px;text-align:right;font-weight:600;">' + formatMb(d.outros_mb) + '</td><td style="padding:8px 4px;text-align:center;"><button class="botao sm ghost" style="font-size:11px;" onclick="limpar(' + vpsId + ',\'limpar_cache\')">Limpar caches</button></td></tr>';
      }
      if (html === '') html = '<tr><td colspan="4" style="padding:12px;color:#94a3b8;text-align:center;">Nenhum item encontrado.</td></tr>';
      tbody.innerHTML = html;
    })
    .catch(function(e) {
      btn.disabled = false; btn.textContent = 'Escanear disco';
      loading.style.display = 'none';
      alert('Erro de rede: ' + e);
    });
}

function limpar(vpsId, acao) {
  var msg = document.getElementById('limpar-msg-' + vpsId);
  msg.innerHTML = '<span style="color:#64748b;">Limpando...</span>';
  fetch('/cliente/armazenamento/limpar', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(CSRF) + '&vps_id=' + vpsId + '&acao=' + encodeURIComponent(acao)
  }).then(function(r){return r.json();}).then(function(d){
    if (d.ok) {
      msg.innerHTML = '<span style="color:#10b981;">✓ Limpeza concluída. Escaneie novamente para ver o espaço liberado.</span>';
      setTimeout(function(){ escanearVps(vpsId); }, 1000);
    } else {
      msg.innerHTML = '<span style="color:#ef4444;">✘ ' + (d.erro || 'Erro') + '</span>';
    }
  }).catch(function(e){ msg.innerHTML = '<span style="color:#ef4444;">Erro: ' + e + '</span>'; });
}

function limparPath(vpsId, path, btn) {
  if (!confirm('Apagar permanentemente todos os arquivos em:\n\n' + path + '\n\nEssa ação é irreversível. Continuar?')) return;
  btn.disabled = true; btn.textContent = '...';
  var msg = document.getElementById('limpar-msg-' + vpsId);
  fetch('/cliente/armazenamento/limpar', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(CSRF) + '&vps_id=' + vpsId + '&acao=limpar_path&path=' + encodeURIComponent(path)
  }).then(function(r){return r.json();}).then(function(d){
    btn.disabled = false; btn.textContent = 'Apagar';
    if (d.ok) {
      msg.innerHTML = '<span style="color:#10b981;">✓ Arquivos apagados.</span>';
      setTimeout(function(){ escanearVps(vpsId); }, 1000);
    } else {
      msg.innerHTML = '<span style="color:#ef4444;">✘ ' + (d.erro || 'Erro') + '</span>';
    }
  }).catch(function(e){ btn.disabled = false; btn.textContent = 'Apagar'; msg.innerHTML = '<span style="color:#ef4444;">Erro: ' + e + '</span>'; });
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
