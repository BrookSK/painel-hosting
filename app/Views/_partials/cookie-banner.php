<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\Csrf;
?>
<!-- Cookie Consent Banner -->
<div id="ck-banner" class="ck-banner" style="display:none" role="dialog" aria-label="<?php echo View::e(I18n::t('cookies.titulo')); ?>">
  <div class="ck-banner-inner">
    <div class="ck-banner-text">
      <strong><?php echo View::e(I18n::t('cookies.titulo')); ?></strong>
      <p><?php echo View::e(I18n::t('cookies.descricao')); ?>
        <a href="/privacidade"><?php echo View::e(I18n::t('cookies.saiba_mais')); ?></a>
      </p>
    </div>
    <div class="ck-banner-actions">
      <button type="button" class="ck-btn ck-btn-primary" onclick="ckAceitarTodos()"><?php echo View::e(I18n::t('cookies.aceitar_todos')); ?></button>
      <button type="button" class="ck-btn ck-btn-outline" onclick="ckRejeitar()"><?php echo View::e(I18n::t('cookies.rejeitar_opcionais')); ?></button>
      <button type="button" class="ck-btn ck-btn-ghost" onclick="ckAbrirModal()"><?php echo View::e(I18n::t('cookies.configurar')); ?></button>
    </div>
  </div>
</div>

<!-- Cookie Settings Modal -->
<div id="ck-modal" class="ck-modal-overlay" style="display:none" role="dialog" aria-modal="true" aria-label="<?php echo View::e(I18n::t('cookies.config_titulo')); ?>" onclick="if(event.target===this)ckFecharModal()">
  <div class="ck-modal">
    <div class="ck-modal-header">
      <strong><?php echo View::e(I18n::t('cookies.config_titulo')); ?></strong>
      <button type="button" class="ck-modal-close" onclick="ckFecharModal()" aria-label="<?php echo View::e(I18n::t('geral.fechar')); ?>">&times;</button>
    </div>
    <div class="ck-modal-body">
      <p class="ck-modal-desc"><?php echo View::e(I18n::t('cookies.config_desc')); ?></p>
      <div class="ck-cat">
        <div class="ck-cat-row">
          <div>
            <strong><?php echo View::e(I18n::t('cookies.cat_necessarios')); ?></strong>
            <p><?php echo View::e(I18n::t('cookies.cat_necessarios_desc')); ?></p>
          </div>
          <label class="ck-switch ck-disabled"><input type="checkbox" checked disabled/><span class="ck-slider"></span></label>
        </div>
        <div class="ck-cat-row">
          <div>
            <strong><?php echo View::e(I18n::t('cookies.cat_analytics')); ?></strong>
            <p><?php echo View::e(I18n::t('cookies.cat_analytics_desc')); ?></p>
          </div>
          <label class="ck-switch"><input type="checkbox" id="ck-analytics"/><span class="ck-slider"></span></label>
        </div>
        <div class="ck-cat-row">
          <div>
            <strong><?php echo View::e(I18n::t('cookies.cat_marketing')); ?></strong>
            <p><?php echo View::e(I18n::t('cookies.cat_marketing_desc')); ?></p>
          </div>
          <label class="ck-switch"><input type="checkbox" id="ck-marketing"/><span class="ck-slider"></span></label>
        </div>
        <div class="ck-cat-row">
          <div>
            <strong><?php echo View::e(I18n::t('cookies.cat_preferencias')); ?></strong>
            <p><?php echo View::e(I18n::t('cookies.cat_preferencias_desc')); ?></p>
          </div>
          <label class="ck-switch"><input type="checkbox" id="ck-preferences"/><span class="ck-slider"></span></label>
        </div>
      </div>
    </div>
    <div class="ck-modal-footer">
      <button type="button" class="ck-btn ck-btn-primary" onclick="ckSalvarPrefs()"><?php echo View::e(I18n::t('cookies.salvar_prefs')); ?></button>
    </div>
  </div>
</div>

