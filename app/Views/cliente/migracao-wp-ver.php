<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$m = $migration ?? [];
$id = (int)($m['id'] ?? 0);
$status = (string)($m['status'] ?? 'pending');
$progress = (int)($m['progress_percent'] ?? 0);

$pageTitle = I18n::t('migracao_wp_cli.detalhe') . ' #' . $id;
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>
<div style="margin-bottom:8px;">
  <a href="/cliente/migracoes-wp" style="color:var(--text-muted);text-decoration:none;">&larr; <?php echo View::e(I18n::t('migracao_wp_cli.voltar')); ?></a>
</div>

<div class="page-title"><?php echo View::e(I18n::t('migracao_wp_cli.detalhe')); ?> #<?php echo $id; ?></div>
<div class="page-subtitle">
  <?php echo View::e((string)($m['source_user']??'root')); ?>@<?php echo View::e((string)($m['source_host']??'')); ?>
  &rarr; <?php echo View::e((string)($m['dest_domain'] ?: 'VPS #' . ($m['vps_id']??''))); ?>
</div>

<!-- Barra de progresso -->
<div class="card-new" style="margin-bottom:16px;">
  <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
    <span id="statusLabel" style="font-weight:600;color:var(--text);"><?php echo View::e(ucfirst(str_replace('_', ' ', $status))); ?></span>
    <span id="progressLabel" style="color:var(--text-muted);"><?php echo $progress; ?>%</span>
  </div>
  <div style="background:var(--border);border-radius:6px;height:12px;overflow:hidden;">
    <div id="progressBar" style="background:<?php echo $status==='failed'?'#ef4444':($status==='completed'?'#10b981':'var(--accent)'); ?>;height:100%;width:<?php echo $progress; ?>%;transition:width .5s ease;border-radius:6px;"></div>
  </div>
  <div id="stepInfo" style="margin-top:8px;font-size:13px;color:var(--text-muted);">
    <?php if ($m['current_step'] ?? ''): ?>
      <?php echo View::e(I18n::t('migracao_wp_cli.etapa')); ?>: <strong><?php echo View::e((string)$m['current_step']); ?></strong>
    <?php endif; ?>
  </div>
</div>

<!-- Timeline de etapas -->
<div class="card-new" style="margin-bottom:16px;">
  <div id="stepsTimeline" style="display:flex;gap:8px;flex-wrap:wrap;">
    <?php
    $steps = ['connecting','syncing_files','dumping_db','importing_db','configuring','finalizing','completed'];
    $stepLabels = [
      I18n::t('migracao_wp_cli.step_connecting'),
      I18n::t('migracao_wp_cli.step_syncing'),
      I18n::t('migracao_wp_cli.step_dump'),
      I18n::t('migracao_wp_cli.step_import'),
      I18n::t('migracao_wp_cli.step_config'),
      I18n::t('migracao_wp_cli.step_final'),
      I18n::t('migracao_wp_cli.concluida'),
    ];
    $currentIdx = array_search($status, $steps);
    foreach ($steps as $i => $s):
      $done = $currentIdx !== false && $i < $currentIdx;
      $active = $s === $status;
      $color = $done ? '#10b981' : ($active ? 'var(--accent)' : 'var(--border)');
      $textColor = ($done || $active) ? '#fff' : 'var(--text-muted)';
    ?>
    <div style="padding:6px 10px;border-radius:16px;font-size:11px;font-weight:500;background:<?php echo $color; ?>;color:<?php echo $textColor; ?>;" data-step="<?php echo $s; ?>">
      <?php echo $done ? '&#10003; ' : ''; ?><?php echo View::e($stepLabels[$i]); ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Ações -->
<div class="card-new" style="margin-bottom:16px;display:flex;gap:12px;align-items:center;">
  <?php if ($status === 'pending'): ?>
  <button class="btn btn-primary" onclick="testarConexao()"><?php echo View::e(I18n::t('migracao_wp_cli.testar_ssh')); ?></button>
  <?php endif; ?>
  <?php if (!in_array($status, ['completed','failed','cancelled'], true)): ?>
  <button class="btn btn-danger" onclick="cancelar()"><?php echo View::e(I18n::t('geral.cancelar')); ?></button>
  <?php endif; ?>
  <span id="actionMsg" style="font-size:13px;color:var(--text-muted);"></span>
