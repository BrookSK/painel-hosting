<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\I18n;

$porta = (int)ConfiguracoesSistema::terminalWsInternalPort();

$pageTitle = I18n::t('eq_terminal.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<meta name="csrf-token" content="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
<div class="page-title"><?php echo View::e(I18n::t('eq_terminal.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_terminal.subtitulo')); ?></div>

<div class="card-new">
  <div class="linha" style="justify-content:space-between;align-items:flex-end;margin-bottom:16px;">
    <div>
      <div class="texto" style="margin:0;"><?php echo View::e(I18n::t('eq_terminal.escolha_node')); ?></div>
    </div>
    <div><span class="badge">WS interno: 127.0.0.1:<?php echo (int)$porta; ?></span></div>
  </div>

  <div class="grid" style="margin-bottom:12px;">
    <div>
      <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_terminal.node')); ?></label>
      <select class="input" id="server_id">
        <option value=""><?php echo View::e(I18n::t('eq_terminal.selecione')); ?></option>
        <?php foreach (((array)($servers??[])) as $s): ?>
          <?php $sid=(int)($s['id']??0);$hn=(string)($s['hostname']??'');$ip=(string)($s['ip_address']??'');$st=(string)($s['status']??'');$online=(int)($s['is_online']??0); ?>
          <option value="<?php echo $sid; ?>">#<?php echo $sid; ?> <?php echo View::e($hn); ?> (<?php echo View::e($ip); ?>)<?php echo $st!=='active'?' [inativo]':''; ?><?php echo $online===1?' [online]':''; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <button class="botao" id="btnConectar" type="button" style="width:100%;margin-top:22px;"><?php echo View::e(I18n::t('eq_terminal.conectar')); ?></button>
    </div>
  </div>

  <div id="alerta" class="erro" style="display:none;margin-bottom:12px;"></div>

  <div style="border:1px solid #0b1220;border-radius:14px;overflow:hidden;background:#020617;">
    <div style="padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.18);color:#e2e8f0;font-size:13px;display:flex;justify-content:space-between;">
      <div>Terminal</div>
      <div style="display:flex;gap:10px;align-items:center;">
        <button id="btnUpload" class="botao sec" type="button" style="padding:4px 10px;font-size:12px;"><?php echo View::e(I18n::t('eq_terminal.upload')); ?></button>
        <button id="btnDownload" class="botao sec" type="button" style="padding:4px 10px;font-size:12px;"><?php echo View::e(I18n::t('eq_terminal.download')); ?></button>
        <div id="status" style="opacity:.9;"><?php echo View::e(I18n::t('eq_terminal.desconectado')); ?></div>
      </div>
    </div>
    <pre id="out" style="margin:0;padding:12px;color:#e2e8f0;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;height:420px;overflow:auto;"></pre>
    <div style="border-top:1px solid rgba(148,163,184,.18);padding:10px 12px;">
      <input class="input" id="in" type="text" autocomplete="off" placeholder="<?php echo View::e(I18n::t('eq_terminal.placeholder')); ?>" style="background:#0b1220;border-color:#1f2937;color:#e2e8f0;" />
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

  <div class="texto" style="margin-top:10px;font-size:13px;opacity:.9;"><?php echo View::e(I18n::t('eq_terminal.proxy_hint')); ?> <strong>/ws/terminal</strong> (WSS).</div>
</div>

<script>
(function(){
  const elOut=document.getElementById('out'),elIn=document.getElementById('in'),elStatus=document.getElementById('status'),elAlerta=document.getElementById('alerta'),elServer=document.getElementById('server_id'),btn=document.getElementById('btnConectar');
  let ws=null,conectado=false;
  function sendResize(){if(!ws||ws.readyState!==1)return;const cols=Math.max(40,Math.floor(elOut.offsetWidth/8)),rows=Math.max(10,Math.floor(elOut.offsetHeight/16));ws.send(JSON.stringify({type:'resize',cols,rows}));}
  const ro=new ResizeObserver(function(){sendResize();});ro.observe(elOut);
  function setErro(msg){elAlerta.style.display='block';elAlerta.textContent=msg;}
  function clearErro(){elAlerta.style.display='none';elAlerta.textContent='';}
  function append(text){elOut.textContent+=text;elOut.scrollTop=elOut.scrollHeight;}
  async function emitirToken(){
    const serverId=(elServer.value||'').trim();
    if(!serverId){throw new Error('Selecione um node.');}
    const csrf=(document.querySelector('meta[name="csrf-token"]')||{}).content||'';
    const body=new URLSearchParams();body.set('server_id',serverId);
    const resp=await fetch('/equipe/terminal/token',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf,'Accept':'application/json'},body});
    const json=await resp.json();
    if(!json||!json.ok){throw new Error((json&&json.erro)?json.erro:'Falha ao emitir token.');}
    return json.token;
  }
  function wsUrl(token){const proto=(location.protocol==='https:')?'wss://':'ws://';return proto+location.host+'/ws/terminal?token='+encodeURIComponent(token);}
  async function conectar(){
    clearErro();if(ws){try{ws.close();}catch(e){}}
    elOut.textContent='';append('Abrindo sessao...\n');elStatus.textContent='Conectando...';
    const token=await emitirToken();
    ws=new WebSocket(wsUrl(token));
    ws.onopen=function(){conectado=true;elStatus.textContent='Conectado';append('Conectado.\n');elIn.focus();sendResize();};
    ws.onmessage=function(ev){append(String(ev.data||''));};
    ws.onclose=function(){conectado=false;elStatus.textContent='Desconectado';append('\n[conexao encerrada]\n');};
    ws.onerror=function(){setErro('Falha no WebSocket. Verifique proxy /ws/terminal e o daemon interno.');};
  }
  btn.addEventListener('click',function(){conectar().catch(e=>setErro(e.message||'Erro ao conectar.'));});
  elIn.addEventListener('keydown',function(ev){
    if(ev.key!=='Enter')return;ev.preventDefault();
    const v=elIn.value;elIn.value='';
    if(!conectado||!ws||ws.readyState!==1){setErro('Nao conectado.');return;}
    ws.send(v+"\n");
  });
  function getServerId(){return(elServer.value||'').trim();}
  document.getElementById('btnUpload').addEventListener('click',function(){document.getElementById('modalUpload').style.display='flex';});
  document.getElementById('btnUploadEnviar').addEventListener('click',async function(){
    const serverId=getServerId();if(!serverId){setErro('Selecione um servidor primeiro.');return;}
    const file=document.getElementById('uploadFile').files[0],path=document.getElementById('uploadPath').value.trim(),statusEl=document.getElementById('uploadStatus');
    if(!file||!path){statusEl.style.display='block';statusEl.textContent='Selecione um arquivo e informe o caminho.';return;}
    statusEl.style.display='block';statusEl.textContent='Enviando...';
    const csrf=(document.querySelector('meta[name="csrf-token"]')||{}).content||'',fd=new FormData();
    fd.append('file',file);fd.append('server_id',serverId);fd.append('remote_path',path);
    try{const resp=await fetch('/equipe/terminal/upload',{method:'POST',headers:{'x-csrf-token':csrf},body:fd});const json=await resp.json();statusEl.textContent=json.ok?'OK: '+(json.mensagem||'Enviado.'):'Erro: '+(json.erro||'Erro.');}catch(e){statusEl.textContent='Erro de rede.';}
  });
  document.getElementById('btnDownload').addEventListener('click',function(){document.getElementById('modalDownload').style.display='flex';});
  document.getElementById('btnDownloadIniciar').addEventListener('click',function(){
    const serverId=getServerId();if(!serverId){setErro('Selecione um servidor primeiro.');return;}
    const path=document.getElementById('downloadPath').value.trim();if(!path)return;
    window.location.href='/equipe/terminal/download?server_id='+serverId+'&remote_path='+encodeURIComponent(path);
    document.getElementById('modalDownload').style.display='none';
  });
})();
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
