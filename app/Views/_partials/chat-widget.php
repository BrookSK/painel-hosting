<?php
// Widget de suporte — aparece em todas as páginas
// Detecta se há cliente logado para liberar chat ao vivo
use LRV\Core\Auth;
use LRV\Core\I18n;
use LRV\Core\View;
$_cwLogado = false;
$_cwCsrf   = '';
try {
    $_cwLogado = (Auth::clienteId() !== null);
    if ($_cwLogado) {
        $_cwCsrf = \LRV\Core\Csrf::token();
    }
} catch (\Throwable $_e) {}
$_cwWsUrl = '';
try {
    $_cwWsUrl = (string)\LRV\Core\Settings::obter('chat.ws_url', '');
} catch (\Throwable $_e) {}
if ($_cwWsUrl === '') {
    try {
        $_cwWsPort = (int)\LRV\Core\Settings::obter('chat.ws_port', 8082);
    } catch (\Throwable $_e) {
        $_cwWsPort = 8082;
    }
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'wss' : 'ws';
    $_cwWsUrl = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ':' . $_cwWsPort;
}
?>
<div id="cw-fab"
     data-logado="<?php echo $_cwLogado ? '1' : '0'; ?>"
     data-csrf="<?php echo htmlspecialchars($_cwCsrf, ENT_QUOTES, 'UTF-8'); ?>"
     data-wsurl="<?php echo htmlspecialchars($_cwWsUrl, ENT_QUOTES, 'UTF-8'); ?>"
     title="<?php echo View::e(I18n::t('widget.suporte')); ?>" aria-label="<?php echo View::e(I18n::t('widget.abrir_suporte')); ?>" role="button" tabindex="0">
  <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
    <path d="M22 3H4a2 2 0 00-2 2v13a2 2 0 002 2h5l4 4 4-4h5a2 2 0 002-2V5a2 2 0 00-2-2z" fill="#fff" opacity=".95"/>
    <circle cx="9" cy="12" r="1.5" fill="#4F46E5"/>
    <circle cx="13" cy="12" r="1.5" fill="#4F46E5"/>
    <circle cx="17" cy="12" r="1.5" fill="#4F46E5"/>
  </svg>
</div>

