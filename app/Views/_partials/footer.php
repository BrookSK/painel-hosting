<?php
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\I18n;
use LRV\Core\View;
$_ft_copyright = SistemaConfig::copyrightText();
$_ft_nome      = SistemaConfig::nome();
?>
<?php require __DIR__ . '/chat-widget.php'; ?>
<?php require __DIR__ . '/cookie-banner.php'; ?>
<footer class="pub-footer">
  <div class="pub-footer-inner">
    <div class="pub-footer-grid">
      <div>
        <div class="pub-footer-brand"><?php echo View::e($_ft_nome); ?></div>
        <div class="pub-footer-desc"><?php echo View::e(I18n::t('footer.desc')); ?></div>
      </div>
      <div>
        <div class="pub-footer-col-title"><?php echo View::e(I18n::t('footer.produto')); ?></div>
        <ul class="pub-footer-links">
          <li><a href="/#funcionalidades"><?php echo View::e(I18n::t('footer.funcionalidades')); ?></a></li>
          <li><a href="/infraestrutura#planos"><?php echo View::e(I18n::t('footer.planos')); ?></a></li>
          <li><a href="/changelog"><?php echo View::e(I18n::t('footer.changelog')); ?></a></li>
          <li><a href="/status"><?php echo View::e(I18n::t('footer.status')); ?></a></li>
        </ul>
      </div>
      <div>
        <div class="pub-footer-col-title"><?php echo View::e(I18n::t('footer.suporte')); ?></div>
        <ul class="pub-footer-links">
          <li><a href="/contato"><?php echo View::e(I18n::t('footer.contato')); ?></a></li>
          <li><a href="/cliente/ajuda"><?php echo View::e(I18n::t('footer.central_ajuda')); ?></a></li>
          <li><a href="/cliente/tickets"><?php echo View::e(I18n::t('footer.tickets')); ?></a></li>
        </ul>
      </div>
      <div>
        <div class="pub-footer-col-title"><?php echo View::e(I18n::t('footer.legal')); ?></div>
        <ul class="pub-footer-links">
          <li><a href="/termos"><?php echo View::e(I18n::t('footer.termos')); ?></a></li>
          <li><a href="/privacidade"><?php echo View::e(I18n::t('footer.privacidade')); ?></a></li>
          <li><a href="/licenca"><?php echo View::e(I18n::t('footer.licenca')); ?></a></li>
          <li><a href="#" onclick="ckAbrirModal();return false"><?php echo View::e(I18n::t('footer.cookies')); ?></a></li>
        </ul>
      </div>
    </div>
    <div class="pub-footer-bottom">
      <span><?php echo View::e($_ft_copyright); ?> · <?php echo View::e($_ft_nome); ?> v<?php echo View::e(SistemaConfig::versao()); ?></span>
      <span class="pub-footer-status-dot"><?php echo View::e(I18n::t('footer.sistemas_op')); ?></span>
    </div>
  </div>
</footer>
<style>
.pub-footer{background:#060d1f;color:rgba(255,255,255,.45);padding:52px 24px 28px}
.pub-footer-inner{max-width:1100px;margin:0 auto}
.pub-footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:36px;margin-bottom:40px}
.pub-footer-brand{font-size:15px;font-weight:700;color:#fff;margin-bottom:8px}
.pub-footer-desc{font-size:12px;line-height:1.7;max-width:240px}
.pub-footer-col-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.3);margin-bottom:12px}
.pub-footer-links{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px}
.pub-footer-links a{color:rgba(255,255,255,.45);text-decoration:none;font-size:13px;transition:color .15s}
.pub-footer-links a:hover{color:#fff}
.pub-footer-bottom{border-top:1px solid rgba(255,255,255,.07);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;font-size:12px}
.pub-footer-status-dot{display:inline-flex;align-items:center;gap:6px}
.pub-footer-status-dot::before{content:'';width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block}
@media(max-width:768px){.pub-footer-grid{grid-template-columns:1fr 1fr;gap:24px}}
@media(max-width:480px){.pub-footer-grid{grid-template-columns:1fr}}
</style>
