<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$m = $migration ?? [];
$id = (int)($m['id'] ?? 0);
$status = (string)($m['status'] ?? 'pending');
$progress = (int)($m['progress_percent'] ?? 0);
$isRunning = !in_array($status, ['completed', 'failed', 'cancelled', 'pending'], true);

$pageTitle = I18n::t('migracao_wp.detalhe_titulo') . ' #' . $id;
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
  <a href="/equipe/migracoes-wp" style="color:var(--text-muted);text-decoration:none;">&larr; <?php echo View::e(I18n::t('migracao_wp.voltar_lista')); ?></a>
</div>

<div class="page-title"><?php echo View::e(I18n::t('migracao_wp.detalhe_titulo')); ?> #<?php echo $id; ?></div>
<div class="page-subtitle">
  <?php echo View::e((string)($m['client_name'] ?? '—')); ?> &mdash;
  <?php echo View::e((string)($m['source_user'] ?? 'root')); ?>@<?php echo View::e((string)($m['source_host'] ?? '')); ?>
  &rarr; <?php echo View::e((string)($m['dest_domain'] ?? 'VPS #' . ($m['vps_id'] ?? ''))); ?>
</div>

<!-- Barra de progresso -->
<div class="card-new" style="margin-bottom:16px;">
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;">
    <div style="flex:1;">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <span id="statusLabel" style="font-weight:600;color:var(--text);"><?php echo View::e(ucfirst(str_replace('_', ' ', $status))); ?></span>
        <span id="progressLabel" style="color:var(--text-muted);"><?php echo $progress; ?>%</span>
      </div>
      <div style="background:var(--border);border-radius:6px;height:12px;overflow:hidden;">
        <div id="progressBar" style="background:<?php echo $status==='failed'?'#ef4444':($status==='completed'?'#10b981':'var(--accent)'); ?>;height:100%;width:<?php echo $progress; ?>%;transition:width .5s ease;border-radius:6px;"></div>
      </div>
    </div>
  </div>

  <div id="stepInfo" style="font-size:13px;color:var(--text-muted);">
    <?php if ($m['current_step'] ?? ''): ?>
      <?php echo View::e(I18n::t('migracao_wp.etapa_atual')); ?>: <strong><?php echo View::e((string)$m['current_step']); ?></strong>
    <?php endif; ?>
  </div>
</div>

<!-- Etapas visuais -->
<div class="card-new" style="margin-bottom:16px;">
  <h4 style="margin:0 0 12px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp.etapas')); ?></h4>
  <div id="stepsTimeline" style="display:flex;gap:8px;flex-wrap:wrap;">
    <?php
    $steps = ['connecting','syncing_files','dumping_db','importing_db','configuring','finalizing','completed'];
    $stepNames = ['Conectando','Sync Arquivos','Dump DB','Importar DB','Configurando','Finalizando','Concluída'];
    $currentIdx = array_search($status, $steps);
    foreach ($steps as $i => $s):
      $done = $currentIdx !== false && $i < $currentIdx;
      $active = $s === $status;
      $color = $done ? '#10b981' : ($active ? 'var(--accent)' : 'var(--border)');
      $textColor = ($done || $active) ? '#fff' : 'var(--text-muted)';
    ?>
    <div style="padding:6px 12px;border-radius:16px;font-size:12px;font-weight:500;background:<?php echo $color; ?>;color:<?php echo $textColor; ?>;" data-step="<?php echo $s; ?>">
      <?php echo $done ? '&#10003; ' : ''; ?><?php echo $stepNames[$i]; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Info da migração -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
  <div class="card-new">
    <h4 style="margin:0 0 10px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp.info_origem')); ?></h4>
    <table style="width:100%;font-size:13px;">
      <tr><td style="color:var(--text-muted);width:40%;">Host</td><td><?php echo View::e((string)($m['source_host']??'')); ?>:<?php echo (int)($m['source_port']??22); ?></td></tr>
      <tr><td style="color:var(--text-muted);">Usuário</td><td><?php echo View::e((string)($m['source_user']??'root')); ?></td></tr>
      <tr><td style="color:var(--text-muted);">Caminho WP</td><td><code><?php echo View::e((string)($m['source_wp_path']??'')); ?></code></td></tr>
      <tr><td style="color:var(--text-muted);">Banco</td><td><?php echo View::e((string)($m['source_db_name']??'')); ?></td></tr>
      <tr><td style="color:var(--text-muted);">DB Host</td><td><?php echo View::e((string)($m['source_db_host']??'localhost')); ?>:<?php echo (int)($m['source_db_port']??3306); ?></td></tr>
    </table>
  </div>
  <div class="card-new">
    <h4 style="margin:0 0 10px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp.info_destino')); ?></h4>
    <table style="width:100%;font-size:13px;">
      <tr><td style="color:var(--text-muted);width:40%;">Domínio</td><td><?php echo View::e((string)($m['dest_domain']??'—')); ?></td></tr>
      <tr><td style="color:var(--text-muted);">Caminho</td><td><code><?php echo View::e((string)($m['dest_wp_path']??'(será definido)')); ?></code></td></tr>
      <tr><td style="color:var(--text-muted);">Banco</td><td><?php echo View::e((string)($m['dest_db_name']??'(será criado)')); ?></td></tr>
      <tr><td style="color:var(--text-muted);">Arquivos</td><td id="filesSize"><?php echo $m['files_size_bytes'] ? number_format((int)$m['files_size_bytes']/1048576, 1) . ' MB' : '—'; ?></td></tr>
      <tr><td style="color:var(--text-muted);">Dump SQL</td><td id="dbSize"><?php echo $m['db_size_bytes'] ? number_format((int)$m['db_size_bytes']/1048576, 1) . ' MB' : '—'; ?></td></tr>
    </table>
  </div>
