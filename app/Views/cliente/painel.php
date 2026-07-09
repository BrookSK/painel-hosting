<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$nome           = (string)($cliente['name'] ?? '');
$email          = (string)($cliente['email'] ?? '');
$totalVps       = (int)($totalVps ?? 0);
$vpsRunning     = (int)($vpsRunning ?? 0);
$ticketsAbertos = (int)($ticketsAbertos ?? 0);
$assinatura     = $assinatura ?? null;
$onboardingDone = (bool)($onboardingDone ?? true);
$trialInfo      = $trialInfo ?? null;
$planoExclusivo = $planoExclusivo ?? null;

$clienteNome  = $nome;
$clienteEmail = $email;
$pageTitle    = I18n::t('painel.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<!-- Saudação -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::tf('painel.bem_vindo', explode(' ', $nome)[0])); ?> 👋</div>
    <div class="page-subtitle" style="margin-bottom:0;">Bem-vindo ao seu painel de controle</div>
  </div>
  <?php if ($assinatura !== null): ?>
    <span class="badge-new badge-green" style="font-size:12px;padding:5px 12px;"><?php echo View::e((string)($assinatura['plan_name'] ?? 'Plano ativo')); ?></span>
  <?php elseif ($planoExclusivo !== null): ?>
    <a href="/cliente/planos/checkout?plan_id=<?php echo (int)$planoExclusivo['id']; ?>" class="botao sm" style="background:#16a34a;">Assinar plano</a>
  <?php elseif (!(\LRV\Core\Auth::clienteGerenciado() && !\LRV\Core\Auth::estaImpersonando())): ?>
    <a href="/cliente/planos" class="botao sm"><?php echo View::e(I18n::t('home.ver_planos')); ?></a>
  <?php endif; ?>
</div>

<?php if (!empty($notificacoes)): ?>
<div class="card-new" style="margin-bottom:20px;border-left:4px solid #4F46E5;">
  <div class="card-new-title">Notificações (<?php echo count($notificacoes); ?>)</div>
  <?php foreach ($notificacoes as $n): ?>
    <div style="padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
      <div style="font-weight:600;margin-bottom:2px;"><?php echo View::e((string)($n['title'] ?? '')); ?></div>
      <div style="color:#64748b;"><?php echo View::e((string)($n['body'] ?? '')); ?></div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($planoExclusivo !== null && $assinatura === null): ?>
<div style="background:linear-gradient(135deg,#0B1C3D,#4F46E5);color:#fff;border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
  <div style="font-size:28px;flex-shrink:0;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></div>
  <div style="flex:1;min-width:180px;">
    <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Seu plano personalizado está pronto</div>
    <div style="font-size:13px;opacity:.8;">
      <?php echo View::e((string)$planoExclusivo['name']); ?> —
      <?php echo (int)$planoExclusivo['cpu']; ?> vCPU ·
      <?php echo round((int)$planoExclusivo['ram'] / 1024); ?> GB RAM ·
      <?php echo round((int)$planoExclusivo['storage'] / 1024); ?> GB Disco ·
      <?php echo View::e(\LRV\Core\I18n::preco((float)$planoExclusivo['price_monthly'])); ?>/mês
    </div>
  </div>
  <a href="/cliente/planos/checkout?plan_id=<?php echo (int)$planoExclusivo['id']; ?>" class="botao sm" style="background:#fff;color:#4F46E5;flex-shrink:0;">Assinar agora</a>
</div>
<?php endif; ?>

<?php if ($trialInfo !== null):
  $diasRestantes = (int)$trialInfo['dias_restantes'];
  $progressPct   = min(100, (int)round($diasRestantes / 7 * 100));
?>
<div style="background:linear-gradient(135deg,#0B1C3D,#4F46E5);color:#fff;border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
  <div style="font-size:28px;flex-shrink:0;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div>
  <div style="flex:1;min-width:180px;">
    <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Período de teste — <?php echo $diasRestantes; ?> dia<?php echo $diasRestantes !== 1 ? 's' : ''; ?> restante<?php echo $diasRestantes !== 1 ? 's' : ''; ?></div>
    <div style="font-size:13px;opacity:.8;margin-bottom:10px;"><?php echo (int)$trialInfo['vcpu']; ?> vCPU · <?php echo (int)$trialInfo['ram_mb']; ?> MB RAM · <?php echo (int)$trialInfo['disco_gb']; ?> GB disco · Expira em <?php echo View::e(date('d/m/Y', strtotime((string)$trialInfo['expires_at']))); ?></div>
    <div style="background:rgba(255,255,255,.2);border-radius:999px;height:6px;overflow:hidden;"><div style="background:#a5b4fc;height:100%;border-radius:999px;width:<?php echo $progressPct; ?>%;"></div></div>
  </div>
  <a href="/cliente/planos" class="botao sm" style="background:#fff;color:#4F46E5;flex-shrink:0;">Assinar plano</a>
</div>
<?php endif; ?>

<!-- Stats -->
<?php $_isManagedStats = \LRV\Core\Auth::clienteGerenciado() && !\LRV\Core\Auth::estaImpersonando(); ?>
<div class="stats-grid" style="grid-template-columns:repeat(<?php echo $_isManagedStats ? '2' : '4'; ?>,1fr);margin-bottom:24px;">
  <?php if (!$_isManagedStats): ?>
  <div class="stat-card-new">
    <div class="stat-card-header">
      <span class="stat-card-label"><?php echo View::e(I18n::t('painel.total_vps')); ?></span>
      <div class="stat-card-icon blue">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/></svg>
      </div>
    </div>
    <div class="stat-card-value"><?php echo $totalVps; ?></div>
  </div>
  <div class="stat-card-new">
    <div class="stat-card-header">
      <span class="stat-card-label"><?php echo View::e(I18n::t('vps.status_running')); ?></span>
      <div class="stat-card-icon green">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M8 7l5 3-5 3V7z" fill="currentColor"/></svg>
      </div>
    </div>
    <div class="stat-card-value" style="color:#16a34a;"><?php echo $vpsRunning; ?></div>
  </div>
  <?php endif; ?>
  <div class="stat-card-new">
    <div class="stat-card-header">
      <span class="stat-card-label"><?php echo View::e(I18n::t('painel.tickets_abertos')); ?></span>
      <div class="stat-card-icon <?php echo $ticketsAbertos > 0 ? 'orange' : 'green'; ?>">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4 4h12a2 2 0 012 2v7a2 2 0 01-2 2H6l-4 3V6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
      </div>
    </div>
    <div class="stat-card-value" style="color:<?php echo $ticketsAbertos > 0 ? '#ea580c' : '#16a34a'; ?>;"><?php echo $ticketsAbertos; ?></div>
  </div>
  <div class="stat-card-new">
    <div class="stat-card-header">
      <span class="stat-card-label"><?php echo View::e(I18n::t('assinaturas.plano')); ?></span>
      <div class="stat-card-icon purple">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M2 9h16" stroke="currentColor" stroke-width="1.6"/></svg>
      </div>
    </div>
    <?php if ($assinatura !== null): ?>
      <div class="stat-card-value sm" style="color:#16a34a;">Ativo</div>
      <div class="stat-card-sub"><?php echo View::e((string)($assinatura['plan_name'] ?? '')); ?></div>
    <?php else: ?>
      <div class="stat-card-value sm" style="color:#94a3b8;">—</div>
      <div class="stat-card-sub"><?php echo View::e(I18n::t('painel.nenhum_plano')); ?></div>
    <?php endif; ?>
  </div>
</div>

<!-- Cards de navegação -->
<?php
  $planLimits = $planLimits ?? null;
  if ($planLimits !== null):
    $badge = $planLimits['badge'];
?>
<div class="card-new" style="margin-bottom:20px;padding:20px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:20px;"><?php echo $badge[3]; ?></span>
      <div>
        <div style="font-size:14px;font-weight:700;color:#0f172a;">Uso do plano</div>
        <div style="font-size:12px;color:#64748b;"><?php echo View::e($planLimits['plan_name']); ?> — <span style="background:<?php echo $badge[1]; ?>;color:<?php echo $badge[2]; ?>;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;"><?php echo View::e($badge[0]); ?></span></div>
      </div>
    </div>
    <a href="/cliente/planos" class="botao ghost sm" style="font-size:12px;">Upgrade</a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
    <?php
      $usageItems = [
        ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>', 'Sites / Apps', $planLimits['sites']['atual'], $planLimits['sites']['max']],
        ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>', 'Bancos de dados', $planLimits['databases']['atual'], $planLimits['databases']['max']],
        ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'Cron Jobs', $planLimits['cron_jobs']['atual'], $planLimits['cron_jobs']['max']],
      ];
      foreach ($usageItems as [$uIcon, $uLabel, $uAtual, $uMax]):
        if ($uMax === null) continue; // Sem limite = não mostrar
        $pct = $uMax > 0 ? min(100, (int)round($uAtual / $uMax * 100)) : 0;
        $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#4F46E5');
    ?>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;">
      <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
        <span style="font-size:14px;"><?php echo $uIcon; ?></span>
        <span style="font-size:12px;font-weight:600;color:#334155;"><?php echo View::e($uLabel); ?></span>
      </div>
      <div style="font-size:18px;font-weight:800;color:#0f172a;margin-bottom:4px;"><?php echo $uAtual; ?> <span style="font-size:12px;font-weight:400;color:#64748b;">/ <?php echo $uMax; ?></span></div>
      <div style="background:#e2e8f0;border-radius:999px;height:5px;overflow:hidden;">
        <div style="background:<?php echo $barColor; ?>;height:100%;border-radius:999px;width:<?php echo $pct; ?>%;transition:width .3s;"></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
  <?php
  $navCards = [
    ['/cliente/vps',          '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',  'Minhas VPS',    'Gerencie seus servidores virtuais',    'vps'],
    ['/cliente/monitoramento','<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',  'Monitoramento', 'CPU, RAM e disco em tempo real',        'monitoramento'],
    ['/cliente/tickets',      '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',  'Tickets',       'Suporte técnico e solicitações',        null],
    ['/cliente/emails',       '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg>',  'E-mails',       'Gerenciar caixas de entrada',           'emails'],
    ['/cliente/aplicacoes',   '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>',  'Aplicações',    'Deploy e gerenciamento de apps',        'aplicacoes'],
    ['/cliente/assinaturas',  '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',  'Assinaturas',   'Planos e histórico de pagamentos',      null],
    ['/cliente/ajuda',        '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',  'Ajuda',         'Documentação e tutoriais',               null],
  ];
  // Cliente gerenciado vê apenas tickets e assinaturas
  $_isManagedPainel = \LRV\Core\Auth::clienteGerenciado() && !\LRV\Core\Auth::estaImpersonando();
  if ($_isManagedPainel) {
    $navCards = [
      ['/cliente/vps',          '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',  'Minhas VPS',    'Gerencie seus servidores virtuais',  'vps'],
      ['/cliente/monitoramento','<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',  'Monitoramento', 'CPU, RAM e disco em tempo real',      'monitoramento'],
      ['/cliente/tickets',     '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',  'Tickets',      'Suporte técnico e solicitações',        null],
      ['/cliente/assinaturas', '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',  'Assinaturas',  'Planos e histórico de pagamentos',      null],
    ];
  }
  // Filtrar por features do plano
  $_clienteIdPainel = \LRV\Core\Auth::clienteId();
  $_featuresPainel = [];
  if ($_clienteIdPainel !== null) {
      try { $_featuresPainel = \LRV\App\Services\Plans\PlanFeatureService::featuresPermitidas($_clienteIdPainel); } catch (\Throwable) {}
  }
  foreach ($navCards as [$href, $icon, $title, $desc, $requiredFeature]):
    if ($requiredFeature !== null && !empty($_featuresPainel) && !in_array($requiredFeature, $_featuresPainel, true)) continue;
  ?>
  <a href="<?php echo $href; ?>" style="background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:20px;text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:8px;transition:border-color .15s,box-shadow .15s,transform .15s;"
     onmouseover="this.style.borderColor='#4F46E5';this.style.boxShadow='0 4px 20px rgba(79,70,229,.1)';this.style.transform='translateY(-2px)'"
     onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.transform='none'">
    <div style="width:40px;height:40px;border-radius:11px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:20px;"><?php echo $icon; ?></div>
    <div style="font-weight:700;font-size:14px;color:#0f172a;"><?php echo View::e($title); ?></div>
    <div style="font-size:12px;color:#64748b;line-height:1.5;"><?php echo View::e($desc); ?></div>
  </a>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>

