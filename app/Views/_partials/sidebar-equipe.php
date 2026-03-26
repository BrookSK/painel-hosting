<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;

$_uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
$_seg = strtok($_uri, '?');

function _nav_ativo(string $path, string $uri): string {
    return str_starts_with($uri, $path) ? ' nav-ativo' : '';
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <?php $_logo = SistemaConfig::logoUrl(); $_favicon = SistemaConfig::faviconUrl(); ?>
    <?php if ($_logo !== ''): ?>
      <img src="<?php echo View::e($_logo); ?>" alt="logo" style="height:32px;width:auto;" />
      <?php if ($_favicon !== ''): ?>
        <img src="<?php echo View::e($_favicon); ?>" alt="icon" class="sidebar-favicon" style="display:none;height:28px;width:28px;object-fit:contain;" />
      <?php else: ?>
        <div class="sidebar-favicon" style="display:none;width:28px;height:28px;border-radius:8px;background:#4F46E5;display:none;align-items:center;justify-content:center;">
          <svg width="16" height="16" viewBox="0 0 28 28" fill="none"><path d="M7 14h14M14 7v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="sidebar-logo-icon">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect width="28" height="28" rx="8" fill="#4F46E5"/><path d="M7 14h14M14 7v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
      </div>
      <span class="sidebar-logo-text"><?php echo View::e(SistemaConfig::nome()); ?></span>
    <?php endif; ?>
    <button class="sidebar-toggle" id="sidebarToggle" title="<?php echo View::e(I18n::t('geral.recolher_menu')); ?>" aria-label="<?php echo View::e(I18n::t('geral.recolher_menu')); ?>">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M11 4L6 9l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label"><?php echo View::e(I18n::t('geral.menu')); ?></div>

    <a href="/equipe/painel" class="nav-item<?php echo _nav_ativo('/equipe/painel', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="2" fill="currentColor" opacity=".9"/><rect x="11" y="2" width="7" height="7" rx="2" fill="currentColor" opacity=".5"/><rect x="2" y="11" width="7" height="7" rx="2" fill="currentColor" opacity=".5"/><rect x="11" y="11" width="7" height="7" rx="2" fill="currentColor" opacity=".9"/></svg>
      <span><?php echo View::e(I18n::t('equipe.painel')); ?></span>
    </a>

    <a href="/equipe/usuarios" class="nav-item<?php echo _nav_ativo('/equipe/usuarios', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="8" cy="6" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M2 17c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M14 8c1.657 0 3 1.343 3 3v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="14" cy="5" r="2" stroke="currentColor" stroke-width="1.6"/></svg>
      <span><?php echo View::e(I18n::t('equipe.usuarios')); ?></span>
    </a>

    <a href="/equipe/clientes" class="nav-item<?php echo _nav_ativo('/equipe/clientes', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="8" cy="6" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M2 17c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M16 11v6M13 14h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.clientes')); ?></span>
    </a>

    <a href="/equipe/vps" class="nav-item<?php echo _nav_ativo('/equipe/vps', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
      <span><?php echo View::e(I18n::t('equipe.vps')); ?></span>
    </a>

    <a href="/equipe/servidores" class="nav-item<?php echo _nav_ativo('/equipe/servidores', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" stroke="currentColor" stroke-width="1.6"/><path d="M7 10h6M10 7v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.servidores')); ?></span>
    </a>

    <a href="/equipe/monitoramento" class="nav-item<?php echo _nav_ativo('/equipe/monitoramento', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M2 13l4-4 3 3 4-5 3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><rect x="2" y="3" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/></svg>
      <span><?php echo View::e(I18n::t('equipe.monitoramento')); ?></span>
    </a>

    <a href="/equipe/assinaturas" class="nav-item<?php echo _nav_ativo('/equipe/assinaturas', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M2 9h16" stroke="currentColor" stroke-width="1.6"/><path d="M6 13h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.assinaturas')); ?></span>
    </a>

    <a href="/equipe/tickets" class="nav-item<?php echo _nav_ativo('/equipe/tickets', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M4 4h12a2 2 0 012 2v7a2 2 0 01-2 2H6l-4 3V6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.tickets')); ?></span>
      <?php if (!empty($notif_tickets) && (int)$notif_tickets > 0): ?>
        <span class="nav-badge"><?php echo (int)$notif_tickets; ?></span>
      <?php endif; ?>
    </a>

    <a href="/equipe/chat" class="nav-item<?php echo _nav_ativo('/equipe/chat', $_seg); if (str_starts_with($_seg, '/equipe/chat-flows')) echo ' '; ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M17 3H3a1 1 0 00-1 1v9a1 1 0 001 1h3l3 3 3-3h5a1 1 0 001-1V4a1 1 0 00-1-1z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.chat')); ?></span>
    </a>

    <a href="/equipe/chat-flows" class="nav-item<?php echo _nav_ativo('/equipe/chat-flows', $_seg); ?>" style="padding-left:36px;font-size:13px;">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none" style="width:16px;height:16px;"><path d="M3 4h14M3 8h10M3 12h14M3 16h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.chat_flows')); ?></span>
    </a>

    <a href="/equipe/satisfacao" class="nav-item<?php echo _nav_ativo('/equipe/satisfacao', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M7 11s1 2 3 2 3-2 3-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="7.5" cy="8.5" r="1" fill="currentColor"/><circle cx="12.5" cy="8.5" r="1" fill="currentColor"/></svg>
      <span><?php echo View::e(I18n::t('equipe.satisfacao')); ?></span>
    </a>

    <div class="sidebar-section-label" style="margin-top:8px;"><?php echo View::e(I18n::t('equipe.operacoes')); ?></div>

    <a href="/equipe/jobs" class="nav-item<?php echo _nav_ativo('/equipe/jobs', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M10 6v4l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.jobs')); ?></span>
    </a>

    <a href="/equipe/aplicacoes" class="nav-item<?php echo _nav_ativo('/equipe/aplicacoes', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.aplicacoes')); ?></span>
    </a>

    <a href="/equipe/backups" class="nav-item<?php echo _nav_ativo('/equipe/backups', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M10 3v10M6 9l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 15h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.backups')); ?></span>
    </a>

    <a href="/equipe/terminal" class="nav-item<?php echo _nav_ativo('/equipe/terminal', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="4" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M6 8l3 2-3 2M11 12h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.terminal')); ?></span>
    </a>

    <a href="/equipe/planos" class="nav-item<?php echo _nav_ativo('/equipe/planos', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M10 2l2.4 5 5.6.8-4 3.9.9 5.5L10 14.5l-4.9 2.7.9-5.5L2 7.8l5.6-.8L10 2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.planos')); ?></span>
    </a>

    <a href="/equipe/emails" class="nav-item<?php echo _nav_ativo('/equipe/emails', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><rect x="2" y="5" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M2 7l8 5 8-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.emails')); ?></span>
    </a>

    <a href="/equipe/erros" class="nav-item<?php echo _nav_ativo('/equipe/erros', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M10 7v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="10" cy="13.5" r="1" fill="currentColor"/></svg>
      <span><?php echo View::e(I18n::t('equipe.erros')); ?></span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="/equipe/minha-conta" class="nav-item<?php echo _nav_ativo('/equipe/minha-conta', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="7" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M4 17c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('geral.minha_conta')); ?></span>
    </a>
    <a href="/equipe/configuracoes" class="nav-item<?php echo _nav_ativo('/equipe/configuracoes', $_seg); ?>">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.6"/><path d="M10 2v2M10 16v2M2 10h2M16 10h2M4.22 4.22l1.42 1.42M14.36 14.36l1.42 1.42M4.22 15.78l1.42-1.42M14.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('equipe.configuracoes')); ?></span>
    </a>
    <a href="/equipe/sair" class="nav-item nav-item-danger">
      <svg class="nav-icon" viewBox="0 0 20 20" fill="none"><path d="M13 10H3M13 10l-3-3M13 10l-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 5H6a2 2 0 00-2 2v6a2 2 0 002 2h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      <span><?php echo View::e(I18n::t('geral.sair')); ?></span>
    </a>
  </div>
</aside>
