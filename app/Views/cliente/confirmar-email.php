<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_nome = SistemaConfig::nome();
$_logo = SistemaConfig::logoUrl();
$etapa = (string)($etapa ?? 'pendente');
$ok    = (string)($ok ?? '');
$erro  = (string)($erro ?? '');
$email = (string)($email ?? '');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Confirme seu e-mail — <?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body{background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
    .rs-card{width:100%;max-width:440px;background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:40px 36px;box-shadow:0 4px 24px rgba(15,23,42,.07);}
    .rs-brand{display:flex;align-items:center;gap:10px;margin-bottom:28px;text-decoration:none;}
    .rs-brand img{height:28px;width:auto;}
    .rs-brand-name{font-size:16px;font-weight:800;color:#0f172a;}
    .rs-title{font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin-bottom:8px;}
    .rs-sub{font-size:14px;color:#64748b;margin-bottom:20px;line-height:1.6;}
    .rs-back{display:block;margin-top:20px;text-align:center;font-size:13px;color:#94a3b8;text-decoration:none;}
  </style>
</head>
<body>
<div class="rs-card">
  <a href="/" class="rs-brand">
    <?php if ($_logo !== ''): ?>
      <img src="<?php echo View::e($_logo); ?>" alt="logo" style="background:#0f172a;padding:6px 12px;border-radius:10px;" />
    <?php else: ?>
      <span class="rs-brand-name"><?php echo View::e($_nome); ?></span>
    <?php endif; ?>
  </a>

  <?php if ($ok !== ''): ?><div class="sucesso" style="margin-bottom:14px;"><?php echo View::e($ok); ?></div><?php endif; ?>
  <?php if ($erro !== ''): ?><div class="erro" style="margin-bottom:14px;"><?php echo View::e($erro); ?></div><?php endif; ?>

  <?php if ($etapa === 'pendente'): ?>
    <div class="rs-title">Confirme seu e-mail</div>
    <div class="rs-sub">
      Enviamos um link de confirmação para <strong><?php echo View::e($email); ?></strong>.
      Abra o e-mail e clique em <strong>Confirmar e-mail</strong> para liberar sua conta.
      Não chegou? Confira a caixa de spam ou peça um novo link.
    </div>
    <div class="rs-sub" style="font-size:13px;">Enquanto isso, você já pode acompanhar pagamentos e falar com o suporte.</div>
    <form method="post" action="/cliente/confirmar-email/reenviar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <button class="botao" type="submit" style="width:100%;justify-content:center;">Enviar novo link</button>
    </form>
    <div style="display:flex;gap:10px;margin-top:12px;">
      <a class="botao ghost" href="/cliente/assinaturas" style="flex:1;justify-content:center;">Assinaturas</a>
      <a class="botao ghost" href="/cliente/tickets" style="flex:1;justify-content:center;">Suporte</a>
    </div>
    <a href="/cliente/sair" class="rs-back">Sair</a>
  <?php elseif ($etapa === 'ok'): ?>
    <div class="rs-title">Tudo certo!</div>
    <a class="botao" href="/cliente/entrar" style="width:100%;justify-content:center;margin-top:8px;">Entrar</a>
  <?php else: ?>
    <div class="rs-title">Não foi possível confirmar</div>
    <a class="botao" href="/cliente/entrar" style="width:100%;justify-content:center;margin-top:8px;">Entrar</a>
  <?php endif; ?>
</div>
</body>
</html>
