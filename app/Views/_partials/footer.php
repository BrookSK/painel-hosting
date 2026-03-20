<?php
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\View;
$_ft_copyright = SistemaConfig::copyrightText();
$_ft_nome      = SistemaConfig::nome();
?>
<footer style="text-align:center;padding:20px 18px;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;margin-top:32px;line-height:2;">
  <?php echo View::e($_ft_copyright); ?> &nbsp;·&nbsp; <?php echo View::e($_ft_nome); ?> v1.4.0
  <br>
  <a href="/termos" style="color:#94a3b8;">Termos</a> &nbsp;·&nbsp;
  <a href="/privacidade" style="color:#94a3b8;">Privacidade</a> &nbsp;·&nbsp;
  <a href="/changelog" style="color:#94a3b8;">Changelog</a> &nbsp;·&nbsp;
  <a href="/status" style="color:#94a3b8;">Status</a> &nbsp;·&nbsp;
  <a href="/contato" style="color:#94a3b8;">Contato</a>
</footer>
