<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$webmailUrl     = (string)($webmail_url ?? '');
$webmailMode    = (string)($webmail_mode ?? 'global');
$dominioPadrao  = (string)($dominio_padrao ?? '');
$dominiosAtivos = is_array($dominios_ativos ?? null) ? $dominios_ativos : [];
$limite         = (int)($limite ?? 5);
$totalEmails    = count(is_array($emails ?? null) ? $emails : []);

// Montar lista de domínios disponíveis para o select
$dominiosSelect = [];
if ($dominioPadrao !== '') {
    $dominiosSelect[] = $dominioPadrao;
}
foreach ($dominiosAtivos as $d) {
    if (!in_array($d, $dominiosSelect, true)) {
        $dominiosSelect[] = $d;
    }
}

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
                <?php foreach ($emails as $em):
                  $emailAddr = (string) ($em['email'] ?? ($em['local_part'] ?? '') . '@' . ($em['domain'] ?? ''));
                  $emailId   = (int) ($em['id'] ?? 0);
                  // Webmail: URL por domínio se possível, senão fallback global
                  $domainPart  = (string) ($em['domain'] ?? '');
                  $webmailLink = $webmailUrl !== '' ? $webmailUrl : ($domainPart !== '' ? 'https://webmail.' . $domainPart : '');
                ?>
                  <tr>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                      <?php echo View::e($emailAddr); ?>
                    </td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#64748b;">
                      <?php echo (int) ($em['quota_mb'] ?? 0); ?> MB
                    </td>
                    <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                      <div class="linha" style="gap:8px;">
                        <?php if ($webmailLink !== ''): ?>
                          <a href="<?php echo View::e($webmailLink); ?>" target="_blank" rel="noopener" class="botao sm ghost">Webmail</a>
                        <?php endif; ?>
                        <button class="botao sm ghost" onclick="abrirAlterarSenha(<?php echo $emailId; ?>, '<?php echo View::e($emailAddr); ?>')">Alterar senha</button>
                        <form method="post" action="/cliente/emails/remover" style="display:inline;" onsubmit="return confirm('Remover <?php echo View::e($emailAddr); ?>?')">
                          <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                          <input type="hidden" name="email_id" value="<?php echo $emailId; ?>" />
                          <button class="botao danger sm" type="submit">Remover</button>
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
        <h2 class="titulo" style="margin-bottom:6px;">Criar novo e-mail</h2>

        <?php if ($totalEmails >= $limite): ?>
          <div class="aviso" style="margin-bottom:12px;">
            Seu plano permite até <strong><?php echo $limite; ?></strong> conta(s) de e-mail.
            <a href="/cliente/planos">Fazer upgrade</a> para criar mais.
          </div>
        <?php else: ?>
          <p class="texto" style="font-size:13px;margin-bottom:14px;">
            <?php echo $totalEmails; ?>/<?php echo $limite; ?> contas usadas.
          </p>
        <?php endif; ?>

        <form method="post" action="/cliente/emails/criar" <?php echo $totalEmails >= $limite ? 'style="opacity:.5;pointer-events:none;"' : ''; ?>>
          <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

          <div style="margin-bottom:10px;">
            <label style="display:block;font-size:13px;margin-bottom:5px;">Usuário</label>
            <input class="input" type="text" name="local_part" placeholder="usuario" required pattern="[a-z0-9._\-]+" />
          </div>

          <div style="margin-bottom:10px;">
            <label style="display:block;font-size:13px;margin-bottom:5px;">Domínio</label>
            <?php if (count($dominiosSelect) > 1): ?>
              <select class="input" name="domain" required>
                <?php foreach ($dominiosSelect as $d): ?>
                  <option value="<?php echo View::e($d); ?>"><?php echo View::e($d); ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif (count($dominiosSelect) === 1): ?>
              <div style="display:flex;align-items:center;gap:8px;">
                <input class="input" type="text" name="domain" value="<?php echo View::e($dominiosSelect[0]); ?>" readonly style="background:#f8fafc;flex:1;" />
              </div>
            <?php else: ?>
              <input class="input" type="text" name="domain" placeholder="seudominio.com" required />
              <p class="texto" style="font-size:12px;margin-top:4px;">
                Nenhum domínio configurado. <a href="/cliente/emails/dominios">Adicione seu domínio</a> ou aguarde o administrador configurar o domínio padrão.
              </p>
            <?php endif; ?>
          </div>

          <div style="margin-bottom:10px;">
            <label style="display:block;font-size:13px;margin-bottom:5px;">Senha</label>
            <input class="input" type="password" name="password" required minlength="8" />
          </div>

          <div style="margin-bottom:14px;">
            <button class="botao" type="submit">Criar e-mail</button>
          </div>
        </form>

        <div style="border-top:1px solid #f1f5f9;padding-top:12px;margin-top:4px;">
          <a href="/cliente/emails/dominios" class="botao ghost sm">🌐 Gerenciar domínios próprios</a>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal alterar senha -->
  <div id="modalSenha" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:24px;max-width:400px;width:90%;">
      <h2 style="font-size:16px;font-weight:700;margin:0 0 14px;">Alterar senha</h2>
      <p id="modalSenhaEmail" style="font-size:13px;color:#64748b;margin:0 0 14px;"></p>
      <form method="post" action="/cliente/emails/alterar-senha" id="formAlterarSenha">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="email_id" id="modalEmailId" value="" />
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:13px;margin-bottom:5px;">Nova senha</label>
          <input class="input" type="password" name="nova_senha" id="modalNovaSenha" required minlength="8" />
        </div>
        <div style="margin-bottom:14px;">
          <label style="display:block;font-size:13px;margin-bottom:5px;">Confirmar senha</label>
          <input class="input" type="password" name="confirmar_senha" id="modalConfirmarSenha" required minlength="8" />
        </div>
        <div id="modalSenhaErro" style="display:none;" class="erro"></div>
        <div class="linha" style="gap:8px;">
          <button class="botao" type="submit" id="btnSalvarSenha">Salvar</button>
          <button class="botao ghost" type="button" onclick="fecharModalSenha()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function abrirAlterarSenha(id, email) {
    document.getElementById('modalEmailId').value = id;
    document.getElementById('modalSenhaEmail').textContent = email;
    document.getElementById('modalNovaSenha').value = '';
    document.getElementById('modalConfirmarSenha').value = '';
    document.getElementById('modalSenhaErro').style.display = 'none';
    document.getElementById('modalSenha').style.display = 'flex';
    document.getElementById('modalNovaSenha').focus();
  }
  function fecharModalSenha() {
    document.getElementById('modalSenha').style.display = 'none';
  }
  document.getElementById('formAlterarSenha').addEventListener('submit', function(e) {
    var s1 = document.getElementById('modalNovaSenha').value;
    var s2 = document.getElementById('modalConfirmarSenha').value;
    if (s1 !== s2) {
      e.preventDefault();
      var err = document.getElementById('modalSenhaErro');
      err.textContent = 'As senhas não coincidem.';
      err.style.display = 'block';
    }
  });
  </script>
  <?php require __DIR__ . '/../_partials/footer.php'; ?>
  <?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
