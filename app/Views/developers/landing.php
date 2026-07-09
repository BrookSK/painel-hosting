<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$_nome = SistemaConfig::nome();
$t = fn(string $k) => I18n::t($k);
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;overflow-x:hidden}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,sans-serif;background:#fff;color:#0f172a}

/* Hero */
.dev-hero{position:relative;overflow:hidden;background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 30%,#1e3a8a 60%,#4F46E5 85%,#7C3AED 100%);color:#fff;padding:110px 24px 100px;text-align:center}
.dev-hero-grid{position:absolute;inset:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 40%,transparent 100%)}
.dev-hero-glow{position:absolute;inset:0;pointer-events:none;background:radial-gradient(ellipse 70% 60% at 60% 40%,rgba(124,58,237,.35) 0%,transparent 70%)}
.dev-hero-inner{max-width:760px;margin:0 auto;position:relative}
.dev-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:#c4b5fd;font-size:12px;font-weight:600;padding:5px 14px;border-radius:999px;margin-bottom:24px;backdrop-filter:blur(8px);letter-spacing:.04em;text-transform:uppercase}
.dev-eyebrow-dot{width:6px;height:6px;border-radius:50%;background:#4ADE80;animation:dpulse 2s infinite}
@keyframes dpulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}
.dev-hero-title{font-size:clamp(32px,6vw,54px);font-weight:900;line-height:1.1;letter-spacing:-.03em;margin-bottom:20px}
.dev-hero-title .grad{background:linear-gradient(135deg,#a5b4fc,#c4b5fd,#f0abfc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.dev-hero-sub{font-size:17px;opacity:.75;line-height:1.7;margin-bottom:36px;max-width:600px;margin-left:auto;margin-right:auto}
.dev-hero-ctas{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:40px}
.dev-btn{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:14px;font-size:15px;font-weight:700;text-decoration:none;transition:transform .15s,box-shadow .15s}
.dev-btn:hover{transform:translateY(-2px)}
.dev-btn.primary{background:#fff;color:#4F46E5;box-shadow:0 4px 20px rgba(255,255,255,.2)}
.dev-btn.primary:hover{box-shadow:0 8px 32px rgba(255,255,255,.3)}
.dev-btn.outline{background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.25);backdrop-filter:blur(8px)}
.dev-btn.outline:hover{background:rgba(255,255,255,.18)}

/* Code block */
.dev-code{max-width:600px;margin:0 auto;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:22px 26px;text-align:left;font-family:'JetBrains Mono','Fira Code',monospace;font-size:.82rem;line-height:1.8;color:rgba(255,255,255,.7);backdrop-filter:blur(8px)}
.dev-code .cm{color:rgba(255,255,255,.35)}
.dev-code .str{color:#86efac}
.dev-code .kw{color:#c4b5fd}

/* Stats bar */
.dev-stats{background:#0f172a;padding:28px 24px}
.dev-stats-inner{max-width:1000px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr)}
.dev-stat{text-align:center;padding:8px 16px;border-right:1px solid rgba(255,255,255,.08)}
.dev-stat:last-child{border-right:none}
.dev-stat h3{font-size:28px;font-weight:800;color:#a5b4fc;line-height:1;margin-bottom:4px}
.dev-stat p{font-size:12px;color:rgba(255,255,255,.5);font-weight:500;margin:0}
@media(max-width:640px){.dev-stats-inner{grid-template-columns:1fr 1fr}.dev-stat:nth-child(2){border-right:none}}

/* Section */
.dev-section{padding:88px 24px}
.dev-section.alt{background:#f8fafc}
.dev-section-inner{max-width:1100px;margin:0 auto}
.dev-section-header{text-align:center;margin-bottom:52px}
.dev-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#7C3AED;margin-bottom:10px}
.dev-section-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.dev-section-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px;margin:0 auto}

/* Features grid */
.dev-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden}
@media(max-width:860px){.dev-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.dev-grid{grid-template-columns:1fr}}
.dev-feat{background:#fff;padding:32px 28px;transition:background .2s}
.dev-feat:hover{background:#eef2ff}
.dev-feat-icon{width:46px;height:46px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:18px;font-size:1.4rem}
.dev-feat h4{font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:8px}
.dev-feat p{font-size:.85rem;color:#64748b;line-height:1.65}

/* Sandbox cards */
.dev-env-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:900px;margin:0 auto}
@media(max-width:700px){.dev-env-grid{grid-template-columns:1fr}}
.dev-env-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;transition:border-color .2s}
.dev-env-card:hover{border-color:#4F46E5}
.dev-env-card h4{font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:8px;display:flex;align-items:center;gap:8px}
.dev-env-card p{font-size:.9rem;color:#64748b;margin-bottom:16px;line-height:1.6}
.dev-env-card ul{list-style:none;padding:0}
.dev-env-card li{padding:6px 0;font-size:.875rem;color:#475569;display:flex;align-items:center;gap:8px}
.dev-env-card li::before{content:'✓';color:#4F46E5;font-weight:700;font-size:.75rem}
.badge-live{background:#DCFCE7;color:#166534;padding:3px 10px;border-radius:99px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-sandbox{background:#FEF3C7;color:#92400E;padding:3px 10px;border-radius:99px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}

/* Quick links */
.dev-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;max-width:900px;margin:0 auto}
.dev-link{display:flex;align-items:center;gap:10px;padding:14px 18px;background:#fff;border-radius:12px;text-decoration:none;color:#0f172a;font-weight:600;font-size:.9rem;border:1px solid #e2e8f0;transition:border-color .2s,transform .15s}
.dev-link:hover{border-color:#4F46E5;transform:translateY(-2px)}

/* CTA */
.dev-cta{background:linear-gradient(135deg,#060d1f 0%,#1e3a8a 60%,#4F46E5 100%);padding:88px 24px;text-align:center;color:#fff}
.dev-cta h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.dev-cta p{font-size:16px;opacity:.7;margin-bottom:32px;max-width:500px;margin-left:auto;margin-right:auto}
</style>
</head>
<body>

<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- ══ HERO ══ -->
<section class="dev-hero">
    <div class="dev-hero-grid"></div>
    <div class="dev-hero-glow"></div>
    <div class="dev-hero-inner">
        <div class="dev-eyebrow"><span class="dev-eyebrow-dot"></span> <span>Public API v1</span></div>
        <h1 class="dev-hero-title"><?= $t('dev_landing.hero_titulo') ?></h1>
        <p class="dev-hero-sub"><?= $t('dev_landing.hero_subtitulo') ?></p>

        <div class="dev-hero-ctas">
            <a href="/developers/api" class="dev-btn primary"><?= $t('dev_landing.cta_docs') ?></a>
            <a href="/developers/api/swagger" class="dev-btn outline"><?= $t('dev_landing.cta_explorer') ?></a>
            <a href="/cliente/api-keys" class="dev-btn outline"><?= $t('dev_landing.cta_painel') ?></a>
        </div>

        <div class="dev-code">
            <span class="cm">// <?= $t('dev_landing.code_comment') ?></span><br>
            curl -H <span class="str">"X-API-Key: lrv_live_sua_chave"</span> \<br>
            &nbsp;&nbsp;&nbsp;&nbsp; <span class="str">https://seudominio.com/api/v1/hosting</span><br><br>
            <span class="cm">// <?= $t('dev_landing.code_response') ?></span><br>
            { <span class="str">"success"</span>: <span class="kw">true</span>, <span class="str">"data"</span>: [...] }
        </div>
    </div>
</section>

<!-- ══ STATS ══ -->
<div class="dev-stats">
    <div class="dev-stats-inner">
        <div class="dev-stat"><h3>50+</h3><p>Endpoints REST</p></div>
        <div class="dev-stat"><h3>21</h3><p>Scopes</p></div>
        <div class="dev-stat"><h3>< 50ms</h3><p>Latency</p></div>
        <div class="dev-stat"><h3>99.9%</h3><p>Uptime</p></div>
    </div>
</div>

<!-- ══ FEATURES ══ -->
<section class="dev-section">
    <div class="dev-section-inner">
        <div class="dev-section-header">
            <div class="dev-label">Features</div>
            <h2 class="dev-section-title"><?= $t('dev_landing.feat_rest_titulo') ?></h2>
            <p class="dev-section-sub"><?= $t('dev_landing.hero_subtitulo') ?></p>
        </div>

        <div class="dev-grid">
            <div class="dev-feat">
                <div class="dev-feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div>
                <h4><?= $t('dev_landing.feat_auth_titulo') ?></h4>
                <p><?= $t('dev_landing.feat_auth_desc') ?></p>
            </div>
            <div class="dev-feat">
                <div class="dev-feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
                <h4><?= $t('dev_landing.feat_rest_titulo') ?></h4>
                <p><?= $t('dev_landing.feat_rest_desc') ?></p>
            </div>
            <div class="dev-feat">
                <div class="dev-feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
                <h4><?= $t('dev_landing.feat_webhooks_titulo') ?></h4>
                <p><?= $t('dev_landing.feat_webhooks_desc') ?></p>
            </div>
            <div class="dev-feat">
                <div class="dev-feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
                <h4><?= $t('dev_landing.feat_sandbox_titulo') ?></h4>
                <p><?= $t('dev_landing.feat_sandbox_desc') ?></p>
            </div>
            <div class="dev-feat">
                <div class="dev-feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg></div>
                <h4><?= $t('dev_landing.feat_rate_titulo') ?></h4>
                <p><?= $t('dev_landing.feat_rate_desc') ?></p>
            </div>
            <div class="dev-feat">
                <div class="dev-feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                <h4><?= $t('dev_landing.feat_docs_titulo') ?></h4>
                <p><?= $t('dev_landing.feat_docs_desc') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ══ SANDBOX vs PRODUCTION ══ -->
<section class="dev-section alt">
    <div class="dev-section-inner">
        <div class="dev-section-header">
            <div class="dev-label">Environments</div>
            <h2 class="dev-section-title"><?= $t('dev_landing.sandbox_titulo') ?></h2>
            <p class="dev-section-sub"><?= $t('dev_landing.sandbox_subtitulo') ?></p>
        </div>

        <div class="dev-env-grid">
            <div class="dev-env-card">
                <h4><span class="badge-sandbox">SANDBOX</span> <?= $t('dev_landing.sandbox_card_titulo') ?></h4>
                <p><?= $t('dev_landing.sandbox_card_desc') ?></p>
                <ul>
                    <li><?= $t('dev_landing.sandbox_item1') ?></li>
                    <li><?= $t('dev_landing.sandbox_item2') ?></li>
                    <li><?= $t('dev_landing.sandbox_item3') ?></li>
                    <li><?= $t('dev_landing.sandbox_item4') ?></li>
                </ul>
            </div>
            <div class="dev-env-card">
                <h4><span class="badge-live">PRODUCTION</span> <?= $t('dev_landing.prod_card_titulo') ?></h4>
                <p><?= $t('dev_landing.prod_card_desc') ?></p>
                <ul>
                    <li><?= $t('dev_landing.prod_item1') ?></li>
                    <li><?= $t('dev_landing.prod_item2') ?></li>
                    <li><?= $t('dev_landing.prod_item3') ?></li>
                    <li><?= $t('dev_landing.prod_item4') ?></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ══ QUICK LINKS ══ -->
<section class="dev-section">
    <div class="dev-section-inner">
        <div class="dev-section-header">
            <div class="dev-label">Resources</div>
            <h2 class="dev-section-title"><?= $t('api_docs.sdks_titulo') ?></h2>
            <p class="dev-section-sub"><?= $t('api_docs.sdks_desc') ?></p>
        </div>

        <div class="dev-links">
            <a href="/developers/api" class="dev-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg> <?= $t('dev_landing.link_docs') ?></a>
            <a href="/developers/api/swagger" class="dev-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> <?= $t('dev_landing.link_explorer') ?></a>
            <a href="/developers/api/postman.json" class="dev-link" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Postman</a>
            <a href="/developers/api/bruno.json" class="dev-link" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Bruno</a>
            <a href="/developers/api/insomnia.json" class="dev-link" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Insomnia</a>
            <a href="/api/v1/openapi.yaml" class="dev-link" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg> OpenAPI Spec</a>
            <a href="/developers/api/changelog" class="dev-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg> Changelog</a>
            <a href="/developers/api/status" class="dev-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg> API Status</a>
        </div>
    </div>
</section>

<!-- ══ CTA FINAL ══ -->
<section class="dev-cta">
    <h2><?= $t('dev_landing.cta_titulo') ?></h2>
    <p><?= $t('dev_landing.cta_desc') ?></p>
    <div class="dev-hero-ctas">
        <a href="/cliente/entrar" class="dev-btn primary"><?= $t('dev_landing.cta_login') ?></a>
        <a href="/developers/api" class="dev-btn outline"><?= $t('dev_landing.cta_ver_docs') ?></a>
    </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
