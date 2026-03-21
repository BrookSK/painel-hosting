<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$st = (string)($ticket['status']??'open');

function badgeStatusEquipeTicket(string $st): string {
    $map = ['open'=>[I18n::t('eq_tickets.aberto'),'badge-blue'],'in_progress'=>[I18n::t('eq_tickets.em_andamento'),'badge-blue'],'waiting_client'=>[I18n::t('eq_tickets.aguard_cliente'),'badge-yellow'],'closed'=>[I18n::t('eq_tickets.fechado'),'badge-gray']];
    $d = $map[$st] ?? [$st,'badge-gray'];
    return '<span class="badge-new '.$d[1].'">'.View::e($d[0]).'</span>';
}
function badgePrioridadeEquipeTicket(string $p): string {
    if ($p==='high') return '<span class="badge-new badge-red">'.I18n::t('eq_tickets.alta').'</span>';
    if ($p==='low')  return '<span class="badge-new badge-green">'.I18n::t('eq_tickets.baixa').'</span>';
    return '<span class="badge-new badge-yellow">'.I18n::t('eq_tickets.media').'</span>';
}

$pageTitle = 'Ticket #'.(int)($ticket['id']??0);
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Ticket #<?php echo (int)($ticket['id']??0); ?></div>
<div class="page-subtitle"><?php echo View::e((string)($ticket['subject']??'')); ?></div>

<div class="card-new" style="margin-bottom:14px;">
  <div class="linha" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
      <div class="linha" style="gap:8px;margin-bottom:8px;">
        <?php echo badgeStatusEquipeTicket($st); ?>
        <?php echo badgePrioridadeEquipeTicket((string)($ticket['priority']??'medium')); ?>
        <span class="badge-new badge-blue"><?php echo View::e((string)($ticket['department']??'')); ?></span>
      </div>
      <div style="font-size:13px;"><?php echo View::e(I18n::t('eq_tickets.cliente')); ?>: <strong><?php echo View::e((string)($ticket['client_name']??'')); ?></strong> (<?php echo View::e((string)($ticket['client_email']??'')); ?>)</div>
      <?php if (!empty($ticket['assigned_name'])): ?>
        <div style="font-size:13px;margin-top:4px;"><?php echo View::e(I18n::t('eq_ticket.atribuido_a')); ?>: <strong><?php echo View::e((string)($ticket['assigned_name']??'')); ?></strong></div>
      <?php endif; ?>
    </div>
    <div style="font-size:13px;opacity:.8;"><?php echo View::e(I18n::t('eq_ticket.atualizado')); ?>: <?php echo View::e((string)($ticket['updated_at']??'')); ?></div>
  </div>
</div>

<div class="card-new" style="margin-bottom:14px;">
  <div class="card-new-title"><?php echo View::e(I18n::t('eq_ticket.mensagens')); ?></div>
  <?php foreach (($mensagens??[]) as $m): ?>
    <?php $tipo = (string)($m['sender_type']??''); ?>
    <div style="border:1px solid #e2e8f0;border-radius:12px;padding:12px;margin-bottom:10px;background:<?php echo $tipo==='team'?'#f0f4ff':'#f8fafc'; ?>;">
      <div class="linha" style="justify-content:space-between;margin-bottom:6px;">
        <div style="font-weight:700;font-size:13px;color:<?php echo $tipo==='team'?'#4F46E5':'#0f172a'; ?>;"><?php echo $tipo==='team'?View::e(I18n::t('eq_ticket.equipe')):View::e(I18n::t('eq_tickets.cliente')); ?></div>
        <div style="font-size:12px;opacity:.8;"><?php echo View::e((string)($m['created_at']??'')); ?></div>
      </div>
      <div style="white-space:pre-wrap;line-height:1.55;font-size:14px;"><?php echo View::e((string)($m['message']??'')); ?></div>
      <?php if (!empty($m['attachment_name'])): ?>
        <div style="margin-top:8px;font-size:12px;"><?php echo View::e(I18n::t('eq_ticket.anexo_label')); ?>: <?php echo View::e((string)($m['attachment_name']??'')); ?><?php if (!empty($m['attachment_size'])): ?> (<?php echo View::e((string)round((int)$m['attachment_size']/1024,1)); ?> KB)<?php endif; ?></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if (empty($mensagens)): ?><div class="texto" style="margin:0;"><?php echo View::e(I18n::t('eq_ticket.nenhuma_msg')); ?></div><?php endif; ?>
