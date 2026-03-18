<?php
 
 declare(strict_types=1);
 
 use LRV\Core\I18n;
 use LRV\Core\View;
 
 ?>
 <!doctype html>
 <html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>LRV Cloud Manager</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu; background:#ffffff; margin:0;}
    .topo{background:linear-gradient(90deg,#0B1C3D,#4F46E5,#7C3AED); color:#fff; padding:28px 18px;}
    .conteudo{max-width:960px; margin:0 auto; padding:24px 18px;}
    .card{border:1px solid #e5e7eb; border-radius:14px; padding:18px; box-shadow:0 6px 18px rgba(15,23,42,.06);}
    .titulo{font-size:20px; margin:0 0 10px 0;}
    .texto{color:#334155; margin:0 0 10px 0; line-height:1.5;}
    .linha{display:flex; gap:12px; flex-wrap:wrap;}
    .badge{display:inline-block; padding:6px 10px; border-radius:999px; background:#eef2ff; color:#1E3A8A; font-size:12px;}
    a{color:#4F46E5; text-decoration:none;}
    .topo a{color:#fff;}
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo">
      <h1 style="margin:0;font-size:26px;">LRV Cloud Manager</h1>
      <div style="margin-top:10px; display:flex; justify-content:flex-end;">
        <?php require __DIR__ . '/_partials/idioma.php'; ?>
      </div>
      <div style="margin-top:8px;" class="linha">
        <span class="badge">Painel</span>
        <span class="badge">API Interna</span>
        <span class="badge">Provisionamento</span>
      </div>
    </div>
  </div>
  <div class="conteudo">
    <div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px;">
      <div class="card">
        <h2 class="titulo">Acesso da equipe</h2>
        <p class="texto">Para administrar o sistema (nodes, cobranças, tickets e permissões).</p>
        <p class="texto"><a href="/equipe/entrar">Entrar como equipe</a></p>
        <p class="texto"><a href="/equipe/primeiro-acesso">Primeiro acesso (criar SuperAdmin)</a></p>
      </div>

      <div class="card">
        <h2 class="titulo">Acesso do cliente</h2>
        <p class="texto">Para ver VPS, aplicações, deploy, backups, monitoramento e tickets.</p>
        <p class="texto"><a href="/cliente/entrar">Entrar como cliente</a></p>
        <p class="texto"><a href="/cliente/criar-conta">Criar conta de cliente</a></p>
      </div>

      <div class="card">
        <h2 class="titulo">API interna</h2>
        <p class="texto">Teste rápido:</p>
        <p class="texto"><a href="/api/saude">/api/saude</a></p>
      </div>
    </div>
  </div>
</body>
</html>
