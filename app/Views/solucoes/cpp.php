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
<title><?php echo I18n::t('sol_cpp.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero C/C++ ── */
.cp-hero{background:linear-gradient(135deg,#1a1a2e 0%,#2d1b3d 30%,#831843 60%,#db2777 85%,#ec4899 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.cp-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.cp-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(236,72,153,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.cp-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.cp-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.cp-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.cp-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.cp-hero h1 em{font-style:italic;color:#fbcfe8}
.cp-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.cp-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.cp-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#db2777;transition:transform .15s;text-decoration:none}
.cp-btn-p:hover{transform:translateY(-2px)}
.cp-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.cp-btn-s:hover{background:rgba(255,255,255,.18)}
.cp-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff;font-family:'Courier New',Courier,monospace}
.cp-mock-bar{display:flex;gap:6px;margin-bottom:16px}.cp-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.cp-mock-dot:first-child{background:#ef4444}.cp-mock-dot:nth-child(2){background:#f59e0b}.cp-mock-dot:nth-child(3){background:#22c55e}
.cp-mock-terminal{display:flex;flex-direction:column;gap:6px}
.cp-mock-line{font-size:.72rem;line-height:1.6;color:rgba(255,255,255,.5)}
.cp-mock-line .prompt{color:#ec4899}
.cp-mock-line .cmd{color:#fbcfe8}
.cp-mock-line .ok{color:#22c55e}
.cp-mock-line .warn{color:#f59e0b}
.cp-mock-line .dim{color:rgba(255,255,255,.25)}
.cp-mock-line .url{color:#93c5fd;text-decoration:underline}
@media(max-width:860px){.cp-hero-inner{grid-template-columns:1fr;text-align:center}.cp-hero p{margin:0 auto 28px}.cp-hero-actions{justify-content:center}.cp-hero-visual{display:none}}

/* ── Stats ── */
.cp-stats{background:#1a1a2e;padding:36px 0}
.cp-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.cp-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.cp-stat:last-child{border:none}
.cp-stat h3{font-size:2rem;font-weight:900;color:#fbcfe8;margin-bottom:4px}.cp-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.cp-stats-inner{grid-template-columns:1fr 1fr}.cp-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.cp-section{padding:80px 24px}.cp-section.alt{background:#f8fafc}.cp-section.dark{background:#1a1a2e;color:#fff}
.cp-inner{max-width:1100px;margin:0 auto}
.cp-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#db2777;margin-bottom:10px}
.cp-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.cp-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.cp-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.cp-feat{background:#fff;padding:32px 24px;transition:background .2s}
.cp-feat:hover{background:#fce7f3}
.cp-feat-icon{width:48px;height:48px;background:#fce7f3;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.cp-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.cp-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.cp-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.cp-features{grid-template-columns:1fr}}

/* ── How it works ── */
.cp-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.cp-step{text-align:center;flex:1;max-width:260px}
.cp-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#db2777,#ec4899);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(219,39,119,.3)}
.cp-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.cp-step p{font-size:13px;color:rgba(255,255,255,.5)}
.cp-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.cp-steps{flex-direction:column;align-items:center}.cp-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.cp-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.cp-compare-card{border-radius:16px;padding:28px 24px}
.cp-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.cp-compare-card.good{background:#fce7f3;border:2px solid #db2777}
.cp-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.cp-compare-card ul{list-style:none;padding:0}
.cp-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.cp-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.cp-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1a1a2e,#831843);text-align:center;color:#fff}
.cp-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.cp-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.cp-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.cp-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.cp-faq details[open]{border-color:#db2777}
.cp-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.cp-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.cp-faq details[open] summary::after{content:'−';color:#db2777}
.cp-faq summary::-webkit-details-marker{display:none}
.cp-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="cp-hero">
  <div class="glow"></div>
  <div class="cp-hero-inner">
    <div>
      <div class="cp-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.08z"/></svg> <?php echo I18n::t('sol_cpp.hero_badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_cpp.hero_title_pre'); ?> <em><?php echo I18n::t('sol_cpp.hero_title_em'); ?></em> <?php echo I18n::t('sol_cpp.hero_title_post'); ?></h1>
      <p><?php echo I18n::t('sol_cpp.hero_desc'); ?></p>
      <div class="cp-hero-actions">
        <a href="#planos" class="cp-btn-p"><?php echo I18n::t('sol_cpp.hero_btn_plans'); ?></a>
        <a href="/contato" class="cp-btn-s"><?php echo I18n::t('sol_cpp.hero_btn_contact'); ?></a>
      </div>
    </div>
    <div class="cp-hero-visual">
      <div class="cp-mock-bar"><div class="cp-mock-dot"></div><div class="cp-mock-dot"></div><div class="cp-mock-dot"></div></div>
      <div class="cp-mock-terminal">
        <div class="cp-mock-line"><span class="prompt">$</span> <span class="cmd">git push origin main</span></div>
        <div class="cp-mock-line"><span class="dim">remote: Detecting project type... C++</span></div>
        <div class="cp-mock-line"><span class="dim">remote: Running cmake -B build .</span></div>
        <div class="cp-mock-line"><span class="dim">remote: -- The CXX compiler: /usr/bin/g++-13</span></div>
        <div class="cp-mock-line"><span class="dim">remote: -- Found Boost 1.83.0</span></div>
        <div class="cp-mock-line"><span class="dim">remote: -- Found OpenSSL 3.0.11</span></div>
        <div class="cp-mock-line"><span class="warn">remote: Building with -O2 -std=c++20</span></div>
        <div class="cp-mock-line"><span class="dim">remote: [100%] Linking CXX executable app</span></div>
        <div class="cp-mock-line"><span class="ok">✓ Build + deploy concluído!</span></div>
        <div class="cp-mock-line"><span class="dim">→</span> <span class="url">https://meuapp.exemplo.com</span></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="cp-stats">
  <div class="cp-stats-inner">
    <div class="cp-stat"><h3>GCC/G++</h3><p><?php echo I18n::t('sol_cpp.stats_gcc_label'); ?></p></div>
    <div class="cp-stat"><h3>99.9%</h3><p><?php echo I18n::t('sol_cpp.stats_uptime_label'); ?></p></div>
    <div class="cp-stat"><h3>CMake</h3><p><?php echo I18n::t('sol_cpp.stats_cmake_label'); ?></p></div>
    <div class="cp-stat"><h3>Nativa</h3><p><?php echo I18n::t('sol_cpp.stats_perf_label'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="cp-section">
  <div class="cp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="cp-label"><?php echo I18n::t('sol_cpp.why_badge'); ?></div>
      <h2 class="cp-title"><?php echo I18n::t('sol_cpp.why_title'); ?> <?php echo View::e($_nome); ?></h2>
      <p class="cp-sub" style="margin:0 auto;"><?php echo I18n::t('sol_cpp.why_desc'); ?></p>
    </div>
    <div class="cp-compare">
      <div class="cp-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_cpp.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_cpp.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_cpp.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_cpp.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_cpp.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_cpp.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_cpp.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="cp-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_cpp.compare_good_title'); ?> <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_cpp.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_cpp.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_cpp.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_cpp.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_cpp.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_cpp.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="cp-section alt">
  <div class="cp-inner">
    <div style="text-align:center;">
      <div class="cp-label"><?php echo I18n::t('sol_cpp.feat_badge'); ?></div>
      <h2 class="cp-title"><?php echo I18n::t('sol_cpp.feat_title'); ?></h2>
    </div>
    <div class="cp-features">
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_1_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_1_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_2_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_2_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_3_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_3_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_4_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_4_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_5_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_5_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_6_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_6_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_7_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_7_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_8_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_8_desc'); ?></p></div>
      <div class="cp-feat"><div class="cp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_cpp.feat_9_title'); ?></h3><p><?php echo I18n::t('sol_cpp.feat_9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="cp-section dark">
  <div class="cp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="cp-label" style="color:#fbcfe8;"><?php echo I18n::t('sol_cpp.steps_badge'); ?></div>
      <h2 class="cp-title" style="color:#fff;"><?php echo I18n::t('sol_cpp.steps_title'); ?></h2>
    </div>
    <div class="cp-steps">
      <div class="cp-step"><div class="cp-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_cpp.step_1_title'); ?></h3><p><?php echo I18n::t('sol_cpp.step_1_desc'); ?></p></div>
      <div class="cp-step-arrow">→</div>
      <div class="cp-step"><div class="cp-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_cpp.step_2_title'); ?></h3><p><?php echo I18n::t('sol_cpp.step_2_desc'); ?></p></div>
      <div class="cp-step-arrow">→</div>
      <div class="cp-step"><div class="cp-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_cpp.step_3_title'); ?></h3><p><?php echo I18n::t('sol_cpp.step_3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#db2777'; $_plan_type = 'cpp'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="cp-section">
  <div class="cp-inner">
    <div style="text-align:center;">
      <div class="cp-label"><?php echo I18n::t('sol_cpp.faq_badge'); ?></div>
      <h2 class="cp-title"><?php echo I18n::t('sol_cpp.faq_title'); ?></h2>
    </div>
    <div class="cp-faq">
      <details><summary><?php echo I18n::t('sol_cpp.faq_1_q'); ?></summary><p><?php echo I18n::t('sol_cpp.faq_1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_cpp.faq_2_q'); ?></summary><p><?php echo I18n::t('sol_cpp.faq_2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_cpp.faq_3_q'); ?></summary><p><?php echo I18n::t('sol_cpp.faq_3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_cpp.faq_4_q'); ?></summary><p><?php echo I18n::t('sol_cpp.faq_4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_cpp.faq_5_q'); ?></summary><p><?php echo I18n::t('sol_cpp.faq_5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_cpp.faq_6_q'); ?></summary><p><?php echo I18n::t('sol_cpp.faq_6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cp-cta-section">
  <h2><?php echo I18n::t('sol_cpp.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_cpp.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="cp-btn-p"><?php echo I18n::t('sol_cpp.cta_btn_plans'); ?></a>
    <a href="/contato" class="cp-btn-s"><?php echo I18n::t('sol_cpp.cta_btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
