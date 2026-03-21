<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$roomId = (int)($room['id']??0);
$status = (string)($room['status']??'open');
$_wsUrl = '';
try {
    $_wsUrl = (string)\LRV\Core\Settings::obter('chat.ws_url', '');
} catch (\Throwable $_e) {}
if ($_wsUrl === '') {
    try {
        $_wsPort = (int)\LRV\Core\Settings::obter('chat.ws_port', 8082);
    } catch (\Throwable $_e) {
        $_wsPort = 8082;
    }
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'wss' : 'ws';
    $_wsUrl = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ':' . $_wsPort;
}

$pageTitle = 'Chat #'.$roomId;
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Chat #<?php echo $roomId; ?></div>
<div class="page-subtitle">
  <?php echo View::e((string)($cliente['name']??'Cliente #'.($room['client_id']??''))); ?>
  &nbsp;·&nbsp; <?php echo $status==='open'?'Aberto':'Encerrado'; ?>
</div>

<style>
.chat-layout{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;}
@media(max-width:900px){.chat-layout{grid-template-columns:1fr;}}
.chat-box{display:flex;flex-direction:column;height:480px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;}
.chat-msgs{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:8px;background:#f8fafc;}
.chat-input-row{display:flex;gap:8px;padding:10px;border-top:1px solid #e2e8f0;background:#fff;}
.chat-input-row input{flex:1;}
.msg-bubble{max-width:72%;padding:8px 12px;border-radius:12px;font-size:14px;line-height:1.4;}
.msg-admin{align-self:flex-end;background:#4F46E5;color:#fff;border-bottom-right-radius:4px;}
.msg-client{align-self:flex-start;background:#e2e8f0;color:#1e293b;border-bottom-left-radius:4px;}
.msg-meta{font-size:11px;opacity:.65;margin-top:3px;}
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
</style>

<div class="chat-layout">
  <div>
    <div class="card-new">
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
  </div>

  <div class="sidebar-info">
    <!-- Info do cliente -->
    <div class="info-card">
      <h3>👤 Cliente</h3>
      <?php if (!empty($cliente)): ?>
        <div class="info-row"><span class="info-label">Nome</span><span class="info-value"><?php echo View::e((string)($cliente['name']??'')); ?></span></div>
        <div class="info-row"><span class="info-label">E-mail</span><span class="info-value"><?php echo View::e((string)($cliente['email']??'')); ?></span></div>
        <div class="info-row"><span class="info-label">Cadastro</span><span class="info-value"><?php echo View::e((string)($cliente['created_at']??'')); ?></span></div>
        <div style="margin-top:8px;">
          <a href="/equipe/clientes/ver?id=<?php echo (int)($cliente['id']??0); ?>" class="botao sm" style="font-size:12px;">Ver perfil completo</a>
        </div>
      <?php else: ?>
        <p class="empty-info">Cliente não encontrado.</p>
      <?php endif; ?>
    </div>

    <!-- Assinaturas -->
    <div class="info-card">
      <h3>💳 Assinaturas</h3>
      <?php if (!empty($assinaturas)): ?>
        <table class="mini-table">
          <thead><tr><th>Plano</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($assinaturas as $a): ?>
              <tr>
                <td><?php echo View::e((string)($a['plan_name']??'—')); ?></td>
                <td><span class="badge-new <?php echo ($a['status']??'')==='active'?'badge-green':'badge-yellow'; ?>"><?php echo View::e((string)($a['status']??'')); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="empty-info">Nenhuma assinatura.</p>
      <?php endif; ?>
    </div>

    <!-- VPS -->
    <div class="info-card">
      <h3>🖥️ VPS</h3>
      <?php if (!empty($vps)): ?>
        <table class="mini-table">
          <thead><tr><th>ID</th><th>CPU/RAM</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($vps as $v): ?>
              <tr>
                <td>#<?php echo (int)($v['id']??0); ?></td>
                <td><?php echo (int)($v['cpu']??0); ?>vCPU / <?php echo round(((int)($v['ram']??0))/1024/1024/1024, 1); ?>GB</td>
                <td><span class="badge-new <?php echo ($v['status']??'')==='active'?'badge-green':'badge-yellow'; ?>"><?php echo View::e((string)($v['status']??'')); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="empty-info">Nenhuma VPS.</p>
      <?php endif; ?>
    </div>

    <!-- Tickets recentes -->
    <div class="info-card">
      <h3>🎫 Tickets recentes</h3>
      <?php if (!empty($tickets)): ?>
        <table class="mini-table">
          <thead><tr><th>Assunto</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($tickets as $t): ?>
              <tr>
                <td><a href="/equipe/tickets/ver?id=<?php echo (int)($t['id']??0); ?>" style="color:#4F46E5;"><?php echo View::e((string)($t['subject']??'')); ?></a></td>
                <td><span class="badge-new <?php echo ($t['status']??'')==='open'?'badge-green':(($t['status']??'')==='closed'?'badge-red':'badge-yellow'); ?>"><?php echo View::e((string)($t['status']??'')); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="empty-info">Nenhum ticket.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($status==='open'): ?>
<script>
(function(){
  var ROOM_ID=<?php echo $roomId; ?>,TOKEN_URL='/equipe/chat/token',CSRF=<?php echo json_encode(Csrf::token()); ?>,WS_URL=<?php echo json_encode($_wsUrl); ?>,ws,reconnectTimer;
  function appendMsg(sender,text,ts){
    var box=document.getElementById('chatMsgs'),div=document.createElement('div'),meta=document.createElement('div');
    div.className='msg-bubble '+(sender==='admin'?'msg-admin':'msg-client');
    meta.className='msg-meta';meta.textContent=(sender==='admin'?'Você':'Cliente')+(ts?' · '+ts.substring(11,16):'');
    div.textContent=text;div.appendChild(meta);box.appendChild(div);box.scrollTop=box.scrollHeight;
  }
  function connect(){
    fetch(TOKEN_URL,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&room_id='+ROOM_ID})
    .then(function(r){return r.json();}).then(function(d){
      if(!d.ok){setTimeout(connect,5000);return;}
      var proto=location.protocol==='https:'?'wss':'ws';
      ws=new WebSocket(WS_URL+'/?token='+encodeURIComponent(d.token));
      ws.onmessage=function(e){try{var msg=JSON.parse(e.data);if(msg.type==='history'){msg.messages.forEach(function(m){appendMsg(m.sender_type,m.message,m.created_at);});}else if(msg.type==='message'){appendMsg(msg.sender_type,msg.message,msg.created_at);}}catch(ex){}};
      ws.onclose=function(){reconnectTimer=setTimeout(connect,4000);};
      ws.onerror=function(){ws.close();};
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