<div id="cw-drawer" role="dialog" aria-modal="true" aria-label="<?php echo View::e(I18n::t('widget.suporte')); ?>">
  <div id="cw-head">
    <div style="display:flex;align-items:center;gap:10px;">
      <div id="cw-head-icon">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
          <path d="M15 2H3a1 1 0 00-1 1v8a1 1 0 001 1h3l3 3 3-3h3a1 1 0 001-1V3a1 1 0 00-1-1z" fill="#fff" opacity=".9"/>
        </svg>
      </div>
      <div>
        <div style="color:#fff;font-weight:700;font-size:14px;"><?php echo View::e(I18n::t('widget.suporte')); ?></div>
        <div style="color:rgba(255,255,255,.65);font-size:12px;"><?php echo View::e(I18n::t('widget.como_ajudar')); ?></div>
      </div>
    </div>
    <button id="cw-close" aria-label="<?php echo View::e(I18n::t('widget.fechar')); ?>">✕</button>
  </div>

  <!-- Tela: menu -->
  <div id="cw-s-menu" class="cw-screen cw-active">
    <div id="cw-msgs-menu">
      <div class="cw-bmsg"><?php echo View::e(I18n::t('widget.ola')); ?></div>
    </div>
    <div class="cw-opts">
      <button class="cw-opt" data-go="bot"><?php echo View::e(I18n::t('widget.tirar_duvida')); ?></button>
      <button class="cw-opt" data-go="live"><?php echo View::e(I18n::t('widget.falar_atendente')); ?></button>
      <button class="cw-opt" data-go="ticket"><?php echo View::e(I18n::t('widget.abrir_ticket')); ?></button>
      <button class="cw-opt" data-go="email"><?php echo View::e(I18n::t('widget.enviar_email')); ?></button>
    </div>
  </div>

  <!-- Tela: bot -->
  <div id="cw-s-bot" class="cw-screen">
    <div id="cw-bot-msgs" class="cw-msglist"></div>
    <div id="cw-bot-opts" class="cw-opts" style="padding:8px 12px;"></div>
    <div id="cw-bot-inp" style="display:none;padding:8px 12px 12px;border-top:1px solid #f1f5f9;display:none;gap:8px;">
      <input id="cw-bot-input" class="cw-input" type="text" placeholder="Digite sua mensagem..." autocomplete="off"/>
      <button id="cw-bot-send" class="cw-sendbtn" aria-label="Enviar">➤</button>
    </div>
  </div>

  <!-- Tela: chat ao vivo -->
  <div id="cw-s-live" class="cw-screen">
    <div id="cw-live-msgs" class="cw-msglist"></div>
    <div id="cw-live-upload-preview" style="padding:4px 12px;background:#fefce8;font-size:11px;color:#854d0e;display:none;align-items:center;gap:6px;">
      <span id="cw-live-upload-name"></span>
      <button id="cw-live-upload-cancel" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:12px;">✕</button>
    </div>
    <div style="padding:8px 12px 12px;border-top:1px solid #f1f5f9;display:flex;gap:6px;flex-shrink:0;align-items:flex-end;">
      <button id="cw-live-emoji" style="background:none;border:none;cursor:pointer;font-size:16px;padding:2px;line-height:1;" title="Emojis">😊</button>
      <button id="cw-live-file-btn" style="background:none;border:none;cursor:pointer;font-size:16px;padding:2px;line-height:1;" title="Arquivo">📎</button>
      <input type="file" id="cw-live-file" accept="image/*,.pdf,.doc,.docx,.txt" style="display:none;" />
      <textarea id="cw-live-inp" class="cw-input" rows="1" placeholder="Digite..." style="resize:none;height:38px;"></textarea>
      <button id="cw-live-send" class="cw-sendbtn" aria-label="Enviar">➤</button>
    </div>
    <div id="cw-live-emoji-picker" style="position:absolute;bottom:60px;left:8px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:6px;box-shadow:0 6px 24px rgba(0,0,0,.1);display:none;flex-wrap:wrap;gap:2px;z-index:10;width:240px;max-height:160px;overflow-y:auto;"></div>
    <div id="cw-live-status" style="font-size:11px;color:#64748b;padding:0 12px 8px;flex-shrink:0;"></div>
  </div>

  <div id="cw-back" style="display:none;padding:8px 12px;border-top:1px solid #f1f5f9;flex-shrink:0;">
    <button id="cw-backbtn" style="background:none;border:none;color:#4F46E5;font-size:13px;cursor:pointer;font-family:inherit;padding:0;"><?php echo View::e(I18n::t('widget.voltar')); ?></button>
  </div>
</div>

<style>
#cw-fab{
  position:fixed;bottom:24px;right:24px;z-index:2147483647;
  width:56px;height:56px;border-radius:50%;
  background:linear-gradient(135deg,#4F46E5,#7C3AED);
  box-shadow:0 4px 24px rgba(79,70,229,.5);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;user-select:none;
  transition:transform .2s,box-shadow .2s;
}
#cw-fab:hover{transform:scale(1.1);box-shadow:0 6px 32px rgba(79,70,229,.6);}
#cw-drawer{
  position:fixed;bottom:90px;right:24px;z-index:2147483646;
  width:340px;max-width:calc(100vw - 32px);
  background:#fff;border-radius:18px;
  box-shadow:0 16px 56px rgba(15,23,42,.2);
  display:none;flex-direction:column;
  max-height:580px;overflow:hidden;
  font-family:system-ui,-apple-system,'Segoe UI',sans-serif;
  font-size:14px;color:#0f172a;
}
#cw-drawer.open{display:flex;}
#cw-head{
  background:linear-gradient(135deg,#4F46E5,#7C3AED);
  padding:14px 16px;display:flex;align-items:center;
  justify-content:space-between;flex-shrink:0;
}
#cw-head-icon{
  width:34px;height:34px;border-radius:50%;
  background:rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
