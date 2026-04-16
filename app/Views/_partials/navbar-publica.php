<?php
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\I18n;
use LRV\Core\View;
$_pn_nome = SistemaConfig::nome();
$_pn_logo = SistemaConfig::logoUrl();
$_pn_trial = !empty($trial_ativo ?? false);
$_pn_trial_label = (string)($trial_label ?? I18n::t('nav.contratar'));
?>
<nav class="pn" id="pnNav">
  <div class="pn-inner">
    <a href="/" class="pn-brand">
      <?php if ($_pn_logo !== ''): ?>
        <img src="<?php echo View::e($_pn_logo); ?>" alt="<?php echo View::e($_pn_nome); ?>"/>
      <?php else: ?>
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect width="28" height="28" rx="8" fill="#4F46E5"/><path d="M7 14h14M14 7v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
        <span class="pn-brand-name"><?php echo View::e($_pn_nome); ?></span>
      <?php endif; ?>
    </a>

    <div class="pn-links">
      <a href="/"><?php echo View::e(I18n::t('nav.inicio')); ?></a>

      <!-- MEGA: Produtos -->
      <div class="pn-mega-trigger" data-mega="produtos">
        <button type="button"><?php echo View::e(I18n::t('nav.produtos')); ?> <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2.5 4L5 6.5 7.5 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      </div>

      <a href="/infraestrutura"><?php echo View::e(I18n::t('nav.infraestrutura')); ?></a>

      <!-- MEGA: Recursos -->
      <div class="pn-mega-trigger" data-mega="recursos">
        <button type="button"><?php echo View::e(I18n::t('nav.recursos')); ?> <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2.5 4L5 6.5 7.5 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      </div>

      <a href="/#planos"><?php echo View::e(I18n::t('nav.planos')); ?></a>
      <a href="/status"><?php echo View::e(I18n::t('nav.status')); ?></a>
      <a href="/contato"><?php echo View::e(I18n::t('nav.contato')); ?></a>
    </div>

    <div class="pn-actions">
      <?php require __DIR__ . '/idioma.php'; ?>
      <a href="/cliente/entrar" class="pn-btn ghost"><?php echo View::e(I18n::t('nav.entrar')); ?></a>
      <a href="/#planos" class="pn-btn solid"><?php echo $_pn_trial ? View::e($_pn_trial_label) : View::e(I18n::t('nav.contratar')); ?></a>
      <button class="pn-hamburger" id="pnHamburger" type="button" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- ══ MEGA MENU: PRODUTOS ══ -->