</div>

<!-- Ações -->
<div class="card-new" style="margin-bottom:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
  <?php if ($status === 'pending'): ?>
  <button class="botao sm" onclick="testarConexao()"><?php echo View::e(I18n::t('migracao_wp.testar_conexao')); ?></button>
  <?php endif; ?>
  <?php if (!in_array($status, ['completed','cancelled'], true)): ?>
  <button class="botao sm" onclick="retomar()" id="btnRetomar">Retomar de onde parou</button>
  <?php endif; ?>
  <?php if (in_array($status, ['failed','cancelled'], true)): ?>
  <button class="botao sm" onclick="reexecutar()"><?php echo View::e(I18n::t('migracao_wp.reexecutar')); ?></button>
  <?php endif; ?>
  <?php if ($status === 'completed' && !empty($m['dest_domain'])): ?>
  <button class="botao sm ghost" onclick="recriarDns()">Recriar DNS</button>
  <?php endif; ?>
  <?php if (!in_array($status, ['completed','cancelled'], true)): ?>
  <button class="botao danger sm" onclick="cancelar()"><?php echo View::e(I18n::t('migracao_wp.cancelar_migracao')); ?></button>
  <?php endif; ?>
  <span id="actionMsg" style="font-size:13px;color:var(--text-muted);"></span>
</div>

<?php if ($status === 'failed' && !empty($m['error_message'])): ?>
<div class="card-new" style="margin-bottom:16px;border-left:3px solid #ef4444;">
  <h4 style="margin:0 0 8px;font-size:14px;color:#ef4444;"><?php echo View::e(I18n::t('migracao_wp.erro')); ?></h4>
  <pre style="white-space:pre-wrap;font-size:12px;color:var(--text);margin:0;"><?php echo View::e((string)$m['error_message']); ?></pre>
</div>
<?php endif; ?>

<!-- Logs -->
<div class="card-new">
  <h4 style="margin:0 0 10px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp.logs')); ?></h4>
  <pre id="logsArea" style="white-space:pre-wrap;font-size:12px;color:var(--text-muted);max-height:400px;overflow:auto;background:var(--bg);padding:12px;border-radius:8px;margin:0;border:1px solid var(--border);"><?php echo View::e((string)($m['logs'] ?? 'Aguardando início...')); ?></pre>
</div>

<!-- Metadata -->
<div style="margin-top:12px;font-size:12px;color:var(--text-muted);">
  <?php echo View::e(I18n::t('migracao_wp.criado_por')); ?>: <?php echo View::e((string)($m['created_by_name'] ?? '—')); ?>
  &middot; <?php echo View::e(I18n::t('migracao_wp.criado_em')); ?>: <?php echo View::e((string)($m['created_at'] ?? '')); ?>
  <?php if ($m['started_at'] ?? ''): ?>&middot; Início: <?php echo View::e((string)$m['started_at']); ?><?php endif; ?>
  <?php if ($m['completed_at'] ?? ''): ?>&middot; Fim: <?php echo View::e((string)$m['completed_at']); ?><?php endif; ?>
</div>

<script>
var MIG_ID = <?php echo $id; ?>;
var CSRF = '<?php echo Csrf::token(); ?>';
var STATUS = '<?php echo $status; ?>';
var POLLING = null;

