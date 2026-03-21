<?php

declare(strict_types=1);

use LRV\Core\View;

$secret  = (string)($secret ?? '');
$qrUrl   = (string)($qr_url ?? '');
$enabled = (bool)($enabled ?? false);
$erro    = (string)($erro ?? '');
$ok      = (string)($ok ?? '');

$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($qrUrl);

$pageTitle = 'Autenticação em dois fatores';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>

<div class="page-title">Autenticação em dois fatores (2FA)</div>
<div class="page-subtitle">Configure o TOTP para proteger sua conta</div>

<div class="card-new" style="max-width:560px;">

  <?php if ($erro !== ''): ?>
    <div class="erro"><?php echo View::e($erro); ?></div>
  <?php endif; ?>

  <?php if ($ok !== ''): ?>
    <div class="sucesso"><?php echo View::e($ok); ?></div>
  <?php endif; ?>

  <?php if ($enabled): ?>
    <div class="sucesso" style="margin-bottom:16px;">
      2FA está <strong>ativado</strong> na sua conta.
    </div>
    <form method="post" action="/equipe/2fa/desativar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <label style="display:block;font-size:13px;margin-bottom:6px;">Código do autenticador para desativar</label>
      <input class="input" type="text" name="codigo" maxlength="6" pattern="\d{6}" autocomplete="one-time-code" placeholder="000000" required />
      <button class="botao" type="submit" style="margin-top:12px;background:#991b1b;">Desativar 2FA</button>
    </form>
  <?php else: ?>
    <p class="texto">Escaneie o QR code com Google Authenticator, Authy ou similar:</p>
    <div style="text-align:center;margin:16px 0;">
      <img src="<?php echo View::e($qrApiUrl); ?>" alt="QR Code 2FA" width="200" height="200" style="border-radius:8px;" />
    </div>
    <p class="texto" style="font-size:13px;">Ou insira manualmente a chave secreta:</p>
    <code style="display:block;background:#f1f5f9;padding:10px;border-radius:8px;font-size:14px;letter-spacing:2px;margin-bottom:16px;"><?php echo View::e($secret); ?></code>
    <form method="post" action="/equipe/2fa/ativar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <label style="display:block;font-size:13px;margin-bottom:6px;">Código do autenticador para confirmar</label>
      <input class="input" type="text" name="codigo" maxlength="6" pattern="\d{6}" autocomplete="one-time-code" placeholder="000000" required />
      <button class="botao" type="submit" style="margin-top:12px;">Ativar 2FA</button>
    </form>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>