<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$webhooks = is_array($webhooks ?? null) ? $webhooks : [];
$novoSecret = (string)($novoSecret ?? '');
$novoUrl = (string)($novoUrl ?? '');
$sucesso = (string)($sucesso ?? '');
$erro = (string)($erro ?? '');

$pageTitle = 'Webhooks';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<style>
.wh-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.wh-secret {
    background:#1e3a5f; border:2px solid #3b82f6; border-radius:10px; padding:16px 20px; margin-bottom:20px; position:relative;
}
.wh-secret code {
    font-family:'JetBrains Mono','Fira Code',monospace; font-size:13px; color:#93c5fd; word-break:break-all; display:block; margin:8px 0;
}
.wh-secret .warning-text { color:#fbbf24; font-size:12px; font-weight:600; margin-top:8px; }
.wh-copy-btn {
    position:absolute; top:12px; right:12px; background:#3b82f6; color:#fff; border:none; border-radius:6px; padding:6px 12px; font-size:12px; cursor:pointer; transition:background .2s;
}
.wh-copy-btn:hover { background:#2563eb; }
.wh-table { width:100%; border-collapse:collapse; font-size:13px; }
.wh-table th { padding:10px 12px; text-align:left; color:#94a3b8; font-weight:600; border-bottom:2px solid #334155; font-size:12px; text-transform:uppercase; letter-spacing:.5px; }
.wh-table td { padding:10px 12px; border-bottom:1px solid #1e293b; color:#cbd5e1; vertical-align:middle; }
.wh-table tr:hover td { background:rgba(59,130,246,.04); }
.wh-url { font-family:'JetBrains Mono',monospace; font-size:12px; color:#cbd5e1; word-break:break-all; }
.wh-event-pill { display:inline-block; background:#1e293b; border:1px solid #334155; border-radius:99px; padding:2px 8px; font-size:11px; color:#93c5fd; margin:2px 2px 0 0; font-family:'JetBrains Mono',monospace; }
.wh-deliveries { margin-top:8px; font-size:12px; }
.wh-del-row { display:flex; gap:10px; padding:6px 10px; border-bottom:1px solid #1e293b; align-items:center; }
@media (max-width:900px){ .wh-table-wrap{ overflow-x:auto; } }
</style>

<div class="wh-header">
  <div>
    <div class="page-title">Webhooks</div>
    <div class="page-subtitle" style="margin-bottom:0;">Receba notificações automáticas no seu sistema quando eventos acontecem (cliente criado, servidor pronto, deploy concluído, etc.).</div>
  </div>
  <button class="botao" onclick="window.location='/cliente/webhooks/novo'">
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:4px;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    Criar Webhook
  </button>
</div>

<?php if ($sucesso === 'criado'): ?>
  <div class="sucesso">Webhook criado com sucesso. Copie o secret abaixo — ele não será mostrado novamente.</div>
<?php elseif ($sucesso === 'removido'): ?>
  <div class="sucesso">Webhook removido.</div>
<?php elseif ($sucesso === 'nao_encontrado'): ?>
  <div class="erro">Webhook não encontrado.</div>
<?php endif; ?>

<?php if ($erro === 'csrf'): ?>
  <div class="erro">Sessão expirada. Tente novamente.</div>
<?php elseif ($erro === 'url'): ?>
  <div class="erro">Informe uma URL HTTPS válida.</div>
<?php elseif ($erro === 'eventos'): ?>
  <div class="erro">Selecione ao menos um evento.</div>
<?php elseif ($erro === 'invalido'): ?>
  <div class="erro">Webhook inválido.</div>
<?php endif; ?>

<?php if ($novoSecret !== ''): ?>
<div class="wh-secret">
  <div style="font-weight:700;color:#e2e8f0;font-size:14px;display:flex;align-items:center;gap:8px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="#93c5fd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
    Secret do webhook (HMAC)
  </div>
  <?php if ($novoUrl !== ''): ?><div style="font-size:12px;color:#94a3b8;margin-top:4px;"><?php echo View::e($novoUrl); ?></div><?php endif; ?>
  <code id="novoSecretTexto"><?php echo View::e($novoSecret); ?></code>
  <button class="wh-copy-btn" onclick="copiarSecret()">Copiar</button>
  <div class="warning-text">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12" style="vertical-align:middle;margin-right:4px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    Guarde este secret agora. Use-o para validar a assinatura <code style="display:inline;margin:0;">X-Webhook-Signature</code> de cada entrega. Não será exibido de novo.
  </div>
</div>
<?php endif; ?>

<div class="card-new">
<?php if (empty($webhooks)): ?>
  <div style="text-align:center;padding:40px 20px;">
    <div style="margin-bottom:10px;"><svg viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></div>
    <div style="font-size:15px;font-weight:600;color:#e2e8f0;margin-bottom:4px;">Nenhum webhook configurado</div>
    <div style="font-size:13px;color:#94a3b8;">Crie um webhook para receber notificações de eventos no seu sistema.</div>
  </div>
<?php else: ?>
  <div class="wh-table-wrap">
    <table class="wh-table">
      <thead>
        <tr>
          <th>URL</th>
          <th>Eventos</th>
          <th><?= I18n::t('geral.status') ?></th>
          <th>Falhas</th>
          <th>Criado</th>
          <th><?= I18n::t('geral.acoes') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($webhooks as $w):
          $wId = (int)($w['id'] ?? 0);
          $wStatus = (string)($w['status'] ?? 'active');
          $wEvents = is_array($w['events'] ?? null) ? $w['events'] : (json_decode((string)($w['events'] ?? '[]'), true) ?: []);
          $wFail = (int)($w['failure_count'] ?? 0);
          $wCreated = date('d/m/Y', strtotime((string)($w['created_at'] ?? 'now')));
          $statusBadge = $wStatus === 'active'
            ? '<span class="badge-new badge-green">Ativo</span>'
            : '<span class="badge-new badge-gray">' . View::e($wStatus) . '</span>';
        ?>
        <tr>
          <td><span class="wh-url"><?php echo View::e((string)($w['url'] ?? '')); ?></span></td>
          <td>
            <?php foreach ($wEvents as $ev): ?><span class="wh-event-pill"><?php echo View::e((string)$ev); ?></span><?php endforeach; ?>
          </td>
          <td><?php echo $statusBadge; ?></td>
          <td style="font-size:12px;<?php echo $wFail > 0 ? 'color:#ef4444;' : ''; ?>"><?php echo $wFail; ?></td>
          <td style="font-size:12px;"><?php echo View::e($wCreated); ?></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
              <button class="botao ghost sm" style="font-size:11px;" onclick="verEntregas(<?php echo $wId; ?>)">Entregas</button>
              <form method="post" action="/cliente/webhooks/remover" style="display:inline;" onsubmit="return confirm('Remover este webhook? Seu sistema deixará de receber os eventos.')">
                <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                <input type="hidden" name="webhook_id" value="<?php echo $wId; ?>" />
                <button class="botao ghost sm" type="submit" style="font-size:11px;color:#ef4444;">Remover</button>
              </form>
            </div>
            <div id="entregas-<?php echo $wId; ?>" class="wh-deliveries" style="display:none;"></div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</div>

<script>
function copiarSecret() {
    const el = document.getElementById('novoSecretTexto');
    if (!el) return;
    navigator.clipboard.writeText(el.textContent.trim()).then(() => {
        const btn = document.querySelector('.wh-copy-btn');
        if (btn) { btn.textContent = 'Copiado!'; setTimeout(() => btn.textContent = 'Copiar', 2000); }
    });
}

function verEntregas(id) {
    const box = document.getElementById('entregas-' + id);
    if (!box) return;
    if (box.style.display === 'block') { box.style.display = 'none'; return; }
    box.style.display = 'block';
    box.innerHTML = '<div style="color:#64748b;padding:6px 0;">Carregando...</div>';
    fetch('/cliente/webhooks/entregas?id=' + id)
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (!d.ok || !Array.isArray(d.deliveries) || d.deliveries.length === 0) {
          box.innerHTML = '<div style="color:#64748b;padding:6px 0;">Nenhuma entrega ainda.</div>';
          return;
        }
        var html = '';
        d.deliveries.forEach(function(x){
          var ok = !!x.success;
          var cor = ok ? '#22c55e' : '#ef4444';
          var icon = ok ? '\u2713' : '\u2717';
          var st = x.response_status ? ('HTTP ' + x.response_status) : (x.error_message || 'sem resposta');
          html += '<div class="wh-del-row"><span style="color:' + cor + ';">' + icon + '</span>'
                + '<span style="flex:1;">' + (x.event_type || '') + '</span>'
                + '<span style="color:#94a3b8;">' + st + '</span>'
                + '<span style="color:#64748b;">' + (x.delivered_at || '') + '</span></div>';
        });
        box.innerHTML = html;
      })
      .catch(function(){ box.innerHTML = '<div style="color:#ef4444;padding:6px 0;">Erro ao carregar.</div>'; });
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