</div>

<?php if ($status === 'failed' && !empty($m['error_message'])): ?>
<div class="card-new" style="margin-bottom:16px;border-left:3px solid #ef4444;">
  <h4 style="margin:0 0 8px;font-size:14px;color:#ef4444;"><?php echo View::e(I18n::t('migracao_wp_cli.erro')); ?></h4>
  <pre style="white-space:pre-wrap;font-size:12px;color:var(--text);margin:0;"><?php echo View::e((string)$m['error_message']); ?></pre>
</div>
<?php endif; ?>

<?php if ($status === 'completed'): ?>
<div class="card-new" style="margin-bottom:16px;border-left:3px solid #10b981;">
  <h4 style="margin:0 0 8px;font-size:14px;color:#10b981;"><?php echo View::e(I18n::t('migracao_wp_cli.sucesso_titulo')); ?></h4>
  <p style="margin:0;font-size:13px;color:var(--text-muted);"><?php echo View::e(I18n::t('migracao_wp_cli.sucesso_desc')); ?></p>
  <?php if ($m['dest_domain'] ?? ''): ?>
  <p style="margin:8px 0 0;"><a href="https://<?php echo View::e((string)$m['dest_domain']); ?>" target="_blank" rel="noopener">https://<?php echo View::e((string)$m['dest_domain']); ?> &rarr;</a></p>
  <?php endif; ?>
</div>

<!-- Ativar / Trocar domínio -->
<div class="card-new" style="margin-bottom:16px;">
  <h4 style="margin:0 0 4px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_titulo')); ?></h4>
  <p style="margin:0 0 12px;font-size:13px;color:var(--text-muted);"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_desc')); ?></p>

  <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
    <div style="flex:1;min-width:200px;">
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_novo')); ?></label>
      <input type="text" id="novoDominioInput" class="form-control" placeholder="meusite.com.br" value="">
    </div>
    <button class="btn btn-primary" onclick="ativarDominio()" id="btnAtivarDominio"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_ativar')); ?></button>
  </div>
  <div id="dominioMsg" style="margin-top:8px;font-size:13px;"></div>

  <?php if ($m['dest_domain'] ?? ''): ?>
  <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted);">
    <?php echo View::e(I18n::t('migracao_wp_cli.dominio_atual')); ?>: <strong><?php echo View::e((string)$m['dest_domain']); ?></strong>
    <span style="margin-left:8px;opacity:.7;"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_nota')); ?></span>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Logs -->
<div class="card-new">
  <h4 style="margin:0 0 10px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp_cli.logs')); ?></h4>
  <pre id="logsArea" style="white-space:pre-wrap;font-size:12px;color:var(--text-muted);max-height:350px;overflow:auto;background:var(--bg);padding:12px;border-radius:8px;margin:0;border:1px solid var(--border);"><?php echo View::e((string)($m['logs'] ?? I18n::t('migracao_wp_cli.aguardando'))); ?></pre>
</div>

<div style="margin-top:12px;font-size:12px;color:var(--text-muted);">
  <?php echo View::e(I18n::t('migracao_wp_cli.criado_em')); ?>: <?php echo View::e((string)($m['created_at']??'')); ?>
  <?php if ($m['started_at'] ?? ''): ?>&middot; <?php echo View::e(I18n::t('migracao_wp_cli.inicio')); ?>: <?php echo View::e((string)$m['started_at']); ?><?php endif; ?>
  <?php if ($m['completed_at'] ?? ''): ?>&middot; <?php echo View::e(I18n::t('migracao_wp_cli.fim')); ?>: <?php echo View::e((string)$m['completed_at']); ?><?php endif; ?>
</div>

<script>
var MIG_ID = <?php echo $id; ?>;
var CSRF = '<?php echo Csrf::token(); ?>';
var STATUS = '<?php echo $status; ?>';
var POLLING = null;

