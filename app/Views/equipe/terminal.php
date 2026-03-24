<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = I18n::t('eq_terminal.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<meta name="csrf-token" content="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
<div class="page-title"><?php echo View::e(I18n::t('eq_terminal.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_terminal.subtitulo_http')); ?></div>

<div class="card-new">
  <div class="grid" style="margin-bottom:12px;">
    <div>
      <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_terminal.node')); ?></label>
      <select class="input" id="server_id">
        <option value=""><?php echo View::e(I18n::t('eq_terminal.selecione')); ?></option>
<?php foreach (((array)($servers??[])) as $s): ?>
<?php $sid=(int)($s['id']??0);$hn=(string)($s['hostname']??'');$ip=(string)($s['ip_address']??'');$st=(string)($s['status']??''); ?>
        <option value="<?php echo $sid; ?>">#<?php echo $sid; ?> <?php echo View::e($hn); ?> (<?php echo View::e($ip); ?>)<?php echo $st!=='active'?' ['.View::e(I18n::t('eq_terminal.inativo')).']':''; ?></option>
<?php endforeach; ?>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:13px;margin-bottom:6px;">&nbsp;</label>
      <div class="linha" style="gap:8px;">
        <button class="botao" id="btnConectar" type="button"><?php echo View::e(I18n::t('eq_terminal.conectar')); ?></button>
        <button class="botao sec" id="btnUpload" type="button"><?php echo View::e(I18n::t('eq_terminal.upload')); ?></button>
        <button class="botao sec" id="btnDownload" type="button"><?php echo View::e(I18n::t('eq_terminal.download')); ?></button>
      </div>
    </div>
  </div>

  <div id="alerta" class="erro" style="display:none;margin-bottom:12px;"></div>

  <div style="border:1px solid #0b1220;border-radius:14px;overflow:hidden;background:#020617;">
    <div style="padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.18);color:#e2e8f0;font-size:13px;display:flex;justify-content:space-between;">
      <div>Terminal <span style="opacity:.6;">(HTTP)</span></div>
      <div id="status" style="opacity:.9;font-size:12px;"><?php echo View::e(I18n::t('eq_terminal.desconectado')); ?></div>
    </div>
    <pre id="out" style="margin:0;padding:12px;color:#e2e8f0;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;height:420px;overflow:auto;white-space:pre-wrap;word-break:break-all;"></pre>
    <div style="border-top:1px solid rgba(148,163,184,.18);padding:10px 12px;display:flex;gap:8px;align-items:center;">
      <span id="prompt" style="color:#22c55e;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;white-space:nowrap;">$</span>
      <input class="input" id="in" type="text" autocomplete="off" placeholder="<?php echo View::e(I18n::t('eq_terminal.placeholder')); ?>" style="background:#0b1220;border-color:#1f2937;color:#e2e8f0;flex:1;" disabled />
    </div>
  </div>

  <!-- Modal upload -->
  <div id="modalUpload" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center;">
    <div class="card-new" style="max-width:420px;width:90%;">
      <div class="card-new-title"><?php echo View::e(I18n::t('eq_terminal.enviar_arquivo')); ?></div>
      <div style="margin-bottom:10px;"><label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo View::e(I18n::t('eq_terminal.arquivo')); ?></label><input type="file" id="uploadFile" class="input" /></div>
      <div style="margin-bottom:12px;"><label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo View::e(I18n::t('eq_terminal.caminho_remoto')); ?></label><input type="text" id="uploadPath" class="input" placeholder="/tmp/arquivo.txt" /></div>
      <div id="uploadStatus" style="font-size:13px;margin-bottom:10px;display:none;"></div>
      <div class="linha" style="gap:8px;">
        <button class="botao" id="btnUploadEnviar" type="button"><?php echo View::e(I18n::t('eq_terminal.enviar')); ?></button>
        <button class="botao sec" type="button" onclick="document.getElementById('modalUpload').style.display='none'"><?php echo View::e(I18n::t('eq_terminal.cancelar')); ?></button>
      </div>
    </div>
  </div>

  <!-- Modal download -->
  <div id="modalDownload" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center;">
    <div class="card-new" style="max-width:420px;width:90%;">
      <div class="card-new-title"><?php echo View::e(I18n::t('eq_terminal.baixar_arquivo')); ?></div>
      <div style="margin-bottom:12px;"><label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo View::e(I18n::t('eq_terminal.caminho_remoto')); ?></label><input type="text" id="downloadPath" class="input" placeholder="/var/log/syslog" /></div>
      <div class="linha" style="gap:8px;">
        <button class="botao" id="btnDownloadIniciar" type="button"><?php echo View::e(I18n::t('eq_terminal.baixar')); ?></button>
        <button class="botao sec" type="button" onclick="document.getElementById('modalDownload').style.display='none'"><?php echo View::e(I18n::t('eq_terminal.cancelar')); ?></button>
      </div>
    </div>
  </div>

  <div class="texto" style="margin-top:10px;font-size:13px;opacity:.9;"><?php echo View::e(I18n::t('eq_terminal.http_hint')); ?></div>
</div>

<?php
// i18n strings for JS (avoid $ conflicts in inline script)
$jsStrings = [
    'selecione_node' => I18n::t('eq_terminal.selecione_node'),
    'conectado_http' => I18n::t('eq_terminal.conectado_http'),
    'sessao_iniciada' => I18n::t('eq_terminal.sessao_iniciada'),
    'dica_http' => I18n::t('eq_terminal.dica_http'),
    'executando' => I18n::t('eq_terminal.executando'),
    'nao_conectado' => I18n::t('eq_terminal.nao_conectado'),
    'preencha_upload' => I18n::t('eq_terminal.preencha_upload'),
    'enviando' => I18n::t('eq_terminal.enviando'),
];
?>
<script>
(function(){
  var T=<?php echo json_encode($jsStrings, JSON_UNESCAPED_UNICODE); ?>;
  var DS='$';
  var elOut=document.getElementById('out'),elIn=document.getElementById('in'),
      elStatus=document.getElementById('status'),elAlerta=document.getElementById('alerta'),
      elServer=document.getElementById('server_id'),elPrompt=document.getElementById('prompt'),
      btn=document.getElementById('btnConectar');
  var conectado=false,executando=false,serverId='',cwd='~';
  var historico=[],histIdx=-1;

  function csrf(){return(document.querySelector('meta[name="csrf-token"]')||{}).content||'';}
  function setErro(msg){elAlerta.style.display='block';elAlerta.textContent=msg;}
  function clearErro(){elAlerta.style.display='none';elAlerta.textContent='';}
  function append(text,cls){
    if(cls){var s=document.createElement('span');s.style.color=cls;s.textContent=text;elOut.appendChild(s);}
    else{elOut.textContent+=text;}
    elOut.scrollTop=elOut.scrollHeight;
  }
  function setPrompt(p){elPrompt.textContent=p;}
  function promptStr(){return cwd+' '+DS;}

  function conectar(){
    clearErro();
    serverId=(elServer.value||'').trim();
    if(!serverId){setErro(T.selecione_node);return;}
    conectado=true;cwd='~';
    elIn.disabled=false;elIn.focus();
    elOut.textContent='';
    elStatus.textContent=T.conectado_http;
    setPrompt(promptStr());
    append(T.sessao_iniciada+'\n','#22c55e');
    append(T.dica_http+'\n\n','#94a3b8');
  }

  async function executarComando(cmd){
    if(executando)return;
    executando=true;
    elIn.disabled=true;
    elStatus.textContent=T.executando;

    var realCmd=cmd;
    var isCD=/^\s*cd\s/.test(cmd)||cmd.trim()==='cd';
    if(isCD){realCmd=cmd+' && pwd';}

    var body=new URLSearchParams();
    body.set('server_id',serverId);
    body.set('command',realCmd);
    try{
      var resp=await fetch('/equipe/terminal/exec',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf(),'Accept':'application/json'},body:body});
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
        if(json.exit_code!==0){
          append('[exit '+json.exit_code+']\n','#f59e0b');
        }
      }
    }catch(e){
      append('Erro de rede: '+e.message+'\n','#ef4444');
    }
    setPrompt(promptStr());
    elStatus.textContent=T.conectado_http;
    executando=false;elIn.disabled=false;elIn.focus();
  }

  btn.addEventListener('click',conectar);
  elIn.addEventListener('keydown',function(ev){
    if(ev.key==='Enter'){
      ev.preventDefault();
      var v=elIn.value;elIn.value='';
      if(!conectado){setErro(T.nao_conectado);return;}
      if(v.trim()==='')return;
      if(v.trim()==='clear'||v.trim()==='cls'){elOut.textContent='';return;}
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

  // Upload
  function getServerId(){return serverId;}
  document.getElementById('btnUpload').addEventListener('click',function(){document.getElementById('modalUpload').style.display='flex';});
  document.getElementById('btnUploadEnviar').addEventListener('click',async function(){
    if(!getServerId()){setErro(T.selecione_node);return;}
    var file=document.getElementById('uploadFile').files[0],path=document.getElementById('uploadPath').value.trim(),statusEl=document.getElementById('uploadStatus');
    if(!file||!path){statusEl.style.display='block';statusEl.textContent=T.preencha_upload;return;}
    statusEl.style.display='block';statusEl.textContent=T.enviando;
    var fd=new FormData();fd.append('file',file);fd.append('server_id',getServerId());fd.append('remote_path',path);
    try{var resp=await fetch('/equipe/terminal/upload',{method:'POST',headers:{'x-csrf-token':csrf()},body:fd});var json=await resp.json();statusEl.textContent=json.ok?'OK: '+(json.mensagem||''):'Erro: '+(json.erro||'');}catch(e){statusEl.textContent='Erro de rede.';}
  });

  // Download
  document.getElementById('btnDownload').addEventListener('click',function(){document.getElementById('modalDownload').style.display='flex';});
  document.getElementById('btnDownloadIniciar').addEventListener('click',function(){
    if(!getServerId()){setErro(T.selecione_node);return;}
    var path=document.getElementById('downloadPath').value.trim();if(!path)return;
    window.location.href='/equipe/terminal/download?server_id='+getServerId()+'&remote_path='+encodeURIComponent(path);
    document.getElementById('modalDownload').style.display='none';
  });
})();
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
