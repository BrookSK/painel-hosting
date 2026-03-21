<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;

$nome           = (string) ($cliente['name'] ?? '');
$email          = (string) ($cliente['email'] ?? '');
$totalVps       = (int) ($totalVps ?? 0);
$vpsRunning     = (int) ($vpsRunning ?? 0);
$ticketsAbertos = (int) ($ticketsAbertos ?? 0);
$assinatura     = $assinatura ?? null;
$onboardingDone = (bool) ($onboardingDone ?? true);
$trialInfo      = $trialInfo ?? null;

$_initials = '';
foreach (explode(' ', trim($nome)) as $w) {
    $_initials .= strtoupper(substr($w, 0, 1));
    if (strlen($_initials) >= 2) break;
}
if ($_initials === '') $_initials = 'U';
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Painel — <?php echo View::e(SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;min-height:100vh;}
    .client-shell{display:flex;flex-direction:column;min-height:100vh;}
    .client-nav{background:rgba(11,28,61,.95);backdrop-filter:blur(16px);border-bottom:1px solid rgba(255,255,255,.08);padding:0 24px;position:sticky;top:0;z-index:100;}
    .client-nav-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:58px;gap:16px;}
    .client-nav-brand{display:flex;align-items:center;gap:9px;text-decoration:none;color:#fff;flex-shrink:0;}
    .client-nav-brand img{height:26px;width:auto;}
    .client-nav-brand span{font-size:15px;font-weight:700;}
    .client-nav-links{display:flex;align-items:center;gap:2px;}
    .client-nav-links a{color:rgba(255,255,255,.65);text-decoration:none;font-size:13px;font-weight:500;padding:6px 11px;border-radius:8px;transition:color .15s,background .15s;}
    .client-nav-links a:hover,.client-nav-links a.ativo{color:#fff;background:rgba(255,255,255,.1);}
    .client-nav-right{display:flex;align-items:center;gap:8px;flex-shrink:0;}
    .client-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .client-nav-exit{color:rgba(255,255,255,.5);font-size:13px;text-decoration:none;padding:6px 10px;border-radius:8px;transition:color .15s,background .15s;}
    .client-nav-exit:hover{color:#fff;background:rgba(239,68,68,.15);}
    @media(max-width:768px){.client-nav-links{display:none;}}
    .client-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:36px 24px 32px;border-bottom:1px solid rgba(255,255,255,.06);}
    .client-hero-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
    .client-hero-title{font-size:22px;font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:4px;}
    .client-hero-sub{font-size:14px;color:rgba(255,255,255,.55);}
    .client-content{max-width:1160px;margin:0 auto;padding:32px 24px 64px;}
    .client-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:28px;}
    .client-stat{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;transition:box-shadow .15s,transform .15s;}
    .client-stat:hover{box-shadow:0 4px 20px rgba(15,23,42,.08);transform:translateY(-1px);}
    .client-stat-val{font-size:30px;font-weight:800;color:#0f172a;line-height:1;margin-bottom:4px;}
    .client-stat-lbl{font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;}
    .client-nav-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;}
    .client-nav-card{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:20px;text-decoration:none;color:inherit;transition:border-color .15s,box-shadow .15s,transform .15s;display:flex;flex-direction:column;gap:8px;}
    .client-nav-card:hover{border-color:#4F46E5;box-shadow:0 4px 20px rgba(79,70,229,.1);transform:translateY(-2px);}
    .client-nav-card-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;background:#f5f3ff;}
    .client-nav-card-title{font-weight:700;font-size:14px;color:#0f172a;}
    .client-nav-card-desc{font-size:12px;color:#64748b;line-height:1.5;}
    .trial-banner{background:linear-gradient(135deg,#0B1C3D,#4F46E5);color:#fff;border-radius:16px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
    .trial-banner-icon{font-size:28px;flex-shrink:0;}
    .trial-banner-body{flex:1;min-width:180px;}
    .trial-banner-title{font-size:15px;font-weight:700;margin-bottom:4px;}
    .trial-banner-sub{font-size:13px;opacity:.8;margin-bottom:10px;}
    .trial-progress{background:rgba(255,255,255,.2);border-radius:999px;height:6px;overflow:hidden;}
    .trial-progress-bar{background:#a5b4fc;height:100%;border-radius:999px;transition:width .4s;}
    .notif-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:24px;border-left:4px solid #4F46E5;}
    .notif-item{padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;}
    .notif-item:last-child{border:none;}
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:999;}
    .modal-box{background:#fff;border-radius:20px;padding:36px 32px;max-width:480px;width:90%;}
    .modal-box h2{font-size:22px;font-weight:800;margin-bottom:10px;color:#0B1C3D;letter-spacing:-.02em;}
    .modal-box p{font-size:14px;color:#475569;margin-bottom:8px;line-height:1.6;}
    .modal-steps{list-style:none;padding:0;margin:14px 0 22px;}
    .modal-steps li{display:flex;align-items:center;gap:12px;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;}
    .modal-steps li:last-child{border:none;}
    .step-num{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    @media(max-width:768px){.client-content{padding:20px 16px 48px;}.client-stats{grid-template-columns:1fr 1fr;}.client-nav-cards{grid-template-columns:1fr 1fr;}}
    @media(max-width:480px){.client-stats{grid-template-columns:1fr;}.client-nav-cards{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<div class="client-shell">
  <nav class="client-nav">
    <div class="client-nav-inner">
      <?php $_logo = SistemaConfig::logoUrl(); ?>
      <a href="/" class="client-nav-brand">
        <?php if ($_logo !== ''): ?>
          <img src="<?php echo View::e($_logo); ?>" alt="logo" />
        <?php else: ?>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect width="24" height="24" rx="6" fill="#4F46E5"/><path d="M6 12h12M12 6v12" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
        <?php endif; ?>
        <span><?php echo View::e(SistemaConfig::nome()); ?></span>
      </a>
      <div class="client-nav-links">
        <a href="/cliente/painel" class="ativo">Painel</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/chat">Chat</a>
        <a href="/cliente/assinaturas">Assinaturas</a>
        <a href="/cliente/ajuda">Ajuda</a>
      </div>
      <div class="client-nav-right">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <div class="client-avatar"><?php echo View::e($_initials); ?></div>
        <a href="/cliente/sair" class="client-nav-exit">Sair</a>
      </div>
    </div>
  </nav>

  <div class="client-hero">
    <div class="client-hero-inner">
      <div>
        <div class="client-hero-title">Olá, <?php echo View::e(explode(' ', $nome)[0]); ?> 👋</div>
        <div class="client-hero-sub">Bem-vindo ao seu painel de controle</div>
      </div>
      <?php if ($assinatura !== null): ?>
        <span class="badge badge-verde"><?php echo View::e((string) ($assinatura['plan_name'] ?? 'Plano ativo')); ?></span>
      <?php else: ?>
        <a href="/cliente/planos" class="botao sm">Ver planos</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="client-content">
    <?php if (!empty($notificacoes)): ?>
      <div class="notif-card">
        <div style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:10px;">Notificações (<?php echo count($notificacoes); ?>)</div>
        <?php foreach ($notificacoes as $n): ?>
          <div class="notif-item">
            <div style="font-weight:600;margin-bottom:2px;"><?php echo View::e((string) ($n['title'] ?? '')); ?></div>
            <div style="color:#64748b;"><?php echo View::e((string) ($n['body'] ?? '')); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($trialInfo !== null):
      $diasRestantes = (int) $trialInfo['dias_restantes'];
      $progressPct = min(100, (int) round($diasRestantes / 7 * 100));
    ?>
    <div class="trial-banner">
      <div class="trial-banner-icon">🚀</div>
      <div class="trial-banner-body">
        <div class="trial-banner-title">Período de teste ativo — <?php echo $diasRestantes; ?> dia<?php echo $diasRestantes !== 1 ? 's' : ''; ?> restante<?php echo $diasRestantes !== 1 ? 's' : ''; ?></div>
        <div class="trial-banner-sub">
          <?php echo (int)$trialInfo['vcpu']; ?> vCPU · <?php echo (int)$trialInfo['ram_mb']; ?> MB RAM · <?php echo (int)$trialInfo['disco_gb']; ?> GB disco
          · Expira em <?php echo View::e(date('d/m/Y', strtotime((string)$trialInfo['expires_at']))); ?>
        </div>
        <div class="trial-progress"><div class="trial-progress-bar" style="width:<?php echo $progressPct; ?>%;"></div></div>
      </div>
      <a href="/cliente/planos" class="botao sm" style="background:#fff;color:#4F46E5;flex-shrink:0;">Assinar plano</a>
    </div>
    <?php endif; ?>

    <div class="client-stats">
      <div class="client-stat">
        <div class="client-stat-val"><?php echo $totalVps; ?></div>
        <div class="client-stat-lbl">VPS total</div>
      </div>
      <div class="client-stat">
        <div class="client-stat-val" style="color:#10b981;"><?php echo $vpsRunning; ?></div>
        <div class="client-stat-lbl">Em execução</div>
      </div>
      <div class="client-stat">
        <div class="client-stat-val" style="color:<?php echo $ticketsAbertos > 0 ? '#f59e0b' : '#10b981'; ?>;"><?php echo $ticketsAbertos; ?></div>
        <div class="client-stat-lbl">Tickets abertos</div>
      </div>
      <div class="client-stat">
        <?php if ($assinatura !== null): ?>
          <div class="client-stat-val" style="font-size:18px;color:#10b981;">Ativo</div>
          <div class="client-stat-lbl"><?php echo View::e((string) ($assinatura['plan_name'] ?? 'Plano')); ?></div>
        <?php else: ?>
          <div class="client-stat-val" style="font-size:18px;color:#94a3b8;">—</div>
          <div class="client-stat-lbl">Sem plano ativo</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="client-nav-cards">
      <a href="/cliente/vps" class="client-nav-card"><div class="client-nav-card-icon">🖥️</div><div class="client-nav-card-title">Minhas VPS</div><div class="client-nav-card-desc">Gerencie seus servidores virtuais</div></a>
      <a href="/cliente/monitoramento" class="client-nav-card"><div class="client-nav-card-icon">📊</div><div class="client-nav-card-title">Monitoramento</div><div class="client-nav-card-desc">CPU, RAM e disco em tempo real</div></a>
      <a href="/cliente/tickets" class="client-nav-card"><div class="client-nav-card-icon">🎫</div><div class="client-nav-card-title">Tickets</div><div class="client-nav-card-desc">Suporte técnico e solicitações</div></a>
      <a href="/cliente/chat" class="client-nav-card"><div class="client-nav-card-icon">💬</div><div class="client-nav-card-title">Chat ao vivo</div><div class="client-nav-card-desc">Fale com nossa equipe agora</div></a>
      <a href="/cliente/emails" class="client-nav-card"><div class="client-nav-card-icon">📧</div><div class="client-nav-card-title">E-mails</div><div class="client-nav-card-desc">Gerenciar caixas de entrada</div></a>
      <a href="/cliente/aplicacoes" class="client-nav-card"><div class="client-nav-card-icon">🚀</div><div class="client-nav-card-title">Aplicações</div><div class="client-nav-card-desc">Deploy e gerenciamento de apps</div></a>
      <a href="/cliente/assinaturas" class="client-nav-card"><div class="client-nav-card-icon">💳</div><div class="client-nav-card-title">Assinaturas</div><div class="client-nav-card-desc">Planos e histórico de pagamentos</div></a>
      <a href="/cliente/ajuda" class="client-nav-card"><div class="client-nav-card-icon">📚</div><div class="client-nav-card-title">Ajuda</div><div class="client-nav-card-desc">Documentação e tutoriais</div></a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
<?php if (!$onboardingDone): ?>
<div class="modal-overlay" id="onboardingModal" style="display:none;">
  <div class="modal-box">
    <h2>👋 Bem-vindo!</h2>
    <p>Sua conta está pronta. Veja como começar:</p>
    <ul class="modal-steps">
      <li><span class="step-num">1</span> Escolha um plano em <strong>Planos</strong></li>
      <li><span class="step-num">2</span> Após assinar, sua VPS será provisionada automaticamente</li>
      <li><span class="step-num">3</span> Acesse o <strong>Terminal</strong> direto pelo painel</li>
      <li><span class="step-num">4</span> Use <strong>Tickets</strong> ou <strong>Chat</strong> para suporte</li>
    </ul>
    <button class="botao" onclick="fecharOnboarding()">Entendido, vamos lá!</button>
  </div>
</div>
<script>
(function(){var m=document.getElementById('onboardingModal');if(m)m.style.display='flex';})();
function fecharOnboarding(){
  var m=document.getElementById('onboardingModal');if(m)m.style.display='none';
  fetch('/cliente/onboarding/concluir',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf=<?php echo View::e(\LRV\Core\Csrf::token()); ?>'});
}
</script>
<?php endif; ?>
</body>
</html>