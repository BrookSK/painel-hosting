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
      <?php endif; ?>
      <span class="pn-brand-name"><?php echo View::e($_pn_nome); ?></span>
    </a>

    <div class="pn-links">
      <a href="/"><?php echo View::e(I18n::t('nav.inicio')); ?></a>
      <a href="/infraestrutura"><?php echo View::e(I18n::t('nav.infraestrutura')); ?></a>
      <a href="/infraestrutura#planos"><?php echo View::e(I18n::t('nav.planos')); ?></a>
      <a href="/status"><?php echo View::e(I18n::t('nav.status')); ?></a>
      <div class="pn-dropdown">
        <button class="pn-dropdown-trigger" type="button"><?php echo View::e(I18n::t('nav.recursos')); ?> <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2.5 4L5 6.5 7.5 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <div class="pn-dropdown-menu">
          <a href="/changelog"><span>📋</span> <?php echo View::e(I18n::t('nav.changelog')); ?></a>
          <a href="/contato"><span>✉️</span> <?php echo View::e(I18n::t('nav.contato')); ?></a>
          <div class="pn-dropdown-sep"></div>
          <a href="/termos"><span>📄</span> <?php echo View::e(I18n::t('nav.termos')); ?></a>
          <a href="/privacidade"><span>🔒</span> <?php echo View::e(I18n::t('nav.privacidade')); ?></a>
          <a href="/licenca"><span>⚖️</span> <?php echo View::e(I18n::t('nav.licenca')); ?></a>
        </div>
      </div>
    </div>

    <div class="pn-actions">
      <?php require __DIR__ . '/idioma.php'; ?>
      <a href="/cliente/entrar" class="pn-btn ghost"><?php echo View::e(I18n::t('nav.entrar')); ?></a>
      <a href="/cliente/criar-conta" class="pn-btn solid"><?php echo $_pn_trial ? View::e($_pn_trial_label) : View::e(I18n::t('nav.contratar')); ?></a>
      <button class="pn-hamburger" id="pnHamburger" type="button" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- DRAWER MOBILE -->
<div class="pn-drawer" id="pnDrawer">
  <a href="/"><?php echo View::e(I18n::t('nav.inicio')); ?></a>
  <a href="/infraestrutura"><?php echo View::e(I18n::t('nav.infraestrutura')); ?></a>
  <a href="/infraestrutura#planos"><?php echo View::e(I18n::t('nav.planos')); ?></a>
  <a href="/status"><?php echo View::e(I18n::t('nav.status')); ?></a>
  <a href="/changelog"><?php echo View::e(I18n::t('nav.changelog')); ?></a>
  <a href="/contato"><?php echo View::e(I18n::t('nav.contato')); ?></a>
  <div class="pn-drawer-sep"></div>
  <a href="/termos"><?php echo View::e(I18n::t('nav.termos')); ?></a>
  <a href="/privacidade"><?php echo View::e(I18n::t('nav.privacidade')); ?></a>
  <a href="/licenca"><?php echo View::e(I18n::t('nav.licenca')); ?></a>
  <div class="pn-drawer-lang">
    <?php require __DIR__ . '/idioma.php'; ?>
  </div>
  <div class="pn-drawer-actions">
    <a href="/cliente/entrar" class="pn-btn ghost"><?php echo View::e(I18n::t('nav.entrar')); ?></a>
    <a href="/cliente/criar-conta" class="pn-btn solid"><?php echo $_pn_trial ? View::e($_pn_trial_label) : View::e(I18n::t('nav.contratar')); ?></a>
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
</style>
<style>
/* ── Dropdown ── */
.pn-dropdown{position:relative}
.pn-dropdown-trigger{display:inline-flex;align-items:center;gap:4px;color:rgba(255,255,255,.72);font-size:13.5px;font-weight:500;padding:6px 12px;border-radius:8px;background:none;border:none;cursor:pointer;transition:color .15s,background .15s}
.pn-dropdown-trigger:hover{color:#fff;background:rgba(255,255,255,.1)}
.pn-dropdown-trigger svg{transition:transform .2s}
.pn-dropdown:hover .pn-dropdown-trigger svg{transform:rotate(180deg)}
.pn-dropdown-menu{display:none;position:absolute;top:calc(100% + 6px);left:50%;transform:translateX(-50%);min-width:200px;background:#0f172a;border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:8px;box-shadow:0 16px 48px rgba(0,0,0,.4);z-index:200}
.pn-dropdown:hover .pn-dropdown-menu{display:block}
.pn-dropdown-menu a{display:flex;align-items:center;gap:8px;padding:9px 12px;color:rgba(255,255,255,.7);font-size:13px;font-weight:500;border-radius:8px;text-decoration:none;transition:background .15s,color .15s}
.pn-dropdown-menu a:hover{background:rgba(255,255,255,.08);color:#fff}
.pn-dropdown-menu a span{font-size:15px;width:20px;text-align:center;flex-shrink:0}
.pn-dropdown-sep{height:1px;background:rgba(255,255,255,.08);margin:6px 4px}

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
.pn-drawer{display:none;position:fixed;top:60px;left:0;right:0;bottom:0;background:rgba(6,13,31,.97);z-index:99;padding:24px 20px;flex-direction:column;gap:4px;overflow-y:auto;-webkit-overflow-scrolling:touch}
.pn-drawer.open{display:flex}
.pn-drawer>a{color:rgba(255,255,255,.8);font-size:16px;font-weight:500;padding:14px 16px;border-radius:10px;border-bottom:1px solid rgba(255,255,255,.06);transition:background .15s,color .15s;text-decoration:none}
.pn-drawer>a:hover{background:rgba(255,255,255,.08);color:#fff}
.pn-drawer-sep{height:1px;background:rgba(255,255,255,.08);margin:8px 0}
.pn-drawer-lang{padding:12px 0;display:flex;justify-content:center}
.pn-drawer-lang .lang-dropdown{display:inline-block}
.pn-drawer-actions{display:flex;flex-direction:column;gap:10px;margin-top:12px;padding-top:20px;border-top:1px solid rgba(255,255,255,.1)}
.pn-drawer-actions .pn-btn{text-align:center;justify-content:center;font-weight:700;padding:14px 16px;border-radius:10px}

/* ── Responsive ── */
@media(max-width:1024px){
  .pn-links>a{padding:6px 8px;font-size:12.5px}
  .pn-dropdown-trigger{padding:6px 8px;font-size:12.5px}
}
@media(max-width:768px){
  .pn-links{display:none}
  .pn-actions>.pn-btn.ghost{display:none}
  .pn-actions>.pn-btn.solid{display:none}
  .pn-hamburger{display:flex}
  .pn-actions>.lang-dropdown{display:none}
}
</style>

<script>
(function(){
  var ham=document.getElementById('pnHamburger'),dr=document.getElementById('pnDrawer');
  if(!ham||!dr)return;
  ham.addEventListener('click',function(){
    var open=dr.classList.toggle('open');
    ham.classList.toggle('open',open);
    document.body.style.overflow=open?'hidden':'';
  });
  dr.querySelectorAll('a').forEach(function(a){
    a.addEventListener('click',function(){
      dr.classList.remove('open');ham.classList.remove('open');document.body.style.overflow='';
    });
  });
})();
</script>
