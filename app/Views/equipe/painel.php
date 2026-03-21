<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;

$nome  = (string) ($usuario['name'] ?? '');
$email = (string) ($usuario['email'] ?? '');
$role  = (string) ($usuario['role'] ?? '');

$totalVps      = (int) ($metricas['total_vps'] ?? 0);
$vpsRunning    = (int) ($metricas['vps_running'] ?? 0);
$totalClientes = (int) ($metricas['total_clientes'] ?? 0);
$totalTickets  = (int) ($metricas['tickets_abertos'] ?? 0);
$jobsPendentes = (int) ($metricas['jobs_pendentes'] ?? 0);
$nodesOnline   = (int) ($metricas['nodes_online'] ?? 0);
$receitaMensal = (string) ($metricas['receita_mensal'] ?? '0.00');

$initials = '';
foreach (explode(' ', trim($nome)) as $w) {
    $initials .= strtoupper(substr($w, 0, 1));
    if (strlen($initials) >= 2) break;
}
if ($initials === '') $initials = 'U';
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo View::e(I18n::t('eq_painel.titulo')); ?></title>
  <?php require __DIR__ . '/../_partials/estilo-equipe.php'; ?>
</head>
<body>
<div class="app-shell" id="appShell">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <?php require __DIR__ . '/../_partials/sidebar-equipe.php'; ?>
  <div class="app-main">
    <?php require __DIR__ . '/../_partials/header-equipe.php'; ?>
    <div class="page-content">
      <div class="page-title"><?php echo View::e(I18n::t('eq_painel.titulo')); ?></div>
      <div class="page-subtitle"><?php echo View::e(I18n::tf('eq_painel.subtitulo', $nome)); ?></div>

      <div class="stats-grid">
        <div class="stat-card-new">
          <div class="stat-card-header">
            <span class="stat-card-label"><?php echo View::e(I18n::t('eq_painel.vps_ativas')); ?></span>
            <div class="stat-card-icon blue">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
            </div>
          </div>
          <div class="stat-card-value"><?php echo $vpsRunning; ?></div>
          <div class="stat-card-sub"><?php echo View::e(I18n::tf('eq_painel.de_total', (string)$totalVps)); ?></div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card-header">
            <span class="stat-card-label"><?php echo View::e(I18n::t('eq_painel.clientes')); ?></span>
            <div class="stat-card-icon purple">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><circle cx="8" cy="6" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M2 17c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M14 8c1.657 0 3 1.343 3 3v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="14" cy="5" r="2" stroke="currentColor" stroke-width="1.6"/></svg>
            </div>
          </div>
          <div class="stat-card-value"><?php echo $totalClientes; ?></div>
          <div class="stat-card-sub"><?php echo View::e(I18n::t('eq_painel.cadastrados')); ?></div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card-header">
            <span class="stat-card-label"><?php echo View::e(I18n::t('eq_painel.tickets_abertos')); ?></span>
            <div class="stat-card-icon orange">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M4 4h12a2 2 0 012 2v7a2 2 0 01-2 2H6l-4 3V6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </div>
          </div>
          <div class="stat-card-value"><?php echo $totalTickets; ?></div>
          <div class="stat-card-sub"><?php echo View::e(I18n::t('eq_painel.aguardando_resposta')); ?></div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card-header">
            <span class="stat-card-label"><?php echo View::e(I18n::t('eq_painel.jobs_pendentes')); ?></span>
            <div class="stat-card-icon red">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M10 6v4l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
          </div>
          <div class="stat-card-value"><?php echo $jobsPendentes; ?></div>
          <div class="stat-card-sub"><?php echo View::e(I18n::t('eq_painel.na_fila')); ?></div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card-header">
            <span class="stat-card-label"><?php echo View::e(I18n::t('eq_painel.nodes_online')); ?></span>
            <div class="stat-card-icon green">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" stroke="currentColor" stroke-width="1.6"/><path d="M7 10l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
          </div>
          <div class="stat-card-value"><?php echo $nodesOnline; ?></div>
          <div class="stat-card-sub"><?php echo View::e(I18n::t('eq_painel.servidores_ativos')); ?></div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card-header">
            <span class="stat-card-label"><?php echo View::e(I18n::t('eq_painel.receita_mensal')); ?></span>
            <div class="stat-card-icon indigo">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M2 9h16" stroke="currentColor" stroke-width="1.6"/><path d="M6 13h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
          </div>
          <div class="stat-card-value sm"><?php echo View::e(I18n::moeda()); ?>&nbsp;<?php echo View::e($receitaMensal); ?></div>
          <div class="stat-card-sub"><?php echo View::e(I18n::t('eq_painel.assinaturas_ativas')); ?></div>
        </div>
      </div>

      <div class="content-grid">
        <div class="card-new">
          <div class="card-new-title">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1v14M1 8h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <?php echo View::e(I18n::t('eq_painel.acoes_rapidas')); ?>
          </div>
          <div class="quick-actions">
            <a href="/equipe/vps" class="quick-action-btn">
              <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="#4F46E5" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="#4F46E5" stroke-width="1.6"/></svg></div>
              <?php echo View::e(I18n::t('eq_painel.gerenciar_vps')); ?>
            </a>
            <a href="/equipe/tickets" class="quick-action-btn">
              <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4 4h12a2 2 0 012 2v7a2 2 0 01-2 2H6l-4 3V6a2 2 0 012-2z" stroke="#4F46E5" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
              <?php echo View::e(I18n::t('eq_painel.ver_tickets')); ?>
            </a>
            <a href="/equipe/usuarios" class="quick-action-btn">
              <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><circle cx="8" cy="6" r="3" stroke="#4F46E5" stroke-width="1.6"/><path d="M2 17c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round"/></svg></div>
              <?php echo View::e(I18n::t('eq_painel.clientes')); ?>
            </a>
            <a href="/equipe/servidores" class="quick-action-btn">
              <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" stroke="#4F46E5" stroke-width="1.6"/></svg></div>
              <?php echo View::e(I18n::t('equipe.servidores')); ?>
            </a>
            <a href="/equipe/monitoramento" class="quick-action-btn">
              <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M2 13l4-4 3 3 4-5 3 3" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
              <?php echo View::e(I18n::t('equipe.monitoramento')); ?>
            </a>
            <a href="/equipe/assinaturas" class="quick-action-btn">
              <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="12" rx="2" stroke="#4F46E5" stroke-width="1.6"/><path d="M2 9h16" stroke="#4F46E5" stroke-width="1.6"/></svg></div>
              <?php echo View::e(I18n::t('equipe.assinaturas')); ?>
            </a>
          </div>
        </div>

        <div class="card-new">
          <div class="card-new-title">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M2 14c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <?php echo View::e(I18n::t('eq_painel.seu_acesso')); ?>
          </div>
          <div class="user-card-avatar"><?php echo View::e($initials); ?></div>
          <div class="user-card-name"><?php echo View::e($nome); ?></div>
          <div class="user-card-email"><?php echo View::e($email); ?></div>
          <div>
            <span class="user-card-badge">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><circle cx="5" cy="5" r="4" stroke="currentColor" stroke-width="1.2"/><path d="M3 5l1.5 1.5L7 3.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <?php echo View::e($role); ?>
            </span>
          </div>
          <div class="user-card-actions">
            <a href="/equipe/2fa/configurar" class="btn-sm btn-primary">
              <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="2" y="5.5" width="8" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/><path d="M4 5.5V4a2 2 0 014 0v1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
              <?php echo View::e(I18n::t('eq_painel.seguranca')); ?>
            </a>
            <a href="/equipe/permissoes" class="btn-sm btn-outline"><?php echo View::e(I18n::t('eq_painel.permissoes')); ?></a>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:4px;">
        <a href="/equipe/jobs" class="quick-action-btn" style="flex:none;">
          <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="#4F46E5" stroke-width="1.6"/><path d="M10 6v4l3 2" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round"/></svg></div>
          Jobs
        </a>
        <a href="/equipe/chat" class="quick-action-btn" style="flex:none;">
          <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M17 3H3a1 1 0 00-1 1v9a1 1 0 001 1h3l3 3 3-3h5a1 1 0 001-1V4a1 1 0 00-1-1z" stroke="#4F46E5" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
          <?php echo View::e(I18n::t('eq_painel.chat_vivo')); ?>
        </a>
        <a href="/equipe/backups" class="quick-action-btn" style="flex:none;">
          <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M10 3v10M6 9l4 4 4-4" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 15h12" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round"/></svg></div>
          <?php echo View::e(I18n::t('equipe.backups')); ?>
        </a>
        <a href="/equipe/terminal" class="quick-action-btn" style="flex:none;">
          <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><rect x="2" y="4" width="16" height="12" rx="2" stroke="#4F46E5" stroke-width="1.6"/><path d="M6 8l3 2-3 2M11 12h3" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
          <?php echo View::e(I18n::t('equipe.terminal')); ?>
        </a>
        <a href="/equipe/inicializacao" class="quick-action-btn" style="flex:none;">
          <div class="quick-action-icon"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="2.5" stroke="#4F46E5" stroke-width="1.6"/><path d="M10 2v2M10 16v2M2 10h2M16 10h2" stroke="#4F46E5" stroke-width="1.6" stroke-linecap="round"/></svg></div>
          <?php echo View::e(I18n::t('eq_painel.inicializacao')); ?>
        </a>
      </div>

    </div>
    <?php require __DIR__ . '/../_partials/footer.php'; ?>
  </div>
