<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$eventos = is_array($eventos ?? null) ? $eventos : [];
$erro = (string)($erro ?? '');

// Descrições amigáveis por evento (os não listados usam o próprio nome)
$descricoes = [
    'client.created'        => 'Um cliente foi criado',
    'client.updated'        => 'Um cliente foi atualizado',
    'hosting.created'       => 'Provisionamento de servidor iniciado',
    'hosting.ready'         => 'Servidor pronto (no ar)',
    'hosting.suspended'     => 'Servidor suspenso',
    'hosting.cancelled'     => 'Servidor cancelado',
    'hosting.restarted'     => 'Servidor reiniciado',
    'application.installed' => 'Aplicação criada / instalada',
    'application.deployed'  => 'Deploy concluído',
    'application.removed'   => 'Aplicação removida',
    'domain.added'          => 'Domínio adicionado',
    'domain.removed'        => 'Domínio removido',
    'backup.created'        => 'Backup criado',
    'backup.restored'       => 'Backup restaurado',
    'subscription.created'  => 'Assinatura criada',
    'subscription.cancelled'=> 'Assinatura cancelada',
    'subscription.renewed'  => 'Assinatura renovada',
    'payment.received'      => 'Pagamento recebido',
    'payment.overdue'       => 'Pagamento em atraso',
    'payment.refunded'      => 'Pagamento reembolsado',
    'ticket.created'        => 'Ticket aberto',
    'ticket.replied'        => 'Ticket respondido',
    'ticket.closed'         => 'Ticket fechado',
    'monitoring.alert'      => 'Alerta de monitoramento',
];

// Eventos sugeridos como padrão (provisionamento) — vêm marcados
$sugeridos = ['hosting.created', 'hosting.ready', 'application.installed', 'application.deployed', 'domain.added'];

$pageTitle = 'Criar Webhook';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<style>
.ev-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:8px; margin-top:10px; }
.ev-item { display:flex; align-items:flex-start; gap:10px; font-size:13px; color:#cbd5e1; padding:10px 12px; background:#1e293b; border-radius:10px; border:1.5px solid #334155; cursor:pointer; transition:all .15s; user-select:none; }
.ev-item:hover { border-color:#6366f1; }
.ev-item:has(input:checked) { border-color:#6366f1; background:rgba(99,102,241,.08); color:#e2e8f0; }
.ev-item input[type="checkbox"] { accent-color:#6366f1; width:16px; height:16px; cursor:pointer; flex-shrink:0; margin-top:2px; }
.ev-item .ev-name { font-family:'JetBrains Mono',monospace; font-size:12px; color:#93c5fd; }
.ev-item .ev-desc { font-size:11px; color:#94a3b8; display:block; }
</style>

<div class="page-title">Criar Webhook</div>
<div class="page-subtitle">Informe a URL do seu sistema e escolha os eventos que deseja receber.</div>

<?php if ($erro === 'url'): ?>
  <div class="erro">Informe uma URL HTTPS válida (ex.: https://seu-sistema.com/webhook).</div>
<?php elseif ($erro === 'eventos'): ?>
  <div class="erro">Selecione ao menos um evento.</div>
<?php elseif ($erro === 'csrf'): ?>
  <div class="erro">Sessão expirada. Tente novamente.</div>
<?php endif; ?>

<form method="post" action="/cliente/webhooks/criar" class="card-new" style="max-width:760px;">
  <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

  <label style="display:block;font-size:13px;font-weight:600;color:#e2e8f0;margin-bottom:6px;">URL de destino (HTTPS)</label>
  <input class="input" type="url" name="url" required placeholder="https://seu-sistema.com/provisioning/lrvWebhook"
         style="width:100%;margin-bottom:4px;" />
  <div style="font-size:12px;color:#94a3b8;margin-bottom:20px;">O LRV Cloud enviará um POST para esta URL a cada evento, com a assinatura <code>X-Webhook-Signature: sha256=...</code>.</div>

  <label style="display:block;font-size:13px;font-weight:600;color:#e2e8f0;margin-bottom:2px;">Eventos</label>
  <div style="font-size:12px;color:#94a3b8;">Os eventos de provisionamento já vêm marcados.</div>
  <div class="ev-grid">
    <?php foreach ($eventos as $ev):
      $ev = (string)$ev;
      $desc = $descricoes[$ev] ?? $ev;
      $checked = in_array($ev, $sugeridos, true) ? 'checked' : '';
    ?>
    <label class="ev-item">
      <input type="checkbox" name="eventos[]" value="<?php echo View::e($ev); ?>" <?php echo $checked; ?> />
      <span>
        <span class="ev-name"><?php echo View::e($ev); ?></span>
        <span class="ev-desc"><?php echo View::e($desc); ?></span>
      </span>
    </label>
    <?php endforeach; ?>
  </div>

  <div style="display:flex;gap:10px;margin-top:24px;">
    <button class="botao" type="submit">Criar Webhook</button>
    <a class="botao ghost" href="/cliente/webhooks">Cancelar</a>
  </div>
</form>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
