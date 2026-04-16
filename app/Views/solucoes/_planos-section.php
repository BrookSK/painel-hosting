<?php
/**
 * Seção de planos com carousel, addons e card sob medida.
 * Variáveis: $planos (array), $_accent (cor hex), $_plan_type (string)
 */
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$_planos = is_array($planos ?? null) ? $planos : [];
$_accent = $_accent ?? '#4F46E5';
$_plan_type = $_plan_type ?? 'vps';
$_emailAdmin = '';
try { $_emailAdmin = (string)\LRV\Core\ConfiguracoesSistema::emailAdmin(); } catch (\Throwable) {}
$_uniqId = 'ps' . substr(md5($_plan_type), 0, 6);
?>
<section class="lps-section" id="planos">
  <div class="lps-inner">
    <div style="text-align:center;margin-bottom:40px;">
      <div class="lps-label" style="color:<?php echo $_accent; ?>;">Planos</div>
      <h2 class="lps-title">Escolha o plano ideal para o seu projeto</h2>
      <p class="lps-sub">Todos os planos incluem SSL grátis, suporte técnico e painel de controle.</p>
    </div>

    <div class="lps-carousel" id="<?php echo $_uniqId; ?>-carousel">
      <div class="lps-track" id="<?php echo $_uniqId; ?>-track">
        <?php foreach ($_planos as $_p):
          $_pid = (int)($_p['id'] ?? 0);
          $_pName = (string)($_p['name'] ?? '');
          $_pDesc = (string)($_p['description'] ?? '');
          $_pPrice = (float)($_p['price_monthly'] ?? 0);
          $_pPriceUsd = (float)($_p['price_monthly_usd'] ?? 0);
          $_pCur = (string)($_p['currency'] ?? 'BRL');
          $_pCpu = (int)($_p['cpu'] ?? 0);
          $_pRam = round((int)($_p['ram'] ?? 0) / 1024);
          $_pDisco = round((int)($_p['storage'] ?? 0) / 1024);
          $_pFeatured = (int)($_p['is_featured'] ?? 0) === 1;
          $_pMaxSites = $_p['max_sites'] ?? null;
          $_pMaxDbs = $_p['max_databases'] ?? null;
          $_pAddons = is_array($_p['addons'] ?? null) ? $_p['addons'] : [];
          $_specs = json_decode((string)($_p['specs_json'] ?? ''), true) ?: [];
          $_displayPrice = $_pCur === 'USD' && $_pPriceUsd > 0 ? $_pPriceUsd : $_pPrice;
          $_displayCur = $_pCur === 'USD' ? 'US$' : 'R$';
          if ($_pPrice <= 0 && $_pPriceUsd <= 0) continue; // Pular plano sob consulta (vai no card custom)
        ?>
        <div class="lps-card<?php echo $_pFeatured ? ' featured' : ''; ?>" data-plan-id="<?php echo $_pid; ?>" data-base-price="<?php echo $_displayPrice; ?>">
          <?php if ($_pFeatured): ?><div class="lps-badge" style="background:<?php echo $_accent; ?>;">Popular</div><?php endif; ?>
          <div class="lps-name"><?php echo View::e($_pName); ?></div>
          <?php if ($_pDesc !== ''): ?><div class="lps-desc"><?php echo View::e($_pDesc); ?></div><?php endif; ?>
          <div class="lps-price">
            <span class="lps-cur"><?php echo $_displayCur; ?></span>
            <span class="lps-amount" id="<?php echo $_uniqId; ?>-price-<?php echo $_pid; ?>"><?php echo number_format($_displayPrice, 2, ',', '.'); ?></span>
            <span class="lps-period">/mês</span>
          </div>
          <ul class="lps-features">
            <li>✓ <?php echo $_pCpu; ?> vCPU dedicada</li>
            <li>✓ <?php echo $_pRam; ?> GB RAM</li>
            <li>✓ <?php echo $_pDisco; ?> GB SSD NVMe</li>
            <?php if ($_pMaxSites !== null): ?><li>✓ Até <?php echo (int)$_pMaxSites; ?> sites</li><?php endif; ?>
            <?php if ($_pMaxDbs !== null): ?><li>✓ Até <?php echo (int)$_pMaxDbs; ?> bancos</li><?php endif; ?>
            <?php if (!empty($_specs['bandwidth'])): ?><li>✓ <?php echo View::e((string)$_specs['bandwidth']); ?> banda</li><?php endif; ?>
            <li>✓ SSL grátis</li>
            <li>✓ Suporte técnico</li>
          </ul>
          <?php if (!empty($_pAddons)): ?>
          <div class="lps-addons">
            <div class="lps-addons-title">Serviços adicionais</div>
            <?php foreach ($_pAddons as $_a):
              $_aid = (int)($_a['id'] ?? 0);
              $_aprice = (float)($_a['price'] ?? 0);
              $_apriceUsd = (float)($_a['price_usd'] ?? 0);
              $_adisplay = $_pCur === 'USD' && $_apriceUsd > 0 ? $_apriceUsd : $_aprice;
            ?>
            <div class="lps-addon" data-addon-price="<?php echo $_adisplay; ?>" onclick="lpsToggleAddon(this,'<?php echo $_uniqId; ?>',<?php echo $_pid; ?>)">
              <div class="lps-addon-check">✓</div>
              <div class="lps-addon-info">
                <div class="lps-addon-name"><?php echo View::e((string)($_a['name'] ?? '')); ?></div>
                <?php if (!empty($_a['description'])): ?><div class="lps-addon-desc"><?php echo View::e((string)$_a['description']); ?></div><?php endif; ?>
              </div>
              <div class="lps-addon-price">+<?php echo $_displayCur; ?> <?php echo number_format($_adisplay, 2, ',', '.'); ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="lps-total" style="background:<?php echo $_accent; ?>;">
            <div class="lps-total-label">Total mensal</div>
            <div class="lps-total-value"><?php echo $_displayCur; ?> <span id="<?php echo $_uniqId; ?>-total-<?php echo $_pid; ?>"><?php echo number_format($_displayPrice, 2, ',', '.'); ?></span></div>
          </div>
          <?php endif; ?>
          <a href="/contratar?plan_id=<?php echo $_pid; ?>" class="lps-cta" style="background:<?php echo $_accent; ?>;">Contratar agora</a>
        </div>
        <?php endforeach; ?>

        <!-- Card Sob Medida -->
        <div class="lps-card custom-card" style="border-color:<?php echo $_accent; ?>;background:linear-gradient(180deg,#fff 0%,<?php echo $_accent; ?>08 100%);">
          <div class="lps-badge" style="background:linear-gradient(135deg,#0B1C3D,<?php echo $_accent; ?>);">PERSONALIZADO</div>
          <div class="lps-name">Plano Sob Medida</div>
          <div class="lps-desc">Precisa de mais recursos ou configuração especial? Montamos um plano exclusivo para o seu projeto.</div>
          <div class="lps-price" style="margin:16px 0;">
            <span style="font-size:16px;color:<?php echo $_accent; ?>;font-weight:700;">Sob consulta</span>
          </div>
          <ul class="lps-features">
            <li>✓ CPU, RAM e disco sob medida</li>
            <li>✓ Gerenciamento completo</li>
            <li>✓ Deploy e suporte dedicado</li>
            <li>✓ Ideal para empresas</li>
          </ul>
          <div style="margin-top:auto;display:flex;flex-direction:column;gap:8px;">
            <a href="https://wa.me/5517988093160?text=<?php echo urlencode('Olá, gostaria de um plano personalizado de ' . ucfirst($_plan_type)); ?>" target="_blank" class="lps-cta" style="background:#25D366;display:flex;align-items:center;justify-content:center;gap:8px;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.612.616l4.532-1.474A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.24 0-4.326-.724-6.022-1.95l-.422-.314-2.688.874.893-2.634-.346-.45A9.963 9.963 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg>
              WhatsApp Vendas
            </a>
            <?php if ($_emailAdmin !== ''): ?>
            <a href="mailto:<?php echo View::e($_emailAdmin); ?>?subject=<?php echo urlencode('Plano Personalizado — ' . ucfirst($_plan_type)); ?>" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border-radius:12px;font-size:13px;font-weight:600;color:<?php echo $_accent; ?>;border:2px solid #e2e8f0;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='<?php echo $_accent; ?>'" onmouseout="this.style.borderColor='#e2e8f0'">📧 Enviar e-mail</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <button class="lps-arrow lps-arrow-prev" onclick="lpsMove('<?php echo $_uniqId; ?>',-1)" aria-label="Anterior">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <button class="lps-arrow lps-arrow-next" onclick="lpsMove('<?php echo $_uniqId; ?>',1)" aria-label="Próximo">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
    </div>
    <div class="lps-dots" id="<?php echo $_uniqId; ?>-dots"></div>
  </div>
