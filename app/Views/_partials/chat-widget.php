<?php
// Widget de suporte — contexto-aware
use LRV\Core\Auth;
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\Settings;

$_cwLogado = false;
$_cwManaged = false;
$_cwCsrf = '';
$_cwTrialAtivo = false;
$_cwEmailAdmin = '';
$_cwIsEquipe = false;
try {
    $_cwIsEquipe = (Auth::equipeId() !== null) && !Auth::estaImpersonando();
    $_cwLogado = (Auth::clienteId() !== null);
    $_cwManaged = $_cwLogado && Auth::clienteGerenciado() && !Auth::estaImpersonando();
    if ($_cwLogado) { $_cwCsrf = \LRV\Core\Csrf::token(); }
    $_cwTrialAtivo = (int)Settings::obter('trial.enabled', 0) === 1;
    $_cwEmailAdmin = (string)\LRV\Core\ConfiguracoesSistema::emailAdmin();
} catch (\Throwable $_e) {}
// Não mostrar widget para equipe logada (eles usam /equipe/chat)
if ($_cwIsEquipe) return;
$_cwWsUrl = '';
try {
    $_cwWsUrl = (string)Settings::obter('chat.ws_url', '');
    if ($_cwWsUrl === '') {
        $_cwWsPort = (int)Settings::obter('chat.ws_port', 8082);
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'wss' : 'ws';
        $_cwWsUrl = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ':' . $_cwWsPort;
    }
} catch (\Throwable $_e) { $_cwWsUrl = ''; }
?>
<div id="cw-fab"
     data-logado="<?php echo $_cwLogado ? '1' : '0'; ?>"
     data-managed="<?php echo $_cwManaged ? '1' : '0'; ?>"
     data-trial="<?php echo $_cwTrialAtivo ? '1' : '0'; ?>"
     data-csrf="<?php echo htmlspecialchars($_cwCsrf, ENT_QUOTES, 'UTF-8'); ?>"
     data-wsurl="<?php echo htmlspecialchars($_cwWsUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-email="<?php echo htmlspecialchars($_cwEmailAdmin, ENT_QUOTES, 'UTF-8'); ?>"
     title="Suporte" role="button" tabindex="0">
  <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
    <path d="M22 3H4a2 2 0 00-2 2v13a2 2 0 002 2h5l4 4 4-4h5a2 2 0 002-2V5a2 2 0 00-2-2z" fill="#fff" opacity=".95"/>
    <circle cx="9" cy="12" r="1.5" fill="#4F46E5"/><circle cx="13" cy="12" r="1.5" fill="#4F46E5"/><circle cx="17" cy="12" r="1.5" fill="#4F46E5"/>
  </svg>
</div>