</div>

<div class="grid">
  <div class="card-new">
    <div class="card-new-title"><?php echo View::e(I18n::t('eq_ticket.responder')); ?></div>
    <?php if (!empty($erro)): ?><div class="erro"><?php echo View::e((string)$erro); ?></div><?php endif; ?>
    <?php if ($st==='closed'): ?>
      <div class="texto" style="margin:0;"><?php echo View::e(I18n::t('eq_ticket.ticket_fechado')); ?></div>
    <?php else: ?>
      <form method="post" action="/equipe/tickets/responder" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="ticket_id" value="<?php echo (int)($ticket['id']??0); ?>" />
        <textarea class="input" name="message" rows="6" style="margin-bottom:10px;" placeholder="<?php echo View::e(I18n::t('eq_ticket.sua_resposta')); ?>"></textarea>
        <div style="margin-bottom:12px;">
          <label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo View::e(I18n::t('eq_ticket.anexo')); ?></label>
          <input type="file" name="attachment" style="font-size:13px;" />
        </div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_ticket.enviar_resposta')); ?></button>
      </form>
    <?php endif; ?>
  </div>

  <div class="card-new">
    <div class="card-new-title"><?php echo View::e(I18n::t('eq_ticket.acoes')); ?></div>
    <?php if ($st!=='closed'): ?>
      <div style="margin-bottom:14px;">
        <div style="font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_ticket.alterar_status')); ?></div>
        <form method="post" action="/equipe/tickets/status">
          <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
          <input type="hidden" name="ticket_id" value="<?php echo (int)($ticket['id']??0); ?>" />
          <div class="linha" style="gap:6px;">
            <select class="input" name="status" style="max-width:180px;">
              <option value="open" <?php echo $st==='open'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.aberto')); ?></option>
              <option value="in_progress" <?php echo $st==='in_progress'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.em_andamento')); ?></option>
              <option value="waiting_client" <?php echo $st==='waiting_client'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.aguard_cliente')); ?></option>
              <option value="closed"><?php echo View::e(I18n::t('eq_tickets.fechado')); ?></option>
            </select>
            <button class="botao" type="submit"><?php echo View::e(I18n::t('geral.salvar')); ?></button>
          </div>
        </form>
      </div>
      <div>
        <div style="font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_ticket.atribuir_para')); ?></div>
        <form method="post" action="/equipe/tickets/atribuir">
          <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
          <input type="hidden" name="ticket_id" value="<?php echo (int)($ticket['id']??0); ?>" />
          <div class="linha" style="gap:6px;">
            <select class="input" name="user_id" style="max-width:180px;">
              <option value="0"><?php echo View::e(I18n::t('eq_ticket.ninguem')); ?></option>
              <?php foreach (($usuarios??[]) as $u): ?>
                <option value="<?php echo (int)($u['id']??0); ?>" <?php echo (int)($ticket['assigned_to']??0)===(int)($u['id']??0)?'selected':''; ?>><?php echo View::e((string)($u['name']??'')); ?></option>
              <?php endforeach; ?>
            </select>
            <button class="botao sec" type="submit"><?php echo View::e(I18n::t('eq_ticket.atribuir')); ?></button>
          </div>
        </form>
      </div>
    <?php else: ?>
      <div class="texto" style="margin:0;"><?php echo View::e(I18n::t('eq_ticket.nenhuma_acao')); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
