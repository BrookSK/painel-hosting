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

<!-- Mensagem tranquilizadora -->
<?php if (!in_array($status, ['completed', 'failed', 'cancelled'], true)): ?>
<div id="reassureBox" class="card-new" style="margin-bottom:16px;background:#f0fdf4;border:1px solid #bbf7d0;">
  <div style="display:flex;align-items:flex-start;gap:12px;">
    <div style="font-size:22px;line-height:1;" id="reassureIcon">&#10003;</div>
    <div>
      <div style="font-size:14px;font-weight:700;color:#166534;margin-bottom:4px;" id="reassureTitle">Tudo está funcionando normalmente</div>
      <div style="font-size:13px;color:#15803d;line-height:1.6;" id="reassureMsg">
        <?php if ($status === 'rsync_transfer' || ($m['current_step'] ?? '') === 'rsync_transfer'): ?>
          Seus arquivos estão sendo copiados de servidor a servidor. Esse processo acontece em segundo plano — você pode fechar esta página, desligar o computador ou sair do sistema que a migração continua normalmente. Sites grandes (com muitas imagens e uploads) podem levar de 30 minutos a 2 horas. Não se preocupe, está tudo certo.
        <?php elseif ($status === 'connecting' || $status === 'syncing_files'): ?>
          Estamos conectando ao servidor de origem e preparando a transferência. Isso leva poucos segundos.
        <?php elseif ($status === 'dumping_db'): ?>
          O banco de dados está sendo exportado do servidor de origem. Normalmente leva de 10 segundos a 5 minutos, dependendo do tamanho.
        <?php elseif ($status === 'importing_db'): ?>
          O banco de dados está sendo importado no novo servidor. Quase lá!
        <?php elseif ($status === 'configuring'): ?>
          Estamos ajustando as configurações do WordPress para o novo servidor (wp-config.php, URLs, Nginx). Falta muito pouco!
        <?php elseif ($status === 'finalizing'): ?>
          Finalizando a migração — aplicando permissões e configurações finais. Questão de segundos!
        <?php else: ?>
          A migração está em andamento. Você pode acompanhar o progresso aqui ou fechar a página — o processo continua no servidor.
        <?php endif; ?>
      </div>
      <div style="margin-top:10px;font-size:12px;color:#166534;opacity:.8;">
        <span id="reassureTimer"></span>
        <span style="margin-left:8px;">&#8226; A página atualiza automaticamente a cada 3 segundos</span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Atividade em tempo real -->
<?php if (!in_array($status, ['completed', 'failed', 'cancelled'], true)): ?>
<div id="activityBox" class="card-new" style="margin-bottom:16px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
    <h4 style="margin:0;font-size:14px;color:var(--text);">Atividade em tempo real</h4>
    <span style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:4px;" id="activityPulse">
      <span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;animation:pulse 1.5s infinite;"></span> Monitorando
    </span>
  </div>
  <div style="display:flex;gap:24px;flex-wrap:wrap;">
    <div style="text-align:center;">
      <div style="font-size:24px;font-weight:700;color:var(--accent);" id="actSize">—</div>
      <div style="font-size:12px;color:var(--text-muted);">Transferido</div>
    </div>
    <div style="text-align:center;">
      <div style="font-size:24px;font-weight:700;color:var(--accent);" id="actFiles">—</div>
      <div style="font-size:12px;color:var(--text-muted);">Arquivos copiados</div>
    </div>
  </div>
  <div style="margin-top:10px;font-size:11px;color:var(--text-muted);">Atualiza a cada 10 segundos. Os dados são consultados diretamente no servidor de destino.</div>
</div>
<style>@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.4;}}</style>
<?php endif; ?>

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
  <button class="botao sm" onclick="testarConexao()"><?php echo View::e(I18n::t('migracao_wp_cli.testar_ssh')); ?></button>
  <?php endif; ?>
  <?php if (!in_array($status, ['completed','failed','cancelled'], true)): ?>
  <button class="botao danger sm" onclick="cancelar()"><?php echo View::e(I18n::t('geral.cancelar')); ?></button>
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
      <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_novo')); ?></label>
      <input type="text" id="novoDominioInput" class="input" placeholder="meusite.com.br" value="">
    </div>
    <button class="botao sm" onclick="ativarDominio()" id="btnAtivarDominio"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_ativar')); ?></button>
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

