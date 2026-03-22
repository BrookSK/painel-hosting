<?php
/**
 * Layout de alta conversão para landing pages de soluções.
 * Variáveis: $sol_prefix, $sol_icon, $sol_cta_link
 */
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
$_p = $sol_prefix ?? 'vps';
$_icon = $sol_icon ?? '🖥️';
$_cta = $sol_cta_link ?? '/cliente/criar-conta';
?>
<!DOCTYPE html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo View::e(I18n::t("sol.{$_p}_titulo")); ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
html{overflow-x:hidden}
body{font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#060d1f;color:#fff;line-height:1.7}
a{text-decoration:none}a:hover{text-decoration:none}
.lp-wrap{max-width:1100px;margin:0 auto;padding:0 20px}

/* Hero */
.lp-hero{padding:100px 20px 70px;text-align:center;background:linear-gradient(180deg,#0b1c3d 0%,#0a1630 100%)}
.lp-hero-badge{display:inline-block;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(129,140,248,.15);color:#818cf8;margin-bottom:20px;letter-spacing:.03em}
.lp-hero h1{font-size:clamp(28px,5vw,46px);font-weight:800;letter-spacing:-.03em;margin-bottom:16px;line-height:1.15}
.lp-hero p{font-size:clamp(16px,2.5vw,20px);color:rgba(255,255,255,.55);max-width:620px;margin:0 auto 32px}
.lp-hero-actions{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- 1. HERO -->
<section class="lp-hero">
  <div class="lp-hero-badge"><?php echo $_icon; ?> <?php echo View::e(I18n::t("sol.{$_p}_titulo")); ?></div>
  <h1><?php echo View::e(I18n::t("sol.{$_p}_titulo")); ?></h1>
  <p><?php echo View::e(I18n::t("sol.{$_p}_subtitulo")); ?></p>
  <div class="lp-hero-actions">
    <a href="<?php echo View::e($_cta); ?>" class="lp-btn primary"><?php echo View::e(I18n::t("sol.{$_p}_cta")); ?></a>
    <a href="/#planos" class="lp-btn outline"><?php echo View::e(I18n::t('sol.cta_planos')); ?></a>
  </div>
</section>

<!-- 2. PROBLEMA -->
<section class="lp-section lp-problema">
  <div class="lp-wrap">
    <div class="lp-two-col">
      <div class="lp-col-icon">😩</div>
      <div>
        <h2><?php echo View::e(I18n::t("sol.{$_p}_problema_titulo")); ?></h2>
        <p><?php echo View::e(I18n::t("sol.{$_p}_problema_desc")); ?></p>
      </div>
    </div>
  </div>
</section>

<!-- 3. SOLUÇÃO -->
<section class="lp-section lp-solucao">
  <div class="lp-wrap">
    <div class="lp-two-col">
      <div>
        <h2><?php echo View::e(I18n::t("sol.{$_p}_solucao_titulo")); ?></h2>
        <p><?php echo View::e(I18n::t("sol.{$_p}_solucao_desc")); ?></p>
        <a href="<?php echo View::e($_cta); ?>" class="lp-btn primary" style="margin-top:24px"><?php echo View::e(I18n::t("sol.{$_p}_cta")); ?></a>
      </div>
      <div class="lp-col-icon">✅</div>
    </div>
  </div>
</section>

<!-- 4. BENEFÍCIOS -->
<section class="lp-section">
  <div class="lp-wrap">
    <div class="lp-section-label"><?php echo View::e(I18n::t('sol.section_beneficios')); ?></div>
    <div class="lp-grid-3">
      <?php for ($i = 1; $i <= 6; $i++): ?>
      <div class="lp-card">
        <div class="lp-card-check">✔</div>
        <h3><?php echo View::e(I18n::t("sol.{$_p}_b{$i}_titulo")); ?></h3>
        <p><?php echo View::e(I18n::t("sol.{$_p}_b{$i}_desc")); ?></p>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- 5. COMO FUNCIONA -->
<section class="lp-section lp-como">
  <div class="lp-wrap">
    <div class="lp-section-label"><?php echo View::e(I18n::t('sol.section_como_funciona')); ?></div>
    <div class="lp-steps">
      <?php for ($s = 1; $s <= 3; $s++): ?>
      <div class="lp-step">
        <div class="lp-step-num"><?php echo $s; ?></div>
        <h3><?php echo View::e(I18n::t("sol.passo{$s}")); ?></h3>
        <p><?php echo View::e(I18n::t("sol.passo{$s}_desc")); ?></p>
      </div>
      <?php if ($s < 3): ?><div class="lp-step-arrow">→</div><?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- 6. PROVA SOCIAL -->
<section class="lp-section lp-prova">
  <div class="lp-wrap">
    <div class="lp-section-label"><?php echo View::e(I18n::t('sol.section_prova')); ?></div>
    <div class="lp-grid-3">
      <?php for ($d = 1; $d <= 3; $d++): ?>
      <div class="lp-depoimento">
        <p class="lp-depoimento-texto">"<?php echo View::e(I18n::t("sol.depoimento{$d}_texto")); ?>"</p>
        <div class="lp-depoimento-autor">
          <div class="lp-depoimento-avatar"><?php echo mb_substr(I18n::t("sol.depoimento{$d}_nome"), 0, 1); ?></div>
          <div>
            <strong><?php echo View::e(I18n::t("sol.depoimento{$d}_nome")); ?></strong>
            <span><?php echo View::e(I18n::t("sol.depoimento{$d}_cargo")); ?></span>
          </div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- 7. CTA FORTE -->
<section class="lp-cta-section">
  <div class="lp-wrap" style="text-align:center">
    <h2><?php echo View::e(I18n::t('sol.cta_final_titulo')); ?></h2>
    <p><?php echo View::e(I18n::t('sol.cta_final_desc')); ?></p>
    <div class="lp-hero-actions" style="margin-top:28px">
      <a href="/#planos" class="lp-btn primary"><?php echo View::e(I18n::t('sol.cta_planos')); ?></a>
      <a href="/contato" class="lp-btn outline"><?php echo View::e(I18n::t('sol.cta_contato')); ?></a>
    </div>
  </div>
</section>

<!-- 8. FAQ -->
<section class="lp-section">
  <div class="lp-wrap">
    <div class="lp-section-label"><?php echo View::e(I18n::t('sol.section_faq')); ?></div>
    <div class="lp-faq">
      <?php for ($f = 1; $f <= 4; $f++): ?>
      <details class="lp-faq-item">
        <summary><?php echo View::e(I18n::t("sol.{$_p}_faq{$f}_p")); ?></summary>
        <p><?php echo View::e(I18n::t("sol.{$_p}_faq{$f}_r")); ?></p>
      </details>
      <?php endfor; ?>
    </div>
  </div>
</section>

<style>
/* Buttons */
.lp-btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 32px;border-radius:12px;font-size:15px;font-weight:700;transition:opacity .15s,transform .1s;cursor:pointer;border:none}
.lp-btn:hover{opacity:.9;transform:translateY(-2px)}
.lp-btn.primary{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff}
.lp-btn.outline{color:rgba(255,255,255,.8);border:1.5px solid rgba(255,255,255,.2);background:none}

/* Sections */
.lp-section{padding:80px 0}
.lp-section-label{text-align:center;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#818cf8;margin-bottom:48px}

/* Two-col */
.lp-two-col{display:flex;align-items:center;gap:48px}
.lp-two-col h2{font-size:clamp(22px,3.5vw,32px);font-weight:800;margin-bottom:14px;letter-spacing:-.02em;line-height:1.2}
.lp-two-col p{font-size:16px;color:rgba(255,255,255,.55);line-height:1.7}
.lp-col-icon{font-size:64px;flex-shrink:0;width:120px;height:120px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.04);border-radius:24px;border:1px solid rgba(255,255,255,.06)}

/* Problema */
.lp-problema{background:rgba(255,60,60,.03);border-top:1px solid rgba(255,60,60,.08);border-bottom:1px solid rgba(255,60,60,.08)}

/* Solução */
.lp-solucao{background:rgba(79,70,229,.04);border-bottom:1px solid rgba(79,70,229,.08)}

/* Grid 3 */
.lp-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}

/* Cards */
.lp-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:28px 24px;transition:border-color .2s,transform .2s}
.lp-card:hover{border-color:rgba(129,140,248,.25);transform:translateY(-3px)}
.lp-card-check{font-size:20px;margin-bottom:10px;color:#818cf8}
.lp-card h3{font-size:16px;font-weight:700;margin-bottom:6px;color:#fff}
.lp-card p{font-size:14px;color:rgba(255,255,255,.5);line-height:1.6}
</style>

<style>
/* Steps */
.lp-como{background:rgba(255,255,255,.02);border-top:1px solid rgba(255,255,255,.05);border-bottom:1px solid rgba(255,255,255,.05)}
.lp-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px}
.lp-step{text-align:center;flex:1;max-width:280px}
.lp-step-num{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:20px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.lp-step h3{font-size:16px;font-weight:700;margin-bottom:6px}
.lp-step p{font-size:14px;color:rgba(255,255,255,.5)}
.lp-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:10px;flex-shrink:0}

/* Depoimentos */
.lp-prova{background:rgba(79,70,229,.03)}
.lp-depoimento{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:28px 24px;display:flex;flex-direction:column;justify-content:space-between}
.lp-depoimento-texto{font-size:15px;color:rgba(255,255,255,.65);font-style:italic;line-height:1.6;margin-bottom:20px;flex:1}
.lp-depoimento-autor{display:flex;align-items:center;gap:12px}
.lp-depoimento-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.lp-depoimento-autor strong{display:block;font-size:14px;color:#fff}
.lp-depoimento-autor span{font-size:12px;color:rgba(255,255,255,.4)}

/* CTA Section */
.lp-cta-section{padding:80px 20px;background:linear-gradient(135deg,rgba(79,70,229,.12),rgba(124,58,237,.08));border-top:1px solid rgba(79,70,229,.15);border-bottom:1px solid rgba(79,70,229,.15)}
.lp-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px;letter-spacing:-.02em}
.lp-cta-section p{font-size:16px;color:rgba(255,255,255,.5);max-width:500px;margin:0 auto}

/* FAQ */
.lp-faq{max-width:700px;margin:0 auto;display:flex;flex-direction:column;gap:12px}
.lp-faq-item{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:12px;overflow:hidden}
.lp-faq-item summary{padding:18px 24px;font-size:15px;font-weight:600;color:#fff;cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between}
.lp-faq-item summary::after{content:'+';font-size:20px;color:rgba(255,255,255,.3);transition:transform .2s}
.lp-faq-item[open] summary::after{content:'−'}
.lp-faq-item summary::-webkit-details-marker{display:none}
.lp-faq-item p{padding:0 24px 18px;font-size:14px;color:rgba(255,255,255,.5);line-height:1.6}

/* Responsive */
@media(max-width:768px){
  .lp-grid-3{grid-template-columns:1fr}
  .lp-two-col{flex-direction:column;gap:24px;text-align:center}
  .lp-solucao .lp-two-col{flex-direction:column-reverse}
  .lp-steps{flex-direction:column;align-items:center}
  .lp-step-arrow{transform:rotate(90deg);padding:0}
  .lp-col-icon{margin:0 auto}
}
@media(min-width:769px) and (max-width:1024px){
  .lp-grid-3{grid-template-columns:repeat(2,1fr)}
}
</style>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
