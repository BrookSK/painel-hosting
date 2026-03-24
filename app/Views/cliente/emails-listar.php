<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$webmailUrl     = (string)($webmail_url ?? '');
$dominioPadrao  = (string)($dominio_padrao ?? '');
$dominiosAtivos = is_array($dominios_ativos ?? null) ? $dominios_ativos : [];
$limite         = (int)($limite ?? 5);
$totalEmails    = count(is_array($emails ?? null) ? $emails : []);
$mailcowHost    = (string)($mailcow_host ?? '');
$cotaTotal      = (int)($cota_total ?? 5120);
$cotaUsada      = (int)($cota_usada ?? 0);
$cotaDisponivel = max(0, $cotaTotal - $cotaUsada);

$dominiosSelect = [];
if ($dominioPadrao !== '') $dominiosSelect[] = $dominioPadrao;
foreach ($dominiosAtivos as $d) {
    if (!in_array($d, $dominiosSelect, true)) $dominiosSelect[] = $d;
}

$pageTitle    = I18n::t('emails.titulo');
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title"><?php echo View::e(I18n::t('emails.titulo')); ?></div>
  <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('emails.subtitulo')); ?></div>
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
      <p style="color:#94a3b8;font-size:13px;"><?php echo View::e(I18n::t('emails.nenhum')); ?></p>
    <?php else: ?>
      <div style="overflow:auto;">
        <table style="width:100%;border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('auth.email')); ?></th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Quota</th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.acoes')); ?></th>
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
                      <a href="<?php echo View::e($webmailLink); ?>" target="_blank" rel="noopener" class="botao sm ghost"><?php echo View::e(I18n::t('emails.webmail')); ?></a>
                    <?php endif; ?>
                    <button class="botao sm ghost" onclick="abrirAlterarSenha(<?php echo $emailId; ?>, '<?php echo View::e($emailAddr); ?>')"><?php echo View::e(I18n::t('emails.alterar_senha')); ?></button>
                    <form method="post" action="/cliente/emails/remover" style="display:inline;" onsubmit="return confirm('Remover <?php echo View::e($emailAddr); ?>?')">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                      <input type="hidden" name="email_id" value="<?php echo $emailId; ?>" />
                      <button class="botao danger sm" type="submit"><?php echo View::e(I18n::t('emails.remover')); ?></button>
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
      <div class="card-new-title" style="margin-bottom:6px;"><?php echo View::e(I18n::t('emails.criar')); ?></div>

      <!-- Cota de armazenamento -->
      <?php
        $cotaPct = $cotaTotal > 0 ? min(100, round($cotaUsada / $cotaTotal * 100)) : 0;
        $cotaCor = $cotaPct > 90 ? '#ef4444' : ($cotaPct > 70 ? '#f59e0b' : '#4F46E5');
        if ($cotaTotal >= 1024) {
            $cotaTotalFmt = round($cotaTotal / 1024, 1) . ' GB';
            $cotaUsadaFmt = round($cotaUsada / 1024, 1) . ' GB';
            $cotaDispFmt  = round($cotaDisponivel / 1024, 1) . ' GB';
        } else {
            $cotaTotalFmt = $cotaTotal . ' MB';
            $cotaUsadaFmt = $cotaUsada . ' MB';
            $cotaDispFmt  = $cotaDisponivel . ' MB';
        }
      ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:4px;">
          <span><?php echo View::e(I18n::t('emails.cota_usada')); ?>: <?php echo $cotaUsadaFmt; ?></span>
          <span><?php echo View::e(I18n::t('emails.cota_total')); ?>: <?php echo $cotaTotalFmt; ?></span>
        </div>
        <div style="background:#e2e8f0;border-radius:6px;height:8px;overflow:hidden;">
          <div style="background:<?php echo $cotaCor; ?>;height:100%;width:<?php echo $cotaPct; ?>%;border-radius:6px;transition:width .3s;"></div>
        </div>
        <div style="font-size:11px;color:#94a3b8;margin-top:3px;"><?php echo View::e(I18n::t('emails.cota_disponivel')); ?>: <?php echo $cotaDispFmt; ?></div>
      </div>

      <?php if ($totalEmails >= $limite): ?>
        <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:10px 12px;border-radius:10px;font-size:13px;margin-bottom:12px;">
          Seu plano permite até <strong><?php echo $limite; ?></strong> conta(s).
          <a href="/cliente/planos">Fazer upgrade</a>
        </div>
      <?php else: ?>
        <p style="font-size:13px;color:#64748b;margin-bottom:14px;"><?php echo $totalEmails; ?>/<?php echo $limite; ?> contas usadas.</p>
      <?php endif; ?>

      <form method="post" action="/cliente/emails/criar" id="formCriarEmail" <?php echo $totalEmails >= $limite ? 'style="opacity:.5;pointer-events:none;"' : ''; ?>
            onsubmit="var q=this.querySelector('[name=quota_mb]'),u=this.querySelector('[name=quota_unit]');if(u&&u.value==='gb'){q.value=q.value*1024}">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('emails.usuario')); ?></label>
          <input class="input" type="text" name="local_part" placeholder="usuario" required pattern="[a-z0-9._\-]+" />
        </div>
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('emails.dominio')); ?></label>
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
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('emails.cota')); ?></label>
          <div style="display:flex;gap:8px;align-items:center;">
            <input class="input" type="number" name="quota_mb" value="1024" min="100" max="<?php echo $cotaDisponivel; ?>" step="100" style="width:120px;" />
            <select name="quota_unit" style="font-size:13px;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;cursor:pointer;">
              <option value="mb">MB</option>
              <option value="gb">GB</option>
            </select>
          </div>
          <p style="font-size:11px;color:#94a3b8;margin-top:4px;"><?php echo View::e(I18n::t('emails.cota_hint')); ?></p>
        </div>
        <div style="margin-bottom:14px;">
          <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('auth.senha')); ?></label>
          <input class="input" type="password" name="password" required minlength="8" />
        </div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('emails.criar')); ?></button>
      </form>

      <div style="border-top:1px solid #f1f5f9;padding-top:12px;margin-top:12px;">
        <a href="/cliente/emails/dominios" class="botao ghost sm"><?php echo View::e(I18n::t('emails.dominios')); ?></a>
      </div>
    </div>
  </div>