<div class="pn-mega" id="megaProdutos">
  <div class="pn-mega-inner">
    <div class="pn-mega-col">
      <h4>🏗️ <?php echo View::e(I18n::t('mega.prod_infra')); ?></h4>
      <a href="/solucoes/vps"><strong><?php echo View::e(I18n::t('mega.prod_vps')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_vps_desc')); ?></span></a>
      <a href="/infraestrutura#planos"><strong><?php echo View::e(I18n::t('mega.prod_enterprise')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_enterprise_desc')); ?></span></a>
      <a href="/infraestrutura"><strong><?php echo View::e(I18n::t('mega.prod_dedicado')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_dedicado_desc')); ?></span></a>
    </div>
    <div class="pn-mega-col">
      <h4>📦 <?php echo View::e(I18n::t('mega.prod_apps')); ?></h4>
      <a href="/solucoes/wordpress"><strong><?php echo View::e(I18n::t('mega.prod_wordpress')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_wordpress_desc')); ?></span></a>
      <a href="/solucoes/aplicacoes"><strong><?php echo View::e(I18n::t('mega.prod_nodejs')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_nodejs_desc')); ?></span></a>
      <a href="/solucoes/webhosting"><strong>Web Hosting</strong><span>Hospedagem com catálogo de apps e git deploy</span></a>
      <a href="/solucoes/aplicacoes"><strong><?php echo View::e(I18n::t('mega.prod_php')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_php_desc')); ?></span></a>
      <a href="/solucoes/aplicacoes"><strong><?php echo View::e(I18n::t('mega.prod_python')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_python_desc')); ?></span></a>
      <a href="/solucoes/cpp"><strong>C/C++ App</strong><span>Aplicações compiladas de alta performance</span></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.prod_deploy')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_deploy_desc')); ?></span></a>
    </div>
    <div class="pn-mega-col">
      <h4>⚙️ <?php echo View::e(I18n::t('mega.prod_devops')); ?></h4>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.prod_terminal')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_terminal_desc')); ?></span></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.prod_monitor')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_monitor_desc')); ?></span></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.prod_backups')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_backups_desc')); ?></span></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.prod_logs')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_logs_desc')); ?></span></a>
    </div>
    <div class="pn-mega-col">
      <h4>💬 <?php echo View::e(I18n::t('mega.prod_comm')); ?></h4>
      <a href="/solucoes/email"><strong><?php echo View::e(I18n::t('mega.prod_email')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_email_desc')); ?></span></a>
      <a href="/solucoes/email"><strong><?php echo View::e(I18n::t('mega.prod_chat')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_chat_desc')); ?></span></a>
      <a href="/contato"><strong><?php echo View::e(I18n::t('mega.prod_tickets')); ?></strong><span><?php echo View::e(I18n::t('mega.prod_tickets_desc')); ?></span></a>
    </div>
  </div>
  <div class="pn-mega-footer">
    <a href="/infraestrutura"><?php echo View::e(I18n::t('mega.prod_ver_todos')); ?> →</a>
  </div>
</div>

<!-- ══ MEGA MENU: RECURSOS ══ -->
<div class="pn-mega pn-mega--recursos" id="megaRecursos">
  <div class="pn-mega-inner">
    <div class="pn-mega-col">
      <h4>🚀 <?php echo View::e(I18n::t('mega.rec_perf')); ?></h4>
      <a href="/solucoes/vps"><strong><?php echo View::e(I18n::t('mega.rec_alta_disp')); ?></strong></a>
      <a href="/solucoes/vps"><strong><?php echo View::e(I18n::t('mega.rec_balanceamento')); ?></strong></a>
    </div>
    <div class="pn-mega-col">
      <h4>🔐 <?php echo View::e(I18n::t('mega.rec_seg')); ?></h4>
      <a href="/solucoes/seguranca"><strong><?php echo View::e(I18n::t('mega.rec_isolamento')); ?></strong></a>
      <a href="/solucoes/seguranca"><strong><?php echo View::e(I18n::t('mega.rec_ddos')); ?></strong></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.rec_backups')); ?></strong></a>
    </div>
    <div class="pn-mega-col">
      <h4>📊 <?php echo View::e(I18n::t('mega.rec_monit')); ?></h4>
      <a href="/status"><strong><?php echo View::e(I18n::t('mega.rec_status')); ?></strong></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.rec_metricas')); ?></strong></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.rec_alertas')); ?></strong></a>
    </div>
    <div class="pn-mega-col">
      <h4>🧑‍💻 <?php echo View::e(I18n::t('mega.rec_exp')); ?></h4>
      <a href="/solucoes/vps"><strong><?php echo View::e(I18n::t('mega.rec_painel')); ?></strong></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.rec_deploy')); ?></strong></a>
      <a href="/solucoes/devops"><strong><?php echo View::e(I18n::t('mega.rec_cli')); ?></strong></a>
    </div>
  </div>
</div>

