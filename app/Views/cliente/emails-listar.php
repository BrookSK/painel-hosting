<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$webmailUrl = (string) ($webmail_url ?? '');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Meus E-mails</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Meus E-mails</div>
        <div style="opacity:.9; font-size:13px;">Gerenciar caixas de entrada</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">

    <?php if (!empty($erro)): ?>
      <div class="erro"><?php echo View::e((string) $erro); ?></div>
    <?php endif; ?>
    <?php if (!empty($sucesso)): ?>
      <div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:10px 12px;border-radius:12px;margin-bottom:10px;">
        <?php echo View::e((string) $sucesso); ?>
      </div>
    <?php endif; ?>

    <div class="grid" style="grid-template-columns:1fr 340px; gap:16px; align-items:start;">

      <div class="card">
        <h1 class="titulo" style="margin-bottom:14px;">Caixas de entrada</h1>
        <?php if (empty($emails)): ?>
          <p class="texto">Nenhum e-mail criado ainda.</p>
        <?php else: ?>
          <div style="overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
              <thead>
                <tr>
                  <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">E-mail</th>
                  <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Quota</th>
                  <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($emails as $em): ?>
                  <tr>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                      <?php echo View::e((string) ($em['local_part'] ?? '') . '@' . (string) ($em['domain'] ?? '')); ?>
                    </td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#64748b;">
                      <?php echo (int) ($em['quota_mb'] ?? 0); ?> MB
                    </td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                      <div class="linha" style="gap:8px;">
                        <?php if ($webmailUrl !== ''): ?>
                          <a href="<?php echo View::e($webmailUrl); ?>" target="_blank" rel="noopener" class="botao sm ghost">Webmail</a>
                        <?php endif; ?>
                        <form method="post" action="/cliente/emails/remover" style="display:inline;">
                          <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                          <input type="hidden" name="local_part" value="<?php echo View::e((string) ($em['local_part'] ?? '')); ?>" />
                          <input type="hidden" name="domain" value="<?php echo View::e((string) ($em['domain'] ?? '')); ?>" />
                          <button class="botao danger sm" type="submit" onclick="return confirm('Remover este e-mail?')">Remover</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2 class="titulo" style="margin-bottom:14px;">Criar novo e-mail</h2>
        <form method="post" action="/cliente/emails/criar">
          <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
          <div style="margin-bottom:10px;">
            <label style="display:block; font-size:13px; margin-bottom:5px;">Usuário</label>
            <input class="input" type="text" name="local_part" placeholder="usuario" required pattern="[a-z0-9._\-]+" />
          </div>
          <div style="margin-bottom:10px;">
            <label style="display:block; font-size:13px; margin-bottom:5px;">Domínio</label>
            <input class="input" type="text" name="domain" placeholder="seudominio.com" required />
          </div>
          <div style="margin-bottom:10px;">
            <label style="display:block; font-size:13px; margin-bottom:5px;">Senha</label>
            <input class="input" type="password" name="password" required minlength="8" />
          </div>
          <div style="margin-bottom:14px;">
            <label style="display:block; font-size:13px; margin-bottom:5px;">Quota (MB)</label>
            <input class="input" type="number" name="quota_mb" value="1024" min="100" max="10240" />
          </div>
          <button class="botao" type="submit">Criar e-mail</button>
        </form>
      </div>

    </div>
  </div>
</body>
</html>
