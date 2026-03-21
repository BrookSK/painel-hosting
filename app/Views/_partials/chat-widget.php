<?php
use LRV\Core\View;
use LRV\Core\SistemaConfig;
try { $_wNome = SistemaConfig::nome(); } catch (\Throwable $_e) { $_wNome = 'Suporte'; }
try { $_wCsrf = \LRV\Core\Csrf::token(); } catch (\Throwable $_e) { $_wCsrf = ''; }
?>
<div id="cw-fab" title="Suporte" aria-label="Abrir suporte" role="button" tabindex="0" style="position:fixed!important;bottom:24px;right:24px;z-index:99999!important;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);box-shadow:0 4px 20px rgba(79,70,229,.45);display:flex!important;align-items:center;justify-content:center;cursor:pointer;user-select:none;">
  <svg width="26" height="26" viewBox="0 0 26 26" fill="none"><path d="M22 3H4a2 2 0 00-2 2v13a2 2 0 002 2h5l4 4 4-4h5a2 2 0 002-2V5a2 2 0 00-2-2z" fill="#fff" opacity=".95"/><circle cx="9" cy="12" r="1.5" fill="#4F46E5"/><circle cx="13" cy="12" r="1.5" fill="#4F46E5"/><circle cx="17" cy="12" r="1.5" fill="#4F46E5"/></svg>
  <span id="cw-badge" style="display:none;"></span>
</div>

<div id="cw-drawer" role="dialog" aria-modal="true" aria-label="Suporte" style="position:fixed!important;bottom:90px;right:24px;z-index:99998!important;width:360px;max-width:calc(100vw - 32px);background:#fff;border-radius:20px;box-shadow:0 12px 48px rgba(15,23,42,.18);display:flex;flex-direction:column;overflow:hidden;transform:scale(.92) translateY(16px);opacity:0;pointer-events:none;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s;max-height:520px;">
  <div id="cw-header">
    <div id="cw-header-info">
      <div id="cw-header-avatar">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M17 3H3a1 1 0 00-1 1v9a1 1 0 001 1h3l3 3 3-3h5a1 1 0 001-1V4a1 1 0 00-1-1z" fill="#fff" opacity=".9"/></svg>
      </div>
      <div>
        <div id="cw-header-title"><?php echo View::e($_wNome); ?></div>
        <div id="cw-header-sub">Suporte ao cliente</div>
      </div>
    </div>
    <button id="cw-close" aria-label="Fechar">✕</button>
  </div>

  <!-- Tela: menu inicial -->
  <div id="cw-screen-menu" class="cw-screen cw-active">
    <div id="cw-bot-area">
      <div class="cw-bot-msg">Olá! Como posso ajudar você hoje?</div>
    </div>
    <div id="cw-options">
      <button class="cw-opt" data-action="chat">💬 Falar com suporte via chat</button>
      <button class="cw-opt" data-action="ticket">🎫 Abrir um ticket</button>
      <button class="cw-opt" data-action="email">📧 Enviar e-mail</button>
      <button class="cw-opt" data-action="history">📋 Ver histórico de chats</button>
    </div>
  </div>

  <!-- Tela: fluxo bot -->
  <div id="cw-screen-bot" class="cw-screen">
    <div id="cw-bot-msgs"></div>
    <div id="cw-bot-opts"></div>
    <div id="cw-bot-input-area" style="display:none;">
      <input id="cw-bot-input" class="cw-input" type="text" placeholder="Digite sua mensagem..." autocomplete="off" />
      <button id="cw-bot-send" class="cw-send-btn" aria-label="Enviar">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M2 9l14-7-7 14V9H2z" fill="currentColor"/></svg>
      </button>
    </div>
  </div>

  <!-- Tela: chat ao vivo -->
  <div id="cw-screen-live" class="cw-screen">
    <div id="cw-live-msgs"></div>
    <div id="cw-live-input-area">
      <textarea id="cw-live-input" class="cw-input" rows="1" placeholder="Digite sua mensagem..." style="resize:none;height:40px;"></textarea>
      <button id="cw-live-send" class="cw-send-btn" aria-label="Enviar">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M2 9l14-7-7 14V9H2z" fill="currentColor"/></svg>
      </button>
    </div>
    <div id="cw-live-status"></div>
  </div>

  <!-- Tela: histórico -->
  <div id="cw-screen-history" class="cw-screen">
    <div id="cw-history-list"></div>
  </div>

  <div id="cw-back-bar" style="display:none;">
    <button id="cw-back">← Voltar</button>
  </div>
