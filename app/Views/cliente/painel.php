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
  <div style="font-size:28px;flex-shrink:0;">📋</div>
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
  <div style="font-size:28px;flex-shrink:0;">🚀</div>
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
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
  <?php
  $navCards = [
    ['/cliente/vps',          '🖥️',  'Minhas VPS',    'Gerencie seus servidores virtuais'],
    ['/cliente/monitoramento','📊',  'Monitoramento', 'CPU, RAM e disco em tempo real'],
    ['/cliente/tickets',      '🎫',  'Tickets',       'Suporte técnico e solicitações'],
    ['/cliente/emails',       '📧',  'E-mails',       'Gerenciar caixas de entrada'],
    ['/cliente/aplicacoes',   '🚀',  'Aplicações',    'Deploy e gerenciamento de apps'],
    ['/cliente/assinaturas',  '💳',  'Assinaturas',   'Planos e histórico de pagamentos'],
    ['/cliente/ajuda',        '📚',  'Ajuda',         'Documentação e tutoriais'],
  ];
  // Cliente gerenciado vê apenas tickets e assinaturas
  $_isManagedPainel = \LRV\Core\Auth::clienteGerenciado() && !\LRV\Core\Auth::estaImpersonando();
  if ($_isManagedPainel) {
    $navCards = [
      ['/cliente/vps',          '🖥️',  'Minhas VPS',    'Gerencie seus servidores virtuais'],
      ['/cliente/monitoramento','📊',  'Monitoramento', 'CPU, RAM e disco em tempo real'],
      ['/cliente/tickets',     '🎫',  'Tickets',      'Suporte técnico e solicitações'],
      ['/cliente/assinaturas', '💳',  'Assinaturas',  'Planos e histórico de pagamentos'],
    ];
  }
  foreach ($navCards as [$href, $icon, $title, $desc]):
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