</div>

<!-- Tutoriais de configuração -->
<?php if ($totalEmails > 0 && $mailcowHost !== ''): ?>
<div style="margin-top:24px;">
  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:6px;"><?php echo View::e(I18n::t('emails.tutoriais_titulo')); ?></div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;"><?php echo View::e(I18n::t('emails.tutoriais_desc')); ?></p>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:16px;">
      <div style="font-weight:600;font-size:13px;margin-bottom:8px;">⚙️ <?php echo View::e(I18n::t('emails.dados_config')); ?></div>
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <tr>
          <td style="padding:5px 10px;color:#64748b;width:160px;">IMAP</td>
          <td style="padding:5px 10px;"><code><?php echo View::e($mailcowHost); ?></code> — <?php echo View::e(I18n::t('emails.porta')); ?> <code>993</code> (SSL/TLS)</td>
        </tr>
        <tr>
          <td style="padding:5px 10px;color:#64748b;">SMTP</td>
          <td style="padding:5px 10px;"><code><?php echo View::e($mailcowHost); ?></code> — <?php echo View::e(I18n::t('emails.porta')); ?> <code>587</code> (STARTTLS)</td>
        </tr>
        <tr>
          <td style="padding:5px 10px;color:#64748b;"><?php echo View::e(I18n::t('emails.usuario_config')); ?></td>
          <td style="padding:5px 10px;"><?php echo View::e(I18n::t('emails.seu_email_completo')); ?></td>
        </tr>
        <tr>
          <td style="padding:5px 10px;color:#64748b;"><?php echo View::e(I18n::t('auth.senha')); ?></td>
          <td style="padding:5px 10px;"><?php echo View::e(I18n::t('emails.senha_definida')); ?></td>
        </tr>
      </table>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;">

      <!-- Outlook -->
      <details style="border:1px solid #e2e8f0;border-radius:10px;padding:0;">
        <summary style="padding:12px 14px;cursor:pointer;font-weight:600;font-size:13px;list-style:none;display:flex;align-items:center;gap:8px;">
          <span style="font-size:18px;">📧</span> Outlook (<?php echo View::e(I18n::t('emails.pc_celular')); ?>)
        </summary>
        <div style="padding:0 14px 14px;font-size:13px;color:#475569;line-height:1.8;">
          <ol style="padding-left:18px;margin:0;">
            <li><?php echo View::e(I18n::t('emails.outlook_1')); ?></li>
            <li><?php echo View::e(I18n::t('emails.outlook_2')); ?></li>
            <li><?php echo View::e(I18n::t('emails.outlook_3')); ?></li>
            <li><?php echo I18n::t('emails.outlook_4'); ?></li>
            <li><?php echo View::e(I18n::t('emails.outlook_5')); ?></li>
          </ol>
          <p style="margin-top:8px;font-size:12px;color:#94a3b8;"><?php echo View::e(I18n::t('emails.outlook_mobile')); ?></p>
        </div>
      </details>

      <!-- Gmail -->
      <details style="border:1px solid #e2e8f0;border-radius:10px;padding:0;">
        <summary style="padding:12px 14px;cursor:pointer;font-weight:600;font-size:13px;list-style:none;display:flex;align-items:center;gap:8px;">
          <span style="font-size:18px;">📨</span> Gmail (<?php echo View::e(I18n::t('emails.pc_celular')); ?>)
        </summary>
        <div style="padding:0 14px 14px;font-size:13px;color:#475569;line-height:1.8;">
          <ol style="padding-left:18px;margin:0;">
            <li><?php echo View::e(I18n::t('emails.gmail_1')); ?></li>
            <li><?php echo View::e(I18n::t('emails.gmail_2')); ?></li>
            <li><?php echo View::e(I18n::t('emails.gmail_3')); ?></li>
            <li><?php echo I18n::t('emails.gmail_4'); ?></li>
            <li><?php echo View::e(I18n::t('emails.gmail_5')); ?></li>
          </ol>
          <p style="margin-top:8px;font-size:12px;color:#94a3b8;"><?php echo View::e(I18n::t('emails.gmail_mobile')); ?></p>
        </div>
      </details>

      <!-- Apple Mail -->
      <details style="border:1px solid #e2e8f0;border-radius:10px;padding:0;">
        <summary style="padding:12px 14px;cursor:pointer;font-weight:600;font-size:13px;list-style:none;display:flex;align-items:center;gap:8px;">
          <span style="font-size:18px;">🍎</span> Apple Mail (Mac / iPhone / iPad)
        </summary>
        <div style="padding:0 14px 14px;font-size:13px;color:#475569;line-height:1.8;">
          <ol style="padding-left:18px;margin:0;">
            <li><?php echo View::e(I18n::t('emails.apple_1')); ?></li>
            <li><?php echo View::e(I18n::t('emails.apple_2')); ?></li>
            <li><?php echo View::e(I18n::t('emails.apple_3')); ?></li>
            <li><?php echo I18n::t('emails.apple_4'); ?></li>
            <li><?php echo View::e(I18n::t('emails.apple_5')); ?></li>
          </ol>
        </div>
      </details>

      <!-- Thunderbird -->
      <details style="border:1px solid #e2e8f0;border-radius:10px;padding:0;">
        <summary style="padding:12px 14px;cursor:pointer;font-weight:600;font-size:13px;list-style:none;display:flex;align-items:center;gap:8px;">
          <span style="font-size:18px;">🦊</span> Thunderbird
        </summary>
        <div style="padding:0 14px 14px;font-size:13px;color:#475569;line-height:1.8;">
          <ol style="padding-left:18px;margin:0;">
            <li><?php echo View::e(I18n::t('emails.thunder_1')); ?></li>
            <li><?php echo View::e(I18n::t('emails.thunder_2')); ?></li>
            <li><?php echo I18n::t('emails.thunder_3'); ?></li>
            <li><?php echo View::e(I18n::t('emails.thunder_4')); ?></li>
          </ol>
        </div>
      </details>

    </div>
  </div>
</div>
<?php endif; ?>

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