</section>

<style>
.lps-section{padding:80px 20px;background:#f8fafc}
.lps-inner{max-width:1200px;margin:0 auto}
.lps-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;margin-bottom:10px}
.lps-title{font-size:clamp(24px,3.5vw,36px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em}
.lps-sub{font-size:15px;color:#64748b;max-width:500px;margin:0 auto}

/* Carousel */
.lps-carousel{position:relative;padding:0 60px}
@media(max-width:640px){.lps-carousel{padding:0 12px}}
.lps-track{display:flex;gap:24px;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;padding:20px 4px;scrollbar-width:none}
.lps-track::-webkit-scrollbar{display:none}

/* Cards */
.lps-card{background:#fff;border:2px solid #e2e8f0;border-radius:18px;padding:32px 24px;min-width:320px;max-width:360px;flex:0 0 auto;scroll-snap-align:start;position:relative;transition:all .3s;display:flex;flex-direction:column}
@media(max-width:640px){.lps-card{min-width:280px;max-width:calc(100vw - 48px);padding:24px 18px}}
.lps-card:hover{transform:translateY(-6px);box-shadow:0 12px 40px rgba(0,0,0,.1)}
.lps-card.featured{border-color:<?php echo $_accent; ?>;box-shadow:0 8px 30px <?php echo $_accent; ?>22}
.lps-card.custom-card:hover{transform:translateY(-6px)}
.lps-badge{position:absolute;top:-12px;right:20px;color:#fff;padding:5px 14px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;z-index:2}
.lps-name{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:6px}
.lps-desc{font-size:13px;color:#64748b;margin-bottom:16px;line-height:1.5;min-height:40px}
.lps-price{margin-bottom:20px;padding-bottom:20px;border-bottom:2px solid #f1f5f9}
.lps-cur{font-size:18px;font-weight:700;color:#64748b;vertical-align:top}
.lps-amount{font-size:42px;font-weight:900;color:<?php echo $_accent; ?>;line-height:1}
.lps-period{font-size:14px;color:#94a3b8}
.lps-features{list-style:none;padding:0;margin:0 0 20px;flex:1}
.lps-features li{padding:7px 0;font-size:13px;color:#475569;border-bottom:1px solid #f8fafc}

/* Addons */
.lps-addons{border-top:2px solid #f1f5f9;padding-top:14px;margin-bottom:12px}
.lps-addons-title{font-size:13px;font-weight:700;color:#0f172a;margin-bottom:10px}
.lps-addon{display:flex;align-items:center;gap:10px;padding:10px;border:1.5px solid #e2e8f0;border-radius:10px;margin-bottom:8px;cursor:pointer;transition:all .15s;background:#fff}
.lps-addon:hover{border-color:<?php echo $_accent; ?>}
.lps-addon.selected{border-color:<?php echo $_accent; ?>;background:<?php echo $_accent; ?>0a}
.lps-addon-check{width:20px;height:20px;border:2px solid #cbd5e1;border-radius:5px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;color:transparent;transition:.15s}
.lps-addon.selected .lps-addon-check{background:<?php echo $_accent; ?>;border-color:<?php echo $_accent; ?>;color:#fff}
.lps-addon-info{flex:1;min-width:0}
.lps-addon-name{font-weight:700;font-size:13px;color:#0f172a}
.lps-addon-desc{font-size:11px;color:#64748b;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lps-addon-price{font-weight:700;font-size:13px;color:<?php echo $_accent; ?>;flex-shrink:0;white-space:nowrap}

/* Total */
.lps-total{color:#fff;padding:14px;border-radius:10px;margin-bottom:12px;text-align:center}
.lps-total-label{font-size:12px;opacity:.85;margin-bottom:4px}
.lps-total-value{font-size:28px;font-weight:900;line-height:1}

/* CTA */
.lps-cta{display:block;text-align:center;padding:14px;color:#fff;border-radius:12px;font-weight:700;font-size:14px;text-decoration:none;transition:opacity .15s,transform .1s;margin-top:auto}
.lps-cta:hover{opacity:.9;transform:translateY(-2px)}

/* Arrows */
.lps-arrow{position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;background:#fff;border:2px solid #e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;z-index:5;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.lps-arrow:hover{background:<?php echo $_accent; ?>;border-color:<?php echo $_accent; ?>}
.lps-arrow:hover svg{stroke:#fff}
.lps-arrow svg{stroke:<?php echo $_accent; ?>;transition:.2s}
.lps-arrow-prev{left:0}
.lps-arrow-next{right:0}
@media(max-width:640px){.lps-arrow{display:none}}

/* Dots */
.lps-dots{display:flex;justify-content:center;gap:8px;margin-top:24px}
.lps-dot{width:10px;height:10px;border-radius:50%;background:#e2e8f0;cursor:pointer;transition:all .2s}
.lps-dot.active{background:<?php echo $_accent; ?>;width:28px;border-radius:5px}
</style>

<script>
(function(){
  var uid='<?php echo $_uniqId; ?>';
  var track=document.getElementById(uid+'-track');
  if(!track)return;
  var cards=track.querySelectorAll('.lps-card');
  var dotsC=document.getElementById(uid+'-dots');
  var cardW=cards.length>0?cards[0].offsetWidth+24:344;
  var perView=Math.max(1,Math.floor(track.parentElement.offsetWidth/cardW));
  var maxIdx=Math.max(0,cards.length-perView);
  var curIdx=0;

  // Build dots
  if(dotsC&&cards.length>1){
    for(var i=0;i<=maxIdx;i++){
      var d=document.createElement('div');d.className='lps-dot'+(i===0?' active':'');
      d.setAttribute('data-i',i);
      d.addEventListener('click',function(){goTo(parseInt(this.getAttribute('data-i')));});
      dotsC.appendChild(d);
    }
  }

  function goTo(idx){
    curIdx=Math.max(0,Math.min(idx,maxIdx));
    track.scrollTo({left:curIdx*cardW,behavior:'smooth'});
    if(dotsC){dotsC.querySelectorAll('.lps-dot').forEach(function(d,i){d.className='lps-dot'+(i===curIdx?' active':'');});}
  }

  window['lpsMove_'+uid]=function(dir){goTo(curIdx+dir);};
  // Global alias
  window.lpsMove=function(id,dir){if(window['lpsMove_'+id])window['lpsMove_'+id](dir);};

  // Scroll sync dots
  var scrollTimer;
  track.addEventListener('scroll',function(){
    clearTimeout(scrollTimer);
    scrollTimer=setTimeout(function(){
      var newIdx=Math.round(track.scrollLeft/cardW);
      if(newIdx!==curIdx){curIdx=newIdx;if(dotsC){dotsC.querySelectorAll('.lps-dot').forEach(function(d,i){d.className='lps-dot'+(i===curIdx?' active':'');});}}
    },100);
  });
})();

// Toggle addon and recalculate total
function lpsToggleAddon(el,uid,planId){
  el.classList.toggle('selected');
  var card=el.closest('.lps-card');
  var base=parseFloat(card.getAttribute('data-base-price'))||0;
  var total=base;
  card.querySelectorAll('.lps-addon.selected').forEach(function(a){
    total+=parseFloat(a.getAttribute('data-addon-price'))||0;
  });
  var priceEl=document.getElementById(uid+'-price-'+planId);
  var totalEl=document.getElementById(uid+'-total-'+planId);
  var formatted=total.toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
  if(totalEl)totalEl.textContent=formatted;
  if(priceEl)priceEl.textContent=formatted;
}
</script>

<?php if (empty($_planos)): ?>
<!-- Sem planos cadastrados — CTA genérico -->
<section style="padding:60px 20px;background:#f8fafc;text-align:center;" id="planos">
  <div style="max-width:600px;margin:0 auto;">
    <h2 style="font-size:28px;font-weight:800;color:#0f172a;margin-bottom:12px;">Planos em breve</h2>
    <p style="font-size:15px;color:#64748b;margin-bottom:24px;">Estamos preparando planos especiais para este produto. Entre em contato para um plano personalizado.</p>
    <a href="/contato" style="display:inline-block;padding:14px 32px;background:<?php echo $_accent; ?>;color:#fff;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;">Falar com a equipe</a>
  </div>
</section>
<?php endif; ?>