function poll(){
  fetch('/cliente/migracoes-wp/progresso?id='+MIG_ID)
    .then(function(r){return r.json();})
    .then(function(d){
      if(!d.ok) return;
      document.getElementById('progressBar').style.width = d.progress+'%';
      document.getElementById('progressLabel').textContent = d.progress+'%';
      document.getElementById('statusLabel').textContent = d.status.replace(/_/g,' ').replace(/^\w/,function(c){return c.toUpperCase();});
      if(d.step) document.getElementById('stepInfo').innerHTML = '<?php echo View::e(I18n::t('migracao_wp_cli.etapa')); ?>: <strong>'+d.step+'</strong>';
      if(d.logs) document.getElementById('logsArea').textContent = d.logs;

      if(d.status==='completed') document.getElementById('progressBar').style.background='#10b981';
      else if(d.status==='failed') document.getElementById('progressBar').style.background='#ef4444';

      var steps=['connecting','syncing_files','dumping_db','importing_db','configuring','finalizing','completed'];
      var idx=steps.indexOf(d.status);
      document.querySelectorAll('#stepsTimeline > div').forEach(function(el,i){
        if(i<idx){el.style.background='#10b981';el.style.color='#fff';}
        else if(i===idx){el.style.background='var(--accent)';el.style.color='#fff';}
        else{el.style.background='var(--border)';el.style.color='var(--text-muted)';}
      });

      if(d.status==='completed'||d.status==='failed'||d.status==='cancelled'){
        clearInterval(POLLING);
        if(d.status==='completed') setTimeout(function(){location.reload();},1500);
      }
      STATUS = d.status;
    }).catch(function(){});
}

if(STATUS!=='completed'&&STATUS!=='failed'&&STATUS!=='cancelled'){
  POLLING = setInterval(poll, 3000);
  setTimeout(poll, 800);
}

function testarConexao(){
  document.getElementById('actionMsg').textContent='<?php echo View::e(I18n::t('migracao_wp_cli.testando')); ?>';
  fetch('/cliente/migracoes-wp/testar-conexao',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID})
    .then(function(r){return r.json();})
    .then(function(d){
      document.getElementById('actionMsg').textContent = d.ok ? '<?php echo View::e(I18n::t('migracao_wp_cli.conexao_ok')); ?>' : (d.erro||'Erro');
      document.getElementById('actionMsg').style.color = d.ok?'#10b981':'#ef4444';
    }).catch(function(e){document.getElementById('actionMsg').textContent='Erro: '+e;});
}

function cancelar(){
  if(!confirm('<?php echo View::e(I18n::t('migracao_wp_cli.confirmar_cancelar')); ?>')) return;
  fetch('/cliente/migracoes-wp/cancelar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID})
    .then(function(r){return r.json();})
    .then(function(d){ if(d.ok) location.reload(); });
}

function ativarDominio(){
  var dom=document.getElementById('novoDominioInput').value.trim();
  if(!dom){document.getElementById('dominioMsg').textContent='<?php echo View::e(I18n::t('migracao_wp_cli.dominio_vazio')); ?>';document.getElementById('dominioMsg').style.color='#ef4444';return;}
  if(!confirm('<?php echo View::e(I18n::t('migracao_wp_cli.dominio_confirmar')); ?> '+dom+'?')) return;
  var btn=document.getElementById('btnAtivarDominio');btn.disabled=true;btn.textContent='<?php echo View::e(I18n::t('geral.processando')); ?>';
  document.getElementById('dominioMsg').textContent='';
  fetch('/cliente/migracoes-wp/ativar-dominio',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&id='+MIG_ID+'&dominio='+encodeURIComponent(dom)})
    .then(function(r){return r.json();})
    .then(function(d){
      btn.disabled=false;btn.textContent='<?php echo View::e(I18n::t('migracao_wp_cli.dominio_ativar')); ?>';
      if(d.ok){
        document.getElementById('dominioMsg').innerHTML='<span style="color:#10b981;">&#10003; <?php echo View::e(I18n::t('migracao_wp_cli.dominio_sucesso')); ?> <a href="'+d.url+'" target="_blank">'+d.url+'</a></span>';
        setTimeout(function(){location.reload();},2000);
      } else {
        document.getElementById('dominioMsg').textContent=d.erro||'Erro';document.getElementById('dominioMsg').style.color='#ef4444';
      }
    }).catch(function(e){btn.disabled=false;btn.textContent='<?php echo View::e(I18n::t('migracao_wp_cli.dominio_ativar')); ?>';document.getElementById('dominioMsg').textContent='Erro: '+e;document.getElementById('dominioMsg').style.color='#ef4444';});
}
</script>
<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
