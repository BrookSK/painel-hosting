<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\SistemaConfig;
use LRV\Core\I18n;

$_uri = (string)($_SERVER['REQUEST_URI'] ?? '');
$_seg = strtok($_uri, '?');

function _nav_ativo_cli(string $path, string $uri): string {
    return str_starts_with($uri, $path) ? ' nav-ativo' : '';
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <?php $_logo = SistemaConfig::logoUrl(); ?>
    <?php if ($_logo !== ''): ?>
      <img src="<?php echo View::e($_logo); ?>" alt="logo" style="height:32px;width:auto;" />
    <?php else: ?>
      <div class="sidebar-logo-icon">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect width="28" height="28" rx="8" fill="#4F46E5"/><path d="M7 14h14M14 7v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
      </div>
      <span class="sidebar-logo-text"><?php echo View::e(SistemaConfig::nome()); ?></span>
    <?php endif; ?>
    <button class="sidebar-toggle" id="sidebarToggle" title="<?php echo View::e(I18n::t('geral.recolher_menu')); ?>" aria-label="<?php echo View::e(I18n::t('geral.recolher_menu')); ?>"
      onclick="(function(){var s=document.getElementById('appShell');if(s){s.classList.toggle('collapsed');localStorage.setItem('lrv_cli_sidebar_collapsed',s.classList.contains('collapsed')?'1':'0');}})()">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M11 4L6 9l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
  </div>

  <nav class="sidebar-nav">
    <a href="/cliente/painel" class="nav-item<?php echo _nav_ativo_cli('/cliente/painel', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.painel')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="2" fill="currentColor" opacity=".9"/><rect x="11" y="2" width="7" height="7" rx="2" fill="currentColor" opacity=".5"/><rect x="2" y="11" width="7" height="7" rx="2" fill="currentColor" opacity=".5"/><rect x="11" y="11" width="7" height="7" rx="2" fill="currentColor" opacity=".9"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.painel')); ?></span>
    </a>
    <a href="/cliente/vps" class="nav-item<?php echo _nav_ativo_cli('/cliente/vps', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.vps')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.vps')); ?></span>
    </a>
    <a href="/cliente/monitoramento" class="nav-item<?php echo _nav_ativo_cli('/cliente/monitoramento', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.monitoramento')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M2 13l4-4 3 3 4-5 3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><rect x="2" y="3" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.monitoramento')); ?></span>
    </a>
    <a href="/cliente/tickets" class="nav-item<?php echo _nav_ativo_cli('/cliente/tickets', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.tickets')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M4 4h12a2 2 0 012 2v7a2 2 0 01-2 2H6l-4 3V6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.tickets')); ?></span>
    </a>
    <a href="/cliente/chat" class="nav-item<?php echo _nav_ativo_cli('/cliente/chat', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.chat')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M17 3H3a1 1 0 00-1 1v9a1 1 0 001 1h3l3 3 3-3h5a1 1 0 001-1V4a1 1 0 00-1-1z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.chat')); ?></span>
    </a>
    <a href="/cliente/emails" class="nav-item<?php echo _nav_ativo_cli('/cliente/emails', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.emails')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M2 7l8 5 8-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.emails')); ?></span>
    </a>
    <a href="/cliente/aplicacoes" class="nav-item<?php echo _nav_ativo_cli('/cliente/aplicacoes', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.aplicacoes')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.aplicacoes')); ?></span>
    </a>
    <a href="/cliente/aplicacoes/catalogo" class="nav-item<?php echo _nav_ativo_cli('/cliente/aplicacoes/catalogo', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.catalogo')); ?>" style="padding-left:32px;font-size:13px;">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none" style="width:16px;height:16px;"><rect x="2" y="2" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/><rect x="11" y="2" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/><rect x="2" y="11" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/><rect x="11" y="11" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.catalogo')); ?></span>
    </a>
    <a href="/cliente/assinaturas" class="nav-item<?php echo _nav_ativo_cli('/cliente/assinaturas', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.assinaturas')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M2 9h16" stroke="currentColor" stroke-width="1.6"/><path d="M6 13h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.assinaturas')); ?></span>
    </a>
    <a href="/cliente/ajuda" class="nav-item<?php echo _nav_ativo_cli('/cliente/ajuda', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.ajuda')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M10 11v-1a2 2 0 10-2-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="10" cy="14" r="1" fill="currentColor"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.ajuda')); ?></span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="/cliente/minha-conta" class="nav-item<?php echo _nav_ativo_cli('/cliente/minha-conta', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('geral.minha_conta')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="7" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M4 17c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('geral.minha_conta')); ?></span>
    </a>
    <a href="/cliente/2fa/configurar" class="nav-item<?php echo _nav_ativo_cli('/cliente/2fa', $_seg); ?>" data-tooltip="<?php echo View::e(I18n::t('sidebar.seguranca')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M10 2l6 2.5V10c0 3.5-2.5 6.5-6 8-3.5-1.5-6-4.5-6-8V4.5L10 2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('sidebar.seguranca')); ?></span>
    </a>
    <a href="/cliente/sair" class="nav-item nav-item-danger" data-tooltip="<?php echo View::e(I18n::t('geral.sair')); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M13 10H3M13 10l-3-3M13 10l-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 5H6a2 2 0 00-2 2v6a2 2 0 002 2h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('geral.sair')); ?></span>
    </a>
  </div>
</aside>
