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
  <?php $seo_titulo = 'Criar conta — ' . $_nome; $seo_noindex = true; require __DIR__ . '/../_partials/seo.php'; ?>
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
    .auth-steps{display:flex;flex-direction:column;gap:14px;}
    .auth-step{display:flex;align-items:flex-start;gap:14px;}
    .auth-step-num{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .auth-step-body{font-size:13px;color:rgba(255,255,255,.75);line-height:1.5;}
    .auth-step-title{font-weight:700;color:#fff;margin-bottom:2px;}
    .auth-right{width:520px;background:#fff;display:flex;flex-direction:column;justify-content:center;padding:52px 48px;overflow-y:auto;}
    .auth-right-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;}
    .auth-right-top a{font-size:13px;color:#64748b;text-decoration:none;transition:color .15s;}
    .auth-right-top a:hover{color:#4F46E5;}
    .auth-form-title{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:6px;letter-spacing:-.02em;}
    .auth-form-sub{font-size:14px;color:#64748b;margin-bottom:24px;}
    .auth-field{margin-bottom:14px;}
    .auth-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
    .auth-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .auth-footer-links{margin-top:18px;text-align:center;font-size:13px;color:#64748b;}
    .auth-footer-links a{color:#4F46E5;font-weight:600;}
    @media(max-width:900px){.auth-left{display:none;}.auth-right{width:100%;padding:40px 28px;}}
    @media(max-width:560px){.auth-grid{grid-template-columns:1fr;}.auth-right{padding:32px 20px;}}
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
      <h1>Comece agora,<br><span class="grad">sem complicação</span></h1>
      <p>Crie sua conta e tenha sua infraestrutura pronta em minutos.</p>
      <div class="auth-steps">
        <div class="auth-step">
          <div class="auth-step-num">1</div>
          <div class="auth-step-body"><div class="auth-step-title">Crie sua conta</div>Cadastro rápido, sem cartão de crédito.</div>
        </div>
        <div class="auth-step">
          <div class="auth-step-num">2</div>
          <div class="auth-step-body"><div class="auth-step-title">Escolha um plano</div>Selecione os recursos que seu projeto precisa.</div>
        </div>
        <div class="auth-step">
          <div class="auth-step-num">3</div>
          <div class="auth-step-body"><div class="auth-step-title">VPS provisionada</div>Seu servidor fica pronto em menos de 60 segundos.</div>
        </div>
        <div class="auth-step">
          <div class="auth-step-num">4</div>
          <div class="auth-step-body"><div class="auth-step-title">Gerencie tudo</div>Terminal, deploy, backups e suporte em um painel.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-right-top">
      <?php require __DIR__ . '/../_partials/idioma.php'; ?>
      <a href="/cliente/entrar">Já tenho conta →</a>
    </div>
    <div class="auth-form-title">Criar conta grátis</div>
    <div class="auth-form-sub">Preencha os dados abaixo para começar.</div>
    <?php if (!empty($erro)): ?>
      <div class="erro"><?php echo View::e((string) $erro); ?></div>
    <?php endif; ?>
    <form method="post" action="/cliente/criar-conta">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <div class="auth-grid">
        <div class="auth-field">
          <label class="auth-label">Nome</label>
          <input class="input" type="text" name="nome" value="<?php echo View::e((string) ($nome ?? '')); ?>" autocomplete="name" placeholder="Seu nome" />
        </div>
        <div class="auth-field">
          <label class="auth-label">E-mail</label>
          <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" placeholder="seu@email.com" />
        </div>
        <div class="auth-field">
          <label class="auth-label">Senha</label>
          <input class="input" type="password" name="senha" autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
        </div>
        <div class="auth-field">
          <label class="auth-label">CPF/CNPJ <span style="color:#94a3b8;font-weight:400;">(opcional)</span></label>
          <input class="input" type="text" name="cpf_cnpj" value="<?php echo View::e((string) ($cpf_cnpj ?? '')); ?>" />
        </div>
        <div class="auth-field">
          <label class="auth-label">Telefone <span style="color:#94a3b8;font-weight:400;">(opcional)</span></label>
          <input class="input" type="text" name="phone" value="<?php echo View::e((string) ($phone ?? '')); ?>" autocomplete="tel" />
        </div>
        <div class="auth-field">
          <label class="auth-label">Celular <span style="color:#94a3b8;font-weight:400;">(opcional)</span></label>
          <input class="input" type="text" name="mobile_phone" value="<?php echo View::e((string) ($mobile_phone ?? '')); ?>" autocomplete="tel" />
        </div>
      </div>
      <button class="botao" type="submit" style="width:100%;justify-content:center;margin-top:8px;">Criar minha conta</button>
    </form>
    <div class="auth-footer-links">
      Já tem conta? <a href="/cliente/entrar">Entrar</a>
    </div>
    <div style="margin-top:10px;text-align:center;font-size:11px;color:#94a3b8;line-height:1.6;">
      Ao criar uma conta você concorda com os <a href="/termos" style="color:#94a3b8;">Termos de Uso</a> e <a href="/privacidade" style="color:#94a3b8;">Política de Privacidade</a>.
    </div>
  </div>
</div>
</body>
</html>
