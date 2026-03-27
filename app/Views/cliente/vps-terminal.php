<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$vps    = (array)($vps ?? []);
$vpsId  = (int)($vps['id'] ?? 0);
$status = (string)($vps['status'] ?? '');

$pageTitle    = I18n::tf('terminal.titulo', $vpsId);
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>
<meta name="csrf-token" content="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::tf('terminal.titulo', $vpsId)); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('terminal.subtitulo')); ?></div>
  </div>
  <a href="/cliente/vps" class="botao ghost sm">← VPS</a>
</div>

<div class="card-new">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
    <div>
      <div style="font-weight:600;font-size:14px;margin-bottom:4px;"><?php echo View::e(I18n::t('terminal.abrir_sessao')); ?></div>
      <div style="font-size:13px;color:#64748b;"><?php echo View::e(I18n::t('terminal.auditado')); ?></div>
    </div>
    <span class="badge-new" style="background:#f1f5f9;color:#334155;">HTTP</span>
  </div>

  <?php if ($status !== 'running'): ?>
    <div class="erro" style="margin-bottom:12px;"><?php echo View::e(I18n::tf('terminal.vps_nao_rodando', $status)); ?></div>
  <?php endif; ?>

  <div style="margin-bottom:12px;">
    <button class="botao" id="btnConectar" type="button" style="width:100%;" <?php echo ($status !== 'running') ? 'disabled' : ''; ?>><?php echo View::e(I18n::t('terminal.conectar')); ?></button>
  </div>

  <div id="alerta" class="erro" style="display:none;margin-bottom:12px;"></div>

  <div style="border:1px solid #0b1220;border-radius:14px;overflow:hidden;background:#020617;">
    <div style="padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.18);color:#e2e8f0;font-size:13px;display:flex;justify-content:space-between;align-items:center;">
      <div>terminal <span style="opacity:.6;">(HTTP)</span></div>
      <div style="display:flex;gap:10px;align-items:center;">
        <button id="btnUpload" class="botao ghost sm" type="button" style="padding:4px 10px;font-size:12px;"><?php echo View::e(I18n::t('terminal.upload')); ?></button>
        <button id="btnDownload" class="botao ghost sm" type="button" style="padding:4px 10px;font-size:12px;"><?php echo View::e(I18n::t('terminal.download')); ?></button>
        <div id="status" style="opacity:.9;font-size:13px;"><?php echo View::e(I18n::t('terminal.desconectado')); ?></div>
      </div>
    </div>
    <pre id="out" style="margin:0;padding:12px;color:#e2e8f0;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;height:420px;overflow:auto;white-space:pre-wrap;word-break:break-all;"></pre>
    <div style="border-top:1px solid rgba(148,163,184,.18);padding:10px 12px;display:flex;gap:8px;align-items:center;">
      <span id="prompt" style="color:#22c55e;font-family:monospace;font-size:13px;">$</span>
      <input class="input" id="in" type="text" autocomplete="off" placeholder="<?php echo View::e(I18n::t('terminal.placeholder') !== 'terminal.placeholder' ? I18n::t('terminal.placeholder') : 'Digite um comando e pressione Enter'); ?>"
             style="background:#0b1220;border-color:#1f2937;color:#e2e8f0;flex:1;" disabled />
    </div>
  </div>
</div>

<!-- Modal upload -->
<div id="modalUpload" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center;">
  <div class="card-new" style="max-width:420px;width:90%;">
    <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('terminal.enviar_arquivo')); ?></div>
    <div style="margin-bottom:10px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo View::e(I18n::t('terminal.arquivo')); ?></label>
      <input type="file" id="uploadFile" class="input" />
    </div>
    <div style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo View::e(I18n::t('terminal.caminho_remoto')); ?></label>
      <input type="text" id="uploadPath" class="input" placeholder="/home/user/arquivo.txt" />
    </div>
    <div id="uploadStatus" style="font-size:13px;margin-bottom:10px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="botao" id="btnUploadEnviar" type="button"><?php echo View::e(I18n::t('geral.enviar')); ?></button>
      <button class="botao ghost" type="button" onclick="document.getElementById('modalUpload').style.display='none'"><?php echo View::e(I18n::t('geral.cancelar')); ?></button>
    </div>
  </div>
</div>

<!-- Modal download -->
<div id="modalDownload" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center;">
  <div class="card-new" style="max-width:420px;width:90%;">
    <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('terminal.baixar_arquivo')); ?></div>
    <div style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo View::e(I18n::t('terminal.caminho_remoto')); ?></label>
      <input type="text" id="downloadPath" class="input" placeholder="/home/user/arquivo.txt" />
    </div>
    <div style="display:flex;gap:8px;">
      <button class="botao" id="btnDownloadIniciar" type="button"><?php echo View::e(I18n::t('terminal.baixar')); ?></button>
      <button class="botao ghost" type="button" onclick="document.getElementById('modalDownload').style.display='none'"><?php echo View::e(I18n::t('geral.cancelar')); ?></button>
    </div>
  </div>
</div>

