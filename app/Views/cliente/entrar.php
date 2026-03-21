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
  <title>Entrar — <?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;display:flex;min-height:100vh;}
    .auth-shell{display:flex;width:100%;min-height:100vh;}
    .auth-left{flex:1;background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 40%,#1e3a8a 70%,#4F46E5 100%);display:flex;flex-direction:column;justify-content:center;padding:60px 56px;position:relative;overflow:hidden;}
    .auth-left::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .auth-left-glow{position:absolute;top:-20%;right:-10%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,.35) 0%,transparent 70%);pointer-events:none;}
    .auth-left-inner{position:relative;max-width:420px;}
    .auth-brand{display:flex;align-items:center;gap:10px;margin-bottom:48px;text-decoration:none;}
    .auth-brand img{height:32px;width:auto;}
    .auth-brand-name{font-size:18px;font-weight:800;color:#fff;letter-spacing:-.01em;}
    .auth-left h1{font-size:clamp(26px,3.5vw,36px);font-weight:900;color:#fff;line-height:1.15;letter-spacing:-.03em;margin-bottom:14px;}
    .auth-left h1 .grad{background:linear-gradient(135deg,#a5b4fc,#c4b5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .auth-left p{font-size:15px;color:rgba(255,255,255,.65);line-height:1.7;margin-bottom:36px;}
    .auth-features{display:flex;flex-direction:column;gap:12px;}
    .auth-feature{display:flex;align-items:center;gap:12px;font-size:13px;color:rgba(255,255,255,.75);}
    .auth-feature-icon{width:32px;height:32px;border-radius:9px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#a5b4fc;}
    .auth-right{width:480px;background:#fff;display:flex;flex-direction:column;justify-content:center;padding:60px 48px;}
    .auth-right-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:40px;}
    .auth-right-top a{font-size:13px;color:#64748b;text-decoration:none;transition:color .15s;}
    .auth-right-top a:hover{color:#4F46E5;}
    .auth-form-title{font-size:24px;font-weight:800;color:#0f172a;margin-bottom:6px;letter-spacing:-.02em;}
    .auth-form-sub{font-size:14px;color:#64748b;margin-bottom:28px;}
    .auth-field{margin-bottom:16px;}
    .auth-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
    .auth-footer-links{margin-top:20px;text-align:center;font-size:13px;color:#64748b;}
    .auth-footer-links a{color:#4F46E5;font-weight:600;}
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
      <h1>Infraestrutura cloud<br><span class="grad">simples e poderosa</span></h1>
      <p>Gerencie VPS, aplicações, e-mails e backups em um único painel. Tudo automatizado e seguro.</p>
      <div class="auth-features">
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="4" width="14" height="4" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="1" y="10" width="14" height="4" rx="1.5" stroke="currentColor" stroke-width="1.4"/><circle cx="12" cy="6" r="1" fill="currentColor"/><circle cx="12" cy="12" r="1" fill="currentColor"/></svg>
          </div>
          VPS provisionada em menos de 60 segundos
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="3" width="14" height="10" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M1 7h14" stroke="currentColor" stroke-width="1.4"/><path d="M5 11h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </div>
          Terminal web, deploy e backups automáticos
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 4h10a1 1 0 011 1v6a1 1 0 01-1 1H5l-3 2V5a1 1 0 011-1z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
          </div>
          Suporte via chat ao vivo e tickets
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2l1.8 3.8 4.2.6-3 2.9.7 4.2L8 11.5l-3.7 2 .7-4.2-3-2.9 4.2-.6L8 2z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
          </div>
          Monitoramento 24/7 com alertas automáticos
        </div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-right-top">
      <?php require __DIR__ . '/../_partials/idioma.php'; ?>
      <a href="/cliente/criar-conta">Criar conta &rarr;</a>
    </div>
    <div class="auth-form-title">Bem-vindo de volta</div>
    <div class="auth-form-sub">Entre com seu e-mail e senha para acessar o painel.</div>
    <?php if (!empty($erro)): ?>
      <div class="erro"><?php echo View::e((string) $erro); ?></div>
    <?php endif; ?>
    <form method="post" action="/cliente/entrar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <div class="auth-field">
        <label class="auth-label">E-mail</label>
        <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" placeholder="seu@email.com" />
      </div>
      <div class="auth-field">
        <label class="auth-label">Senha</label>
        <input class="input" type="password" name="senha" autocomplete="current-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
      </div>
      <button class="botao" type="submit" style="width:100%;justify-content:center;margin-top:4px;">Entrar na conta</button>
    </form>
    <div class="auth-footer-links">
      Não tem conta? <a href="/cliente/criar-conta">Criar conta grátis</a>
    </div>
    <div style="margin-top:12px;text-align:center;font-size:12px;">
      <a href="/equipe/entrar" style="color:#94a3b8;">Acesso da equipe</a>
    </div>
  </div>
</div>
</body>
</html>