<!-- Dados da migração (campos preenchidos) -->
<div class="card-new" style="margin-bottom:16px;">
  <details>
    <summary style="font-size:14px;font-weight:700;cursor:pointer;">Dados da migração (ver informações preenchidas)</summary>
    <div style="margin-top:14px;">
      <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px;">Servidor de origem — SSH</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;margin-bottom:16px;">
        <div><span style="font-size:12px;color:var(--text-muted);">Host / IP</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;"><?php echo View::e((string)($m['source_host'] ?? '')); ?> <button onclick="navigator.clipboard.writeText('<?php echo View::e((string)($m['source_host'] ?? '')); ?>')" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--accent);">copiar</button></div></div>
        <div><span style="font-size:12px;color:var(--text-muted);">Porta SSH</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;"><?php echo (int)($m['source_port'] ?? 22); ?></div></div>
        <div><span style="font-size:12px;color:var(--text-muted);">Usuário</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;"><?php echo View::e((string)($m['source_user'] ?? '')); ?> <button onclick="navigator.clipboard.writeText('<?php echo View::e((string)($m['source_user'] ?? '')); ?>')" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--accent);">copiar</button></div></div>
        <div><span style="font-size:12px;color:var(--text-muted);">Senha SSH</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;"><span id="pwd-ssh"><?php echo View::e($m['_source_password_masked']); ?></span> <button onclick="revelarSenha('source_password_enc','pwd-ssh')" style="background:none;border:none;cursor:pointer;font-size:14px;" title="Ver senha">👁</button></div></div>
        <div style="grid-column:span 2;"><span style="font-size:12px;color:var(--text-muted);">Caminho do WordPress</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;"><?php echo View::e((string)($m['source_wp_path'] ?? '')); ?> <button onclick="navigator.clipboard.writeText('<?php echo View::e((string)($m['source_wp_path'] ?? '')); ?>')" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--accent);">copiar</button></div></div>
        <?php if ((int)($m['source_use_sudo'] ?? 0) === 1): ?>
        <div style="grid-column:span 2;"><span style="font-size:12px;color:var(--text-muted);">Sudo</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;">Ativado — Senha: <span id="pwd-sudo"><?php echo View::e($m['_source_sudo_password_masked']); ?></span> <button onclick="revelarSenha('source_sudo_password_enc','pwd-sudo')" style="background:none;border:none;cursor:pointer;font-size:14px;" title="Ver senha">👁</button></div></div>
        <?php endif; ?>
      </div>

      <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px;">Servidor de origem — Banco de Dados</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;margin-bottom:16px;">
        <div><span style="font-size:12px;color:var(--text-muted);">Nome do banco</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;"><?php echo View::e((string)($m['source_db_name'] ?? '')); ?> <button onclick="navigator.clipboard.writeText('<?php echo View::e((string)($m['source_db_name'] ?? '')); ?>')" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--accent);">copiar</button></div></div>
        <div><span style="font-size:12px;color:var(--text-muted);">Usuário MySQL</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;"><?php echo View::e((string)($m['source_db_user'] ?? '')); ?> <button onclick="navigator.clipboard.writeText('<?php echo View::e((string)($m['source_db_user'] ?? '')); ?>')" style="background:none;border:none;cursor:pointer;font-size:11px;color:var(--accent);">copiar</button></div></div>
        <div><span style="font-size:12px;color:var(--text-muted);">Senha MySQL</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;display:flex;justify-content:space-between;align-items:center;"><span id="pwd-db"><?php echo View::e($m['_source_db_password_masked']); ?></span> <button onclick="revelarSenha('source_db_password_enc','pwd-db')" style="background:none;border:none;cursor:pointer;font-size:14px;" title="Ver senha">👁</button></div></div>
        <div><span style="font-size:12px;color:var(--text-muted);">Host MySQL</span><div style="font-family:monospace;font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;"><?php echo View::e((string)($m['source_db_host'] ?? '')); ?>:<?php echo (int)($m['source_db_port'] ?? 3306); ?></div></div>
      </div>

      <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px;">Destino</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;">
        <div><span style="font-size:12px;color:var(--text-muted);">VPS</span><div style="font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;">#<?php echo (int)($m['vps_id'] ?? 0); ?></div></div>
        <div><span style="font-size:12px;color:var(--text-muted);">Domínio</span><div style="font-size:13px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;"><?php echo View::e((string)($m['dest_domain'] ?? '(temporário)')); ?></div></div>
      </div>
    </div>
  </details>
