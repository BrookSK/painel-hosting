<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$webmailUrl     = (string)($webmail_url ?? '');
$dominioPadrao  = (string)($dominio_padrao ?? '');
$dominiosAtivos = is_array($dominios_ativos ?? null) ? $dominios_ativos : [];
$limite         = (int)($limite ?? 5);
$totalEmails    = count(is_array($emails ?? null) ? $emails : []);

$dominiosSelect = [];
if ($dominioPadrao !== '') $dominiosSelect[] = $dominioPadrao;
foreach ($dominiosAtivos as $d) {
    if (!in_array($d, $dominiosSelect, true)) $dominiosSelect[] = $d;
}

$pageTitle    = 'Meus E-mails';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title">Meus E-mails</div>
  <div class="page-subtitle" style="margin-bottom:0;">Gerenciar caixas de entrada</div>
</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e((string)$erro); ?></div>
<?php endif; ?>
<?php if (!empty($sucesso)): ?>
  <div class="sucesso"><?php echo View::e((string)$sucesso); ?></div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:1fr 340px;gap:16px;align-items:start;">

  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:14px;">Caixas de entrada</div>
    <?php if (empty($emails)): ?>
      <p style="color:#94a3b8;font-size:13px;">Nenhum e-mail criado ainda.</p>
    <?php else: ?>
      <div style="overflow:auto;">
        <table style="width:100%;border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">E-mail</th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Quota</th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($emails as $em):
              $emailAddr  = (string)($em['email'] ?? ($em['local_part'] ?? '') . '@' . ($em['domain'] ?? ''));
              $emailId    = (int)($em['id'] ?? 0);
              $domainPart = (string)($em['domain'] ?? '');
              $webmailLink = $webmailUrl !== '' ? $webmailUrl : ($domainPart !== '' ? 'https://webmail.' . $domainPart : '');
            ?>
              <tr>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e($emailAddr); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;font-size:13px;color:#64748b;"><?php echo (int)($em['quota_mb'] ?? 0); ?> MB</td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
                  <div style="display:flex;gap:8px;flex-wrap:wrap;">
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

  <div>
    <div class="card-new">
      <div class="card-new-title" style="margin-bottom:6px;">Criar novo e-mail</div>
      <?php if ($totalEmails >= $limite): ?>
        <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:10px 12px;border-radius:10px;font-size:13px;margin-bottom:12px;">
          Seu plano permite até <strong><?php echo $limite; ?></strong> conta(s).
          <a href="/cliente/planos">Fazer upgrade</a>
        </div>
      <?php else: ?>
        <p style="font-size:13px;color:#64748b;margin-bottom:14px;"><?php echo $totalEmails; ?>/<?php echo $limite; ?> contas usadas.</p>
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
            <input class="input" type="text" name="domain" value="<?php echo View::e($dominiosSelect[0]); ?>" readonly style="background:#f8fafc;" />
          <?php else: ?>
            <input class="input" type="text" name="domain" placeholder="seudominio.com" required />
            <p style="font-size:12px;color:#94a3b8;margin-top:4px;">Nenhum domínio configurado. <a href="/cliente/emails/dominios">Adicione seu domínio</a>.</p>
          <?php endif; ?>
        </div>
        <div style="margin-bottom:14px;">
          <label style="display:block;font-size:13px;margin-bottom:5px;">Senha</label>
          <input class="input" type="password" name="password" required minlength="8" />
        </div>
        <button class="botao" type="submit">Criar e-mail</button>
      </form>

      <div style="border-top:1px solid #f1f5f9;padding-top:12px;margin-top:12px;">
        <a href="/cliente/emails/dominios" class="botao ghost sm">Gerenciar domínios próprios</a>
      </div>
    </div>
  </div>

</div>

<!-- Modal alterar senha -->
<div id="modalSenha" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center;">
  <div class="card-new" style="max-width:400px;width:90%;">
    <div class="card-new-title" style="margin-bottom:14px;">Alterar senha</div>
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
      <div style="display:flex;gap:8px;">
        <button class="botao" type="submit">Salvar</button>
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
    err.textContent = 'As senhas nao coincidem.';
    err.style.display = 'block';
  }
});
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
