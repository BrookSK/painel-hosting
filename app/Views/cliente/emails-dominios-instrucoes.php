<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$dominio     = is_array($dominio ?? null) ? $dominio : [];
$dkim        = (string)($dkim ?? '');
$dnsTemplate = (string)($dns_template ?? '');
$domain      = (string)($dominio['domain'] ?? '');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Instruções DNS — <?php echo View::e($domain); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Instruções DNS</div>
        <div style="opacity:.9;font-size:13px;"><?php echo View::e($domain); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/emails/dominios">← Domínios</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:780px;margin:0 auto;">
      <h1 class="titulo">Configure o DNS do domínio <code><?php echo View::e($domain); ?></code></h1>

      <div class="aviso" style="margin-bottom:16px;">
        Após configurar os registros abaixo no seu provedor de DNS, volte e clique em <strong>Verificar DNS</strong>.
        A propagação pode levar de alguns minutos até 48 horas.
      </div>

      <!-- MX -->
      <div class="card" style="margin:0 0 12px 0;">
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
              <td style="padding:8px 10px;"><span class="badge">MX</span></td>
              <td style="padding:8px 10px;"><code>@</code></td>
              <td style="padding:8px 10px;"><code>mail.<?php echo View::e($domain); ?></code></td>
              <td style="padding:8px 10px;"><code>10</code></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- SPF -->
      <div class="card" style="margin:0 0 12px 0;">
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
              <td style="padding:8px 10px;"><span class="badge">TXT</span></td>
              <td style="padding:8px 10px;"><code>@</code></td>
              <td style="padding:8px 10px;"><code>v=spf1 mx ~all</code></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- DKIM -->
      <div class="card" style="margin:0 0 12px 0;">
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
                <td style="padding:8px 10px;"><span class="badge">TXT</span></td>
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
          <div class="aviso" style="margin:0;">
            O registro DKIM ainda não está disponível. Ele será gerado automaticamente pelo servidor de email após a propagação do MX.
            Volte aqui após verificar o DNS para obter o valor.
          </div>
        <?php endif; ?>
      </div>

      <!-- Template adicional -->
      <?php if ($dnsTemplate !== ''): ?>
        <div class="card" style="margin:0 0 16px 0;">
          <div style="font-weight:600;font-size:13px;margin-bottom:8px;color:#64748b;">Instruções adicionais</div>
          <pre style="white-space:pre-wrap;font-size:13px;color:#334155;background:#f8fafc;padding:12px;border-radius:10px;overflow:auto;"><?php echo View::e($dnsTemplate); ?></pre>
        </div>
      <?php endif; ?>

      <div class="linha" style="gap:10px;flex-wrap:wrap;">
        <a href="/cliente/emails/dominios" class="botao ghost">← Voltar</a>
        <form method="post" action="/cliente/emails/dominios/verificar" style="display:inline;">
          <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
          <input type="hidden" name="dominio_id" value="<?php echo (int)($dominio['id'] ?? 0); ?>" />
          <button class="botao" type="submit">Verificar DNS agora</button>
        </form>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/../_partials/footer.php'; ?>
  <?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