</div>

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
var startTime = new Date('<?php echo (string)($m['started_at'] ?? $m['created_at'] ?? ''); ?>'.replace(' ', 'T'));

var reassureMsgs = {
  'connecting': 'Estamos conectando ao servidor de origem e preparando a transferência. Isso leva poucos segundos.',
  'syncing_files': 'Seus arquivos estão sendo copiados de servidor a servidor. Esse processo acontece em segundo plano — você pode fechar esta página, desligar o computador ou sair do sistema que a migração continua normalmente. Sites grandes (com muitas imagens e uploads) podem levar de 30 minutos a 2 horas. Não se preocupe, está tudo certo.',
  'rsync_transfer': 'Seus arquivos estão sendo copiados de servidor a servidor. Esse processo acontece em segundo plano — você pode fechar esta página, desligar o computador ou sair do sistema que a migração continua normalmente. Sites grandes (com muitas imagens e uploads) podem levar de 30 minutos a 2 horas. Não se preocupe, está tudo certo.',
  'dumping_db': 'O banco de dados está sendo exportado do servidor de origem. Normalmente leva de 10 segundos a 5 minutos, dependendo do tamanho.',
  'importing_db': 'O banco de dados está sendo importado no novo servidor. Quase lá!',
  'configuring': 'Estamos ajustando as configurações do WordPress para o novo servidor (wp-config.php, URLs, Nginx). Falta muito pouco!',
  'finalizing': 'Finalizando a migração — aplicando permissões e configurações finais. Questão de segundos!'
};

function updateTimer(){
  var el=document.getElementById('reassureTimer');
  if(!el||!startTime||isNaN(startTime.getTime()))return;
  var diff=Math.floor((Date.now()-startTime.getTime())/1000);
  var h=Math.floor(diff/3600),m=Math.floor((diff%3600)/60),s=diff%60;
  var t='Tempo decorrido: ';
  if(h>0)t+=h+'h '+m+'min';
  else if(m>0)t+=m+'min '+s+'s';
  else t+=s+'s';
  el.textContent=t;
}
setInterval(updateTimer,1000);
updateTimer();

// Atividade em tempo real
var lastSize = '';
function pollActivity(){
  fetch('/cliente/migracoes-wp/atividade?id='+MIG_ID)
    .then(function(r){return r.json();})
    .then(function(d){
      if(!d.ok||!d.active)return;
      var sizeEl=document.getElementById('actSize');
      var filesEl=document.getElementById('actFiles');
      if(sizeEl&&d.size&&d.size!=='—'){
        if(lastSize!==d.size){sizeEl.style.transition='transform .3s';sizeEl.style.transform='scale(1.1)';setTimeout(function(){sizeEl.style.transform='scale(1)';},300);}
        sizeEl.textContent=d.size;lastSize=d.size;
      }
      if(filesEl&&d.files>0){filesEl.textContent=d.files.toLocaleString('pt-BR');}
    }).catch(function(){});
}
if(STATUS!=='completed'&&STATUS!=='failed'&&STATUS!=='cancelled'){
  setInterval(pollActivity, 10000);
  setTimeout(pollActivity, 2000);
}

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
        var rbox=document.getElementById('reassureBox');
        if(rbox){
          if(d.status==='completed'){rbox.style.background='#f0fdf4';rbox.style.borderColor='#bbf7d0';}
          else{rbox.style.display='none';}
        }
        if(d.status==='completed') setTimeout(function(){location.reload();},1500);
      } else {
        // Atualizar mensagem tranquilizadora
        var rmsg=document.getElementById('reassureMsg');
        if(rmsg&&reassureMsgs[d.status]){rmsg.textContent=reassureMsgs[d.status];}
        else if(rmsg&&d.step&&reassureMsgs[d.step]){rmsg.textContent=reassureMsgs[d.step];}
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

function revelarSenha(campo, spanId){
  var span=document.getElementById(spanId);
  if(!span)return;
  if(span.dataset.revealed==='1'){span.textContent='••••••••';span.dataset.revealed='0';return;}
  span.textContent='...';
  fetch('/cliente/migracoes-wp/revelar-senha?id='+MIG_ID+'&campo='+encodeURIComponent(campo))
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok){span.textContent=d.valor||'(vazio)';span.dataset.revealed='1';}
      else{span.textContent='(erro)';}
    }).catch(function(){span.textContent='(erro)';});
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