function poll(){
  fetch('/equipe/migracoes-wp/progresso?id='+MIG_ID)
    .then(function(r){return r.json();})
    .then(function(d){
      if(!d.ok) return;
      document.getElementById('progressBar').style.width = d.progress+'%';
      document.getElementById('progressLabel').textContent = d.progress+'%';
      document.getElementById('statusLabel').textContent = d.status.replace(/_/g,' ').replace(/^\w/,function(c){return c.toUpperCase();});
      if(d.step) document.getElementById('stepInfo').innerHTML = 'Etapa atual: <strong>'+d.step+'</strong>';
      if(d.logs) document.getElementById('logsArea').textContent = d.logs;

      // Atualizar barra de cor
      if(d.status==='completed') document.getElementById('progressBar').style.background='#10b981';
      else if(d.status==='failed') document.getElementById('progressBar').style.background='#ef4444';

      // Atualizar etapas visuais
      var steps=['connecting','syncing_files','dumping_db','importing_db','configuring','finalizing','completed'];
      var idx=steps.indexOf(d.status);
      document.querySelectorAll('#stepsTimeline > div').forEach(function(el,i){
        if(i<idx){el.style.background='#10b981';el.style.color='#fff';}
        else if(i===idx){el.style.background='var(--accent)';el.style.color='#fff';}
        else{el.style.background='var(--border)';el.style.color='var(--text-muted)';}
      });

      if(d.status==='completed'||d.status==='failed'||d.status==='cancelled'){
        clearInterval(POLLING);
        if(d.status==='completed') location.reload();
      }
      STATUS = d.status;
    }).catch(function(){});
}

if(STATUS!=='completed'&&STATUS!=='failed'&&STATUS!=='cancelled'){
  POLLING = setInterval(poll, 3000);
  setTimeout(poll, 500);
}

function testarConexao(){
  document.getElementById('actionMsg').textContent='Testando...';
  fetch('/equipe/migracoes-wp/testar-conexao',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID})
    .then(function(r){return r.json();})
    .then(function(d){
      document.getElementById('actionMsg').textContent = d.ok ? 'Conexão OK: '+d.output : 'Falhou: '+(d.erro||'erro');
      document.getElementById('actionMsg').style.color = d.ok?'#10b981':'#ef4444';
    }).catch(function(e){document.getElementById('actionMsg').textContent='Erro: '+e;});
}

function retomar(){
  if(!confirm('Retomar a migração de onde parou? O sistema verifica automaticamente o que já foi feito.')) return;
  var btn=document.getElementById('btnRetomar');btn.disabled=true;btn.textContent='Retomando...';
  document.getElementById('actionMsg').textContent='Verificando e criando job...';
  fetch('/equipe/migracoes-wp/reexecutar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID})
    .then(function(r){return r.json();})
    .then(function(d){
      btn.disabled=false;btn.textContent='Retomar de onde parou';
      if(d.ok){document.getElementById('actionMsg').innerHTML='<span style="color:#10b981;">✓ Job criado. Aguarde...</span>';setTimeout(function(){location.reload();},2000);}
      else{document.getElementById('actionMsg').innerHTML='<span style="color:#ef4444;">✘ '+(d.erro||'Erro')+'</span>';}
    }).catch(function(e){btn.disabled=false;btn.textContent='Retomar de onde parou';});
}

function recriarDns(){
  document.getElementById('actionMsg').textContent='Criando DNS...';
  fetch('/equipe/migracoes-wp/recriar-dns',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID})
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok){document.getElementById('actionMsg').innerHTML='<span style="color:#10b981;">✓ DNS: '+d.domain+' → '+d.ip+'</span>';}
      else{document.getElementById('actionMsg').innerHTML='<span style="color:#ef4444;">✘ '+(d.erro||'Erro')+'</span>';}
    }).catch(function(e){document.getElementById('actionMsg').textContent='Erro: '+e;});
}

function reexecutar(){
  if(!confirm('Reexecutar esta migração?')) return;
  fetch('/equipe/migracoes-wp/reexecutar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID})
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok) location.reload();
      else document.getElementById('actionMsg').textContent=d.erro||'Erro';
    });
}

function cancelar(){
  if(!confirm('Cancelar esta migração?')) return;
  fetch('/equipe/migracoes-wp/cancelar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID})
    .then(function(r){return r.json();})
    .then(function(d){ if(d.ok) location.reload(); });
}
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