<style>
/* Banner */
.ck-banner{position:fixed;bottom:0;left:0;right:0;z-index:99999;background:rgba(6,13,31,.97);border-top:1px solid rgba(129,140,248,.15);backdrop-filter:blur(12px);padding:18px 24px;animation:ckSlideUp .3s ease}
@keyframes ckSlideUp{from{transform:translateY(100%);opacity:0}to{transform:translateY(0);opacity:1}}
.ck-banner-inner{max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.ck-banner-text{flex:1;min-width:280px}
.ck-banner-text strong{color:#fff;font-size:14px;display:block;margin-bottom:4px}
.ck-banner-text p{font-size:13px;color:rgba(255,255,255,.5);margin:0;line-height:1.5}
.ck-banner-text a{color:#818cf8;text-decoration:none}
.ck-banner-text a:hover{color:#a5b4fc}
.ck-banner-actions{display:flex;gap:10px;flex-wrap:wrap}
/* Buttons */
.ck-btn{padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:opacity .15s,transform .1s}
.ck-btn:hover{opacity:.9;transform:translateY(-1px)}
.ck-btn-primary{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff}
.ck-btn-outline{color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.15);background:none}
.ck-btn-ghost{color:rgba(255,255,255,.5);background:none;border:none;text-decoration:underline;padding:10px 12px}
</style>
<style>
/* Modal */
.ck-modal-overlay{position:fixed;inset:0;z-index:100000;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;padding:20px;animation:ckFadeIn .2s ease}
@keyframes ckFadeIn{from{opacity:0}to{opacity:1}}
.ck-modal{background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:16px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.ck-modal-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.07)}
.ck-modal-header strong{color:#fff;font-size:16px}
.ck-modal-close{background:none;border:none;color:rgba(255,255,255,.4);font-size:24px;cursor:pointer;padding:0 4px;line-height:1}
.ck-modal-close:hover{color:#fff}
.ck-modal-body{padding:20px 24px}
.ck-modal-desc{font-size:13px;color:rgba(255,255,255,.45);margin:0 0 20px;line-height:1.6}
.ck-cat{display:flex;flex-direction:column;gap:16px}
.ck-cat-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:10px}
.ck-cat-row strong{color:#fff;font-size:13px;display:block;margin-bottom:2px}
.ck-cat-row p{font-size:12px;color:rgba(255,255,255,.4);margin:0;line-height:1.5}
/* Toggle switch */
.ck-switch{position:relative;width:44px;height:24px;flex-shrink:0}
.ck-switch input{opacity:0;width:0;height:0}
.ck-slider{position:absolute;inset:0;background:rgba(255,255,255,.1);border-radius:24px;cursor:pointer;transition:background .2s}
.ck-slider::before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:transform .2s}
.ck-switch input:checked+.ck-slider{background:#4F46E5}
.ck-switch input:checked+.ck-slider::before{transform:translateX(20px)}
.ck-switch.ck-disabled .ck-slider{opacity:.5;cursor:not-allowed}
.ck-modal-footer{padding:16px 24px;border-top:1px solid rgba(255,255,255,.07);text-align:right}
@media(max-width:600px){
  .ck-banner-inner{flex-direction:column;text-align:center}
  .ck-banner-actions{justify-content:center}
}
</style>
<script>
(function(){
  var COOKIE='cookie_consent',CSRF='<?php echo Csrf::token(); ?>';
  function getCk(){try{var c=document.cookie.match('(^|;)\\s*'+COOKIE+'=([^;]*)');return c?JSON.parse(decodeURIComponent(c[2])):null}catch(e){return null}}
  function hasCk(){return getCk()!==null}
  function showBanner(){var b=document.getElementById('ck-banner');if(b)b.style.display=''}
  function hideBanner(){var b=document.getElementById('ck-banner');if(b)b.style.display='none'}
  function sendPrefs(p){
    var fd=new FormData();fd.append('_csrf',CSRF);
    fd.append('analytics',p.analytics?'1':'');
    fd.append('marketing',p.marketing?'1':'');
    fd.append('preferences',p.preferences?'1':'');
    fetch('/cookies/consent',{method:'POST',body:fd,credentials:'same-origin'}).catch(function(){});
    hideBanner();ckFecharModal();
    window.dispatchEvent(new CustomEvent('cookieConsentUpdated',{detail:p}));
  }
  window.ckAceitarTodos=function(){sendPrefs({necessary:true,analytics:true,marketing:true,preferences:true})};
  window.ckRejeitar=function(){sendPrefs({necessary:true,analytics:false,marketing:false,preferences:false})};
  window.ckAbrirModal=function(){
    var m=document.getElementById('ck-modal');if(!m)return;
    var c=getCk()||{};
    var a=document.getElementById('ck-analytics');if(a)a.checked=!!c.analytics;
    var mk=document.getElementById('ck-marketing');if(mk)mk.checked=!!c.marketing;
    var pr=document.getElementById('ck-preferences');if(pr)pr.checked=!!c.preferences;
    m.style.display='';
  };
  window.ckFecharModal=function(){var m=document.getElementById('ck-modal');if(m)m.style.display='none'};
  window.ckSalvarPrefs=function(){
    sendPrefs({
      necessary:true,
      analytics:!!document.getElementById('ck-analytics').checked,
      marketing:!!document.getElementById('ck-marketing').checked,
      preferences:!!document.getElementById('ck-preferences').checked
    });
  };
  window.ckTemPermissao=function(cat){if(cat==='necessary')return true;var c=getCk();return c?!!c[cat]:false};
  // Mostrar banner se não tem consentimento
  if(!hasCk()){document.addEventListener('DOMContentLoaded',function(){setTimeout(showBanner,800)})}
})();
</script>
