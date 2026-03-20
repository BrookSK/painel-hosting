<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Settings;
$roomId = (int) ($room['id'] ?? 0);
$wsPort = (int) Settings::obter('chat.ws_port', '8082');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Chat de Suporte</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    #chat-box{height:420px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:12px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:12px;}
    .msg{max-width:75%;padding:10px 14px;border-radius:14px;font-size:14px;line-height:1.5;word-break:break-word;}
    .msg.client{align-self:flex-end;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;border-bottom-right-radius:4px;}
    .msg.admin{align-self:flex-start;background:#fff;border:1px solid #e2e8f0;color:#0f172a;border-bottom-left-radius:4px;}
    .msg.system{align-self:center;background:#f1f5f9;color:#64748b;font-size:12px;padding:4px 10px;border-radius:999px;}
    .msg-time{font-size:11px;opacity:.6;margin-top:4px;}
    #chat-status{font-size:13px;color:#64748b;margin-bottom:8px;display:flex;align-items:center;gap:6px;}
    #chat-input-area{display:flex;gap:8px;}
    #chat-input-area textarea{flex:1;resize:none;height:44px;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;outline:none;font-family:inherit;}
    #chat-input-area textarea:focus{border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.12);}
  </style>
</head>
<body>
<div class="topo">
  <div class="topo-inner">
    <div>
      <div class="topo-titulo">Chat de Suporte</div>
      <div class="topo-sub">Fale com nossa equipe em tempo real</div>
    </div>
    <nav class="nav">
      <?php require __DIR__ . '/../_partials/idioma.php'; ?>
      <a href="/cliente/painel">Painel</a>
      <a href="/cliente/tickets">Tickets</a>
      <a href="/cliente/sair">Sair</a>
    </nav>
  </div>
</div>

<div class="conteudo">
  <div class="card">
    <div id="chat-status"><span class="dot-pending"></span> Conectando...</div>
    <div id="chat-box"></div>
    <div id="chat-input-area">
      <textarea id="chat-input" placeholder="Digite sua mensagem..." disabled></textarea>
      <button class="botao" id="btn-enviar" disabled>Enviar</button>
    </div>
  </div>
</div>

<script>
(function(){
  const roomId = <?php echo $roomId; ?>;
  const wsPort = <?php echo $wsPort; ?>;
  const box    = document.getElementById('chat-box');
  const input  = document.getElementById('chat-input');
  const btn    = document.getElementById('btn-enviar');
  const status = document.getElementById('chat-status');
  let ws = null;

  function setStatus(txt, dotClass){
    status.innerHTML = '<span class="' + dotClass + '"></span> ' + txt;
  }

  function addMsg(senderType, text, time){
    const div = document.createElement('div');
    div.className = 'msg ' + senderType;
    const t = time ? new Date(time).toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'}) : '';
    div.innerHTML = escHtml(text) + (t ? '<div class="msg-time">' + t + '</div>' : '');
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
  }

  function addSystem(text){
    const div = document.createElement('div');
    div.className = 'msg system';
    div.textContent = text;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
  }

  function escHtml(s){
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function connect(){
    setStatus('Conectando...', 'dot-pending');

    fetch('/cliente/chat/token', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': getCsrf()}, body:'_csrf=' + encodeURIComponent(getCsrf())})
      .then(r => r.json())
      .then(data => {
        if(!data.ok){ setStatus('Erro ao obter token.', 'dot-offline'); return; }
        const proto = location.protocol === 'https:' ? 'wss' : 'ws';
        ws = new WebSocket(proto + '://127.0.0.1:' + wsPort + '/?token=' + encodeURIComponent(data.token));

        ws.onopen = function(){
          setStatus('Conectado', 'dot-online');
          input.disabled = false;
          btn.disabled = false;
          input.focus();
        };

        ws.onmessage = function(e){
          try{
            const msg = JSON.parse(e.data);
            if(msg.type === 'history'){
              box.innerHTML = '';
              (msg.messages || []).forEach(function(m){
                addMsg(m.sender_type, m.message, m.created_at);
              });
            } else if(msg.type === 'message'){
              addMsg(msg.sender_type, msg.message, msg.created_at);
            } else if(msg.type === 'system'){
              addSystem(msg.message);
            } else if(msg.type === 'error'){
              addSystem('Erro: ' + msg.message);
            }
          } catch(err){}
        };

        ws.onclose = function(){
          setStatus('Desconectado. Reconectando...', 'dot-offline');
          input.disabled = true;
          btn.disabled = true;
          setTimeout(connect, 3000);
        };

        ws.onerror = function(){
          ws.close();
        };
      })
      .catch(function(){ setStatus('Erro de conexão.', 'dot-offline'); setTimeout(connect, 5000); });
  }

  function enviar(){
    const txt = input.value.trim();
    if(!txt || !ws || ws.readyState !== 1) return;
    ws.send(JSON.stringify({message: txt}));
    input.value = '';
    input.focus();
  }

  btn.addEventListener('click', enviar);
  input.addEventListener('keydown', function(e){
    if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); enviar(); }
  });

  function getCsrf(){
    const m = document.cookie.match(/csrf_token=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : '';
  }

  connect();
})();
</script>
</body>
</html>
