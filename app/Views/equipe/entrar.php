<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_nome = SistemaConfig::nome();
$_logo = SistemaConfig::logoUrl();
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Equipe — <?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;display:flex;min-height:100vh;}
    .auth-shell{display:flex;width:100%;min-height:100vh;}
    .auth-left{flex:1;background:linear-gradient(160deg,#060d1f 0%,#0B1C3D 50%,#0f172a 100%);display:flex;flex-direction:column;justify-content:center;padding:60px 56px;position:relative;overflow:hidden;border-right:1px solid rgba(255,255,255,.06);}
    .auth-left::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .auth-left-glow{position:absolute;bottom:-10%;left:-10%;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(79,70,229,.25) 0%,transparent 70%);pointer-events:none;}
    .auth-left-inner{position:relative;max-width:400px;}
    .auth-brand{display:flex;align-items:center;gap:10px;margin-bottom:52px;text-decoration:none;}
    .auth-brand img{height:32px;width:auto;}
    .auth-brand-name{font-size:18px;font-weight:800;color:#fff;letter-spacing:-.01em;}
    .auth-left h1{font-size:clamp(22px,3vw,30px);font-weight:900;color:#fff;line-height:1.2;letter-spacing:-.03em;margin-bottom:12px;}
    .auth-left p{font-size:14px;color:rgba(255,255,255,.5);line-height:1.7;margin-bottom:32px;}
    .auth-security-items{display:flex;flex-direction:column;gap:10px;}
    .auth-security-item{display:flex;align-items:center;gap:10px;font-size:12px;color:rgba(255,255,255,.55);}
    .auth-security-dot{width:6px;height:6px;border-radius:50%;background:#4F46E5;flex-shrink:0;}
    .auth-right{width:460px;background:#0f172a;border-left:1px solid rgba(255,255,255,.06);display:flex;flex-direction:column;justify-content:center;padding:60px 48px;}
    .auth-right-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:40px;}
    .auth-right-top a{font-size:13px;color:rgba(255,255,255,.4);text-decoration:none;transition:color .15s;}
    .auth-right-top a:hover{color:rgba(255,255,255,.8);}
    .auth-form-title{font-size:22px;font-weight:800;color:#fff;margin-bottom:6px;letter-spacing:-.02em;}
    .auth-form-sub{font-size:14px;color:rgba(255,255,255,.45);margin-bottom:28px;}
    .auth-field{margin-bottom:16px;}
    .auth-label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,.65);margin-bottom:6px;}
    .auth-right .input{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);color:#fff;}
    .auth-right .input:focus{border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.2);}
    .auth-right .input::placeholder{color:rgba(255,255,255,.25);}
    .auth-right .erro{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#fca5a5;}
    .auth-footer-links{margin-top:20px;text-align:center;font-size:13px;color:rgba(255,255,255,.35);}
    .auth-footer-links a{color:rgba(165,180,252,.8);font-weight:600;}
    @media(max-width:900px){.auth-left{display:none;}.auth-right{width:100%;padding:40px 28px;}}
    @media(max-width:480px){.auth-right{padding:32px 20px;}}
  </style>
</head>
<body>
<div class="auth-shell">
  <div class="auth-left">
    <div class="auth-left-glow"></div>
    <div class="auth-left-inner">
      <a href="/" class="auth-brand">
        <?php if ($_logo !== ''): ?>
          <img src="<?php echo View::e($_logo); ?>" alt="logo" />
        <?php else: ?>
          <svg width="30" height="30" viewBox="0 0 30 30" fill="none"><rect width="30" height="30" rx="8" fill="#4F46E5"/><path d="M7 15h16M15 7v16" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
        <?php endif; ?>
        <span class="auth-brand-name"><?php echo View::e($_nome); ?></span>
      </a>
      <h1>Painel administrativo da equipe</h1>
      <p>Acesso restrito. Use suas credenciais de membro da equipe para entrar no painel de controle.</p>
      <div class="auth-security-items">
        <div class="auth-security-item"><div class="auth-security-dot"></div>Autenticação em dois fatores (2FA) disponível</div>
        <div class="auth-security-item"><div class="auth-security-dot"></div>Controle de acesso por roles e permissões</div>
        <div class="auth-security-item"><div class="auth-security-dot"></div>Auditoria completa de ações</div>
        <div class="auth-security-item"><div class="auth-security-dot"></div>Sessão com expiração automática</div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-right-top">
      <?php require __DIR__ . '/../_partials/idioma.php'; ?>
      <a href="/cliente/entrar">Área do cliente →</a>
    </div>
    <div class="auth-form-title">Acesso da equipe</div>
    <div class="auth-form-sub">Use seu e-mail e senha para acessar o painel administrativo.</div>
    <?php if (!empty($erro)): ?>
      <div class="erro"><?php echo View::e((string) $erro); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['sessao']) && $_GET['sessao'] === 'expirada'): ?>
      <div class="erro">Sua sessão expirou por inatividade. Faça login novamente.</div>
    <?php endif; ?>
    <form method="post" action="/equipe/entrar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <div class="auth-field">
        <label class="auth-label">E-mail</label>
        <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" placeholder="equipe@empresa.com" />
      </div>
      <div class="auth-field">
        <label class="auth-label">Senha</label>
        <input class="input" type="password" name="senha" autocomplete="current-password" placeholder="••••••••" />
      </div>
      <button class="botao" type="submit" style="width:100%;justify-content:center;margin-top:4px;">Entrar no painel</button>
    </form>
    <div class="auth-footer-links">
      <a href="/">← Voltar ao início</a>
    </div>
  </div>
</div>
</body>
</html>