<!-- ══ DRAWER MOBILE ══ -->
<div class="pn-drawer" id="pnDrawer">
  <a href="/"><?php echo View::e(I18n::t('nav.inicio')); ?></a>

  <!-- Accordion: Produtos -->
  <button class="pn-acc-toggle" type="button"><?php echo View::e(I18n::t('nav.produtos')); ?> <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
  <div class="pn-acc-panel">
    <span class="pn-acc-heading">🏗️ <?php echo View::e(I18n::t('mega.prod_infra')); ?></span>
    <a href="/solucoes/vps"><?php echo View::e(I18n::t('mega.prod_vps')); ?></a>
    <a href="/infraestrutura#planos"><?php echo View::e(I18n::t('mega.prod_enterprise')); ?></a>
    <a href="/infraestrutura"><?php echo View::e(I18n::t('mega.prod_dedicado')); ?></a>
    <span class="pn-acc-heading">📦 <?php echo View::e(I18n::t('mega.prod_apps')); ?></span>
    <a href="/solucoes/wordpress"><?php echo View::e(I18n::t('mega.prod_wordpress')); ?></a>
    <a href="/solucoes/aplicacoes"><?php echo View::e(I18n::t('mega.prod_nodejs')); ?></a>
    <a href="/solucoes/webhosting">Web Hosting</a>
    <a href="/solucoes/aplicacoes"><?php echo View::e(I18n::t('mega.prod_php')); ?></a>
    <a href="/solucoes/aplicacoes"><?php echo View::e(I18n::t('mega.prod_python')); ?></a>
    <a href="/solucoes/cpp">C/C++ App</a>
    <span class="pn-acc-heading">⚙️ <?php echo View::e(I18n::t('mega.prod_devops')); ?></span>
    <a href="/solucoes/devops"><?php echo View::e(I18n::t('mega.prod_terminal')); ?></a>
    <a href="/solucoes/devops"><?php echo View::e(I18n::t('mega.prod_monitor')); ?></a>
    <a href="/solucoes/devops"><?php echo View::e(I18n::t('mega.prod_backups')); ?></a>
    <span class="pn-acc-heading">💬 <?php echo View::e(I18n::t('mega.prod_comm')); ?></span>
    <a href="/solucoes/email"><?php echo View::e(I18n::t('mega.prod_email')); ?></a>
    <a href="/solucoes/email"><?php echo View::e(I18n::t('mega.prod_chat')); ?></a>
    <a href="/contato"><?php echo View::e(I18n::t('mega.prod_tickets')); ?></a>
  </div>

  <a href="/infraestrutura"><?php echo View::e(I18n::t('nav.infraestrutura')); ?></a>

  <!-- Accordion: Recursos -->
  <button class="pn-acc-toggle" type="button"><?php echo View::e(I18n::t('nav.recursos')); ?> <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
  <div class="pn-acc-panel">
    <span class="pn-acc-heading">🚀 <?php echo View::e(I18n::t('mega.rec_perf')); ?></span>
    <a href="/solucoes/vps"><?php echo View::e(I18n::t('mega.rec_alta_disp')); ?></a>
    <a href="/solucoes/vps"><?php echo View::e(I18n::t('mega.rec_balanceamento')); ?></a>
    <span class="pn-acc-heading">🔐 <?php echo View::e(I18n::t('mega.rec_seg')); ?></span>
    <a href="/solucoes/seguranca"><?php echo View::e(I18n::t('mega.rec_isolamento')); ?></a>
    <a href="/solucoes/seguranca"><?php echo View::e(I18n::t('mega.rec_ddos')); ?></a>
    <span class="pn-acc-heading">📊 <?php echo View::e(I18n::t('mega.rec_monit')); ?></span>
    <a href="/status"><?php echo View::e(I18n::t('mega.rec_status')); ?></a>
    <a href="/solucoes/devops"><?php echo View::e(I18n::t('mega.rec_metricas')); ?></a>
    <span class="pn-acc-heading">🧑‍💻 <?php echo View::e(I18n::t('mega.rec_exp')); ?></span>
    <a href="/solucoes/vps"><?php echo View::e(I18n::t('mega.rec_painel')); ?></a>
    <a href="/solucoes/devops"><?php echo View::e(I18n::t('mega.rec_deploy')); ?></a>
    <a href="/solucoes/devops"><?php echo View::e(I18n::t('mega.rec_cli')); ?></a>
  </div>

  <a href="/#planos"><?php echo View::e(I18n::t('nav.planos')); ?></a>
  <a href="/status"><?php echo View::e(I18n::t('nav.status')); ?></a>
  <a href="/contato"><?php echo View::e(I18n::t('nav.contato')); ?></a>
  <div class="pn-drawer-sep"></div>
  <a href="/changelog"><?php echo View::e(I18n::t('nav.changelog')); ?></a>
  <a href="/termos"><?php echo View::e(I18n::t('nav.termos')); ?></a>
  <a href="/privacidade"><?php echo View::e(I18n::t('nav.privacidade')); ?></a>
  <a href="/licenca"><?php echo View::e(I18n::t('nav.licenca')); ?></a>
  <div class="pn-drawer-lang">
    <?php require __DIR__ . '/idioma.php'; ?>
  </div>
  <div class="pn-drawer-actions">
    <a href="/cliente/entrar" class="pn-btn ghost"><?php echo View::e(I18n::t('nav.entrar')); ?></a>
    <a href="/#planos" class="pn-btn solid"><?php echo $_pn_trial ? View::e($_pn_trial_label) : View::e(I18n::t('nav.contratar')); ?></a>
  </div>
</div>