<div id="cw-drawer" role="dialog" aria-modal="true">
  <div id="cw-head">
    <div style="display:flex;align-items:center;gap:10px;">
      <div id="cw-head-icon"><svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M15 2H3a1 1 0 00-1 1v8a1 1 0 001 1h3l3 3 3-3h3a1 1 0 001-1V3a1 1 0 00-1-1z" fill="#fff" opacity=".9"/></svg></div>
      <div>
        <div style="color:#fff;font-weight:700;font-size:14px;">Suporte</div>
        <div style="color:rgba(255,255,255,.65);font-size:12px;">Como podemos ajudar?</div>
      </div>
    </div>
    <button id="cw-close" aria-label="Fechar">✕</button>
  </div>

  <!-- Menu principal -->
  <div id="cw-s-menu" class="cw-screen cw-active">
    <div id="cw-msgs-menu"><div class="cw-bmsg" id="cw-welcome"></div></div>
    <div class="cw-opts" id="cw-menu-opts"></div>
  </div>

  <!-- Bot FAQ -->
  <div id="cw-s-bot" class="cw-screen">
    <div id="cw-bot-msgs" class="cw-msglist"></div>
    <div id="cw-bot-opts" class="cw-opts" style="padding:8px 12px;"></div>
    <div id="cw-bot-inp" style="display:none;padding:8px 12px 12px;border-top:1px solid #f1f5f9;gap:8px;">
      <input id="cw-bot-input" class="cw-input" type="text" placeholder="Digite sua mensagem..." autocomplete="off"/>
      <button id="cw-bot-send" class="cw-sendbtn">➤</button>
    </div>
  </div>

  <!-- Pré-triagem antes do chat -->
  <div id="cw-s-triage" class="cw-screen">
    <div class="cw-msglist" style="padding:16px;">
      <div class="cw-bmsg">Para agilizar seu atendimento, preencha as informações abaixo:</div>
      <div style="margin-top:12px;display:flex;flex-direction:column;gap:10px;">
        <div>
          <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Assunto</label>
          <select id="cw-tri-assunto" class="cw-input" style="width:100%;">
            <option value="">Selecione...</option>
            <option value="Dúvida técnica">Dúvida técnica</option>
            <option value="Problema com VPS">Problema com VPS</option>
            <option value="Pagamento/Assinatura">Pagamento / Assinatura</option>
            <option value="Domínios/DNS">Domínios / DNS</option>
            <option value="E-mail">E-mail</option>
            <option value="API/Integrações">API / Integrações</option>
            <option value="Plano personalizado">Plano personalizado</option>
            <option value="Outro">Outro</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Descreva brevemente</label>
          <textarea id="cw-tri-desc" class="cw-input" rows="3" style="width:100%;resize:none;" placeholder="Ex: Minha VPS não está respondendo..."></textarea>
        </div>
        <button id="cw-tri-start" class="cw-sendbtn" style="width:100%;border-radius:8px;padding:10px;font-size:13px;">Iniciar chat</button>
      </div>
    </div>
  </div>

  <!-- Chat ao vivo -->
  <div id="cw-s-live" class="cw-screen">
    <div id="cw-live-msgs" class="cw-msglist"></div>
    <div id="cw-live-upload-preview" style="padding:4px 12px;background:#fefce8;font-size:11px;color:#854d0e;display:none;align-items:center;gap:6px;">
      <span id="cw-live-upload-name"></span>
      <button id="cw-live-upload-cancel" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:12px;">✕</button>
    </div>
    <div style="padding:8px 12px 12px;border-top:1px solid #f1f5f9;display:flex;gap:6px;flex-shrink:0;align-items:flex-end;">
      <button id="cw-live-emoji" style="background:none;border:none;cursor:pointer;font-size:16px;padding:2px;" title="Emojis">😊</button>
      <button id="cw-live-file-btn" style="background:none;border:none;cursor:pointer;font-size:16px;padding:2px;" title="Arquivo">📎</button>
      <input type="file" id="cw-live-file" accept="image/*,.pdf,.doc,.docx,.txt" style="display:none;" />
      <textarea id="cw-live-inp" class="cw-input" rows="1" placeholder="Digite..." style="resize:none;height:38px;"></textarea>
      <button id="cw-live-send" class="cw-sendbtn">➤</button>
    </div>
    <div id="cw-live-emoji-picker" style="position:absolute;bottom:60px;left:8px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:6px;box-shadow:0 6px 24px rgba(0,0,0,.1);display:none;flex-wrap:wrap;gap:2px;z-index:10;width:240px;max-height:160px;overflow-y:auto;"></div>
    <div id="cw-live-status" style="font-size:11px;color:#64748b;padding:0 12px 8px;flex-shrink:0;"></div>
  </div>

  <div id="cw-back" style="display:none;padding:8px 12px;border-top:1px solid #f1f5f9;flex-shrink:0;">
    <button id="cw-backbtn" style="background:none;border:none;color:#4F46E5;font-size:13px;cursor:pointer;font-family:inherit;padding:0;">← Voltar</button>
  </div>
</div>

