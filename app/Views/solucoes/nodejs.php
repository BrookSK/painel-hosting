<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;
$_nome = SistemaConfig::nome();
$_planos = is_array($planos ?? null) ? $planos : [];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo I18n::t('sol_node.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Node.js ── */
.nj-hero{background:linear-gradient(135deg,#1c1917 0%,#292524 30%,#78350f 60%,#b45309 85%,#f59e0b 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.nj-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.nj-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(245,158,11,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.nj-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.nj-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.nj-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.nj-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.nj-hero h1 em{font-style:italic;color:#fde68a}
.nj-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.nj-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.nj-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#d97706;transition:transform .15s;text-decoration:none}
.nj-btn-p:hover{transform:translateY(-2px)}
.nj-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.nj-btn-s:hover{background:rgba(255,255,255,.18)}
.nj-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff;font-family:'Courier New',Courier,monospace}
.nj-mock-bar{display:flex;gap:6px;margin-bottom:16px}.nj-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.nj-mock-dot:first-child{background:#ef4444}.nj-mock-dot:nth-child(2){background:#f59e0b}.nj-mock-dot:nth-child(3){background:#22c55e}
.nj-mock-terminal{display:flex;flex-direction:column;gap:6px}
.nj-mock-line{font-size:.72rem;line-height:1.6;color:rgba(255,255,255,.5)}
.nj-mock-line .prompt{color:#f59e0b}
.nj-mock-line .cmd{color:#fde68a}
.nj-mock-line .ok{color:#22c55e}
.nj-mock-line .dim{color:rgba(255,255,255,.25)}
.nj-mock-line .url{color:#93c5fd;text-decoration:underline}
@media(max-width:860px){.nj-hero-inner{grid-template-columns:1fr;text-align:center}.nj-hero p{margin:0 auto 28px}.nj-hero-actions{justify-content:center}.nj-hero-visual{display:none}}

/* ── Stats ── */
.nj-stats{background:#1c1917;padding:36px 0}
.nj-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.nj-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.nj-stat:last-child{border:none}
.nj-stat h3{font-size:2rem;font-weight:900;color:#fde68a;margin-bottom:4px}.nj-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.nj-stats-inner{grid-template-columns:1fr 1fr}.nj-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.nj-section{padding:80px 24px}.nj-section.alt{background:#f8fafc}.nj-section.dark{background:#1c1917;color:#fff}
.nj-inner{max-width:1100px;margin:0 auto}
.nj-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#d97706;margin-bottom:10px}
.nj-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.nj-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.nj-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.nj-feat{background:#fff;padding:32px 24px;transition:background .2s}
.nj-feat:hover{background:#fef3c7}
.nj-feat-icon{width:48px;height:48px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.nj-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.nj-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.nj-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.nj-features{grid-template-columns:1fr}}

/* ── How it works ── */
.nj-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.nj-step{text-align:center;flex:1;max-width:260px}
.nj-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(217,119,6,.3)}
.nj-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.nj-step p{font-size:13px;color:rgba(255,255,255,.5)}
.nj-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.nj-steps{flex-direction:column;align-items:center}.nj-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.nj-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.nj-compare-card{border-radius:16px;padding:28px 24px}
.nj-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.nj-compare-card.good{background:#fef3c7;border:2px solid #d97706}
.nj-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.nj-compare-card ul{list-style:none;padding:0}
.nj-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.nj-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.nj-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1c1917,#78350f);text-align:center;color:#fff}
.nj-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.nj-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.nj-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.nj-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.nj-faq details[open]{border-color:#d97706}
.nj-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.nj-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.nj-faq details[open] summary::after{content:'−';color:#d97706}
.nj-faq summary::-webkit-details-marker{display:none}
.nj-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="nj-hero">
  <div class="glow"></div>
  <div class="nj-hero-inner">
    <div>
      <div class="nj-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1.5l9.5 5.5v11L12 23.5 2.5 18V7z"/></svg> <?php echo I18n::t('sol_node.badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_node.hero_title_pre'); ?> <em><?php echo I18n::t('sol_node.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_node.hero_desc'); ?></p>
      <div class="nj-hero-actions">
        <a href="#planos" class="nj-btn-p"><?php echo I18n::t('sol_node.btn_plans'); ?></a>
        <a href="/contato" class="nj-btn-s"><?php echo I18n::t('sol_node.btn_contact'); ?></a>
      </div>
    </div>
    <div class="nj-hero-visual">
      <div class="nj-mock-bar"><div class="nj-mock-dot"></div><div class="nj-mock-dot"></div><div class="nj-mock-dot"></div></div>
      <div class="nj-mock-terminal">
        <div class="nj-mock-line"><span class="prompt">$</span> <span class="cmd">git push origin main</span></div>
        <div class="nj-mock-line"><span class="dim">Enumerating objects: 42, done.</span></div>
        <div class="nj-mock-line"><span class="dim">Compressing objects: 100% (38/38)</span></div>
        <div class="nj-mock-line"><span class="dim">remote: Installing dependencies...</span></div>
        <div class="nj-mock-line"><span class="dim">remote: npm install ✓</span></div>
        <div class="nj-mock-line"><span class="dim">remote: Building application...</span></div>
        <div class="nj-mock-line"><span class="ok">✓ Deploy concluído com sucesso!</span></div>
        <div class="nj-mock-line"><span class="dim">→</span> <span class="url">https://meuapp.exemplo.com</span></div>
        <div class="nj-mock-line" style="margin-top:6px"><span class="prompt">$</span> <span class="dim" style="animation:blink 1s infinite">_</span></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="nj-stats">
  <div class="nj-stats-inner">
    <div class="nj-stat"><h3>Git</h3><p><?php echo I18n::t('sol_node.stat1_desc'); ?></p></div>
    <div class="nj-stat"><h3>99.9%</h3><p><?php echo I18n::t('sol_node.stat2_desc'); ?></p></div>
    <div class="nj-stat"><h3>WS</h3><p><?php echo I18n::t('sol_node.stat3_desc'); ?></p></div>
    <div class="nj-stat"><h3>SSL</h3><p><?php echo I18n::t('sol_node.stat4_desc'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="nj-section">
  <div class="nj-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="nj-label"><?php echo I18n::t('sol_node.compare_label'); ?></div>
      <h2 class="nj-title"><?php echo I18n::t('sol_node.compare_title'); ?></h2>
      <p class="nj-sub" style="margin:0 auto;"><?php echo I18n::t('sol_node.compare_desc'); ?></p>
    </div>
    <div class="nj-compare">
      <div class="nj-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_node.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_node.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_node.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_node.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_node.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_node.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_node.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="nj-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Node.js App <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_node.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_node.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_node.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_node.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_node.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_node.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="nj-section alt">
  <div class="nj-inner">
    <div style="text-align:center;">
      <div class="nj-label"><?php echo I18n::t('sol_node.feat_label'); ?></div>
      <h2 class="nj-title"><?php echo I18n::t('sol_node.feat_title'); ?></h2>
    </div>
    <div class="nj-features">
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div><h3><?php echo I18n::t('sol_node.feat1_title'); ?></h3><p><?php echo I18n::t('sol_node.feat1_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><h3><?php echo I18n::t('sol_node.feat2_title'); ?></h3><p><?php echo I18n::t('sol_node.feat2_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><h3><?php echo I18n::t('sol_node.feat3_title'); ?></h3><p><?php echo I18n::t('sol_node.feat3_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div><h3><?php echo I18n::t('sol_node.feat4_title'); ?></h3><p><?php echo I18n::t('sol_node.feat4_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div><h3><?php echo I18n::t('sol_node.feat5_title'); ?></h3><p><?php echo I18n::t('sol_node.feat5_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></div><h3><?php echo I18n::t('sol_node.feat6_title'); ?></h3><p><?php echo I18n::t('sol_node.feat6_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><h3><?php echo I18n::t('sol_node.feat7_title'); ?></h3><p><?php echo I18n::t('sol_node.feat7_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3><?php echo I18n::t('sol_node.feat8_title'); ?></h3><p><?php echo I18n::t('sol_node.feat8_desc'); ?></p></div>
      <div class="nj-feat"><div class="nj-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_node.feat9_title'); ?></h3><p><?php echo I18n::t('sol_node.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="nj-section dark">
  <div class="nj-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="nj-label" style="color:#fde68a;"><?php echo I18n::t('sol_node.steps_label'); ?></div>
      <h2 class="nj-title" style="color:#fff;"><?php echo I18n::t('sol_node.steps_title'); ?></h2>
    </div>
    <div class="nj-steps">
      <div class="nj-step"><div class="nj-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_node.step1_title'); ?></h3><p><?php echo I18n::t('sol_node.step1_desc'); ?></p></div>
      <div class="nj-step-arrow">→</div>
      <div class="nj-step"><div class="nj-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_node.step2_title'); ?></h3><p><?php echo I18n::t('sol_node.step2_desc'); ?></p></div>
      <div class="nj-step-arrow">→</div>
      <div class="nj-step"><div class="nj-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_node.step3_title'); ?></h3><p><?php echo I18n::t('sol_node.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#d97706'; $_plan_type = 'nodejs'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="nj-section">
  <div class="nj-inner">
    <div style="text-align:center;">
      <div class="nj-label"><?php echo I18n::t('sol_node.faq_label'); ?></div>
      <h2 class="nj-title"><?php echo I18n::t('sol_node.faq_title'); ?></h2>
    </div>
    <div class="nj-faq">
      <details><summary><?php echo I18n::t('sol_node.faq1_q'); ?></summary><p><?php echo I18n::t('sol_node.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_node.faq2_q'); ?></summary><p><?php echo I18n::t('sol_node.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_node.faq3_q'); ?></summary><p><?php echo I18n::t('sol_node.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_node.faq4_q'); ?></summary><p><?php echo I18n::t('sol_node.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_node.faq5_q'); ?></summary><p><?php echo I18n::t('sol_node.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_node.faq6_q'); ?></summary><p><?php echo I18n::t('sol_node.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="nj-cta-section">
  <h2><?php echo I18n::t('sol_node.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_node.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="nj-btn-p"><?php echo I18n::t('sol_node.btn_plans'); ?></a>
    <a href="/contato" class="nj-btn-s"><?php echo I18n::t('sol_node.btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