<style>
/* ── Public Navbar ── */
.pn{position:sticky;top:0;z-index:100;background:rgba(11,28,61,.94);backdrop-filter:blur(18px) saturate(180%);-webkit-backdrop-filter:blur(18px) saturate(180%);border-bottom:1px solid rgba(255,255,255,.08);padding:0 20px}
.pn-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:60px;gap:12px}
.pn-brand{display:flex;align-items:center;gap:9px;text-decoration:none;color:#fff;flex-shrink:0}
.pn-brand img{height:28px;width:auto}
.pn-brand-name{font-size:15px;font-weight:700;letter-spacing:-.01em;white-space:nowrap}
.pn-links{display:flex;align-items:center;gap:2px;flex:1;justify-content:center}
.pn-links>a{color:rgba(255,255,255,.72);text-decoration:none;font-size:13.5px;font-weight:500;padding:6px 12px;border-radius:8px;transition:color .15s,background .15s}
.pn-links>a:hover{color:#fff;background:rgba(255,255,255,.1)}

/* ── Mega trigger (desktop) ── */
.pn-mega-trigger{position:relative;display:inline-flex}
.pn-mega-trigger button{display:inline-flex;align-items:center;gap:4px;color:rgba(255,255,255,.72);font-size:13.5px;font-weight:500;padding:6px 12px;border-radius:8px;background:none;border:none;cursor:pointer;transition:color .15s,background .15s;font-family:inherit}
.pn-mega-trigger button:hover,.pn-mega-trigger.active button{color:#fff;background:rgba(255,255,255,.1)}
.pn-mega-trigger button svg{transition:transform .2s}
.pn-mega-trigger.active button svg{transform:rotate(180deg)}

/* ── Mega panel (desktop) ── */
.pn-mega{display:none;position:fixed;top:60px;left:0;right:0;z-index:98;background:#0b1c3d;border-bottom:1px solid rgba(255,255,255,.08);box-shadow:0 20px 60px rgba(0,0,0,.5);animation:megaFadeIn .2s ease}
.pn-mega.open{display:block}
@keyframes megaFadeIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.pn-mega-inner{max-width:1160px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:0;padding:32px 20px 24px}
.pn-mega-col{padding:0 20px;border-right:1px solid rgba(255,255,255,.06)}
.pn-mega-col:last-child{border-right:none}
.pn-mega-col h4{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin:0 0 14px;padding:0 8px}
.pn-mega-col a{display:block;padding:8px;border-radius:8px;text-decoration:none;transition:background .15s;margin-bottom:2px}
.pn-mega-col a:hover{background:rgba(255,255,255,.07)}
.pn-mega-col a strong{display:block;color:#fff;font-size:13.5px;font-weight:600;line-height:1.3}
.pn-mega-col a span{display:block;color:rgba(255,255,255,.45);font-size:12px;font-weight:400;margin-top:2px;line-height:1.35}
.pn-mega-footer{max-width:1160px;margin:0 auto;padding:0 20px 20px;border-top:1px solid rgba(255,255,255,.06);padding-top:14px}
.pn-mega-footer a{color:#818cf8;font-size:13px;font-weight:600;text-decoration:none;transition:color .15s}
.pn-mega-footer a:hover{color:#a5b4fc}

/* ── Actions ── */
.pn-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
.pn-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:opacity .15s,transform .1s;white-space:nowrap}
.pn-btn:hover{opacity:.88;transform:translateY(-1px)}
.pn-btn.ghost{color:rgba(255,255,255,.85);border:1.5px solid rgba(255,255,255,.2)}
.pn-btn.solid{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff}

/* ── Hamburger ── */
.pn-hamburger{display:none;flex-direction:column;justify-content:center;gap:5px;width:36px;height:36px;background:none;border:none;cursor:pointer;padding:4px;flex-shrink:0}
.pn-hamburger span{display:block;height:2px;background:#fff;border-radius:2px;transition:transform .25s,opacity .25s}
.pn-hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.pn-hamburger.open span:nth-child(2){opacity:0}
.pn-hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}

/* ── Drawer ── */
.pn-drawer{display:none;position:fixed;top:60px;left:0;right:0;bottom:0;background:rgba(6,13,31,.97);z-index:99;padding:24px 20px;flex-direction:column;gap:0;overflow-y:auto;-webkit-overflow-scrolling:touch}
.pn-drawer.open{display:flex}
.pn-drawer>a{color:rgba(255,255,255,.8);font-size:16px;font-weight:500;padding:14px 16px;border-radius:10px;border-bottom:1px solid rgba(255,255,255,.06);transition:background .15s,color .15s;text-decoration:none}
.pn-drawer>a:hover{background:rgba(255,255,255,.08);color:#fff}
.pn-drawer-sep{height:1px;background:rgba(255,255,255,.08);margin:8px 0}
.pn-drawer-lang{padding:12px 0;display:flex;justify-content:center}
.pn-drawer-lang .lang-dropdown{display:inline-block}
.pn-drawer-actions{display:flex;flex-direction:column;gap:10px;margin-top:12px;padding-top:20px;border-top:1px solid rgba(255,255,255,.1)}
.pn-drawer-actions .pn-btn{text-align:center;justify-content:center;font-weight:700;padding:14px 16px;border-radius:10px}

/* ── Accordion (mobile drawer) ── */
.pn-acc-toggle{display:flex;align-items:center;justify-content:space-between;width:100%;color:rgba(255,255,255,.8);font-size:16px;font-weight:500;padding:14px 16px;border:none;border-bottom:1px solid rgba(255,255,255,.06);background:none;cursor:pointer;font-family:inherit;text-align:left}
.pn-acc-toggle svg{transition:transform .25s;flex-shrink:0}
.pn-acc-toggle.open svg{transform:rotate(180deg)}
.pn-acc-panel{display:none;padding:4px 0 8px 12px}
.pn-acc-panel.open{display:block}
.pn-acc-heading{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);padding:10px 16px 4px;margin-top:4px}
.pn-acc-panel a{display:block;color:rgba(255,255,255,.7);font-size:14.5px;font-weight:500;padding:10px 16px;border-radius:8px;text-decoration:none;transition:background .15s,color .15s}
.pn-acc-panel a:hover{background:rgba(255,255,255,.08);color:#fff}

/* ── Responsive ── */
@media(max-width:1024px){
  .pn-links>a{padding:6px 8px;font-size:12.5px}
  .pn-mega-trigger button{padding:6px 8px;font-size:12.5px}
  .pn-mega-inner{grid-template-columns:repeat(2,1fr);gap:20px 0}
  .pn-mega-col:nth-child(2){border-right:none}
}
@media(max-width:768px){
  .pn-links{display:none}
  .pn-mega{display:none!important}
  .pn-actions>.pn-btn.ghost{display:none}
  .pn-actions>.pn-btn.solid{display:none}
  .pn-hamburger{display:flex}
  .pn-actions>.lang-dropdown{display:none}
}
</style>

<script>
(function(){
  /* ── Hamburger / Drawer ── */
  var ham=document.getElementById('pnHamburger'),dr=document.getElementById('pnDrawer');
  if(ham&&dr){
    ham.addEventListener('click',function(){
      var open=dr.classList.toggle('open');
      ham.classList.toggle('open',open);
      document.body.style.overflow=open?'hidden':'';
    });
    dr.querySelectorAll('a').forEach(function(a){
      a.addEventListener('click',function(){dr.classList.remove('open');ham.classList.remove('open');document.body.style.overflow='';});
    });
  }

  /* ── Mega menus (desktop hover) ── */
  var megaMap={'produtos':'megaProdutos','recursos':'megaRecursos'};
  var triggers=document.querySelectorAll('.pn-mega-trigger');
  var closeTimer=null;

  function closeAll(){
    triggers.forEach(function(t){t.classList.remove('active')});
    Object.values(megaMap).forEach(function(id){
      var el=document.getElementById(id);if(el)el.classList.remove('open');
    });
  }

  function openMega(key){
    if(closeTimer){clearTimeout(closeTimer);closeTimer=null;}
    closeAll();
    var panel=document.getElementById(megaMap[key]);
    if(!panel)return;
    panel.classList.add('open');
    var trig=document.querySelector('[data-mega="'+key+'"]');
    if(trig)trig.classList.add('active');
  }

  triggers.forEach(function(trig){
    var key=trig.getAttribute('data-mega');
    trig.addEventListener('mouseenter',function(){openMega(key);});
    trig.addEventListener('mouseleave',function(){
      closeTimer=setTimeout(closeAll,250);
    });
  });

  Object.values(megaMap).forEach(function(id){
    var panel=document.getElementById(id);
    if(!panel)return;
    panel.addEventListener('mouseenter',function(){if(closeTimer){clearTimeout(closeTimer);closeTimer=null;}});
    panel.addEventListener('mouseleave',function(){closeTimer=setTimeout(closeAll,250);});
  });

  /* close mega on click outside */
  document.addEventListener('click',function(e){
    var inTrigger=e.target.closest('.pn-mega-trigger');
    var inPanel=e.target.closest('.pn-mega');
    if(!inTrigger&&!inPanel)closeAll();
  });

  /* ── Accordion (mobile) ── */
  document.querySelectorAll('.pn-acc-toggle').forEach(function(btn){
    btn.addEventListener('click',function(){
      var panel=btn.nextElementSibling;
      var isOpen=panel.classList.toggle('open');
      btn.classList.toggle('open',isOpen);
    });
  });
})();
</script>
