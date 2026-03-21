<?php
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\View;
$_ft_copyright = SistemaConfig::copyrightText();
$_ft_nome      = SistemaConfig::nome();
$_ft_empresa   = SistemaConfig::empresaNome();
?>
<?php require __DIR__ . '/chat-widget.php'; ?>
<footer class="pub-footer">
  <div class="pub-footer-inner">
    <div class="pub-footer-grid">
      <div>
        <div class="pub-footer-brand"><?php echo View::e($_ft_nome); ?></div>
        <div class="pub-footer-desc">Infraestrutura cloud gerenciada. VPS, deploy, e-mails, backups e suporte em um único painel.</div>
      </div>
      <div>
        <div class="pub-footer-col-title">Produto</div>
        <ul class="pub-footer-links">
          <li><a href="/#funcionalidades">Funcionalidades</a></li>
          <li><a href="/#planos">Planos</a></li>
          <li><a href="/changelog">Changelog</a></li>
          <li><a href="/status">Status</a></li>
        </ul>
      </div>
      <div>
        <div class="pub-footer-col-title">Suporte</div>
        <ul class="pub-footer-links">
          <li><a href="/contato">Contato</a></li>
          <li><a href="/cliente/ajuda">Central de ajuda</a></li>
          <li><a href="/cliente/tickets">Tickets</a></li>
        </ul>
      </div>
      <div>
        <div class="pub-footer-col-title">Legal</div>
        <ul class="pub-footer-links">
          <li><a href="/termos">Termos de uso</a></li>
          <li><a href="/privacidade">Privacidade</a></li>
        </ul>
      </div>
    </div>
    <div class="pub-footer-bottom">
      <span><?php echo View::e($_ft_copyright); ?> · <?php echo View::e($_ft_nome); ?> v<?php echo View::e(SistemaConfig::versao()); ?></span>
      <span class="pub-footer-status-dot">Todos os sistemas operacionais</span>
    </div>
  </div>
</footer>
<style>
.pub-footer{background:#060d1f;color:rgba(255,255,255,.45);padding:52px 24px 28px;}
.pub-footer-inner{max-width:1100px;margin:0 auto;}
.pub-footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:36px;margin-bottom:40px;}
.pub-footer-brand{font-size:15px;font-weight:700;color:#fff;margin-bottom:8px;}
.pub-footer-desc{font-size:12px;line-height:1.7;max-width:240px;}
.pub-footer-col-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.3);margin-bottom:12px;}
.pub-footer-links{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;}
.pub-footer-links a{color:rgba(255,255,255,.45);text-decoration:none;font-size:13px;transition:color .15s;}
.pub-footer-links a:hover{color:#fff;}
.pub-footer-bottom{border-top:1px solid rgba(255,255,255,.07);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;font-size:12px;}
.pub-footer-status-dot{display:inline-flex;align-items:center;gap:6px;}
.pub-footer-status-dot::before{content:'';width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;}
@media(max-width:768px){.pub-footer-grid{grid-template-columns:1fr 1fr;gap:24px;}}
@media(max-width:480px){.pub-footer-grid{grid-template-columns:1fr;}}
</style>