<style>
#cw-fab{position:fixed;bottom:24px;right:24px;z-index:2147483647;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);box-shadow:0 4px 24px rgba(79,70,229,.5);display:flex;align-items:center;justify-content:center;cursor:pointer;user-select:none;transition:transform .2s,box-shadow .2s;}
#cw-fab:hover{transform:scale(1.1);box-shadow:0 6px 32px rgba(79,70,229,.6);}
#cw-drawer{position:fixed;bottom:90px;right:24px;z-index:2147483646;width:360px;max-width:calc(100vw - 32px);background:#fff;border-radius:18px;box-shadow:0 16px 56px rgba(15,23,42,.2);display:none;flex-direction:column;max-height:640px;overflow:hidden;font-family:system-ui,-apple-system,'Segoe UI',sans-serif;font-size:14px;color:#0f172a;}
#cw-drawer.open{display:flex;}
#cw-head{background:linear-gradient(135deg,#4F46E5,#7C3AED);padding:16px 18px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
#cw-head-icon{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
#cw-close{background:none;border:none;color:rgba(255,255,255,.7);font-size:18px;cursor:pointer;padding:4px 6px;border-radius:6px;line-height:1;}
#cw-close:hover{color:#fff;}
.cw-screen{display:none;flex-direction:column;flex:1;overflow:hidden;min-height:0;}
.cw-screen.cw-active{display:flex;}
#cw-s-menu{padding:14px;overflow-y:auto;}
.cw-msglist{flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:6px;padding:12px;min-height:60px;}
.cw-opts{overflow-y:auto;max-height:320px;flex-shrink:0;}
.cw-bmsg{background:#f1f5f9;border-radius:12px 12px 12px 3px;padding:10px 13px;font-size:13px;color:#0f172a;line-height:1.5;max-width:88%;}
.cw-umsg{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;border-radius:12px 12px 3px 12px;padding:10px 13px;font-size:13px;line-height:1.5;max-width:88%;align-self:flex-end;word-break:break-word;}
.cw-sysmsg{background:#f1f5f9;border-radius:10px;padding:8px 12px;font-size:12px;line-height:1.5;color:#64748b;text-align:center;align-self:center;max-width:90%;font-style:italic;}
.cw-msg-img{max-width:180px;border-radius:8px;margin-top:4px;cursor:pointer;}
.cw-msg-file{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;font-size:12px;margin-top:4px;text-decoration:none;color:inherit;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);}
.cw-opts{display:flex;flex-direction:column;gap:8px;padding:4px 0 8px;}
.cw-opt{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:11px;padding:11px 14px;font-size:13px;font-weight:600;color:#0f172a;cursor:pointer;text-align:left;transition:border-color .15s,background .15s;font-family:inherit;}
.cw-opt:hover{border-color:#7C3AED;background:#f5f3ff;color:#4F46E5;}
.cw-input{flex:1;padding:8px 11px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;outline:none;font-family:inherit;color:#0f172a;background:#fff;transition:border-color .15s;}
.cw-input:focus{border-color:#7C3AED;}
.cw-sendbtn{width:34px;height:34px;border-radius:9px;flex-shrink:0;background:linear-gradient(135deg,#4F46E5,#7C3AED);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;}
.cw-typing{display:flex;gap:4px;align-items:center;padding:8px 12px;background:#f1f5f9;border-radius:12px 12px 12px 3px;width:fit-content;}
.cw-typing span{width:6px;height:6px;border-radius:50%;background:#94a3b8;animation:cwdot .9s infinite;}
.cw-typing span:nth-child(2){animation-delay:.15s;}
.cw-typing span:nth-child(3){animation-delay:.3s;}
@keyframes cwdot{0%,60%,100%{transform:translateY(0);}30%{transform:translateY(-5px);}}
@media(max-width:480px){#cw-fab{bottom:16px;right:16px;}#cw-drawer{width:calc(100vw - 16px);right:8px;bottom:80px;max-height:85vh;}}
</style>

<script>
(function(){
var fab=document.getElementById('cw-fab'),drawer=document.getElementById('cw-drawer');
var LOGADO=fab.dataset.logado==='1',MANAGED=fab.dataset.managed==='1',TRIAL=fab.dataset.trial==='1';
var CSRF=fab.dataset.csrf||'',WS_URL=fab.dataset.wsurl||'',EMAIL_ADMIN=fab.dataset.email||'';
var open=false;
function openW(){open=true;drawer.classList.add('open');}
function closeW(){open=false;drawer.classList.remove('open');}
fab.addEventListener('click',function(){open?closeW():openW();});
fab.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();open?closeW():openW();}});
document.getElementById('cw-close').addEventListener('click',closeW);
document.getElementById('cw-backbtn').addEventListener('click',function(){showScreen('menu');});

var screens={menu:'cw-s-menu',bot:'cw-s-bot',triage:'cw-s-triage',live:'cw-s-live'};
var backBar=document.getElementById('cw-back');
function showScreen(n){Object.keys(screens).forEach(function(k){var el=document.getElementById(screens[k]);if(el)el.classList.toggle('cw-active',k===n);});backBar.style.display=n==='menu'?'none':'';if(n==='bot'&&!document.getElementById('cw-bot-msgs').children.length)startBot();}

// ── Build menu based on context ──
var menuOpts=document.getElementById('cw-menu-opts');
var welcomeEl=document.getElementById('cw-welcome');

if(LOGADO && MANAGED){
  welcomeEl.textContent='Olá! Como podemos ajudar com sua hospedagem gerenciada?';
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg> Abrir um ticket',function(){window.location.href='/cliente/tickets/novo';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',function(){showScreen('triage');});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg> Ver monitoramento',function(){window.location.href='/cliente/monitoramento';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Minhas assinaturas',function(){window.location.href='/cliente/assinaturas';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> API Keys',function(){window.location.href='/cliente/api-keys';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> Documentação API',function(){window.location.href='/developers/api';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg> WhatsApp Vendas',function(){window.open('https://wa.me/5517988093160','_blank');});
} else if(LOGADO){
  welcomeEl.textContent='Olá! Sou o assistente virtual. Como posso ajudar?';
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg> Tirar uma dúvida',function(){showScreen('bot');});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',function(){showScreen('triage');});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg> Abrir um ticket',function(){window.location.href='/cliente/tickets/novo';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> Enviar e-mail',function(){window.location.href='/cliente/tickets/novo';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> API Keys',function(){window.location.href='/cliente/api-keys';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> Documentação API',function(){window.location.href='/developers/api';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg> API Explorer',function(){window.location.href='/developers/api/swagger';});
} else {
  welcomeEl.textContent='Olá! Sou o assistente virtual. Como posso ajudar?';
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg> Tirar uma dúvida',function(){showScreen('bot');});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg> VPS Cloud',function(){window.location.href='/solucoes/vps';closeW();});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> WordPress Gerenciado',function(){window.location.href='/solucoes/wordpress';closeW();});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Web Hosting',function(){window.location.href='/solucoes/webhosting';closeW();});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1.5l9.5 5.5v11L12 23.5 2.5 18V7z"/></svg> Node.js App',function(){window.location.href='/solucoes/nodejs';closeW();});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.08z"/></svg> C/C++ App',function(){window.location.href='/solucoes/cpp';closeW();});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Ver planos',function(){window.location.href='/#planos';closeW();});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><path d="M9 22v-4h6v4"/></svg> Plano personalizado',function(){window.open('https://wa.me/5517988093160?text=Ol%C3%A1%2C%20gostaria%20de%20saber%20mais%20sobre%20planos%20personalizados','_blank');});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Entrar na minha conta',function(){window.location.href='/cliente/entrar';});
  addMenuBtn('<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> Entrar em contato',function(){window.location.href='/contato';});
}

function addMenuBtn(label,fn){var b=document.createElement('button');b.className='cw-opt';b.innerHTML=label;b.addEventListener('click',fn);menuOpts.appendChild(b);}

// ── Triage (pre-chat) ──
document.getElementById('cw-tri-start').addEventListener('click',function(){
  var assunto=document.getElementById('cw-tri-assunto').value;
  var desc=document.getElementById('cw-tri-desc').value.trim();
  if(!assunto){document.getElementById('cw-tri-assunto').style.borderColor='#ef4444';return;}
  var ctx='[Assunto: '+assunto+'] '+(desc||'Sem descrição');
  showScreen('live');connectLive(ctx);
});

// ── BOT FAQ ──
var botMsgs=document.getElementById('cw-bot-msgs'),botOpts=document.getElementById('cw-bot-opts');
var botInpArea=document.getElementById('cw-bot-inp'),botInput=document.getElementById('cw-bot-input'),botSend=document.getElementById('cw-bot-send');

var FLOW={
  start:{msg:'Sobre o que você precisa de ajuda?',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg> VPS / Servidores',n:'vps'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> WordPress Gerenciado',n:'wordpress'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Web Hosting',n:'webhosting'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1.5l9.5 5.5v11L12 23.5 2.5 18V7z"/></svg> Node.js App',n:'nodejs'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.08z"/></svg> C/C++ App',n:'cpp'},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Planos e preços',n:'planos'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> E-mail profissional',n:'email_faq'},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg> Deploy de aplicações',n:'deploy'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Segurança e backups',n:'seguranca'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><path d="M9 22v-4h6v4"/></svg> Plano personalizado',n:'personalizado'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Outra dúvida',n:'outro'}
  ]},
  wordpress:{msg:'Nosso WordPress Gerenciado inclui instalação em 1 clique, banco de dados dedicado, SSL grátis, backups diários e gerenciador de arquivos.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Ver planos WordPress',a:function(){window.location.href='/cliente/planos?tipo=wordpress';}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Saiba mais',a:function(){window.location.href='/solucoes/wordpress';closeW();}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  webhosting:{msg:'Web Hosting é ideal para quem quer hospedar sites e apps sem a complexidade de um VPS. Inclui catálogo de apps, git deploy, banco de dados e gerenciador de arquivos.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Ver planos Web Hosting',a:function(){window.location.href='/cliente/planos?tipo=webhosting';}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Saiba mais',a:function(){window.location.href='/solucoes/webhosting';closeW();}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  nodejs:{msg:'Deploy de aplicações Node.js com banco de dados incluso, domínio customizado com SSL e git deploy integrado.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Ver planos Node.js',a:function(){window.location.href='/cliente/planos?tipo=nodejs';}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Saiba mais',a:function(){window.location.href='/solucoes/nodejs';closeW();}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  cpp:{msg:'Hospede aplicações compiladas em C/C++ com build automatizado (GCC, CMake), banco de dados, domínio com SSL e git deploy.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Ver planos C/C++',a:function(){window.location.href='/cliente/planos?tipo=cpp';}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Saiba mais',a:function(){window.location.href='/solucoes/cpp';closeW();}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  vps:{msg:'Oferecemos VPS com recursos dedicados, SSD NVMe e uptime 99.9%.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Quais recursos?',n:'vps_recursos'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> Como é o provisionamento?',n:'vps_prov'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Como acesso minha VPS?',n:'vps_acesso'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  vps_recursos:{msg:'Nossas VPS têm vCPU dedicada, RAM ECC, disco SSD NVMe, IP dedicado, proteção DDoS nativa e painel completo com terminal web, monitoramento e backups.'},
  vps_prov:{msg:'Após confirmação do pagamento, sua VPS é provisionada automaticamente. Você recebe acesso direto no painel.'},
  vps_acesso:{msg:'Acesse via SSH (IP + credenciais do painel) ou pelo Terminal Web integrado, sem instalar nada.'},
  planos:{msg:'Temos planos mensais e anuais com desconto. Todos incluem suporte técnico, painel de controle e backups.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Ver planos',a:function(){if(LOGADO){window.location.href='/cliente/planos';}else{window.location.href='/#planos';closeW();}}},
    TRIAL?{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg> Tem período de teste?',n:'trial'}:{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><path d="M9 22v-4h6v4"/></svg> Plano personalizado',n:'personalizado'},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  trial:TRIAL?{msg:'Sim! Oferecemos um período de teste gratuito. Crie sua conta e ative o trial no painel.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg> Criar conta',a:function(){window.location.href='/cliente/criar-conta';}},{l:'← Voltar',n:'start'}
  ]}:{msg:'No momento não temos período de teste disponível. Mas temos planos acessíveis e planos personalizados sob consulta.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Ver planos',a:function(){window.location.href='/#planos';closeW();}},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><path d="M9 22v-4h6v4"/></svg> Plano personalizado',n:'personalizado'},{l:'← Voltar',n:'start'}
  ]},
  personalizado:{msg:'Montamos planos sob medida para empresas. Recursos exclusivos, gerenciamento completo e suporte dedicado. Entre em contato com nossa equipe de vendas.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg> WhatsApp Vendas',a:function(){window.open('https://wa.me/5517988093160?text=Ol%C3%A1%2C%20gostaria%20de%20saber%20mais%20sobre%20planos%20personalizados','_blank');}},
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> E-mail',a:function(){window.location.href='mailto:'+EMAIL_ADMIN+'?subject=Plano%20Personalizado';}},
    {l:'← Voltar',n:'start'}
  ]},
  email_faq:{msg:'E-mail profissional com seu domínio, webmail, IMAP/SMTP, antispam e DKIM/SPF automáticos.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Como configuro meu domínio?',n:'email_dns'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> Limite de caixas?',n:'email_limite'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  email_dns:{msg:'Adicione seu domínio no painel, configure os registros MX, SPF e DKIM no seu DNS. A verificação é automática após propagação.'},
  email_limite:{msg:'O número de caixas depende do seu plano. Veja o limite em "Meus E-mails". Para mais caixas, faça upgrade.'},
  deploy:{msg:'Suportamos deploy via Git, Docker e scripts. Integração com Cloudflare para DNS e SSL automático.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><rect x="5" y="10" width="4" height="4"/><rect x="11" y="10" width="4" height="4"/><line x1="2" y1="4" x2="7" y2="4"/></svg> Docker?',n:'deploy_docker'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> SSL automático?',n:'deploy_ssl'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  deploy_docker:{msg:'Sim! Rode containers Docker na sua VPS. O painel suporta deploy via Docker Compose e gerenciamento de containers.'},
  deploy_ssl:{msg:'SSL configurado automaticamente via Cloudflare. Aponte seu domínio e o certificado é emitido em minutos.'},
  seguranca:{msg:'Proteção DDoS nativa, backups automáticos, 2FA e logs de auditoria completos.',opts:[
    {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Backups?',n:'backup'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> DDoS inclusa?',n:'ddos'},{l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Falar com atendente',n:'escalate'}
  ]},
  backup:{msg:'Backups automáticos diários, disponíveis por 7 dias. Restaure com um clique no painel.'},
  ddos:{msg:'Proteção DDoS inclusa em todos os planos. Filtragem na camada de rede antes de chegar ao seu servidor.'},
  outro:{msg:'Descreva sua dúvida e vou tentar ajudar.',input:true,onSend:function(t){addBotMsg('Para dúvidas específicas, recomendo falar com um atendente.');showEscalate();}},
  escalate:{
    msg:LOGADO?'Vou conectar você com um atendente. Preencha as informações para agilizar.':'Para falar com um atendente, faça login primeiro.',
    opts:LOGADO?[
      {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Iniciar chat',a:function(){showScreen('triage');}},
      {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg> Abrir ticket',a:function(){window.location.href='/cliente/tickets/novo';}}
    ]:[
      {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Fazer login',a:function(){window.location.href='/cliente/entrar';}},
      {l:'<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> Entrar em contato',a:function(){window.location.href='/contato';}},
      {l:'← Voltar',n:'start'}
    ]
  }
};

function showEscalate(){setTimeout(function(){runStep('escalate');},600);}
function addBotMsg(txt){var typing=document.createElement('div');typing.className='cw-typing';typing.innerHTML='<span></span><span></span><span></span>';botMsgs.appendChild(typing);botMsgs.scrollTop=botMsgs.scrollHeight;return new Promise(function(res){setTimeout(function(){typing.remove();var d=document.createElement('div');d.className='cw-bmsg';d.textContent=txt;botMsgs.appendChild(d);botMsgs.scrollTop=botMsgs.scrollHeight;res();},500);});}
function addUserMsg(txt){var d=document.createElement('div');d.className='cw-umsg';d.textContent=txt;botMsgs.appendChild(d);botMsgs.scrollTop=botMsgs.scrollHeight;}
function addBotOpts(opts){botOpts.innerHTML='';opts.forEach(function(o){var b=document.createElement('button');b.className='cw-opt';b.innerHTML=o.l;b.addEventListener('click',function(){addUserMsg(o.l.replace(/<[^>]*>/g,''));botOpts.innerHTML='';if(o.a){o.a();return;}if(o.n)runStep(o.n);});botOpts.appendChild(b);});}
function runStep(k){var s=FLOW[k];if(!s)return;botOpts.innerHTML='';botInpArea.style.display='none';addBotMsg(s.msg).then(function(){if(s.input){botInpArea.style.display='flex';botInput.focus();botSend.onclick=function(){var t=botInput.value.trim();if(!t)return;addUserMsg(t);botInput.value='';botInpArea.style.display='none';if(s.onSend)s.onSend(t);};botInput.onkeydown=function(e){if(e.key==='Enter'){e.preventDefault();botSend.onclick();}};return;}if(s.opts)addBotOpts(s.opts);else setTimeout(function(){addBotOpts([{l:'← Voltar ao início',n:'start'}]);},300);});}
function startBot(){botMsgs.innerHTML='';botOpts.innerHTML='';runStep('start');}

// ── LIVE CHAT ──
var liveMsgs=document.getElementById('cw-live-msgs'),liveInp=document.getElementById('cw-live-inp'),liveSend=document.getElementById('cw-live-send'),liveStatus=document.getElementById('cw-live-status');
var liveWs=null,livePendingFile=null,liveMode='ws',liveWsFails=0,livePollTimer=null,liveLastId=0,liveRoomId=0;

// Emoji picker
var EMOJIS_W=['😊','😂','😍','🥰','😎','🤔','👍','👎','❤️','🔥','✅','❌','⭐','🎉','💬','📎','🖥️','🚀','💡','⚡','🛡️','🔐','📧','🎫','👋','🙏','💪','👀','🤝','✨'];
var ePicker=document.getElementById('cw-live-emoji-picker');
EMOJIS_W.forEach(function(e){var s=document.createElement('span');s.textContent=e;s.style.cssText='cursor:pointer;font-size:18px;padding:3px;border-radius:4px;line-height:1;';s.addEventListener('click',function(){liveInp.value+=e;liveInp.focus();ePicker.style.display='none';});ePicker.appendChild(s);});
document.getElementById('cw-live-emoji').addEventListener('click',function(ev){ev.stopPropagation();ePicker.style.display=ePicker.style.display==='flex'?'none':'flex';});
document.addEventListener('click',function(ev){if(!ePicker.contains(ev.target))ePicker.style.display='none';});

// File upload
var liveFileInput=document.getElementById('cw-live-file'),liveUpPreview=document.getElementById('cw-live-upload-preview'),liveUpName=document.getElementById('cw-live-upload-name');
document.getElementById('cw-live-file-btn').addEventListener('click',function(){liveFileInput.click();});
liveFileInput.addEventListener('change',function(){if(!this.files||!this.files[0])return;livePendingFile=this.files[0];liveUpName.textContent=livePendingFile.name;liveUpPreview.style.display='flex';});
document.getElementById('cw-live-upload-cancel').addEventListener('click',function(){livePendingFile=null;liveFileInput.value='';liveUpPreview.style.display='none';});

function isImgUrl(u){return /\.(png|jpe?g|gif|webp)$/i.test(u||'');}
function escH(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

var liveLastDate='',liveRenderedIds={},liveLastSender='';
function liveDaySep(ts){if(!ts)return;var d=ts.substring(0,10);if(d===liveLastDate)return;liveLastDate=d;var p=d.split('-');var sep=document.createElement('div');sep.style.cssText='text-align:center;font-size:10px;color:#94a3b8;padding:6px 0;display:flex;align-items:center;gap:8px;';sep.innerHTML='<span style="flex:1;height:1px;background:#e2e8f0;"></span><span>'+p[2]+'/'+p[1]+'/'+p[0]+'</span><span style="flex:1;height:1px;background:#e2e8f0;"></span>';liveMsgs.appendChild(sep);}

function addLiveMsg(type,txt,ts,fileUrl,fileName,senderName){
  liveDaySep(ts);var d=document.createElement('div');
  if(type==='system')d.className='cw-sysmsg';else d.className=type==='client'?'cw-umsg':'cw-bmsg';
  if(type==='admin'&&senderName&&senderName!==liveLastSender){var nm=document.createElement('div');nm.style.cssText='font-size:10px;font-weight:600;opacity:.7;margin-bottom:2px;';nm.textContent=senderName;d.appendChild(nm);}
  liveLastSender=type==='admin'?(senderName||''):'';
  var satMatch=txt?txt.match(/\{\{satisfaction:(\d+):(https?:\/\/[^\}]+)\}\}/):null;
  if(satMatch){
    var satRoomId=satMatch[1];
    var satDiv=document.createElement('div');
    satDiv.innerHTML='<div style="text-align:center;padding:12px;"><div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:8px;">Como foi seu atendimento?</div><div class="cw-stars" style="display:flex;justify-content:center;gap:4px;margin-bottom:10px;"><span data-star="1" style="font-size:28px;cursor:pointer;color:#e2e8f0;">★</span><span data-star="2" style="font-size:28px;cursor:pointer;color:#e2e8f0;">★</span><span data-star="3" style="font-size:28px;cursor:pointer;color:#e2e8f0;">★</span><span data-star="4" style="font-size:28px;cursor:pointer;color:#e2e8f0;">★</span><span data-star="5" style="font-size:28px;cursor:pointer;color:#e2e8f0;">★</span></div><textarea class="cw-input cw-sat-comment" placeholder="Comentário (opcional)" rows="2" style="width:100%;resize:none;margin-bottom:8px;font-size:12px;"></textarea><button class="cw-sendbtn cw-sat-submit" style="width:100%;border-radius:8px;padding:8px;font-size:13px;">Enviar avaliação</button><div class="cw-sat-done" style="display:none;color:#10b981;font-size:13px;font-weight:600;padding:8px;">✓ Obrigado!</div></div>';
    d.appendChild(satDiv);
    setTimeout(function(){var stars=satDiv.querySelectorAll('[data-star]');var rating=0;stars.forEach(function(s){s.addEventListener('mouseenter',function(){var v=parseInt(this.dataset.star);stars.forEach(function(x){x.style.color=parseInt(x.dataset.star)<=v?'#f59e0b':'#e2e8f0';});});s.addEventListener('mouseleave',function(){stars.forEach(function(x){x.style.color=parseInt(x.dataset.star)<=rating?'#f59e0b':'#e2e8f0';});});s.addEventListener('click',function(){rating=parseInt(this.dataset.star);stars.forEach(function(x){x.style.color=parseInt(x.dataset.star)<=rating?'#f59e0b':'#e2e8f0';});});});var sub=satDiv.querySelector('.cw-sat-submit'),com=satDiv.querySelector('.cw-sat-comment'),done=satDiv.querySelector('.cw-sat-done');sub.addEventListener('click',function(){if(rating<=0)return;sub.disabled=true;sub.textContent='Enviando...';fetch('/cliente/avaliar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)+'&type=chat&reference_id='+satRoomId+'&rating='+rating+'&comment='+encodeURIComponent(com.value)}).then(function(){sub.style.display='none';com.style.display='none';satDiv.querySelector('.cw-stars').style.pointerEvents='none';done.style.display='block';}).catch(function(){sub.disabled=false;sub.textContent='Enviar avaliação';});});},0);
  } else if(txt){var sp=document.createElement('span');sp.innerHTML=escH(txt);d.appendChild(sp);}
  if(fileUrl){if(isImgUrl(fileUrl)){var img=document.createElement('img');img.src=fileUrl;img.className='cw-msg-img';img.alt=fileName||'';img.addEventListener('click',function(){window.open(fileUrl,'_blank');});d.appendChild(img);}else{var a=document.createElement('a');a.href=fileUrl;a.target='_blank';a.className='cw-msg-file';a.textContent='<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> '+(fileName||'arquivo');d.appendChild(a);}}
  if(ts){var t=document.createElement('div');t.style.cssText='font-size:10px;opacity:.55;margin-top:3px;';t.textContent=new Date(ts).toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});d.appendChild(t);}
  liveMsgs.appendChild(d);liveMsgs.scrollTop=liveMsgs.scrollHeight;
}

function liveLoadMsgs(msgs){(msgs||[]).forEach(function(m){var id=parseInt(m.id)||0;if(id>0&&liveRenderedIds[id])return;if(id>0)liveRenderedIds[id]=true;if(id>liveLastId)liveLastId=id;addLiveMsg(m.sender_type,m.message,m.created_at,m.file_url,m.file_name,m.sender_name||null);});}

function liveDoUpload(file,cb){var fd=new FormData();fd.append('arquivo',file);fd.append('_csrf',CSRF);fetch('/chat/upload',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){if(!d.ok){liveStatus.textContent=d.erro||'Erro no upload';return;}cb(d.file_url,d.file_name);}).catch(function(){liveStatus.textContent='Erro ao enviar arquivo.';});}

function liveSendPayload(text,fu,fn){
  if(liveMode==='ws'&&liveWs&&liveWs.readyState===1){var p={message:text||''};if(fu){p.file_url=fu;p.file_name=fn;}liveWs.send(JSON.stringify(p));}
  else{var body='_csrf='+encodeURIComponent(CSRF)+'&message='+encodeURIComponent(text||'');if(fu)body+='&file_url='+encodeURIComponent(fu)+'&file_name='+encodeURIComponent(fn||'');fetch('/cliente/chat/enviar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body}).then(function(r){return r.json();}).then(function(d){if(d.ok&&d.msg){var id=parseInt(d.msg.id)||0;if(id>liveLastId)liveLastId=id;addLiveMsg(d.msg.sender_type,d.msg.message,d.msg.created_at,d.msg.file_url,d.msg.file_name,d.msg.sender_name||null);}}).catch(function(){});}
}

function tryWsLive(ctx){
  liveStatus.textContent='Conectando...';liveStatus.style.color='#64748b';liveInp.disabled=true;liveSend.disabled=true;
  fetch('/cliente/chat/token',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf='+encodeURIComponent(CSRF)}).then(function(r){return r.json();}).then(function(d){
    if(!d.ok){liveWsFails++;liveCheckFallback(ctx);return;}
    if(d.room_id)liveRoomId=d.room_id;
    liveWs=new WebSocket(WS_URL+'/?token='+encodeURIComponent(d.token));
    liveWs.onopen=function(){liveMode='ws';liveWsFails=0;liveStatus.textContent='● Online';liveStatus.style.color='#22c55e';liveInp.disabled=false;liveSend.disabled=false;liveInp.focus();if(livePollTimer){clearInterval(livePollTimer);livePollTimer=null;}if(ctx)liveWs.send(JSON.stringify({message:ctx}));};
    liveWs.onmessage=function(e){try{var m=JSON.parse(e.data);if(m.type==='history'){liveMsgs.innerHTML='';liveLastId=0;liveLastDate='';liveRenderedIds={};liveLastSender='';liveLoadMsgs(m.messages);}else if(m.type==='message'||m.type==='system'){var id=parseInt(m.id)||0;if(id>liveLastId)liveLastId=id;addLiveMsg(m.sender_type||'system',m.message,m.created_at,m.file_url,m.file_name,m.sender_name||null);}}catch(err){}};
    liveWs.onclose=function(){liveWsFails++;liveCheckFallback('');};
    liveWs.onerror=function(){try{liveWs.close();}catch(x){}};
  }).catch(function(){liveWsFails++;liveCheckFallback(ctx);});
}

function liveCheckFallback(ctx){if(liveWsFails>=2)startLivePolling();else setTimeout(function(){tryWsLive(ctx);},3000);}
function startLivePolling(){liveMode='poll';liveStatus.textContent='● Online';liveStatus.style.color='#22c55e';liveInp.disabled=false;liveSend.disabled=false;liveInp.focus();doLivePoll();if(!livePollTimer)livePollTimer=setInterval(doLivePoll,3000);}
function doLivePoll(){fetch('/cliente/chat/poll?after='+liveLastId).then(function(r){return r.json();}).then(function(d){if(!d.ok)return;if(d.room_id)liveRoomId=d.room_id;if(d.status==='closed'){liveLoadMsgs(d.messages);liveStatus.textContent='● Chat encerrado';liveStatus.style.color='#ef4444';liveInp.disabled=true;liveSend.disabled=true;if(livePollTimer){clearInterval(livePollTimer);livePollTimer=null;}return;}liveLoadMsgs(d.messages);}).catch(function(){});}

function connectLive(ctx){
  if(!LOGADO)return;
  liveMsgs.innerHTML='';liveLastId=0;liveLastDate='';liveRenderedIds={};liveLastSender='';
  fetch('/cliente/chat/historico').then(function(r){return r.json();}).then(function(d){
    if(d.ok&&d.messages&&d.messages.length>0){liveLoadMsgs(d.messages);if(d.room_id)liveRoomId=d.room_id;}
    if(d.ok&&d.status==='closed'&&d.messages&&d.messages.length>0){
      liveStatus.textContent='● Chat encerrado';liveStatus.style.color='#ef4444';liveInp.disabled=true;liveSend.disabled=true;
      setTimeout(function(){liveStatus.innerHTML='<a href="#" id="cw-new-chat" style="color:#4F46E5;font-size:12px;">Iniciar novo chat</a>';var nc=document.getElementById('cw-new-chat');if(nc)nc.addEventListener('click',function(ev){ev.preventDefault();liveMsgs.innerHTML='';liveLastId=0;liveLastDate='';liveRenderedIds={};liveLastSender='';liveRoomId=0;liveStatus.textContent='';liveInp.disabled=false;liveSend.disabled=false;tryWsLive(ctx);});},5000);
      return;
    }
    tryWsLive(ctx);
  }).catch(function(){tryWsLive(ctx);});
}

function sendLive(){var t=liveInp.value.trim();if(!t&&!livePendingFile)return;if(livePendingFile){var f=livePendingFile,txt=t;livePendingFile=null;liveFileInput.value='';liveUpPreview.style.display='none';liveInp.value='';liveInp.style.height='38px';liveDoUpload(f,function(fu,fn){liveSendPayload(txt,fu,fn);});return;}liveSendPayload(t,null,null);liveInp.value='';liveInp.style.height='38px';liveInp.focus();}
liveSend.addEventListener('click',sendLive);
liveInp.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendLive();}});
liveInp.addEventListener('input',function(){this.style.height='38px';this.style.height=Math.min(this.scrollHeight,90)+'px';});
})();
</script>
