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

<div class="page-title">Autenticação em dois fatores</div>
<div class="page-subtitle">Proteja sua conta com TOTP (Google Authenticator, Authy, etc.)</div>

<?php if ($erro !== ''): ?>
  <div class="erro" style="max-width:520px;margin-bottom:16px;"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if ($ok !== ''): ?>
  <div class="sucesso" style="max-width:520px;margin-bottom:16px;"><?php echo View::e($ok); ?></div>
<?php endif; ?>

<?php if ($enabled): ?>

<div class="card-new" style="max-width:520px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <div style="width:44px;height:44px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">✓</div>
    <div>
      <div style="font-size:15px;font-weight:700;color:#166534;">2FA ativado</div>
      <div style="font-size:13px;color:#64748b;margin-top:2px;">Sua conta está protegida com autenticação em dois fatores.</div>
    </div>
  </div>

  <div style="border-top:1px solid #f1f5f9;padding-top:16px;">
    <div class="card-new-title" style="margin-bottom:12px;">Desativar 2FA</div>
    <p class="texto" style="margin-bottom:12px;">Para desativar, confirme com o código do seu autenticador.</p>
    <form method="post" action="/equipe/2fa/desativar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Código do autenticador</label>
      <input class="input" type="text" name="codigo" maxlength="6" pattern="\d{6}" autocomplete="one-time-code" placeholder="000000" required style="max-width:180px;letter-spacing:4px;font-size:18px;text-align:center;" />
      <div style="margin-top:12px;">
        <button class="botao" type="submit" style="background:#991b1b;">Desativar 2FA</button>
      </div>
    </form>
  </div>
</div>

<?php else: ?>

<div class="card-new" style="max-width:520px;">
  <div class="card-new-title" style="margin-bottom:4px;">Configurar autenticador</div>
  <p class="texto" style="margin-bottom:20px;">Escaneie o QR code com seu aplicativo autenticador ou insira a chave manualmente.</p>

  <!-- QR Code -->
  <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;margin-bottom:20px;">
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px;display:inline-flex;">
      <img src="<?php echo View::e($qrApiUrl); ?>" alt="QR Code 2FA" width="180" height="180" />
    </div>
    <div style="flex:1;min-width:200px;">
      <div style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Chave secreta (manual)</div>
      <code style="display:block;background:#f1f5f9;padding:10px 12px;border-radius:8px;font-size:13px;letter-spacing:3px;color:#0f172a;word-break:break-all;margin-bottom:12px;"><?php echo View::e($secret); ?></code>
      <div style="font-size:12px;color:#64748b;line-height:1.5;">
        Use Google Authenticator, Authy, Bitwarden ou qualquer app TOTP compatível.
      </div>
    </div>
  </div>

  <!-- Formulário de ativação -->
  <div style="border-top:1px solid #f1f5f9;padding-top:16px;">
    <form method="post" action="/equipe/2fa/ativar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Código do autenticador para confirmar</label>
      <input class="input" type="text" name="codigo" maxlength="6" pattern="\d{6}" autocomplete="one-time-code" placeholder="000000" required autofocus style="max-width:180px;letter-spacing:4px;font-size:18px;text-align:center;" />
      <div style="margin-top:12px;">
        <button class="botao" type="submit">Ativar 2FA</button>
      </div>
    </form>
  </div>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
