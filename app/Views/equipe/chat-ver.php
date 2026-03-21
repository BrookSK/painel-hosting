<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Settings;
use LRV\Core\Csrf;

$roomId = (int)($room['id']??0);
$status = (string)($room['status']??'open');
$wsPort = (int)Settings::obter('chat.ws_port','8082');

$pageTitle = 'Chat #'.$roomId;
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Chat #<?php echo $roomId; ?></div>
<div class="page-subtitle">Cliente: <?php echo View::e((string)($room['client_name']??'#'.($room['client_id']??''))); ?> &nbsp;|&nbsp; <?php echo $status==='open'?'Aberto':'Encerrado'; ?></div>

<style>
.chat-box{display:flex;flex-direction:column;height:420px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;}
.chat-msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:8px;background:#f8fafc;}
.chat-input-row{display:flex;gap:8px;padding:10px;border-top:1px solid #e2e8f0;background:#fff;}
.chat-input-row input{flex:1;}
.msg-bubble{max-width:72%;padding:8px 12px;border-radius:12px;font-size:14px;line-height:1.4;}
.msg-admin{align-self:flex-end;background:#4F46E5;color:#fff;border-bottom-right-radius:4px;}
.msg-client{align-self:flex-start;background:#e2e8f0;color:#1e293b;border-bottom-left-radius:4px;}
.msg-meta{font-size:11px;opacity:.65;margin-top:3px;}
</style>

<div class="card-new" style="max-width:760px;">
  <?php if ($status==='open'): ?>
    <div class="chat-box">
      <div class="chat-msgs" id="chatMsgs"></div>
      <div class="chat-input-row">
        <input class="input" type="text" id="chatInput" placeholder="Digite sua mensagem..." autocomplete="off" />
        <button class="botao" id="chatEnviar">Enviar</button>
      </div>
    </div>
    <form method="post" action="/equipe/chat/fechar" style="margin-top:12px;">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
      <input type="hidden" name="room_id" value="<?php echo $roomId; ?>" />
      <button class="botao danger sm" type="submit" onclick="return confirm('Encerrar este chat?')">Encerrar chat</button>
    </form>
  <?php else: ?>
    <div class="chat-box">
      <div class="chat-msgs" id="chatMsgs"></div>
    </div>
    <p class="texto" style="margin-top:10px;">Este chat foi encerrado.</p>
  <?php endif; ?>
</div>

<?php if ($status==='open'): ?>
<script>
(function(){
  var ROOM_ID=<?php echo $roomId; ?>,WS_PORT=<?php echo $wsPort; ?>,TOKEN_URL='/equipe/chat/token',CSRF=<?php echo json_encode(Csrf::token()); ?>,ws,reconnectTimer;
  function appendMsg(sender,text,ts){
    var box=document.getElementById('chatMsgs'),div=document.createElement('div'),meta=document.createElement('div');
    div.className='msg-bubble '+(sender==='admin'?'msg-admin':'msg-client');
    meta.className='msg-meta';meta.textContent=(sender==='admin'?'Voce':'Cliente')+(ts?' · '+ts.substring(11,16):'');
    div.textContent=text;div.appendChild(meta);box.appendChild(div);box.scrollTop=box.scrollHeight;
  }
  function connect(){
    fetch(TOKEN_URL,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&room_id='+ROOM_ID})
    .then(function(r){return r.json();}).then(function(d){
      if(!d.ok){setTimeout(connect,5000);return;}
      var proto=location.protocol==='https:'?'wss':'ws';
      ws=new WebSocket(proto+'://'+location.hostname+':'+WS_PORT+'/?token='+encodeURIComponent(d.token));
      ws.onmessage=function(e){try{var msg=JSON.parse(e.data);if(msg.type==='history'){msg.messages.forEach(function(m){appendMsg(m.sender_type,m.message,m.created_at);});}else if(msg.type==='message'){appendMsg(msg.sender_type,msg.message,msg.created_at);}}catch(ex){}};
      ws.onclose=function(){reconnectTimer=setTimeout(connect,4000);};ws.onerror=function(){ws.close();};
    }).catch(function(){setTimeout(connect,5000);});
  }
  function enviar(){var inp=document.getElementById('chatInput'),txt=inp.value.trim();if(!txt||!ws||ws.readyState!==1)return;ws.send(JSON.stringify({type:'message',message:txt}));inp.value='';}
  document.getElementById('chatEnviar').addEventListener('click',enviar);
  document.getElementById('chatInput').addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();enviar();}});
  connect();
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