</div>

<script>
(function () {
  var shell    = document.getElementById('appShell');
  var toggle   = document.getElementById('sidebarToggle');
  var mobileBtn = document.getElementById('mobileMenuBtn');
  var overlay  = document.getElementById('sidebarOverlay');
  var sidebar  = document.getElementById('sidebar');

  if (localStorage.getItem('lrv_sidebar_collapsed') === '1') {
    shell.classList.add('collapsed');
  }

  if (toggle) {
    toggle.addEventListener('click', function () {
      shell.classList.toggle('collapsed');
      localStorage.setItem('lrv_sidebar_collapsed', shell.classList.contains('collapsed') ? '1' : '0');
    });
  }

  function openMobile() {
    sidebar.classList.add('mobile-open');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeMobile() {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }
  if (mobileBtn) mobileBtn.addEventListener('click', openMobile);
  if (overlay) overlay.addEventListener('click', closeMobile);

  var avatarWrap = document.getElementById('avatarMenu');
  var avatarDrop = document.getElementById('avatarDropdown');
  if (avatarWrap && avatarDrop) {
    avatarWrap.addEventListener('click', function (e) {
      e.stopPropagation();
      avatarDrop.classList.toggle('open');
    });
    document.addEventListener('click', function () {
      avatarDrop.classList.remove('open');
    });
  }
})();
</script>
</body>
</html>
