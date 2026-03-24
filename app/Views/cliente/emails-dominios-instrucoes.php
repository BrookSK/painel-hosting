<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$dominio      = is_array($dominio ?? null) ? $dominio : [];
$dkim         = (string)($dkim ?? '');
$dnsTemplate  = (string)($dns_template ?? '');
$domain       = (string)($dominio['domain'] ?? '');
$mailcowHost  = (string)($mailcow_host ?? '');

$pageTitle    = 'Instruções DNS — ' . $domain;
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Instruções DNS</div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e($domain); ?></div>
  </div>
  <a href="/cliente/emails/dominios" class="botao ghost sm">← <?php echo View::e(I18n::t('emails.dominios')); ?></a>
</div>

<div class="card-new" style="max-width:780px;">
  <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;">
    Após configurar os registros abaixo no seu provedor de DNS, volte e clique em <strong>Verificar DNS</strong>.
    A propagação pode levar de alguns minutos até 48 horas.
  </div>

  <!-- MX -->
  <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:12px;">
    <div style="font-weight:600;font-size:13px;margin-bottom:8px;color:#4F46E5;">Registro MX</div>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr>
          <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Tipo</th>
          <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Nome</th>
          <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Valor</th>
          <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Prioridade</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding:8px 10px;"><span class="badge-new">MX</span></td>
          <td style="padding:8px 10px;"><code>@</code></td>
          <td style="padding:8px 10px;"><code><?php echo View::e($mailcowHost !== '' ? $mailcowHost : 'mail.' . $domain); ?></code></td>
          <td style="padding:8px 10px;"><code>10</code></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- SPF -->
  <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:12px;">
    <div style="font-weight:600;font-size:13px;margin-bottom:8px;color:#4F46E5;">Registro SPF (TXT)</div>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr>
          <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Tipo</th>
          <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Nome</th>
          <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Valor</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding:8px 10px;"><span class="badge-new">TXT</span></td>
          <td style="padding:8px 10px;"><code>@</code></td>
          <td style="padding:8px 10px;"><code>v=spf1 mx ~all</code></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- DKIM -->
  <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:12px;">
    <div style="font-weight:600;font-size:13px;margin-bottom:8px;color:#4F46E5;">Registro DKIM (TXT)</div>
    <?php if ($dkim !== ''): ?>
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr>
            <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Tipo</th>
            <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Nome</th>
            <th style="text-align:left;padding:6px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;">Valor</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding:8px 10px;"><span class="badge-new">TXT</span></td>
            <td style="padding:8px 10px;"><code>dkim._domainkey</code></td>
            <td style="padding:8px 10px;word-break:break-all;">
              <code style="font-size:11px;"><?php echo View::e($dkim); ?></code>
              <button type="button" onclick="navigator.clipboard.writeText(this.dataset.v)" data-v="<?php echo View::e($dkim); ?>"
                      style="margin-left:8px;background:none;border:1px solid #e2e8f0;border-radius:6px;padding:2px 8px;font-size:11px;cursor:pointer;">
                Copiar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    <?php else: ?>
      <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:10px 12px;border-radius:8px;font-size:13px;">
        O registro DKIM ainda não está disponível. Ele será gerado automaticamente após a propagação do MX.
      </div>
    <?php endif; ?>
  </div>

  <?php if ($dnsTemplate !== ''): ?>
    <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:16px;">
      <div style="font-weight:600;font-size:13px;margin-bottom:8px;color:#64748b;">Instruções adicionais</div>
      <pre style="white-space:pre-wrap;font-size:13px;color:#334155;background:#f8fafc;padding:12px;border-radius:10px;overflow:auto;"><?php echo View::e($dnsTemplate); ?></pre>
    </div>
  <?php endif; ?>

  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="/cliente/emails/dominios" class="botao ghost">← <?php echo View::e(I18n::t('geral.voltar')); ?></a>
    <form method="post" action="/cliente/emails/dominios/verificar" style="display:inline;">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <input type="hidden" name="dominio_id" value="<?php echo (int)($dominio['id'] ?? 0); ?>" />
      <button class="botao" type="submit"><?php echo View::e(I18n::t('emails.verificar_dns')); ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