#cw-close{
  background:none;border:none;color:rgba(255,255,255,.7);
  font-size:18px;cursor:pointer;padding:4px 6px;
  border-radius:6px;line-height:1;
}
#cw-close:hover{color:#fff;}
.cw-screen{display:none;flex-direction:column;flex:1;overflow:hidden;min-height:0;}
.cw-screen.cw-active{display:flex;}
#cw-s-menu{padding:12px;}
.cw-msglist{
  flex:1;overflow-y:auto;
  display:flex;flex-direction:column;gap:6px;
  padding:12px;min-height:80px;
}
.cw-bmsg{
  background:#f1f5f9;border-radius:12px 12px 12px 3px;
  padding:10px 13px;font-size:13px;color:#0f172a;
  line-height:1.5;max-width:88%;
}
.cw-umsg{
  background:linear-gradient(135deg,#4F46E5,#7C3AED);
  color:#fff;border-radius:12px 12px 3px 12px;
  padding:10px 13px;font-size:13px;line-height:1.5;
  max-width:88%;align-self:flex-end;word-break:break-word;
}
.cw-sysmsg{
  background:#f1f5f9;border-radius:10px;
  padding:8px 12px;font-size:12px;line-height:1.5;
  color:#64748b;text-align:center;align-self:center;
  max-width:90%;font-style:italic;
}
.cw-msg-img{max-width:180px;border-radius:8px;margin-top:4px;cursor:pointer;}
.cw-msg-file{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;font-size:12px;margin-top:4px;text-decoration:none;color:inherit;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);}
.cw-opts{display:flex;flex-direction:column;gap:10px;padding:4px 0 8px;}
.cw-opt{
  background:#f8fafc;border:1.5px solid #e2e8f0;
  border-radius:11px;padding:12px 14px;
  font-size:13px;font-weight:600;color:#0f172a;
  cursor:pointer;text-align:left;
  transition:border-color .15s,background .15s;
  font-family:inherit;
}
.cw-opt:hover{border-color:#7C3AED;background:#f5f3ff;color:#4F46E5;}
.cw-input{
  flex:1;padding:8px 11px;
  border:1.5px solid #e2e8f0;border-radius:9px;
  font-size:13px;outline:none;font-family:inherit;
  color:#0f172a;background:#fff;
  transition:border-color .15s;
}
.cw-input:focus{border-color:#7C3AED;}
.cw-sendbtn{
  width:34px;height:34px;border-radius:9px;flex-shrink:0;
  background:linear-gradient(135deg,#4F46E5,#7C3AED);
  border:none;color:#fff;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:14px;
}
.cw-typing{display:flex;gap:4px;align-items:center;padding:8px 12px;background:#f1f5f9;border-radius:12px 12px 12px 3px;width:fit-content;}
.cw-typing span{width:6px;height:6px;border-radius:50%;background:#94a3b8;animation:cwdot .9s infinite;}
.cw-typing span:nth-child(2){animation-delay:.15s;}
.cw-typing span:nth-child(3){animation-delay:.3s;}
@keyframes cwdot{0%,60%,100%{transform:translateY(0);}30%{transform:translateY(-5px);}}
#cw-back{border-top:1px solid #f1f5f9;}
@media(max-width:480px){
  #cw-fab{bottom:16px;right:16px;}
  #cw-drawer{width:calc(100vw - 16px);right:8px;bottom:80px;}
}
</style>

<script>
(function(){
var fab     = document.getElementById('cw-fab');
var drawer  = document.getElementById('cw-drawer');
var btnClose= document.getElementById('cw-close');
var btnBack = document.getElementById('cw-backbtn');
var backBar = document.getElementById('cw-back');

var LOGADO  = fab.dataset.logado === '1';
var CSRF    = fab.dataset.csrf   || '';
var WS_URL  = fab.dataset.wsurl  || '';

// ── open/close ────────────────────────────────────────
var open = false;
function openW(){ open=true;  drawer.classList.add('open');    fab.setAttribute('aria-expanded','true'); }
function closeW(){ open=false; drawer.classList.remove('open'); fab.setAttribute('aria-expanded','false'); }
fab.addEventListener('click', function(){ open ? closeW() : openW(); });
fab.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); open?closeW():openW(); }});
btnClose.addEventListener('click', closeW);
btnBack.addEventListener('click', function(){ showScreen('menu'); });

// ── screens ───────────────────────────────────────────
var screens = { menu:'cw-s-menu', bot:'cw-s-bot', live:'cw-s-live' };
function showScreen(name){
  Object.keys(screens).forEach(function(k){
    var el = document.getElementById(screens[k]);
    if(el) el.classList.toggle('cw-active', k===name);
  });
  backBar.style.display = name==='menu' ? 'none' : '';
  if(name==='bot' && document.getElementById('cw-bot-msgs').children.length===0) startBot();
}

// ── menu ──────────────────────────────────────────────
document.querySelectorAll('.cw-opt[data-go]').forEach(function(btn){
  btn.addEventListener('click', function(){
    var go = this.dataset.go;
    if(go==='email'){ window.location.href='/contato'; return; }
    if(go==='ticket'){
      if(!LOGADO){ window.location.href='/cliente/entrar?redirect=/cliente/tickets/novo'; return; }
      window.location.href='/cliente/tickets/novo'; return;
    }
    if(go==='live'){
      if(!LOGADO){ showScreen('bot'); escalateMsgLogin(); return; }
      showScreen('live'); connectLive(''); return;
    }
    if(go==='bot'){ showScreen('bot'); return; }
  });
});

// ── BOT ───────────────────────────────────────────────
var botMsgs = document.getElementById('cw-bot-msgs');
var botOpts = document.getElementById('cw-bot-opts');
var botInpArea = document.getElementById('cw-bot-inp');
var botInput   = document.getElementById('cw-bot-input');
var botSend    = document.getElementById('cw-bot-send');

var FLOW = {
  start:{
    msg:'Sobre o que você precisa de ajuda?',
    opts:[
      {l:'🖥️ VPS / Servidores',      n:'vps'},
      {l:'💳 Planos e preços',        n:'planos'},
      {l:'📧 E-mail profissional',    n:'email'},
      {l:'🚀 Deploy de aplicações',   n:'deploy'},
      {l:'🔒 Segurança e backups',    n:'seguranca'},
      {l:'❓ Outra dúvida',           n:'outro'},
    ]
  },
  vps:{
    msg:'Oferecemos VPS com recursos dedicados, SSD NVMe, proteção DDoS e uptime 99.9%. O que você quer saber?',
    opts:[
      {l:'📦 Quais recursos estão disponíveis?', n:'vps_recursos'},
      {l:'⚡ Como é o provisionamento?',         n:'vps_prov'},
      {l:'🔑 Como acesso minha VPS?',            n:'vps_acesso'},
      {l:'💬 Falar com atendente',               n:'escalate'},
    ]
  },
  vps_recursos:{
    msg:'Nossas VPS têm vCPU dedicada, RAM ECC, disco SSD NVMe, IP dedicado, proteção DDoS nativa e painel de controle completo com terminal web, monitoramento e backups automáticos.'
  },
  vps_prov:{
    msg:'Após a confirmação do pagamento, sua VPS é provisionada automaticamente em até 6 horas úteis. Você recebe as credenciais de acesso diretamente no painel.'
  },
  vps_acesso:{
    msg:'Você pode acessar sua VPS via SSH (usando o IP e as credenciais do painel) ou pelo Terminal Web integrado no painel do cliente, sem precisar instalar nada.'
  },
  planos:{
    msg:'Temos planos mensais e anuais com desconto. Todos incluem suporte técnico, painel de controle e backups. Quer ver os planos disponíveis?',
    opts:[
      {l:'📋 Ver planos e preços', a:function(){ window.location.href='/#planos'; }},
      {l:'💰 Tem período de teste?', n:'trial'},
      {l:'💬 Falar com atendente',  n:'escalate'},
    ]
  },
  trial:{
    msg:'Sim! Oferecemos um período de teste gratuito para novos clientes. Crie sua conta e ative o trial diretamente no painel.',
    opts:[
      {l:'🚀 Criar conta grátis', a:function(){ window.location.href='/cliente/criar-conta'; }},
      {l:'← Voltar ao início',    n:'start'},
    ]
  },
  email:{
    msg:'Oferecemos e-mail profissional com seu domínio próprio, webmail, IMAP/SMTP, proteção antispam e DKIM/SPF configurados automaticamente.',
    opts:[
      {l:'🌐 Como configuro meu domínio?', n:'email_dns'},
      {l:'📥 Qual é o limite de caixas?',  n:'email_limite'},
      {l:'💬 Falar com atendente',         n:'escalate'},
    ]
  },
  email_dns:{
    msg:'Após adicionar seu domínio no painel, você recebe os registros MX, SPF e DKIM para configurar no seu provedor de DNS. A verificação é automática após a propagação (até 48h).'
  },
  email_limite:{
    msg:'O número de caixas de e-mail depende do seu plano. Você pode ver o limite no painel em "Meus E-mails". Para mais caixas, basta fazer upgrade do plano.'
  },
  deploy:{
    msg:'Suportamos deploy via Git, Docker e scripts personalizados. O painel tem integração com Cloudflare para DNS e SSL automático.',
    opts:[
      {l:'🐳 Suportam Docker?',          n:'deploy_docker'},
      {l:'🌐 SSL automático?',           n:'deploy_ssl'},
      {l:'💬 Falar com atendente',       n:'escalate'},
    ]
  },
  deploy_docker:{
    msg:'Sim! Você pode rodar containers Docker na sua VPS. O painel tem suporte a deploy via Docker Compose e gerenciamento de containers.'
  },
  deploy_ssl:{
    msg:'Sim, o SSL é configurado automaticamente via integração com Cloudflare. Basta apontar seu domínio e o certificado é emitido em minutos.'
  },
  seguranca:{
    msg:'Segurança é prioridade. Oferecemos proteção DDoS nativa, backups automáticos diários, 2FA para acesso ao painel e logs de auditoria completos.',
    opts:[
      {l:'💾 Como funcionam os backups?', n:'backup'},
      {l:'🛡️ Proteção DDoS inclusa?',    n:'ddos'},
      {l:'💬 Falar com atendente',        n:'escalate'},
    ]
  },
  backup:{
    msg:'Backups automáticos são realizados diariamente e ficam disponíveis por 7 dias. Você pode restaurar qualquer backup com um clique no painel.'
  },
  ddos:{
    msg:'Sim, proteção DDoS está inclusa em todos os planos sem custo adicional. Nossa infraestrutura filtra ataques na camada de rede antes de chegarem ao seu servidor.'
  },
  outro:{
    msg:'Tudo bem! Descreva sua dúvida e vou tentar ajudar. Se precisar de suporte especializado, posso conectar você com um atendente.',
    input:true,
    onSend:function(txt){ addBotMsg('Entendido! Para dúvidas mais específicas, recomendo falar com um de nossos atendentes.'); showEscalate(); }
  },
  escalate:{
    msg: LOGADO
      ? 'Vou conectar você com um atendente agora.'
      : 'Para falar com um atendente, você precisa estar logado.',
    opts: LOGADO
      ? [{l:'💬 Iniciar chat', a:function(){ showScreen('live'); connectLive(''); }},
         {l:'🎫 Abrir ticket', a:function(){ window.location.href='/cliente/tickets/novo'; }}]
      : [{l:'🔑 Fazer login', a:function(){ window.location.href='/cliente/entrar?redirect=/cliente/chat'; }},
         {l:'📧 Enviar e-mail', a:function(){ window.location.href='/contato'; }}]
  }
};

function escalateMsgLogin(){
  addBotMsg('Para falar com um atendente via chat, você precisa estar logado.');
  setTimeout(function(){
    addBotOpts([
      {l:'🔑 Fazer login', a:function(){ window.location.href='/cliente/entrar?redirect=/cliente/chat'; }},
      {l:'📧 Enviar e-mail', a:function(){ window.location.href='/contato'; }},
      {l:'← Voltar', n:'start'}
    ]);
  }, 400);
}

function showEscalate(){
  setTimeout(function(){ runStep('escalate'); }, 600);
}

function addBotMsg(txt){
  var typing = document.createElement('div');
  typing.className='cw-typing';
  typing.innerHTML='<span></span><span></span><span></span>';
  botMsgs.appendChild(typing);
  botMsgs.scrollTop=botMsgs.scrollHeight;
  return new Promise(function(res){
    setTimeout(function(){
      typing.remove();
      var d=document.createElement('div');
      d.className='cw-bmsg'; d.textContent=txt;
      botMsgs.appendChild(d);
      botMsgs.scrollTop=botMsgs.scrollHeight;
      res();
    },500);
  });
}

function addUserMsg(txt){
  var d=document.createElement('div');
  d.className='cw-umsg'; d.textContent=txt;
  botMsgs.appendChild(d);
  botMsgs.scrollTop=botMsgs.scrollHeight;
}

function addBotOpts(opts){
  botOpts.innerHTML='';
  opts.forEach(function(opt){
    var b=document.createElement('button');
    b.className='cw-opt'; b.textContent=opt.l;
    b.addEventListener('click',function(){
      addUserMsg(opt.l); botOpts.innerHTML='';
      if(opt.a){ opt.a(); return; }
      if(opt.n){ runStep(opt.n); }
    });
    botOpts.appendChild(b);
  });
}

function runStep(key){
  var step=FLOW[key]; if(!step) return;
  botOpts.innerHTML='';
  botInpArea.style.display='none';
  addBotMsg(step.msg).then(function(){
    if(step.input){
      botInpArea.style.display='flex';
      botInput.focus();
      botSend.onclick=function(){
        var t=botInput.value.trim(); if(!t) return;
        addUserMsg(t); botInput.value='';
        botInpArea.style.display='none';
        if(step.onSend) step.onSend(t);
      };
      botInput.onkeydown=function(e){ if(e.key==='Enter'){ e.preventDefault(); botSend.onclick(); }};
      return;
    }
    if(step.opts) addBotOpts(step.opts);
    else {
      // Leaf node — show back button via opts
      setTimeout(function(){
        addBotOpts([{l:'← Voltar ao início', n:'start'}]);
      }, 300);
    }
  });
}

function startBot(){
  botMsgs.innerHTML=''; botOpts.innerHTML='';
  runStep('start');
}

// ── LIVE CHAT ─────────────────────────────────────────
var liveMsgs   = document.getElementById('cw-live-msgs');
var liveInp    = document.getElementById('cw-live-inp');
var liveSend   = document.getElementById('cw-live-send');
var liveStatus = document.getElementById('cw-live-status');
var liveWs     = null;
var livePendingFile=null,liveMode='ws',liveWsFails=0,livePollTimer=null,liveLastId=0,liveRoomId=0;

// Emoji picker
var EMOJIS_W=['😊','😂','😍','🥰','😎','🤔','👍','👎','❤️','🔥','✅','❌','⭐','🎉','💬','📎','🖥️','🚀','💡','⚡','🛡️','🔒','📧','🎫','👋','🙏','💪','👀','🤝','✨'];
var ePicker=document.getElementById('cw-live-emoji-picker');
EMOJIS_W.forEach(function(e){var s=document.createElement('span');s.textContent=e;s.style.cssText='cursor:pointer;font-size:18px;padding:3px;border-radius:4px;line-height:1;';s.addEventListener('click',function(){liveInp.value+=e;liveInp.focus();ePicker.style.display='none';});ePicker.appendChild(s);});
document.getElementById('cw-live-emoji').addEventListener('click',function(ev){ev.stopPropagation();ePicker.style.display=ePicker.style.display==='flex'?'none':'flex';});
document.addEventListener('click',function(ev){if(!ePicker.contains(ev.target))ePicker.style.display='none';});

// File upload
var liveFileInput=document.getElementById('cw-live-file'),liveUpPreview=document.getElementById('cw-live-upload-preview'),liveUpName=document.getElementById('cw-live-upload-name');
document.getElementById('cw-live-file-btn').addEventListener('click',function(){liveFileInput.click();});
liveFileInput.addEventListener('change',function(){if(!this.files||!this.files[0])return;livePendingFile=this.files[0];liveUpName.textContent='📎 '+livePendingFile.name;liveUpPreview.style.display='flex';});
document.getElementById('cw-live-upload-cancel').addEventListener('click',function(){livePendingFile=null;liveFileInput.value='';liveUpPreview.style.display='none';});

function isImgUrl(u){return /\.(png|jpe?g|gif|webp)$/i.test(u||'');}
function escH(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

var liveLastDate='',liveRenderedIds={};
function liveDaySep(ts){
  if(!ts)return;
  var d=ts.substring(0,10);
  if(d===liveLastDate)return;
  liveLastDate=d;
  var p=d.split('-');
  var sep=document.createElement('div');
  sep.style.cssText='text-align:center;font-size:10px;color:#94a3b8;padding:6px 0;display:flex;align-items:center;gap:8px;';
  sep.innerHTML='<span style="flex:1;height:1px;background:#e2e8f0;"></span><span>'+p[2]+'/'+p[1]+'/'+p[0]+'</span><span style="flex:1;height:1px;background:#e2e8f0;"></span>';
  liveMsgs.appendChild(sep);
}

var liveLastSender='';
function addLiveMsg(type,txt,ts,fileUrl,fileName,senderName){
  liveDaySep(ts);
  var d=document.createElement('div');
  if(type==='system'){d.className='cw-sysmsg';}
  else{d.className=type==='client'?'cw-umsg':'cw-bmsg';}
  // Show agent name only when sender changes
  if(type==='admin'&&senderName&&senderName!==liveLastSender){
    var nm=document.createElement('div');nm.style.cssText='font-size:10px;font-weight:600;opacity:.7;margin-bottom:2px;';nm.textContent=senderName;d.appendChild(nm);
  }
  liveLastSender=type==='admin'?(senderName||''):'';
  if(txt){var sp=document.createElement('span');sp.innerHTML=escH(txt);d.appendChild(sp);}
  if(fileUrl){
    if(isImgUrl(fileUrl)){var img=document.createElement('img');img.src=fileUrl;img.className='cw-msg-img';img.alt=fileName||'';img.addEventListener('click',function(){window.open(fileUrl,'_blank');});d.appendChild(img);}
    else{var a=document.createElement('a');a.href=fileUrl;a.target='_blank';a.className='cw-msg-file';a.textContent='📄 '+(fileName||'arquivo');d.appendChild(a);}
  }
  if(ts){var t=document.createElement('div');t.style.cssText='font-size:10px;opacity:.55;margin-top:3px;';t.textContent=new Date(ts).toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});d.appendChild(t);}
  liveMsgs.appendChild(d);liveMsgs.scrollTop=liveMsgs.scrollHeight;
}

function liveLoadMsgs(msgs){
  (msgs||[]).forEach(function(m){
    var id=parseInt(m.id)||0;
    if(id>0 && liveRenderedIds[id]) return;
    if(id>0) liveRenderedIds[id]=true;
    if(id>liveLastId)liveLastId=id;
    addLiveMsg(m.sender_type,m.message,m.created_at,m.file_url,m.file_name,m.sender_name||null);
  });
}

function liveDoUpload(file,cb){
  var fd=new FormData();fd.append('arquivo',file);fd.append('_csrf',CSRF);
  fetch('/chat/upload',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){
    if(!d.ok){liveStatus.textContent=d.erro||'Erro no upload';return;}
    cb(d.file_url,d.file_name);
  }).catch(function(){liveStatus.textContent='Erro ao enviar arquivo.';});
}

function liveSendPayload(text,fu,fn){
  if(liveMode==='ws'&&liveWs&&liveWs.readyState===1){
    var p={message:text||''};if(fu){p.file_url=fu;p.file_name=fn;}
    liveWs.send(JSON.stringify(p));
  } else {
    var body='_csrf='+encodeURIComponent(CSRF)+'&message='+encodeURIComponent(text||'');
    if(fu)body+='&file_url='+encodeURIComponent(fu)+'&file_name='+encodeURIComponent(fn||'');
    fetch('/cliente/chat/enviar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
    .then(function(r){return r.json();}).then(function(d){
      if(d.ok&&d.msg){var id=parseInt(d.msg.id)||0;if(id>liveLastId)liveLastId=id;addLiveMsg(d.msg.sender_type,d.msg.message,d.msg.created_at,d.msg.file_url,d.msg.file_name,d.msg.sender_name||null);}
    }).catch(function(){});
  }
}

function tryWsLive(ctx){
  liveStatus.textContent='Conectando...';liveStatus.style.color='#64748b';
  liveInp.disabled=true;liveSend.disabled=true;
  fetch('/cliente/chat/token',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)})
  .then(function(r){return r.json();}).then(function(d){
    if(!d.ok){liveWsFails++;liveCheckFallback(ctx);return;}
    if(d.room_id)liveRoomId=d.room_id;
    liveWs=new WebSocket(WS_URL+'/?token='+encodeURIComponent(d.token));
    liveWs.onopen=function(){
      liveMode='ws';liveWsFails=0;
      liveStatus.textContent='● Online';liveStatus.style.color='#22c55e';
      liveInp.disabled=false;liveSend.disabled=false;liveInp.focus();
      if(livePollTimer){clearInterval(livePollTimer);livePollTimer=null;}
      if(ctx)liveWs.send(JSON.stringify({message:ctx}));
    };
    liveWs.onmessage=function(e){
      try{var m=JSON.parse(e.data);
        if(m.type==='history'){liveMsgs.innerHTML='';liveLastId=0;liveLastDate='';liveRenderedIds={};liveLastSender='';liveLoadMsgs(m.messages);}
        else if(m.type==='message'||m.type==='system'){var id=parseInt(m.id)||0;if(id>liveLastId)liveLastId=id;addLiveMsg(m.sender_type||'system',m.message,m.created_at,m.file_url,m.file_name,m.sender_name||null);}
      }catch(err){}
    };
    liveWs.onclose=function(){liveWsFails++;liveCheckFallback('');};
    liveWs.onerror=function(){try{liveWs.close();}catch(x){}};
  }).catch(function(){liveWsFails++;liveCheckFallback(ctx);});
}

