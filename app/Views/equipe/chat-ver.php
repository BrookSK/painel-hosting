<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$roomId = (int)($room['id']??0);
$status = (string)($room['status']??'open');
$_wsUrl = '';
try { $_wsUrl = (string)\LRV\Core\Settings::obter('chat.ws_url', ''); } catch (\Throwable $_e) {}
if ($_wsUrl === '') {
    try { $_wsPort = (int)\LRV\Core\Settings::obter('chat.ws_port', 8082); } catch (\Throwable $_e) { $_wsPort = 8082; }
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'wss' : 'ws';
    $_wsUrl = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ':' . $_wsPort;
}
$pageTitle = 'Chat #'.$roomId;
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Chat #<?php echo $roomId; ?></div>
<div class="page-subtitle">
  <?php echo View::e((string)($cliente['name']??'Cliente #'.($room['client_id']??''))); ?>
  &nbsp;·&nbsp; <?php echo $status==='open'? View::e(I18n::t('eq_chat_ver.aberto')) : View::e(I18n::t('eq_chat_ver.encerrado')); ?>
</div>

<style>
.chat-layout{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;}
@media(max-width:900px){.chat-layout{grid-template-columns:1fr;}}
.chat-box{display:flex;flex-direction:column;height:480px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;}
.chat-msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:8px;background:#f8fafc;}
.chat-input-row{display:flex;gap:8px;padding:10px;border-top:1px solid #e2e8f0;background:#fff;align-items:flex-end;}
.chat-input-row input[type=text]{flex:1;}
.msg-bubble{max-width:72%;padding:8px 12px;border-radius:12px;font-size:14px;line-height:1.4;word-break:break-word;}
.msg-admin{align-self:flex-end;background:#4F46E5;color:#fff;border-bottom-right-radius:4px;}
.msg-client{align-self:flex-start;background:#e2e8f0;color:#1e293b;border-bottom-left-radius:4px;}
.msg-system{align-self:center;background:#f1f5f9;color:#64748b;border-radius:10px;text-align:center;font-style:italic;font-size:13px;max-width:85%;}
.msg-meta{font-size:11px;opacity:.65;margin-top:3px;}
.msg-img{max-width:220px;border-radius:8px;margin-top:4px;cursor:pointer;}
.msg-file{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;background:rgba(255,255,255,.15);border-radius:8px;font-size:13px;margin-top:4px;text-decoration:none;color:inherit;}
.msg-admin .msg-file{border:1px solid rgba(255,255,255,.3);}
.msg-client .msg-file{background:rgba(0,0,0,.05);border:1px solid #d1d5db;}
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
</style>
<style>
.sidebar-info{display:flex;flex-direction:column;gap:16px;}
.info-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;}
.info-card h3{margin:0 0 10px;font-size:14px;font-weight:700;color:#1e293b;}
.info-row{display:flex;justify-content:space-between;font-size:13px;padding:4px 0;border-bottom:1px solid #f1f5f9;}
.info-row:last-child{border-bottom:none;}
.info-label{color:#64748b;font-weight:500;}
.info-value{color:#1e293b;text-align:right;}
.mini-table{width:100%;font-size:13px;border-collapse:collapse;}
.mini-table th{text-align:left;font-weight:600;color:#64748b;padding:4px 6px;border-bottom:1px solid #e2e8f0;font-size:12px;}
.mini-table td{padding:4px 6px;border-bottom:1px solid #f1f5f9;}
.mini-table tr:last-child td{border-bottom:none;}
.empty-info{font-size:13px;color:#94a3b8;padding:4px 0;}
.chat-toolbar{display:flex;gap:4px;align-items:center;}
.chat-toolbar button{background:none;border:none;cursor:pointer;font-size:18px;padding:4px 6px;border-radius:6px;color:#64748b;line-height:1;}
.chat-toolbar button:hover{background:#f1f5f9;color:#4F46E5;}
.emoji-picker{position:absolute;bottom:60px;left:10px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:8px;box-shadow:0 8px 32px rgba(0,0,0,.12);display:none;z-index:10;width:280px;max-height:200px;overflow-y:auto;}
.emoji-picker.open{display:flex;flex-wrap:wrap;gap:2px;}
.emoji-picker span{cursor:pointer;font-size:20px;padding:4px;border-radius:6px;line-height:1;}
.emoji-picker span:hover{background:#f1f5f9;}
.chat-upload-preview{padding:6px 10px;border-top:1px solid #f1f5f9;background:#fefce8;font-size:12px;color:#854d0e;display:none;align-items:center;gap:8px;}
.chat-upload-preview button{background:none;border:none;cursor:pointer;color:#dc2626;font-size:14px;}
</style>

<div class="chat-layout">
  <div>
    <div class="card-new" style="position:relative;">
      <?php if ($status==='open'): ?>
        <div class="chat-box">
          <div class="chat-msgs" id="chatMsgs">
            <div id="chatLoading" style="display:flex;align-items:center;justify-content:center;flex:1;gap:8px;color:#94a3b8;font-size:13px;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="#cbd5e1" stroke-width="3" fill="none"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#7C3AED" stroke-width="3" stroke-linecap="round" fill="none"/></svg>
              <?php echo View::e(I18n::t('eq_chat_ver.carregando')); ?>
            </div>
          </div>
          <div class="chat-upload-preview" id="uploadPreview">
            <span id="uploadFileName"></span>
            <button id="uploadCancel" title="<?php echo View::e(I18n::t('geral.cancelar')); ?>" aria-label="<?php echo View::e(I18n::t('eq_chat_ver.cancelar_arquivo')); ?>">✕</button>
          </div>
          <div class="chat-input-row">
            <div class="chat-toolbar">
              <button type="button" id="btnEmoji" title="<?php echo View::e(I18n::t('eq_chat_ver.emojis')); ?>" aria-label="<?php echo View::e(I18n::t('eq_chat_ver.emojis')); ?>">😊</button>
              <button type="button" id="btnFile" title="<?php echo View::e(I18n::t('eq_chat_ver.enviar_arquivo')); ?>" aria-label="<?php echo View::e(I18n::t('eq_chat_ver.enviar_arquivo')); ?>">📎</button>
              <input type="file" id="fileInput" accept="image/*,.pdf,.doc,.docx,.txt" style="display:none;" />
            </div>
            <input class="input" type="text" id="chatInput" placeholder="<?php echo View::e(I18n::t('eq_chat_ver.placeholder')); ?>" autocomplete="off" />
            <button class="botao" id="chatEnviar"><?php echo View::e(I18n::t('eq_chat_ver.enviar')); ?></button>
          </div>
          <div class="emoji-picker" id="emojiPicker"></div>
        </div>
        <form method="post" action="/equipe/chat/fechar" style="margin-top:12px;">
          <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
          <input type="hidden" name="room_id" value="<?php echo $roomId; ?>" />
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button class="botao danger sm" type="submit" onclick="return confirm('<?php echo View::e(I18n::t('eq_chat_ver.confirmar_encerrar')); ?>')"><?php echo View::e(I18n::t('eq_chat_ver.encerrar')); ?></button>
            <div style="position:relative;display:inline-block;">
              <button type="button" class="botao sec sm" id="btnFlows"><?php echo View::e(I18n::t('chat_flows.fluxos')); ?> ▾</button>
              <div id="flowsDropdown" style="display:none;position:absolute;bottom:100%;left:0;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);min-width:220px;z-index:20;padding:6px;margin-bottom:4px;">
                <div style="font-size:12px;font-weight:600;color:#64748b;padding:6px 8px;"><?php echo View::e(I18n::t('chat_flows.disparar_fluxo')); ?></div>
                <div id="flowsList" style="max-height:200px;overflow-y:auto;"></div>
                <div id="flowsEmpty" style="font-size:12px;color:#94a3b8;padding:6px 8px;display:none;"><?php echo View::e(I18n::t('geral.nenhum_resultado')); ?></div>
              </div>
            </div>
            <span id="flowMsg" style="font-size:12px;color:#22c55e;display:none;"></span>
          </div>
        </form>
      <?php else: ?>
        <div class="chat-box">
          <div class="chat-msgs" id="chatMsgs">
            <?php
              $lastDay = '';
              foreach ($mensagens as $m):
                $ts = (string)($m['created_at']??'');
                $day = substr($ts, 0, 10);
                if ($day !== '' && $day !== $lastDay):
                  $lastDay = $day;
                  $parts = explode('-', $day);
            ?>
              <div style="text-align:center;font-size:11px;color:#94a3b8;padding:8px 0;display:flex;align-items:center;gap:10px;">
                <span style="flex:1;height:1px;background:#e2e8f0;"></span>
                <span><?php echo $parts[2].'/'.$parts[1].'/'.$parts[0]; ?></span>
                <span style="flex:1;height:1px;background:#e2e8f0;"></span>
              </div>
            <?php endif; ?>
            <?php
              $st = (string)($m['sender_type']??'');
              $sn = (string)($m['sender_name']??'');
            ?>
              <div class="msg-bubble <?php echo $st==='admin'?'msg-admin':($st==='system'?'msg-system':'msg-client'); ?>">
                <?php if ($st==='admin' && $sn !== ''): ?>
                  <div style="font-size:11px;font-weight:600;opacity:.7;margin-bottom:2px;"><?php echo View::e($sn); ?></div>
                <?php endif; ?>
                <?php if ($st==='system'): ?>
                  <span style="font-size:11px;">⚙️ <?php echo View::e(I18n::t('chat_flows.sistema')); ?></span><br/>
                <?php endif; ?>
                <?php if (($m['message']??'') !== ''): ?>
                  <?php echo View::e((string)$m['message']); ?>
                <?php endif; ?>
                <?php
                  $fu = (string)($m['file_url']??'');
                  $fn = (string)($m['file_name']??'');
                  if ($fu !== ''):
                    if (preg_match('/\.(png|jpe?g|gif|webp)$/i', $fu)):
                ?>
                  <img src="<?php echo View::e($fu); ?>" class="msg-img" alt="<?php echo View::e($fn); ?>" />
                <?php else: ?>
                  <a href="<?php echo View::e($fu); ?>" target="_blank" class="msg-file">📄 <?php echo View::e($fn ?: 'arquivo'); ?></a>
                <?php endif; endif; ?>
                <div class="msg-meta">
                  <?php
                    $metaParts = [];
                    if ($st === 'admin' && $sn !== '') $metaParts[] = $sn;
                    elseif ($st === 'client') $metaParts[] = 'Cliente';
                    elseif ($st === 'system') $metaParts[] = 'Sistema';
                    if ($ts !== '') $metaParts[] = substr($ts, 11, 5);
                    echo View::e(implode(' · ', $metaParts));
                  ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <p class="texto" style="margin-top:10px;"><?php echo View::e(I18n::t('eq_chat_ver.chat_encerrado')); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="sidebar-info">
    <div class="info-card">
      <h3>👤 <?php echo View::e(I18n::t('eq_chat_ver.cliente_label')); ?></h3>
      <?php if (!empty($cliente)): ?>
        <div class="info-row"><span class="info-label"><?php echo View::e(I18n::t('eq_chat_ver.nome')); ?></span><span class="info-value"><?php echo View::e((string)($cliente['name']??'')); ?></span></div>
        <div class="info-row"><span class="info-label"><?php echo View::e(I18n::t('eq_chat_ver.email')); ?></span><span class="info-value"><?php echo View::e((string)($cliente['email']??'')); ?></span></div>
        <div class="info-row"><span class="info-label"><?php echo View::e(I18n::t('eq_chat_ver.cadastro')); ?></span><span class="info-value"><?php echo View::e((string)($cliente['created_at']??'')); ?></span></div>
        <div style="margin-top:8px;"><a href="/equipe/clientes/ver?id=<?php echo (int)($cliente['id']??0); ?>" class="botao sm" style="font-size:12px;"><?php echo View::e(I18n::t('eq_chat_ver.ver_perfil')); ?></a></div>
      <?php else: ?>
        <p class="empty-info"><?php echo View::e(I18n::t('eq_chat_ver.nao_encontrado')); ?></p>
      <?php endif; ?>
    </div>
    <div class="info-card">
      <h3>💳 <?php echo View::e(I18n::t('eq_chat_ver.assinaturas')); ?></h3>
      <?php if (!empty($assinaturas)): ?>
        <table class="mini-table"><thead><tr><th><?php echo View::e(I18n::t('eq_chat_ver.plano')); ?></th><th><?php echo View::e(I18n::t('geral.status')); ?></th></tr></thead><tbody>
          <?php foreach ($assinaturas as $a): ?>
            <tr><td><?php echo View::e((string)($a['plan_name']??'—')); ?></td><td><span class="badge-new <?php echo ($a['status']??'')==='active'?'badge-green':'badge-yellow'; ?>"><?php echo View::e((string)($a['status']??'')); ?></span></td></tr>
          <?php endforeach; ?>
        </tbody></table>
      <?php else: ?><p class="empty-info"><?php echo View::e(I18n::t('eq_chat_ver.nenhuma_assinatura')); ?></p><?php endif; ?>
    </div>
    <div class="info-card">
      <h3>🖥️ <?php echo View::e(I18n::t('eq_chat_ver.vps')); ?></h3>
      <?php if (!empty($vps)): ?>
        <table class="mini-table"><thead><tr><th>ID</th><th>CPU/RAM</th><th>Status</th></tr></thead><tbody>
          <?php foreach ($vps as $v): ?>
            <tr><td>#<?php echo (int)($v['id']??0); ?></td><td><?php echo (int)($v['cpu']??0); ?>vCPU / <?php echo round(((int)($v['ram']??0))/1024/1024/1024,1); ?>GB</td><td><span class="badge-new <?php echo ($v['status']??'')==='active'?'badge-green':'badge-yellow'; ?>"><?php echo View::e((string)($v['status']??'')); ?></span></td></tr>
          <?php endforeach; ?>
        </tbody></table>
      <?php else: ?><p class="empty-info"><?php echo View::e(I18n::t('eq_chat_ver.nenhuma_vps')); ?></p><?php endif; ?>
    </div>
    <div class="info-card">
      <h3>🎫 <?php echo View::e(I18n::t('eq_chat_ver.tickets_recentes')); ?></h3>
      <?php if (!empty($tickets)): ?>
        <table class="mini-table"><thead><tr><th><?php echo View::e(I18n::t('tickets.assunto')); ?></th><th><?php echo View::e(I18n::t('geral.status')); ?></th></tr></thead><tbody>
          <?php foreach ($tickets as $t): ?>
            <tr><td><a href="/equipe/tickets/ver?id=<?php echo (int)($t['id']??0); ?>" style="color:#4F46E5;"><?php echo View::e((string)($t['subject']??'')); ?></a></td><td><span class="badge-new <?php echo ($t['status']??'')==='open'?'badge-green':(($t['status']??'')==='closed'?'badge-red':'badge-yellow'); ?>"><?php echo View::e((string)($t['status']??'')); ?></span></td></tr>
          <?php endforeach; ?>
        </tbody></table>
      <?php else: ?><p class="empty-info"><?php echo View::e(I18n::t('eq_chat_ver.nenhum_ticket')); ?></p><?php endif; ?>
    </div>
  </div>
</div>

<?php if ($status==='open'): ?>
<script>
(function(){
  var ROOM_ID=<?php echo $roomId; ?>,CSRF=<?php echo json_encode(Csrf::token()); ?>,WS_URL=<?php echo json_encode($_wsUrl); ?>;
  var box=document.getElementById('chatMsgs'),inp=document.getElementById('chatInput');
  var ws=null,pendingFile=null,mode='ws',wsFails=0,pollTimer=null,lastMsgId=0;

  var EMOJIS=['😊','😂','😍','🥰','😎','🤔','👍','👎','❤️','🔥','✅','❌','⭐','🎉','💬','📎','🖥️','🚀','💡','⚡','🛡️','🔒','📧','🎫','👋','🙏','💪','👀','🤝','✨'];
  var picker=document.getElementById('emojiPicker');
  EMOJIS.forEach(function(e){var s=document.createElement('span');s.textContent=e;s.addEventListener('click',function(){inp.value+=e;inp.focus();picker.classList.remove('open');});picker.appendChild(s);});
  document.getElementById('btnEmoji').addEventListener('click',function(ev){ev.stopPropagation();picker.classList.toggle('open');});
  document.addEventListener('click',function(ev){if(!picker.contains(ev.target))picker.classList.remove('open');});

  var fileInput=document.getElementById('fileInput'),preview=document.getElementById('uploadPreview'),previewName=document.getElementById('uploadFileName');
  document.getElementById('btnFile').addEventListener('click',function(){fileInput.click();});
  fileInput.addEventListener('change',function(){if(!this.files||!this.files[0])return;pendingFile=this.files[0];previewName.textContent='📎 '+pendingFile.name;preview.style.display='flex';});
  document.getElementById('uploadCancel').addEventListener('click',function(){pendingFile=null;fileInput.value='';preview.style.display='none';});

  function escHtml(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
  function isImage(u){return /\.(png|jpe?g|gif|webp)$/i.test(u||'');}

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

  function appendMsg(sender,text,ts,fu,fn,sn){
    maybeDaySep(ts);
    var div=document.createElement('div'),meta=document.createElement('div');
    div.className='msg-bubble '+(sender==='admin'?'msg-admin':(sender==='system'?'msg-system':'msg-client'));
    meta.className='msg-meta';
    var parts=[];
    if(sender==='admin')parts.push(sn||'Você');
    else if(sender==='system')parts.push('Sistema');
    else parts.push('Cliente');
    if(ts)parts.push(ts.substring(11,16));
    meta.textContent=parts.join(' · ');
    if(text)div.innerHTML=escHtml(text);
    if(fu){
      if(isImage(fu)){var img=document.createElement('img');img.src=fu;img.className='msg-img';img.alt=fn||'';img.onclick=function(){window.open(fu,'_blank');};div.appendChild(img);}
      else{var a=document.createElement('a');a.href=fu;a.target='_blank';a.className='msg-file';a.textContent='📄 '+(fn||'arquivo');div.appendChild(a);}
    }
    div.appendChild(meta);box.appendChild(div);box.scrollTop=box.scrollHeight;
  }

  function hideLoading(){var el=document.getElementById('chatLoading');if(el)el.remove();}

  function loadMessages(msgs){hideLoading();(msgs||[]).forEach(function(m){var id=parseInt(m.id)||0;if(id>lastMsgId)lastMsgId=id;appendMsg(m.sender_type,m.message,m.created_at,m.file_url,m.file_name,m.sender_name||null);});}

  function doUpload(file,cb){
    var fd=new FormData();fd.append('arquivo',file);fd.append('_csrf',CSRF);
    fetch('/chat/upload',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){if(!d.ok){alert(d.erro||'Erro');return;}cb(d.file_url,d.file_name);}).catch(function(){alert('Erro ao enviar arquivo.');});
  }

  function enviar(){
    var txt=inp.value.trim();if(!txt&&!pendingFile)return;
    if(pendingFile){var f=pendingFile,t=txt;pendingFile=null;fileInput.value='';preview.style.display='none';inp.value='';doUpload(f,function(fu,fn){sendPayload(t,fu,fn);});return;}
    sendPayload(txt,null,null);inp.value='';
  }

  function sendPayload(text,fu,fn){
    if(mode==='ws'&&ws&&ws.readyState===1){var p={message:text||''};if(fu){p.file_url=fu;p.file_name=fn;}ws.send(JSON.stringify(p));}
    else{var body='_csrf='+encodeURIComponent(CSRF)+'&room_id='+ROOM_ID+'&message='+encodeURIComponent(text||'');if(fu)body+='&file_url='+encodeURIComponent(fu)+'&file_name='+encodeURIComponent(fn||'');
      fetch('/equipe/chat/enviar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body}).then(function(r){return r.json();}).then(function(d){if(d.ok&&d.msg){var id=parseInt(d.msg.id)||0;if(id>lastMsgId)lastMsgId=id;appendMsg(d.msg.sender_type,d.msg.message,d.msg.created_at,d.msg.file_url,d.msg.file_name,d.msg.sender_name||null);}}).catch(function(){});
    }
  }

  function connectWs(){
    fetch('/equipe/chat/token',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&room_id='+ROOM_ID})
    .then(function(r){return r.json();}).then(function(d){
      if(!d.ok){wsFails++;checkFallback();return;}
      ws=new WebSocket(WS_URL+'/?token='+encodeURIComponent(d.token));
      ws.onopen=function(){mode='ws';wsFails=0;if(pollTimer){clearInterval(pollTimer);pollTimer=null;}};
      ws.onmessage=function(e){try{var msg=JSON.parse(e.data);if(msg.type==='history'){box.innerHTML='';lastMsgId=0;lastDateStr='';loadMessages(msg.messages);}else if(msg.type==='message'){hideLoading();var id=parseInt(msg.id)||0;if(id>lastMsgId)lastMsgId=id;appendMsg(msg.sender_type,msg.message,msg.created_at,msg.file_url,msg.file_name,msg.sender_name||null);}}catch(ex){}};
      ws.onclose=function(){wsFails++;checkFallback();};
      ws.onerror=function(){try{ws.close();}catch(x){}};
    }).catch(function(){wsFails++;checkFallback();});
  }

  function checkFallback(){if(wsFails>=1){startPolling();}else{setTimeout(connectWs,2000);}}

  function startPolling(){mode='poll';doPoll();if(!pollTimer)pollTimer=setInterval(doPoll,3000);}

  function doPoll(){
    fetch('/equipe/chat/poll?room_id='+ROOM_ID+'&after='+lastMsgId).then(function(r){return r.json();}).then(function(d){if(!d.ok)return;loadMessages(d.messages);}).catch(function(){});
  }

  document.getElementById('chatEnviar').addEventListener('click',enviar);
  inp.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();enviar();}});
  connectWs();

  // Flows dropdown
  var btnFlows=document.getElementById('btnFlows'),flowsDd=document.getElementById('flowsDropdown'),flowsList=document.getElementById('flowsList'),flowsEmpty=document.getElementById('flowsEmpty'),flowMsg=document.getElementById('flowMsg');
  var flowsLoaded=false;
  btnFlows.addEventListener('click',function(ev){
    ev.stopPropagation();
    var open=flowsDd.style.display==='block';
    flowsDd.style.display=open?'none':'block';
    if(!open&&!flowsLoaded){
      flowsLoaded=true;
      fetch('/equipe/chat-flows/dispatch',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&flow_id=0&room_id=0'})
      .catch(function(){});
      // Load manual flows via a simple GET to the list page and parse, or use a dedicated endpoint
      // For simplicity, we'll fetch the flows list from the page data
      loadManualFlows();
    }
  });
  document.addEventListener('click',function(){flowsDd.style.display='none';});
  flowsDd.addEventListener('click',function(ev){ev.stopPropagation();});

  function loadManualFlows(){
    // Fetch flows list page and extract manual flows
    fetch('/equipe/chat-flows').then(function(r){return r.text();}).then(function(html){
      // Parse manual flows from the page - but this is fragile
      // Instead, let's use a simpler approach: embed the flows data
      flowsList.innerHTML='';
      var flows=<?php
        $manualFlows = (new \LRV\App\Services\Chat\ChatFlowService())->listarPorTrigger('manual');
        echo json_encode(array_map(function($f) {
            return ['id' => (int)$f['id'], 'name' => (string)$f['name'], 'description' => (string)($f['description'] ?? '')];
        }, $manualFlows));
      ?>;
      if(!flows.length){flowsEmpty.style.display='block';return;}
      flows.forEach(function(f){
        var btn=document.createElement('button');
        btn.style.cssText='display:block;width:100%;text-align:left;padding:8px;border:none;background:none;cursor:pointer;border-radius:6px;font-size:13px;font-family:inherit;color:#1e293b;';
        btn.textContent=f.name;
        if(f.description)btn.title=f.description;
        btn.addEventListener('mouseover',function(){btn.style.background='#f1f5f9';});
        btn.addEventListener('mouseout',function(){btn.style.background='none';});
        btn.addEventListener('click',function(){
          flowsDd.style.display='none';
          fetch('/equipe/chat-flows/dispatch',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&flow_id='+f.id+'&room_id='+ROOM_ID})
          .then(function(r){return r.json();}).then(function(d){
            flowMsg.style.display='inline';
            if(d.ok){flowMsg.style.color='#22c55e';flowMsg.textContent='✓ Fluxo disparado!';}
            else{flowMsg.style.color='#ef4444';flowMsg.textContent=d.erro||'Erro';}
            setTimeout(function(){flowMsg.style.display='none';},4000);
          }).catch(function(){flowMsg.style.display='inline';flowMsg.style.color='#ef4444';flowMsg.textContent='Erro de rede';setTimeout(function(){flowMsg.style.display='none';},4000);});
        });
        flowsList.appendChild(btn);
      });
    }).catch(function(){});
  }
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
