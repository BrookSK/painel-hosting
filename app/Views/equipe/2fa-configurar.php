<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
$secret = (string) ($secret ?? '');
$qrUrl = (string) ($qr_url ?? '');
$enabled = (bool) ($enabled ?? false);
$erro = (string) ($erro ?? '');
$ok = (string) ($ok ?? '');
// Gerar URL do QR code via API pública (sem dependência)
$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($qrUrl);
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Autenticação em dois fatores</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div><div style="font-size:18px;font-weight:700;">Autenticação em dois fatores (2FA)</div></div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>
  <div class="conteudo">
    <div class="card" style="max-width:560px; margin:0 auto;">
      <?php if ($erro !== ''): ?>
        <div class="erro"><?php echo View::e($erro); ?></div>
      <?php endif; ?>
      <?php if ($ok !== ''): ?>
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:10px 12px;border-radius:12px;margin-bottom:10px;"><?php echo View::e($ok); ?></div>
      <?php endif; ?>

      <?php if ($enabled): ?>
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:10px 12px;border-radius:12px;margin-bottom:16px;">
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
  </div>
</body>
</html>
