<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;
$_nome = SistemaConfig::nome();
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Segurança — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Segurança ── */
.sg-hero{background:linear-gradient(135deg,#020617 0%,#0f172a 30%,#1e293b 60%,#334155 85%,#475569 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.sg-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.sg-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(51,65,85,.5),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.sg-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.sg-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.sg-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.06em;text-transform:uppercase}
.sg-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.sg-hero h1 em{font-style:italic;color:#94a3b8}
.sg-hero p{font-size:1rem;color:rgba(255,255,255,.5);line-height:1.8;margin-bottom:28px;max-width:480px}
.sg-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.sg-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#334155;transition:transform .15s;text-decoration:none}
.sg-btn-p:hover{transform:translateY(-2px)}
.sg-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.15);text-decoration:none;transition:background .15s}
.sg-btn-s:hover{background:rgba(255,255,255,.14)}

/* Hero Visual — Security Shield */
.sg-hero-visual{background:rgba(255,255,255,.04);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:28px;color:#fff}
.sg-mock-bar{display:flex;gap:6px;margin-bottom:16px}.sg-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.1)}.sg-mock-dot:first-child{background:#ef4444}.sg-mock-dot:nth-child(2){background:#f59e0b}.sg-mock-dot:nth-child(3){background:#22c55e}
.sg-shield{text-align:center;margin-bottom:16px}
.sg-shield-icon{font-size:3rem;margin-bottom:8px;display:block}
.sg-shield-status{font-size:.75rem;font-weight:700;color:#4ade80;text-transform:uppercase;letter-spacing:.08em}
.sg-checks{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.sg-check-item{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:10px 12px}
.sg-check-item .icon{font-size:.9rem}
.sg-check-item .txt{font-size:.7rem;color:rgba(255,255,255,.5)}
.sg-check-item .status{font-size:.65rem;font-weight:700;color:#4ade80;margin-left:auto}
.sg-threat-bar{margin-top:12px;background:rgba(0,0,0,.3);border-radius:8px;padding:10px 12px;display:flex;align-items:center;gap:8px}
.sg-threat-bar .dot{width:8px;height:8px;border-radius:50%;background:#4ade80;box-shadow:0 0 6px rgba(74,222,128,.4)}
.sg-threat-bar .txt{font-size:.68rem;color:rgba(255,255,255,.4)}
.sg-threat-bar .count{font-size:.68rem;font-weight:700;color:#4ade80;margin-left:auto}
@media(max-width:860px){.sg-hero-inner{grid-template-columns:1fr;text-align:center}.sg-hero p{margin:0 auto 28px}.sg-hero-actions{justify-content:center}.sg-hero-visual{display:none}}

/* ── Stats ── */
.sg-stats{background:#0f172a;padding:36px 0}
.sg-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.sg-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.sg-stat:last-child{border:none}
.sg-stat h3{font-size:2rem;font-weight:900;color:#94a3b8;margin-bottom:4px}.sg-stat p{font-size:.8rem;color:rgba(255,255,255,.35)}
@media(max-width:640px){.sg-stats-inner{grid-template-columns:1fr 1fr}.sg-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.sg-section{padding:80px 24px}.sg-section.alt{background:#f8fafc}.sg-section.dark{background:#0f172a;color:#fff}
.sg-inner{max-width:1100px;margin:0 auto}
.sg-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#475569;margin-bottom:10px}
.sg-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.sg-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.sg-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.sg-feat{background:#fff;padding:32px 24px;transition:background .2s}
.sg-feat:hover{background:#f1f5f9}
.sg-feat-icon{width:48px;height:48px;background:#f1f5f9;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.sg-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.sg-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.sg-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.sg-features{grid-template-columns:1fr}}

/* ── How it works ── */
.sg-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.sg-step{text-align:center;flex:1;max-width:260px}
.sg-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#334155,#475569);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(51,65,85,.3)}
.sg-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.sg-step p{font-size:13px;color:rgba(255,255,255,.5)}
.sg-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.sg-steps{flex-direction:column;align-items:center}.sg-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.sg-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.sg-compare-card{border-radius:16px;padding:28px 24px}
.sg-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.sg-compare-card.good{background:#f1f5f9;border:2px solid #334155}
.sg-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.sg-compare-card ul{list-style:none;padding:0}
.sg-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.sg-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.sg-cta-section{padding:80px 24px;background:linear-gradient(135deg,#020617,#0f172a);text-align:center;color:#fff}
.sg-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.sg-cta-section p{font-size:16px;color:rgba(255,255,255,.5);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.sg-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.sg-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.sg-faq details[open]{border-color:#334155}
.sg-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.sg-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.sg-faq details[open] summary::after{content:'−';color:#334155}
.sg-faq summary::-webkit-details-marker{display:none}
.sg-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="sg-hero">
  <div class="glow"></div>
  <div class="sg-hero-inner">
    <div>
      <div class="sg-hero-badge"><span>🔐 Segurança</span></div>
      <h1>Proteção completa para sua <em>infraestrutura</em></h1>
      <p>Isolamento de containers, proteção DDoS, SSL automático, autenticação em dois fatores e auditoria completa. Segurança em todas as camadas.</p>
      <div class="sg-hero-actions">
        <a href="/cliente/criar-conta" class="sg-btn-p">Começar agora</a>
        <a href="/contato" class="sg-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="sg-hero-visual">
      <div class="sg-mock-bar"><div class="sg-mock-dot"></div><div class="sg-mock-dot"></div><div class="sg-mock-dot"></div></div>
      <div class="sg-shield">
        <span class="sg-shield-icon">🛡️</span>
        <div class="sg-shield-status">● Proteção ativa</div>
      </div>
      <div class="sg-checks">
        <div class="sg-check-item"><span class="icon">🛡️</span><span class="txt">DDoS</span><span class="status">✓</span></div>
        <div class="sg-check-item"><span class="icon">🔒</span><span class="txt">SSL</span><span class="status">✓</span></div>
        <div class="sg-check-item"><span class="icon">🔑</span><span class="txt">2FA</span><span class="status">✓</span></div>
        <div class="sg-check-item"><span class="icon">🧱</span><span class="txt">Firewall</span><span class="status">✓</span></div>
      </div>
      <div class="sg-threat-bar">
        <div class="dot"></div>
        <span class="txt">Ameaças bloqueadas (24h)</span>
        <span class="count">1.247</span>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="sg-stats">
  <div class="sg-stats-inner">
    <div class="sg-stat"><h3>DDoS</h3><p>Proteção inclusa</p></div>
    <div class="sg-stat"><h3>SSL</h3><p>Grátis e automático</p></div>
    <div class="sg-stat"><h3>2FA</h3><p>Autenticação dupla</p></div>
    <div class="sg-stat"><h3>100%</h3><p>Isolamento total</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="sg-section">
  <div class="sg-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="sg-label">Por que proteger?</div>
      <h2 class="sg-title">Segurança básica vs Segurança <?php echo View::e($_nome); ?></h2>
      <p class="sg-sub" style="margin:0 auto;">Não espere um incidente para investir em segurança. Proteção proativa em todas as camadas.</p>
    </div>
    <div class="sg-compare">
      <div class="sg-compare-card bad">
        <h3>❌ Segurança básica</h3>
        <ul>
          <li>❌ Sem proteção DDoS — site cai sob ataque</li>
          <li>❌ SSL manual e frequentemente expirado</li>
          <li>❌ Login apenas com senha simples</li>
          <li>❌ Servidores compartilhados sem isolamento</li>
          <li>❌ Sem logs de auditoria</li>
          <li>❌ Backups sem criptografia</li>
        </ul>
      </div>
      <div class="sg-compare-card good">
        <h3>✅ Segurança <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ Mitigação DDoS automática na infraestrutura</li>
          <li>✅ SSL Let's Encrypt renovado automaticamente</li>
          <li>✅ 2FA com TOTP (Google Authenticator)</li>
          <li>✅ Containers isolados por cliente</li>
          <li>✅ Auditoria completa de todas as ações</li>
          <li>✅ Backups criptografados em repouso</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="sg-section alt">
  <div class="sg-inner">
    <div style="text-align:center;">
      <div class="sg-label">Camadas de proteção</div>
      <h2 class="sg-title">Segurança em profundidade</h2>
    </div>
    <div class="sg-features">
      <div class="sg-feat"><div class="sg-feat-icon">🛡️</div><h3>Proteção DDoS</h3><p>Mitigação automática de ataques volumétricos e de aplicação. Sua infraestrutura sempre disponível.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">🔒</div><h3>SSL Let's Encrypt</h3><p>Certificados SSL gratuitos emitidos e renovados automaticamente. HTTPS em todos os domínios.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">🔑</div><h3>2FA (TOTP)</h3><p>Autenticação em dois fatores com Google Authenticator ou qualquer app TOTP. Camada extra de proteção.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">📦</div><h3>Containers isolados</h3><p>Cada cliente em container separado com namespaces e cgroups. Isolamento total de processos e rede.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">🧱</div><h3>Firewall</h3><p>Regras de firewall configuráveis por servidor. Bloqueie portas, IPs e protocolos indesejados.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">📋</div><h3>Auditoria de logs</h3><p>Registro completo de logins, alterações e ações administrativas. Rastreabilidade total.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">💾</div><h3>Backups criptografados</h3><p>Backups automáticos criptografados em repouso. Seus dados protegidos mesmo em caso de acesso físico.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">👥</div><h3>Permissões granulares</h3><p>Controle quem acessa o quê. Papéis e permissões configuráveis por usuário e recurso.</p></div>
      <div class="sg-feat"><div class="sg-feat-icon">🔍</div><h3>Monitoramento de ameaças</h3><p>Detecção de atividades suspeitas em tempo real. Alertas automáticos para tentativas de invasão.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="sg-section dark">
  <div class="sg-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="sg-label" style="color:#94a3b8;">Como funciona</div>
      <h2 class="sg-title" style="color:#fff;">Proteção em 3 passos</h2>
    </div>
    <div class="sg-steps">
      <div class="sg-step"><div class="sg-step-num">1</div><h3 style="color:#fff;">Ative o 2FA</h3><p>Habilite autenticação em dois fatores na sua conta. Leva menos de 1 minuto.</p></div>
      <div class="sg-step-arrow">→</div>
      <div class="sg-step"><div class="sg-step-num">2</div><h3 style="color:#fff;">Configure SSL</h3><p>SSL é ativado automaticamente para todos os domínios. Zero configuração necessária.</p></div>
      <div class="sg-step-arrow">→</div>
      <div class="sg-step"><div class="sg-step-num">3</div><h3 style="color:#fff;">Monitore ameaças</h3><p>Acompanhe tentativas de ataque, logins e atividades pelo painel de segurança.</p></div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="sg-section">
  <div class="sg-inner">
    <div style="text-align:center;">
      <div class="sg-label">Perguntas frequentes</div>
      <h2 class="sg-title">Dúvidas sobre Segurança</h2>
    </div>
    <div class="sg-faq">
      <details><summary>Como funciona a proteção DDoS?</summary><p>A mitigação DDoS opera na camada de rede e aplicação. Tráfego malicioso é filtrado automaticamente antes de chegar ao seu servidor, sem impacto na performance para visitantes legítimos.</p></details>
      <details><summary>O SSL é realmente gratuito?</summary><p>Sim. Usamos Let's Encrypt para emitir certificados SSL gratuitos. A emissão e renovação são 100% automáticas. Todos os domínios configurados recebem HTTPS sem custo adicional.</p></details>
      <details><summary>Como configuro o 2FA?</summary><p>No painel, acesse Minha Conta → Segurança → Ativar 2FA. Escaneie o QR code com Google Authenticator, Authy ou qualquer app TOTP. A partir daí, cada login exige o código temporário.</p></details>
      <details><summary>Meus dados ficam isolados de outros clientes?</summary><p>Sim. Cada cliente opera em containers isolados com namespaces de processo, rede e sistema de arquivos separados. Um cliente não tem visibilidade nem acesso aos recursos de outro.</p></details>
      <details><summary>Posso ver logs de auditoria?</summary><p>Sim. O painel registra todas as ações: logins, alterações de configuração, criação de recursos e ações administrativas. Logs com timestamps, IP de origem e detalhes da ação.</p></details>
      <details><summary>A plataforma atende requisitos de compliance?</summary><p>A infraestrutura implementa controles de segurança alinhados com boas práticas de mercado: criptografia em trânsito e repouso, isolamento, auditoria e controle de acesso. Para compliance específica (LGPD, SOC2), entre em contato.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="sg-cta-section">
  <h2>Proteja sua infraestrutura agora</h2>
  <p>DDoS, SSL, 2FA e isolamento total. Segurança em todas as camadas, sem custo extra.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="/cliente/criar-conta" class="sg-btn-p">Criar conta grátis</a>
    <a href="/contato" class="sg-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