<?php if (!$onboardingDone): ?>
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:999;" id="onboardingModal">
  <div style="background:#fff;border-radius:20px;padding:36px 32px;max-width:480px;width:90%;">
    <div style="font-size:22px;font-weight:800;margin-bottom:10px;color:#0B1C3D;">👋 Bem-vindo!</div>
    <p style="font-size:14px;color:#475569;margin-bottom:8px;line-height:1.6;">Sua conta está pronta. Veja como começar:</p>
    <ul style="list-style:none;padding:0;margin:14px 0 22px;">
      <?php foreach (['Escolha um plano em Planos','Após assinar, sua VPS será provisionada automaticamente','Acesse o Terminal direto pelo painel','Use Tickets ou Chat para suporte'] as $i => $step): ?>
      <li style="display:flex;align-items:center;gap:12px;font-size:14px;padding:8px 0;border-bottom:1px solid #f1f5f9;">
        <span style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?php echo $i+1; ?></span>
        <?php echo View::e($step); ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <button class="botao" onclick="fecharOnboarding()">Entendido, vamos lá!</button>
  </div>
</div>
<script>
function fecharOnboarding(){
  document.getElementById('onboardingModal').style.display='none';
  fetch('/cliente/onboarding/concluir',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf=<?php echo View::e(\LRV\Core\Csrf::token()); ?>'});
}
</script>
<?php endif; ?>