function liveCheckFallback(ctx){
  if(liveWsFails>=2){startLivePolling();}
  else{setTimeout(function(){tryWsLive(ctx);},3000);}
}

function startLivePolling(){
  liveMode='poll';
  liveStatus.textContent='● Online';liveStatus.style.color='#22c55e';
  liveInp.disabled=false;liveSend.disabled=false;liveInp.focus();
  doLivePoll();
  if(!livePollTimer)livePollTimer=setInterval(doLivePoll,3000);
}

function doLivePoll(){
  fetch('/cliente/chat/poll?after='+liveLastId).then(function(r){return r.json();}).then(function(d){
    if(!d.ok)return;
    if(d.room_id)liveRoomId=d.room_id;
    if(d.status==='closed'){liveStatus.textContent='● Chat encerrado';liveStatus.style.color='#ef4444';liveInp.disabled=true;liveSend.disabled=true;if(livePollTimer){clearInterval(livePollTimer);livePollTimer=null;}return;}
    liveLoadMsgs(d.messages);
  }).catch(function(){});
}

function connectLive(ctx){
  if(!LOGADO)return;
  // Load existing chat history first
  liveMsgs.innerHTML='';liveLastId=0;liveLastDate='';liveRenderedIds={};liveLastSender='';
  fetch('/cliente/chat/historico').then(function(r){return r.json();}).then(function(d){
    if(d.ok && d.messages && d.messages.length > 0){
      liveLoadMsgs(d.messages);
      if(d.room_id) liveRoomId = d.room_id;
    }
    tryWsLive(ctx);
  }).catch(function(){ tryWsLive(ctx); });
}

function sendLive(){
  var t=liveInp.value.trim();
  if(!t&&!livePendingFile)return;
  if(livePendingFile){
    var f=livePendingFile,txt=t;livePendingFile=null;liveFileInput.value='';liveUpPreview.style.display='none';liveInp.value='';liveInp.style.height='38px';
    liveDoUpload(f,function(fu,fn){liveSendPayload(txt,fu,fn);});
    return;
  }
  liveSendPayload(t,null,null);liveInp.value='';liveInp.style.height='38px';liveInp.focus();
}
liveSend.addEventListener('click',sendLive);
liveInp.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendLive();}});
liveInp.addEventListener('input',function(){this.style.height='38px';this.style.height=Math.min(this.scrollHeight,90)+'px';});

})();
</script>