</div>

<style>
#cw-fab{position:fixed!important;bottom:24px;right:24px;z-index:99999!important;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);box-shadow:0 4px 20px rgba(79,70,229,.45);display:flex!important;align-items:center;justify-content:center;cursor:pointer;transition:transform .2s,box-shadow .2s;user-select:none;}
#cw-fab:hover{transform:scale(1.08);box-shadow:0 6px 28px rgba(79,70,229,.55);}
#cw-badge{position:absolute;top:4px;right:4px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;border-radius:999px;min-width:16px;height:16px;padding:0 4px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;}
#cw-drawer{position:fixed!important;bottom:90px;right:24px;z-index:99998!important;width:360px;max-width:calc(100vw - 32px);background:#fff;border-radius:20px;box-shadow:0 12px 48px rgba(15,23,42,.18);display:flex;flex-direction:column;overflow:hidden;transform:scale(.92) translateY(16px);opacity:0;pointer-events:none;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s;max-height:520px;}
#cw-drawer.cw-open{transform:scale(1) translateY(0);opacity:1;pointer-events:all;}
#cw-header{background:linear-gradient(135deg,#4F46E5,#7C3AED);padding:14px 16px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
#cw-header-info{display:flex;align-items:center;gap:10px;}
#cw-header-avatar{width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
#cw-header-title{color:#fff;font-weight:700;font-size:14px;}
#cw-header-sub{color:rgba(255,255,255,.7);font-size:12px;}
#cw-close{background:none;border:none;color:rgba(255,255,255,.7);font-size:18px;cursor:pointer;padding:4px 6px;border-radius:6px;line-height:1;transition:color .15s;}
#cw-close:hover{color:#fff;}
.cw-screen{display:none;flex-direction:column;flex:1;overflow:hidden;}
.cw-screen.cw-active{display:flex;}
#cw-screen-menu{padding:16px;}
#cw-bot-area{margin-bottom:12px;}
.cw-bot-msg{background:#f1f5f9;border-radius:14px 14px 14px 4px;padding:10px 14px;font-size:13px;color:#0f172a;line-height:1.5;margin-bottom:8px;max-width:90%;}
.cw-user-msg{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;border-radius:14px 14px 4px 14px;padding:10px 14px;font-size:13px;line-height:1.5;margin-bottom:8px;max-width:90%;align-self:flex-end;}
#cw-options{display:flex;flex-direction:column;gap:8px;}
.cw-opt{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:10px 14px;font-size:13px;font-weight:600;color:#0f172a;cursor:pointer;text-align:left;transition:border-color .15s,background .15s;font-family:inherit;}
.cw-opt:hover{border-color:#7C3AED;background:#f5f3ff;color:#4F46E5;}
#cw-screen-bot{padding:12px 16px 0;}
#cw-bot-msgs{flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:6px;padding-bottom:8px;min-height:120px;max-height:280px;}
#cw-bot-opts{display:flex;flex-direction:column;gap:6px;padding:8px 0;}
#cw-bot-input-area{display:flex;gap:8px;padding:8px 0 12px;border-top:1px solid #f1f5f9;}
#cw-screen-live{padding:0;}
#cw-live-msgs{flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:6px;padding:12px 16px;min-height:200px;max-height:340px;}
#cw-live-input-area{display:flex;gap:8px;padding:10px 16px;border-top:1px solid #f1f5f9;flex-shrink:0;}
#cw-live-status{font-size:11px;color:#64748b;padding:0 16px 8px;flex-shrink:0;}
#cw-screen-history{padding:12px 16px;overflow-y:auto;}
#cw-history-list{display:flex;flex-direction:column;gap:8px;}
.cw-history-item{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:10px 14px;cursor:pointer;transition:border-color .15s;}
.cw-history-item:hover{border-color:#7C3AED;}
.cw-history-item-title{font-size:13px;font-weight:600;color:#0f172a;margin-bottom:2px;}
.cw-history-item-sub{font-size:12px;color:#64748b;}
.cw-input{flex:1;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;font-family:inherit;color:#0f172a;background:#fff;transition:border-color .15s;}
.cw-input:focus{border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.1);}
.cw-send-btn{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:opacity .15s;}
.cw-send-btn:hover{opacity:.85;}
#cw-back-bar{padding:8px 16px;border-top:1px solid #f1f5f9;flex-shrink:0;}
#cw-back{background:none;border:none;color:#4F46E5;font-size:13px;cursor:pointer;font-family:inherit;padding:0;}
#cw-back:hover{text-decoration:underline;}
.cw-typing{display:flex;gap:4px;align-items:center;padding:8px 12px;background:#f1f5f9;border-radius:14px 14px 14px 4px;width:fit-content;}
.cw-typing span{width:6px;height:6px;border-radius:50%;background:#94a3b8;animation:cwdot .9s infinite;}
.cw-typing span:nth-child(2){animation-delay:.15s;}
.cw-typing span:nth-child(3){animation-delay:.3s;}
@keyframes cwdot{0%,60%,100%{transform:translateY(0);}30%{transform:translateY(-5px);}}
@media(max-width:480px){#cw-drawer{width:calc(100vw - 16px);right:8px;bottom:80px;}#cw-fab{bottom:16px;right:16px;}}
</style>

<script>
(function(){
'use strict';
var CSRF   = <?php echo json_encode($_wCsrf); ?>;
var WS_PORT = <?php try { echo (int)\LRV\Core\Settings::obter('chat.ws_port', 8082); } catch (\Throwable $_e) { echo 8082; } ?>;

var fab     = document.getElementById('cw-fab');
var drawer  = document.getElementById('cw-drawer');
var btnClose= document.getElementById('cw-close');
var btnBack = document.getElementById('cw-back');
var backBar = document.getElementById('cw-back-bar');

// ── screens ──────────────────────────────────────────────
var screens = {
  menu:    document.getElementById('cw-screen-menu'),
  bot:     document.getElementById('cw-screen-bot'),
  live:    document.getElementById('cw-screen-live'),
  history: document.getElementById('cw-screen-history'),
};
var currentScreen = 'menu';

function showScreen(name, showBack){
  Object.keys(screens).forEach(function(k){
    screens[k].classList.toggle('cw-active', k === name);
  });
  currentScreen = name;
  backBar.style.display = showBack ? '' : 'none';
}

// ── open/close ────────────────────────────────────────────
var isOpen = false;
function openDrawer(){
  isOpen = true;
  drawer.classList.add('cw-open');
  fab.setAttribute('aria-expanded','true');
}
function closeDrawer(){
  isOpen = false;
  drawer.classList.remove('cw-open');
  fab.setAttribute('aria-expanded','false');
}
fab.addEventListener('click', function(){ isOpen ? closeDrawer() : openDrawer(); });
fab.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); isOpen?closeDrawer():openDrawer(); }});
btnClose.addEventListener('click', closeDrawer);
btnBack.addEventListener('click', function(){ showScreen('menu', false); resetBot(); });

// ── menu options ──────────────────────────────────────────
document.querySelectorAll('.cw-opt').forEach(function(btn){
  btn.addEventListener('click', function(){
    var action = this.dataset.action;
    if(action === 'ticket')  { window.location.href = '/cliente/tickets/novo'; return; }
    if(action === 'email')   { window.location.href = 'mailto:suporte@' + location.hostname; return; }
    if(action === 'history') { loadHistory(); showScreen('history', true); return; }
    if(action === 'chat')    { startBot(); showScreen('bot', true); return; }
  });
});

// ── BOT FLOW ──────────────────────────────────────────────
var botMsgs = document.getElementById('cw-bot-msgs');
var botOpts = document.getElementById('cw-bot-opts');
var botInputArea = document.getElementById('cw-bot-input-area');
var botInput = document.getElementById('cw-bot-input');
var botSend  = document.getElementById('cw-bot-send');

var BOT_FLOW = {
  start: {
    msg: 'Sobre o que você precisa de ajuda?',
    opts: [
      { label: '🖥️ Problema com minha VPS',    next: 'vps' },
      { label: '💳 Dúvida sobre cobrança',       next: 'billing' },
      { label: '📧 Problema com e-mail',         next: 'email_issue' },
      { label: '🚀 Deploy / aplicação',          next: 'deploy' },
      { label: '❓ Outro assunto',               next: 'other' },
    ]
  },
  vps: {
    msg: 'Qual é o problema com sua VPS?',
    opts: [
      { label: '🔴 VPS offline / não responde', next: 'escalate' },
      { label: '🐢 Lentidão ou alta CPU',        next: 'escalate' },
      { label: '🔑 Perdi acesso SSH',            next: 'escalate' },
      { label: '📦 Preciso de mais recursos',    next: 'upgrade' },
    ]
  },
  billing: {
    msg: 'Qual é a sua dúvida sobre cobrança?',
    opts: [
      { label: '🧾 Não reconheço uma cobrança', next: 'escalate' },
      { label: '💰 Quero cancelar meu plano',   next: 'cancel' },
      { label: '📄 Preciso de nota fiscal',      next: 'escalate' },
    ]
  },
  email_issue: {
    msg: 'Qual é o problema com e-mail?',
    opts: [
      { label: '📥 Não estou recebendo e-mails', next: 'escalate' },
      { label: '📤 Não consigo enviar',           next: 'escalate' },
      { label: '🔐 Esqueci a senha do e-mail',    next: 'escalate' },
    ]
  },
  deploy: {
    msg: 'Qual é o problema com deploy?',
    opts: [
      { label: '❌ Deploy falhou',               next: 'escalate' },
      { label: '🌐 Domínio não aponta',          next: 'escalate' },
      { label: '🔒 Certificado SSL',             next: 'escalate' },
    ]
  },
  upgrade: {
    msg: 'Para upgrade de plano, acesse a área de Planos no painel.',
    opts: [
      { label: '📋 Ver planos disponíveis', action: function(){ window.location.href='/cliente/planos'; } },
      { label: '💬 Falar com atendente',    next: 'escalate' },
    ]
  },
  cancel: {
    msg: 'Para cancelamento, acesse Assinaturas no painel ou fale com um atendente.',
    opts: [
      { label: '💳 Ir para Assinaturas', action: function(){ window.location.href='/cliente/assinaturas'; } },
      { label: '💬 Falar com atendente', next: 'escalate' },
    ]
  },
  other: {
    msg: 'Tudo bem! Descreva brevemente o que precisa e um atendente irá ajudar.',
    input: true,
    onSend: function(text){ escalateToLive(text); }
  },
  escalate: {
    msg: 'Entendido! Vou conectar você com um atendente agora.',
    opts: [
      { label: '💬 Iniciar chat com atendente', action: function(){ escalateToLive(''); } },
      { label: '🎫 Prefiro abrir um ticket',    action: function(){ window.location.href='/cliente/tickets/novo'; } },
    ]
  }
};

function resetBot(){
  botMsgs.innerHTML = '';
  botOpts.innerHTML = '';
  botInputArea.style.display = 'none';
}

function startBot(){
  resetBot();
  runBotStep('start');
}

function addBotMsg(text){
  var typing = document.createElement('div');
  typing.className = 'cw-typing';
  typing.innerHTML = '<span></span><span></span><span></span>';
  botMsgs.appendChild(typing);
  botMsgs.scrollTop = botMsgs.scrollHeight;
  return new Promise(function(resolve){
    setTimeout(function(){
      typing.remove();
      var d = document.createElement('div');
      d.className = 'cw-bot-msg';
      d.textContent = text;
      botMsgs.appendChild(d);
      botMsgs.scrollTop = botMsgs.scrollHeight;
      resolve();
    }, 600);
  });
}

function addUserMsg(text){
  var d = document.createElement('div');
  d.className = 'cw-user-msg';
  d.textContent = text;
  botMsgs.appendChild(d);
  botMsgs.scrollTop = botMsgs.scrollHeight;
}

function runBotStep(stepKey){
  var step = BOT_FLOW[stepKey];
  if(!step) return;
  botOpts.innerHTML = '';
  botInputArea.style.display = 'none';

  addBotMsg(step.msg).then(function(){
    if(step.input){
      botInputArea.style.display = 'flex';
      botInput.focus();
      botSend.onclick = function(){
        var txt = botInput.value.trim();
        if(!txt) return;
        addUserMsg(txt);
        botInput.value = '';
        botInputArea.style.display = 'none';
        if(step.onSend) step.onSend(txt);
      };
      botInput.onkeydown = function(e){
        if(e.key==='Enter'){ e.preventDefault(); botSend.onclick(); }
      };
      return;
    }
    if(step.opts){
      step.opts.forEach(function(opt){
        var btn = document.createElement('button');
        btn.className = 'cw-opt';
        btn.textContent = opt.label;
        btn.addEventListener('click', function(){
          addUserMsg(opt.label);
          botOpts.innerHTML = '';
          if(opt.action){ opt.action(); return; }
          if(opt.next){ runBotStep(opt.next); }
        });
        botOpts.appendChild(btn);
      });
    }
  });
}

// ── LIVE CHAT ─────────────────────────────────────────────
var liveWs = null;
var liveMsgs = document.getElementById('cw-live-msgs');
var liveInput = document.getElementById('cw-live-input');
var liveSend  = document.getElementById('cw-live-send');
var liveStatus= document.getElementById('cw-live-status');

function escalateToLive(context){
  showScreen('live', true);
  connectLive(context);
}

function addLiveMsg(senderType, text, time){
  var d = document.createElement('div');
  d.className = senderType === 'client' ? 'cw-user-msg' : (senderType === 'system' ? 'cw-bot-msg' : 'cw-bot-msg');
  d.textContent = text;
  if(time){
    var t = document.createElement('div');
    t.style.cssText = 'font-size:10px;opacity:.6;margin-top:3px;';
    t.textContent = new Date(time).toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});
    d.appendChild(t);
  }
  liveMsgs.appendChild(d);
  liveMsgs.scrollTop = liveMsgs.scrollHeight;
}

function connectLive(context){
  liveStatus.textContent = 'Conectando...';
  liveInput.disabled = true;
  liveSend.disabled  = true;

  fetch('/cliente/chat/token', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'_csrf='+encodeURIComponent(CSRF)
  })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(!data.ok){ liveStatus.textContent = 'Erro ao conectar.'; return; }
    var proto = location.protocol==='https:'?'wss':'ws';
    liveWs = new WebSocket(proto+'://'+location.hostname+':'+WS_PORT+'/?token='+encodeURIComponent(data.token));

    liveWs.onopen = function(){
      liveStatus.textContent = '● Online';
      liveStatus.style.color = '#22c55e';
      liveInput.disabled = false;
      liveSend.disabled  = false;
      liveInput.focus();
      if(context){ liveWs.send(JSON.stringify({message: context})); }
    };

    liveWs.onmessage = function(e){
      try{
        var msg = JSON.parse(e.data);
        if(msg.type==='history'){
          liveMsgs.innerHTML = '';
          (msg.messages||[]).forEach(function(m){ addLiveMsg(m.sender_type, m.message, m.created_at); });
        } else if(msg.type==='message'){
          addLiveMsg(msg.sender_type, msg.message, msg.created_at);
        } else if(msg.type==='system'){
          addLiveMsg('system', msg.message, null);
        }
      }catch(err){}
    };

    liveWs.onclose = function(){
      liveStatus.textContent = '● Desconectado';
      liveStatus.style.color = '#ef4444';
      liveInput.disabled = true;
      liveSend.disabled  = true;
    };
    liveWs.onerror = function(){ liveWs.close(); };
  })
  .catch(function(){ liveStatus.textContent = 'Erro de rede.'; });
}

function sendLive(){
  var txt = liveInput.value.trim();
  if(!txt || !liveWs || liveWs.readyState!==1) return;
  liveWs.send(JSON.stringify({message: txt}));
  liveInput.value = '';
  liveInput.style.height = '40px';
  liveInput.focus();
}

liveSend.addEventListener('click', sendLive);
liveInput.addEventListener('keydown', function(e){
  if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendLive(); }
});
liveInput.addEventListener('input', function(){
  this.style.height = '40px';
  this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});

// ── HISTORY ───────────────────────────────────────────────
var historyList = document.getElementById('cw-history-list');

function loadHistory(){
  historyList.innerHTML = '<div style="font-size:13px;color:#64748b;padding:8px 0;">Carregando...</div>';
  fetch('/cliente/chat/historico', {
    headers:{'x-requested-with':'XMLHttpRequest'}
  })
  .then(function(r){ return r.json(); })
  .then(function(data){
    historyList.innerHTML = '';
    if(!data.rooms || data.rooms.length === 0){
      historyList.innerHTML = '<div style="font-size:13px;color:#64748b;padding:8px 0;">Nenhum chat anterior.</div>';
      return;
    }
    data.rooms.forEach(function(room){
      var d = document.createElement('div');
      d.className = 'cw-history-item';
      var status = room.status === 'open' ? '🟢 Aberto' : '⚫ Encerrado';
      d.innerHTML = '<div class="cw-history-item-title">Chat #'+room.id+' — '+status+'</div>'
        +'<div class="cw-history-item-sub">'+room.created_at+' · '+room.total_messages+' mensagens</div>';
      d.addEventListener('click', function(){
        if(room.status === 'open'){
          showScreen('live', true);
          connectLive('');
        } else {
          window.location.href = '/cliente/chat';
        }
      });
      historyList.appendChild(d);
    });
  })
  .catch(function(){
    historyList.innerHTML = '<div style="font-size:13px;color:#ef4444;">Erro ao carregar histórico.</div>';
  });
}

})();
</script>
