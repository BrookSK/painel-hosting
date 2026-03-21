<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_topo_links = [
    ['href' => '/status',         'label' => 'Status'],
    ['href' => '/cliente/entrar', 'label' => 'Entrar'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php $seo_titulo = 'Contato — ' . SistemaConfig::nome(); require __DIR__ . '/_partials/seo.php'; ?>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;}
    .pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden;}
    .pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .pub-page-hero-inner{position:relative;max-width:600px;margin:0 auto;}
    .pub-page-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#a78bfa;margin-bottom:12px;}
    .pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:10px;}
    .pub-page-sub{font-size:15px;color:rgba(255,255,255,.6);line-height:1.7;}
    .pub-content{max-width:680px;margin:0 auto;padding:48px 24px 64px;}
    .pub-card{background:#fff;border-radius:20px;padding:36px;box-shadow:0 4px 32px rgba(0,0,0,.25);}
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/topo-publico.php'; ?>

  <div class="pub-page-hero">
    <div class="pub-page-hero-inner">
      <div class="pub-page-label">Fale conosco</div>
      <h1 class="pub-page-title">Contato</h1>
      <p class="pub-page-sub">Tem alguma dúvida ou precisa de ajuda? Envie uma mensagem e retornaremos em breve.</p>
    </div>
  </div>

  <div class="pub-content">
    <div class="pub-card">
      <?php if (!empty($ok)): ?>
        <div class="sucesso"><?php echo View::e((string) $ok); ?></div>
      <?php endif; ?>
      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>
      <?php if (empty($ok)): ?>
        <form method="post" action="/contato">
          <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Nome</label>
              <input class="input" type="text" name="name" value="<?php echo View::e((string) ($form['name'] ?? '')); ?>" maxlength="120" required placeholder="Seu nome" />
            </div>
            <div>
              <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">E-mail</label>
              <input class="input" type="email" name="email" value="<?php echo View::e((string) ($form['email'] ?? '')); ?>" maxlength="190" required placeholder="seu@email.com" />
            </div>
          </div>
          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Assunto</label>
            <input class="input" type="text" name="subject" value="<?php echo View::e((string) ($form['subject'] ?? '')); ?>" maxlength="190" required placeholder="Como podemos ajudar?" />
          </div>
          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Mensagem</label>
            <textarea class="input" name="message" rows="6" maxlength="3000" required placeholder="Descreva sua dúvida ou solicitação..."><?php echo View::e((string) ($form['message'] ?? '')); ?></textarea>
          </div>
          <button class="botao" type="submit">Enviar mensagem</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
