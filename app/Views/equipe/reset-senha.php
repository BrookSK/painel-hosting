<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_nome = SistemaConfig::nome();
$_logo = SistemaConfig::logoUrl();
$etapa = (string)($etapa ?? 'solicitar');
$ok    = (string)($ok ?? '');
$erro  = (string)($erro ?? '');
$token = (string)($token ?? '');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Redefinir senha — <?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
    .rs-card{width:100%;max-width:420px;background:#0f172a;border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:40px 36px;}
    .rs-brand{display:flex;align-items:center;gap:10px;margin-bottom:32px;text-decoration:none;}
    .rs-brand img{height:28px;width:auto;}
    .rs-brand-name{font-size:16px;font-weight:800;color:#fff;}
    .rs-title{font-size:22px;font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:6px;}
    .rs-sub{font-size:14px;color:rgba(255,255,255,.45);margin-bottom:24px;line-height:1.6;}
    .rs-label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,.65);margin-bottom:6px;}
    .rs-field{margin-bottom:16px;}
    .rs-card .input{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);color:#fff;}
    .rs-card .input:focus{border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.2);}
    .rs-card .input::placeholder{color:rgba(255,255,255,.25);}
    .rs-card .erro{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#fca5a5;}
    .rs-card .sucesso{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:#86efac;}
    .rs-back{display:block;margin-top:20px;text-align:center;font-size:13px;color:rgba(255,255,255,.35);text-decoration:none;transition:color .15s;}
    .rs-back:hover{color:rgba(255,255,255,.7);}
  </style>
</head>
<body>
<div class="rs-card">
  <a href="/equipe/entrar" class="rs-brand">
    <?php if ($_logo !== ''): ?>
      <img src="<?php echo View::e($_logo); ?>" alt="logo" />
    <?php else: ?>
      <svg width="26" height="26" viewBox="0 0 26 26" fill="none"><rect width="26" height="26" rx="7" fill="#4F46E5"/><path d="M6 13h14M13 6v14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
      <span class="rs-brand-name"><?php echo View::e($_nome); ?></span>
    <?php endif; ?>
  </a>

  <?php if ($etapa === 'solicitar'): ?>
    <div class="rs-title">Redefinir senha</div>
    <div class="rs-sub">Informe o e-mail da sua conta. Enviaremos um link para criar uma nova senha.</div>

    <?php if ($ok !== ''): ?><div class="sucesso"><?php echo View::e($ok); ?></div><?php endif; ?>
    <?php if ($erro !== ''): ?><div class="erro"><?php echo View::e($erro); ?></div><?php endif; ?>

    <?php if ($ok === ''): ?>
    <form method="post" action="/equipe/reset-senha/solicitar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <div class="rs-field">
        <label class="rs-label">E-mail</label>
        <input class="input" type="email" name="email" placeholder="equipe@empresa.com" autocomplete="email" required />
      </div>
      <button class="botao" type="submit" style="width:100%;justify-content:center;">Enviar link de redefinição</button>
    </form>
    <?php endif; ?>

  <?php else: ?>
    <div class="rs-title">Nova senha</div>
    <div class="rs-sub">Escolha uma senha forte com ao menos 8 caracteres.</div>

    <?php if ($erro !== ''): ?><div class="erro"><?php echo View::e($erro); ?></div><?php endif; ?>

    <form method="post" action="/equipe/reset-senha/salvar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <input type="hidden" name="token" value="<?php echo View::e($token); ?>" />
      <div class="rs-field">
        <label class="rs-label">Nova senha</label>
        <input class="input" type="password" name="nova_senha" placeholder="••••••••" minlength="8" autocomplete="new-password" required />
      </div>
      <div class="rs-field">
        <label class="rs-label">Confirmar senha</label>
        <input class="input" type="password" name="confirmar_senha" placeholder="••••••••" minlength="8" autocomplete="new-password" required />
      </div>
      <button class="botao" type="submit" style="width:100%;justify-content:center;">Salvar nova senha</button>
    </form>
  <?php endif; ?>

  <a href="/equipe/entrar" class="rs-back">← Voltar ao login</a>
</div>
</body>
</html>
