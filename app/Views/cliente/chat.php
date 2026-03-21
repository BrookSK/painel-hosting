<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Settings;

$roomId = (int)($room['id'] ?? 0);
$wsUrl = (string)Settings::obter('chat.ws_url', '');
if ($wsUrl === '') {
    $wsPort = (int)Settings::obter('chat.ws_port', '8082');
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'wss' : 'ws';
    $wsUrl = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ':' . $wsPort;
}
$pageTitle    = 'Chat de Suporte';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<style>
#chat-box{height:420px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:12px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:12px;}
.msg{max-width:75%;padding:10px 14px;border-radius:14px;font-size:14px;line-height:1.5;word-break:break-word;}
.msg.client{align-self:flex-end;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;border-bottom-right-radius:4px;}
.msg.admin{align-self:flex-start;background:#fff;border:1px solid #e2e8f0;color:#0f172a;border-bottom-left-radius:4px;}
.msg.system{align-self:center;background:#f1f5f9;color:#64748b;font-size:12px;padding:4px 10px;border-radius:999px;}
.msg-time{font-size:11px;opacity:.6;margin-top:4px;}
.msg-img{max-width:220px;border-radius:8px;margin-top:4px;cursor:pointer;}
.msg-file{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:8px;font-size:13px;margin-top:4px;text-decoration:none;color:inherit;}
.msg.client .msg-file{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);}
.msg.admin .msg-file{background:rgba(0,0,0,.05);border:1px solid #d1d5db;}
#chat-status{font-size:13px;color:#64748b;margin-bottom:8px;display:flex;align-items:center;gap:6px;}
#chat-input-area{display:flex;gap:8px;align-items:flex-end;}
#chat-input-area textarea{flex:1;resize:none;height:44px;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;outline:none;font-family:inherit;}
#chat-input-area textarea:focus{border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.12);}
.chat-toolbar{display:flex;gap:2px;align-items:center;}
.chat-toolbar button{background:none;border:none;cursor:pointer;font-size:18px;padding:4px 6px;border-radius:6px;color:#64748b;line-height:1;}
.chat-toolbar button:hover{background:#f1f5f9;color:#4F46E5;}
.emoji-picker-c{position:absolute;bottom:60px;left:0;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:8px;box-shadow:0 8px 32px rgba(0,0,0,.12);display:none;z-index:10;width:280px;max-height:200px;overflow-y:auto;}
.emoji-picker-c.open{display:flex;flex-wrap:wrap;gap:2px;}
.emoji-picker-c span{cursor:pointer;font-size:20px;padding:4px;border-radius:6px;line-height:1;}
.emoji-picker-c span:hover{background:#f1f5f9;}
.chat-upload-preview-c{padding:6px 10px;background:#fefce8;font-size:12px;color:#854d0e;display:none;align-items:center;gap:8px;border-radius:8px;margin-bottom:8px;}
.chat-upload-preview-c button{background:none;border:none;cursor:pointer;color:#dc2626;font-size:14px;}
</style>

<div style="margin-bottom:24px;">
  <div class="page-title">Chat de Suporte</div>
  <div class="page-subtitle" style="margin-bottom:0;">Fale com nossa equipe em tempo real</div>
</div>

<div class="card-new" style="max-width:760px;position:relative;">
  <?php if ((string)($room['status'] ?? '') === 'closed'): ?>
    <div style="font-size:13px;color:#64748b;margin-bottom:16px;">Este chat foi encerrado.</div>
    <?php
      $_jaAvaliou = false;
      try {
          $_pdo = \LRV\Core\BancoDeDados::pdo();
          $_s = $_pdo->prepare('SELECT id FROM satisfaction_surveys WHERE type = :t AND reference_id = :r LIMIT 1');
          $_s->execute([':t' => 'chat', ':r' => $roomId]);
          $_jaAvaliou = (bool)$_s->fetch();
      } catch (\Throwable) {}
    ?>
    <?php if (!$_jaAvaliou): ?>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-bottom:16px;">
        <div style="font-weight:600;font-size:14px;color:#166534;margin-bottom:4px;">Como foi o atendimento?</div>
        <p style="font-size:13px;color:#15803d;margin:0 0 12px;">Avalie este chat e nos ajude a melhorar.</p>
        <a href="/cliente/avaliar?type=chat&id=<?php echo $roomId; ?>" class="botao" style="font-size:13px;padding:8px 18px;">Avaliar atendimento</a>
      </div>
    <?php else: ?>
      <div style="font-size:13px;color:#16a34a;margin-bottom:16px;">Voce ja avaliou este atendimento. Obrigado!</div>
    <?php endif; ?>
    <a href="/cliente/painel" class="botao ghost">Voltar ao painel</a>
  <?php else: ?>
    <div id="chat-status"><span class="dot-pending"></span> Conectando...</div>
    <div id="chat-box"></div>
    <div class="chat-upload-preview-c" id="uploadPreviewC">
      <span id="uploadFileNameC"></span>
      <button id="uploadCancelC" title="Cancelar">✕</button>
    </div>
    <div id="chat-input-area">
      <div class="chat-toolbar">
        <button type="button" id="btnEmojiC" title="Emojis">😊</button>
        <button type="button" id="btnFileC" title="Enviar arquivo">📎</button>
        <input type="file" id="fileInputC" accept="image/*,.pdf,.doc,.docx,.txt" style="display:none;" />
      </div>
      <textarea id="chat-input" placeholder="Digite sua mensagem..." disabled></textarea>
      <button class="botao" id="btn-enviar" disabled>Enviar</button>
    </div>
    <div class="emoji-picker-c" id="emojiPickerC"></div>
  <?php endif; ?>
</div>

<?php if ((string)($room['status'] ?? '') !== 'closed'): ?>
<script>
(function(){
  var WS_URL=<?php echo json_encode($wsUrl); ?>,CSRF=<?php echo json_encode(\LRV\Core\Csrf::token()); ?>;
  var box=document.getElementById('chat-box'),input=document.getElementById('chat-input'),btn=document.getElementById('btn-enviar'),statusEl=document.getElementById('chat-status');
  var ws=null,pendingFile=null,mode='ws',wsFails=0,pollTimer=null,lastMsgId=0;

  // Emoji
  var EMOJIS=['😊','😂','😍','🥰','😎','🤔','👍','👎','❤️','🔥','✅','❌','⭐','🎉','💬','📎','🖥️','🚀','💡','⚡','🛡️','🔒','📧','🎫','👋','🙏','💪','👀','🤝','✨'];
  var picker=document.getElementById('emojiPickerC');
  EMOJIS.forEach(function(e){var s=document.createElement('span');s.textContent=e;s.addEventListener('click',function(){input.value+=e;input.focus();picker.classList.remove('open');});picker.appendChild(s);});
  document.getElementById('btnEmojiC').addEventListener('click',function(ev){ev.stopPropagation();picker.classList.toggle('open');});
  document.addEventListener('click',function(ev){if(!picker.contains(ev.target))picker.classList.remove('open');});

  // File
  var fileInput=document.getElementById('fileInputC'),preview=document.getElementById('uploadPreviewC'),previewName=document.getElementById('uploadFileNameC');
  document.getElementById('btnFileC').addEventListener('click',function(){fileInput.click();});
  fileInput.addEventListener('change',function(){if(!this.files||!this.files[0])return;pendingFile=this.files[0];previewName.textContent='📎 '+pendingFile.name;preview.style.display='flex';});
  document.getElementById('uploadCancelC').addEventListener('click',function(){pendingFile=null;fileInput.value='';preview.style.display='none';});

  function setStatus(t,c){statusEl.innerHTML='<span class="'+c+'"></span> '+t;}
  function escHtml(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
  function isImg(u){return /\.(png|jpe?g|gif|webp)$/i.test(u||'');}

  var lastDateStr='';
  function maybeDaySep(ts){
    if(!ts)return;
    var d=ts.substring(0,10);
    if(d===lastDateStr)return;
    lastDateStr=d;
    var parts=d.split('-');
    var label=parts[2]+'/'+parts[1]+'/'+parts[0];
    var sep=document.createElement('div');
    sep.style.cssText='text-align:center;font-size:11px;color:#94a3b8;padding:8px 0;display:flex;align-items:center;gap:10px;';
    sep.innerHTML='<span style="flex:1;height:1px;background:#e2e8f0;"></span><span>'+label+'</span><span style="flex:1;height:1px;background:#e2e8f0;"></span>';
    box.appendChild(sep);
  }

  function addMsg(st,text,time,fu,fn,sn){
    maybeDaySep(time);
    var div=document.createElement('div');div.className='msg '+st;
    if(text)div.innerHTML=escHtml(text);
    if(fu){
      if(isImg(fu)){var img=document.createElement('img');img.src=fu;img.className='msg-img';img.alt=fn||'';img.onclick=function(){window.open(fu,'_blank');};div.appendChild(img);}
      else{var a=document.createElement('a');a.href=fu;a.target='_blank';a.className='msg-file';a.textContent='📄 '+(fn||'arquivo');div.appendChild(a);}
    }
    if(time){var tm=document.createElement('div');tm.className='msg-time';
      var parts=[];
      if(st==='admin'&&sn)parts.push(sn);
      parts.push(new Date(time).toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'}));
      tm.textContent=parts.join(' · ');
      div.appendChild(tm);}
    box.appendChild(div);box.scrollTop=box.scrollHeight;
  }

  function loadMessages(msgs){
    (msgs||[]).forEach(function(m){
      var id=parseInt(m.id)||0;
      if(id>lastMsgId)lastMsgId=id;
      addMsg(m.sender_type,m.message,m.created_at,m.file_url,m.file_name,m.sender_name||null);
    });
  }

  // ── Upload helper ──
  function doUpload(file,cb){
    var fd=new FormData();fd.append('arquivo',file);fd.append('_csrf',CSRF);
    fetch('/chat/upload',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){
      if(!d.ok){alert(d.erro||'Erro no upload');return;}
      cb(d.file_url,d.file_name);
    }).catch(function(){alert('Erro ao enviar arquivo.');});
  }

  // ── Send (works in both modes) ──
  function enviar(){
    var txt=input.value.trim();
    if(!txt&&!pendingFile)return;
    if(pendingFile){
      var f=pendingFile,t=txt;pendingFile=null;fileInput.value='';preview.style.display='none';input.value='';
      doUpload(f,function(fu,fn){sendPayload(t,fu,fn);});
      return;
    }
    sendPayload(txt,null,null);input.value='';input.focus();
  }

  function sendPayload(text,fu,fn){
    if(mode==='ws'&&ws&&ws.readyState===1){
      var p={message:text||''};if(fu){p.file_url=fu;p.file_name=fn;}
      ws.send(JSON.stringify(p));
    } else {
      // HTTP fallback
      var body='_csrf='+encodeURIComponent(CSRF)+'&message='+encodeURIComponent(text||'');
      if(fu)body+='&file_url='+encodeURIComponent(fu)+'&file_name='+encodeURIComponent(fn||'');
      fetch('/cliente/chat/enviar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
      .then(function(r){return r.json();}).then(function(d){
        if(d.ok&&d.msg){var id=parseInt(d.msg.id)||0;if(id>lastMsgId)lastMsgId=id;addMsg(d.msg.sender_type,d.msg.message,d.msg.created_at,d.msg.file_url,d.msg.file_name,d.msg.sender_name||null);}
      }).catch(function(){});
    }
  }

  // ── WebSocket ──
  function connectWs(){
    setStatus('Conectando...','dot-pending');
    fetch('/cliente/chat/token',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)})
    .then(function(r){return r.json();}).then(function(d){
      if(!d.ok){wsFails++;checkFallback();return;}
      ws=new WebSocket(WS_URL+'/?token='+encodeURIComponent(d.token));
      ws.onopen=function(){mode='ws';wsFails=0;setStatus('Conectado','dot-online');input.disabled=false;btn.disabled=false;input.focus();if(pollTimer){clearInterval(pollTimer);pollTimer=null;}};
      ws.onmessage=function(e){
        try{var msg=JSON.parse(e.data);
          if(msg.type==='history'){box.innerHTML='';lastMsgId=0;lastDateStr='';loadMessages(msg.messages);}
          else if(msg.type==='message'){var id=parseInt(msg.id)||0;if(id>lastMsgId)lastMsgId=id;addMsg(msg.sender_type,msg.message,msg.created_at,msg.file_url,msg.file_name,msg.sender_name||null);}
          else if(msg.type==='error'){setStatus(msg.message,'dot-offline');}
        }catch(ex){}
      };
      ws.onclose=function(){wsFails++;checkFallback();};
      ws.onerror=function(){try{ws.close();}catch(x){}};
    }).catch(function(){wsFails++;checkFallback();});
  }

  function checkFallback(){
    if(wsFails>=2){startPolling();}
    else{setTimeout(connectWs,3000);}
  }

  // ── Polling fallback ──
  function startPolling(){
    mode='poll';
    setStatus('Conectado (modo alternativo)','dot-online');
    input.disabled=false;btn.disabled=false;input.focus();
    // Load initial messages
    doPoll();
    if(!pollTimer)pollTimer=setInterval(doPoll,3000);
  }

  function doPoll(){
    fetch('/cliente/chat/poll?after='+lastMsgId).then(function(r){return r.json();}).then(function(d){
      if(!d.ok)return;
      if(d.status==='closed'){setStatus('Chat encerrado','dot-offline');input.disabled=true;btn.disabled=true;if(pollTimer){clearInterval(pollTimer);pollTimer=null;}return;}
      loadMessages(d.messages);
    }).catch(function(){});
  }

  btn.addEventListener('click',enviar);
  input.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();enviar();}});
  connectWs();
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
