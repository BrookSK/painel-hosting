<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$nome           = (string) ($cliente['name'] ?? '');
$email          = (string) ($cliente['email'] ?? '');
$totalVps       = (int) ($totalVps ?? 0);
$vpsRunning     = (int) ($vpsRunning ?? 0);
$ticketsAbertos = (int) ($ticketsAbertos ?? 0);
$assinatura     = $assinatura ?? null;
$onboardingDone = (bool) ($onboardingDone ?? true);
$trialInfo      = $trialInfo ?? null;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Painel do cliente</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:20px; }
    .stat-card  { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:18px 16px; }
    .stat-val   { font-size:32px; font-weight:700; color:#4F46E5; line-height:1; margin-bottom:4px; }
    .stat-lbl   { font-size:13px; color:#64748b; }
    .nav-cards  { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; }
    .nav-card   { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:18px 16px; text-decoration:none; color:inherit; transition:border-color .15s, box-shadow .15s; }
    .nav-card:hover { border-color:#4F46E5; box-shadow:0 2px 12px #4F46E511; }
    .nav-card-icon { font-size:24px; margin-bottom:8px; }
    .nav-card-title { font-weight:600; font-size:15px; margin-bottom:4px; }
    .nav-card-desc  { font-size:13px; color:#64748b; }
    /* Onboarding modal */
    .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); display:flex; align-items:center; justify-content:center; z-index:999; }
    .modal-box { background:#fff; border-radius:18px; padding:32px 28px; max-width:480px; width:90%; }
    .modal-box h2 { font-size:22px; font-weight:700; margin-bottom:10px; color:#0B1C3D; }
    .modal-box p  { font-size:14px; color:#475569; margin-bottom:8px; line-height:1.6; }
    .modal-steps  { list-style:none; padding:0; margin:14px 0 20px; }
    .modal-steps li { display:flex; align-items:center; gap:10px; font-size:14px; padding:6px 0; border-bottom:1px solid #f1f5f9; }
    .modal-steps li:last-child { border:none; }
    .step-num { width:26px; height:26px; border-radius:50%; background:#4F46E5; color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    /* Trial banner */
    .trial-banner {
      background: linear-gradient(135deg, #0B1C3D, #4F46E5);
      color: #fff; border-radius: 14px; padding: 18px 20px;
      margin-bottom: 20px; display: flex; align-items: center;
      gap: 16px; flex-wrap: wrap;
    }
    .trial-banner-icon { font-size: 28px; flex-shrink: 0; }
    .trial-banner-body { flex: 1; min-width: 180px; }
    .trial-banner-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .trial-banner-sub   { font-size: 13px; opacity: .85; margin-bottom: 10px; }
    .trial-progress { background: rgba(255,255,255,.2); border-radius: 999px; height: 6px; overflow: hidden; }
    .trial-progress-bar { background: #a5b4fc; height: 100%; border-radius: 999px; transition: width .4s; }
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Painel do cliente</div>
        <div style="opacity:.9; font-size:13px;">Bem-vindo, <?php echo View::e($nome); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/">Início</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/chat">Chat</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">

    <?php if (!empty($notificacoes)): ?>
      <div class="card" style="margin-bottom:14px; border-left:3px solid #4F46E5;">
        <h2 class="titulo" style="font-size:15px; margin-bottom:10px;">Notificações (<?php echo count($notificacoes); ?>)</h2>
        <?php foreach ($notificacoes as $n): ?>
          <div style="padding:8px 0; border-bottom:1px solid #1e293b; font-size:13px;">
            <div style="font-weight:600; margin-bottom:2px;"><?php echo View::e((string) ($n['title'] ?? '')); ?></div>
            <div style="color:#94a3b8;"><?php echo View::e((string) ($n['body'] ?? '')); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($trialInfo !== null): ?>
    <?php
      $diasRestantes = (int) $trialInfo['dias_restantes'];
      $trialDiasTotal = max(1, $diasRestantes); // fallback
      // Tentar calcular total de dias a partir do settings (não disponível aqui, usar 7 como padrão)
      $trialDiasTotal = max($diasRestantes, 1);
      $progressPct = $diasRestantes > 0 ? min(100, (int) round($diasRestantes / max(1, $diasRestantes) * 100)) : 0;
      // Barra: quanto resta (dias restantes / dias totais configurados — usamos 7 como fallback)
      $progressPct = min(100, (int) round($diasRestantes / 7 * 100));
    ?>
    <div class="trial-banner">
      <div class="trial-banner-icon">🚀</div>
      <div class="trial-banner-body">
        <div class="trial-banner-title">
          Período de teste ativo —
          <?php echo $diasRestantes; ?> dia<?php echo $diasRestantes !== 1 ? 's' : ''; ?> restante<?php echo $diasRestantes !== 1 ? 's' : ''; ?>
        </div>
        <div class="trial-banner-sub">
          Servidor: <?php echo (int)$trialInfo['vcpu']; ?> vCPU · <?php echo (int)$trialInfo['ram_mb']; ?> MB RAM · <?php echo (int)$trialInfo['disco_gb']; ?> GB disco
          · Expira em <?php echo View::e(date('d/m/Y', strtotime((string)$trialInfo['expires_at']))); ?>
        </div>
        <div class="trial-progress">
          <div class="trial-progress-bar" style="width:<?php echo $progressPct; ?>%;"></div>
        </div>
      </div>
      <a href="/cliente/planos" class="botao sm" style="background:#fff;color:#4F46E5;flex-shrink:0;">Assinar plano</a>
    </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-val"><?php echo $totalVps; ?></div>
        <div class="stat-lbl">VPS total</div>
      </div>
      <div class="stat-card">
        <div class="stat-val" style="color:#10b981;"><?php echo $vpsRunning; ?></div>
        <div class="stat-lbl">VPS em execução</div>
      </div>
      <div class="stat-card">
        <div class="stat-val" style="color:<?php echo $ticketsAbertos > 0 ? '#f59e0b' : '#10b981'; ?>;"><?php echo $ticketsAbertos; ?></div>
        <div class="stat-lbl">Tickets abertos</div>
      </div>
      <div class="stat-card">
        <?php if ($assinatura !== null): ?>
          <div class="stat-val" style="font-size:18px; color:#10b981;">Ativo</div>
          <div class="stat-lbl"><?php echo View::e((string) ($assinatura['plan_name'] ?? 'Plano')); ?></div>
        <?php else: ?>
          <div class="stat-val" style="font-size:18px; color:#94a3b8;">—</div>
          <div class="stat-lbl">Sem plano ativo</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Nav cards -->
    <div class="nav-cards">
      <a href="/cliente/vps" class="nav-card">
        <div class="nav-card-icon">🖥️</div>
        <div class="nav-card-title">Minhas VPS</div>
        <div class="nav-card-desc">Gerencie seus servidores virtuais</div>
      </a>
      <a href="/cliente/monitoramento" class="nav-card">
        <div class="nav-card-icon">📊</div>
        <div class="nav-card-title">Monitoramento</div>
        <div class="nav-card-desc">CPU, RAM e disco em tempo real</div>
      </a>
      <a href="/cliente/tickets" class="nav-card">
        <div class="nav-card-icon">🎫</div>
        <div class="nav-card-title">Tickets</div>
        <div class="nav-card-desc">Suporte técnico e solicitações</div>
      </a>
      <a href="/cliente/chat" class="nav-card">
        <div class="nav-card-icon">💬</div>
        <div class="nav-card-title">Chat ao vivo</div>
        <div class="nav-card-desc">Fale com nossa equipe agora</div>
      </a>
      <a href="/cliente/emails" class="nav-card">
        <div class="nav-card-icon">📧</div>
        <div class="nav-card-title">E-mails</div>
        <div class="nav-card-desc">Gerenciar caixas de entrada</div>
      </a>
      <a href="/cliente/aplicacoes" class="nav-card">
        <div class="nav-card-icon">🚀</div>
        <div class="nav-card-title">Aplicações</div>
        <div class="nav-card-desc">Deploy e gerenciamento de apps</div>
      </a>
      <a href="/cliente/assinaturas" class="nav-card">
        <div class="nav-card-icon">💳</div>
        <div class="nav-card-title">Assinaturas</div>
        <div class="nav-card-desc">Planos e histórico de pagamentos</div>
      </a>
      <a href="/cliente/ajuda" class="nav-card">
        <div class="nav-card-icon">📚</div>
        <div class="nav-card-title">Ajuda</div>
        <div class="nav-card-desc">Documentação e tutoriais</div>
      </a>
    </div>

  </div>

  <?php if (!$onboardingDone): ?>
  <div class="modal-overlay" id="onboardingModal" style="display:none;">
    <div class="modal-box">
      <h2>👋 Bem-vindo ao LRV Cloud!</h2>
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
  // Ativar modal via JS — garante que não trava se CSS falhar
  (function() {
    var m = document.getElementById('onboardingModal');
    if (m) { m.style.display = 'flex'; }
  })();
  function fecharOnboarding() {
    var m = document.getElementById('onboardingModal');
    if (m) { m.style.display = 'none'; }
    fetch('/cliente/onboarding/concluir', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf=<?php echo View::e(\LRV\Core\Csrf::token()); ?>'
    });
  }
  </script>
  <?php endif; ?>

  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>