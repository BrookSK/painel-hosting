<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php $seo_titulo = I18n::t('contato.titulo') . ' — ' . SistemaConfig::nome(); require __DIR__ . '/_partials/seo.php'; ?>
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
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
    @media(max-width:560px){.form-row{grid-template-columns:1fr}.pub-card{padding:24px 18px}}
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/navbar-publica.php'; ?>

  <div class="pub-page-hero">
    <div class="pub-page-hero-inner">
      <div class="pub-page-label"><?php echo View::e(I18n::t('contato.label')); ?></div>
      <h1 class="pub-page-title"><?php echo View::e(I18n::t('contato.titulo')); ?></h1>
      <p class="pub-page-sub"><?php echo View::e(I18n::t('contato.sub')); ?></p>
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
          <div class="form-row">
            <div>
              <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;"><?php echo View::e(I18n::t('contato.nome')); ?></label>
              <input class="input" type="text" name="name" value="<?php echo View::e((string) ($form['name'] ?? '')); ?>" maxlength="120" required placeholder="<?php echo View::e(I18n::t('contato.ph_nome')); ?>" />
            </div>
            <div>
              <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;"><?php echo View::e(I18n::t('contato.email')); ?></label>
              <input class="input" type="email" name="email" value="<?php echo View::e((string) ($form['email'] ?? '')); ?>" maxlength="190" required placeholder="<?php echo View::e(I18n::t('contato.ph_email')); ?>" />
            </div>
          </div>
          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;"><?php echo View::e(I18n::t('contato.assunto')); ?></label>
            <input class="input" type="text" name="subject" value="<?php echo View::e((string) ($form['subject'] ?? '')); ?>" maxlength="190" required placeholder="<?php echo View::e(I18n::t('contato.ph_assunto')); ?>" />
          </div>
          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;"><?php echo View::e(I18n::t('contato.mensagem')); ?></label>
            <textarea class="input" name="message" rows="6" maxlength="3000" required placeholder="<?php echo View::e(I18n::t('contato.ph_mensagem')); ?>"><?php echo View::e((string) ($form['message'] ?? '')); ?></textarea>
          </div>
          <button class="botao" type="submit"><?php echo View::e(I18n::t('contato.enviar')); ?></button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