<script>
(function(){
  var VPS_ID=<?php echo $vpsId; ?>;
  var elOut=document.getElementById('out');
  var elIn=document.getElementById('in');
  var elStatus=document.getElementById('status');
  var elAlerta=document.getElementById('alerta');
  var elPrompt=document.getElementById('prompt');
  var btn=document.getElementById('btnConectar');
  var conectado=false,executando=false,cwd='~';
  var historico=[],histIdx=0;

  function csrf(){return(document.querySelector('meta[name="csrf-token"]')||{}).content||'';}
  function setErro(msg){elAlerta.style.display='block';elAlerta.textContent=msg;}
  function clearErro(){elAlerta.style.display='none';}
  function append(text,color){
    var span=document.createElement('span');
    span.textContent=text;
    if(color)span.style.color=color;
    elOut.appendChild(span);
    elOut.scrollTop=elOut.scrollHeight;
  }
  function promptStr(){return cwd+'$';}

  function conectar(){
    clearErro();
    elOut.textContent='';
    conectado=true;
    elIn.disabled=false;
    elIn.focus();
    elStatus.textContent='Conectado (HTTP)';
    elPrompt.textContent=promptStr();
    append('Sessão iniciada via HTTP.\n','#22c55e');
    append('Comandos são executados dentro do container da sua VPS.\n\n','#94a3b8');
  }

  async function executarComando(cmd){
    if(executando)return;
    executando=true;elIn.disabled=true;
    elStatus.textContent='Executando...';

    var realCmd=cmd;
    var isCD=/^\s*cd\s/.test(cmd)||cmd.trim()==='cd';
    if(isCD) realCmd=cmd+' && pwd';

    var body=new URLSearchParams();
    body.set('vps_id',String(VPS_ID));
    body.set('command',realCmd);
    try{
      var resp=await fetch('/cliente/vps/terminal/exec',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf(),'Accept':'application/json'},body:body});
      var json=await resp.json();
      if(!json.ok){
        append((json.erro||'Erro')+'\n','#ef4444');
      }else{
        var out=(json.output||'').replace(/\r\n/g,'\n');
        if(isCD&&json.exit_code===0&&out.trim()!==''){
          var lines=out.trim().split('\n');
          cwd=lines[lines.length-1]||cwd;
          if(lines.length>1)append(lines.slice(0,-1).join('\n')+'\n');
        }else if(out!==''){
          append(out+'\n');
        }
        if(json.exit_code!==0) append('[exit '+json.exit_code+']\n','#f59e0b');
      }
    }catch(e){
      append('Erro de rede: '+e.message+'\n','#ef4444');
    }
    elPrompt.textContent=promptStr();
    elStatus.textContent='Conectado (HTTP)';
    executando=false;elIn.disabled=false;elIn.focus();
  }

  btn.addEventListener('click',conectar);
  elIn.addEventListener('keydown',function(ev){
    if(ev.key==='Enter'){
      ev.preventDefault();
      var v=elIn.value;elIn.value='';
      if(!conectado){setErro('Não conectado.');return;}
      if(v.trim()==='')return;
      historico.push(v);histIdx=historico.length;
      append(promptStr()+' '+v+'\n','#22c55e');
      executarComando(v);
    }else if(ev.key==='ArrowUp'){
      ev.preventDefault();
      if(histIdx>0){histIdx--;elIn.value=historico[histIdx]||'';}
    }else if(ev.key==='ArrowDown'){
      ev.preventDefault();
      if(histIdx<historico.length-1){histIdx++;elIn.value=historico[histIdx]||'';}
      else{histIdx=historico.length;elIn.value='';}
    }
  });

  document.getElementById('btnUpload').addEventListener('click',function(){document.getElementById('modalUpload').style.display='flex';});
  document.getElementById('btnUploadEnviar').addEventListener('click',async function(){
    var file=document.getElementById('uploadFile').files[0];
    var path=document.getElementById('uploadPath').value.trim();
    var statusEl=document.getElementById('uploadStatus');
    if(!file||!path){statusEl.style.display='block';statusEl.textContent='Selecione arquivo e caminho.';return;}
    statusEl.style.display='block';statusEl.textContent='Enviando...';
    var fd=new FormData();fd.append('file',file);fd.append('vps_id',String(VPS_ID));fd.append('remote_path',path);
    try{var resp=await fetch('/cliente/vps/terminal/upload',{method:'POST',headers:{'x-csrf-token':csrf()},body:fd});var json=await resp.json();statusEl.textContent=json.ok?'✓ '+(json.mensagem||'Enviado.'):'✗ '+(json.erro||'Erro.');}catch(e){statusEl.textContent='✗ Erro de rede.';}
  });
  document.getElementById('btnDownload').addEventListener('click',function(){document.getElementById('modalDownload').style.display='flex';});
  document.getElementById('btnDownloadIniciar').addEventListener('click',function(){
    var path=document.getElementById('downloadPath').value.trim();
    if(!path)return;
    window.location.href='/cliente/vps/terminal/download?vps_id='+VPS_ID+'&remote_path='+encodeURIComponent(path);
    document.getElementById('modalDownload').style.display='none';
  });
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
