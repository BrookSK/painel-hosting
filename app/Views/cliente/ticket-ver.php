<?php
declare(strict_types=1);
use LRV\Core\View;

$st = (string)($ticket['status'] ?? 'open');

function badgeStatusTicket(string $st): string {
    if ($st === 'closed') return '<span class="badge-new" style="background:#f1f5f9;color:#334155;">Fechado</span>';
    return '<span class="badge-new badge-green">Aberto</span>';
}

function badgePrioridadeTicket(string $p): string {
    if ($p === 'high') return '<span class="badge-new badge-red">Alta</span>';
    if ($p === 'low')  return '<span class="badge-new badge-green">Baixa</span>';
    return '<span class="badge-new badge-yellow">Média</span>';
}

$pageTitle    = 'Ticket #' . (int)($ticket['id'] ?? 0);
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Ticket #<?php echo (int)($ticket['id'] ?? 0); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e((string)($ticket['subject'] ?? '')); ?></div>
  </div>
  <a href="/cliente/tickets" class="botao ghost sm">Voltar</a>
</div>

<div class="card-new" style="margin-bottom:14px;">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php echo badgeStatusTicket((string)($ticket['status'] ?? 'open')); ?>
      <?php echo badgePrioridadeTicket((string)($ticket['priority'] ?? 'medium')); ?>
      <span class="badge-new" style="background:#e0e7ff;color:#1e3a8a;"><?php echo View::e((string)($ticket['department'] ?? '')); ?></span>
    </div>
    <div style="font-size:13px;color:#64748b;">Atualizado: <?php echo View::e((string)($ticket['updated_at'] ?? '')); ?></div>
  </div>
</div>

<div class="card-new" style="margin-bottom:14px;">
  <div class="card-new-title" style="margin-bottom:12px;">Mensagens</div>
  <?php foreach (($mensagens ?? []) as $m): ?>
    <?php $tipo = (string)($m['sender_type'] ?? ''); ?>
    <div style="border:1px solid #e5e7eb;border-radius:12px;padding:12px;margin-bottom:10px;background:<?php echo $tipo === 'client' ? '#ffffff' : '#f8fafc'; ?>;">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <div style="font-weight:700;font-size:13px;"><?php echo $tipo === 'client' ? 'Você' : 'Equipe'; ?></div>
        <div style="font-size:12px;color:#94a3b8;"><?php echo View::e((string)($m['created_at'] ?? '')); ?></div>
      </div>
      <div style="white-space:pre-wrap;line-height:1.55;color:#0f172a;font-size:14px;"><?php echo View::e((string)($m['message'] ?? '')); ?></div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($mensagens)): ?>
    <div style="color:#94a3b8;font-size:13px;">Nenhuma mensagem ainda.</div>
  <?php endif; ?>
</div>

<div class="card-new">
  <div class="card-new-title" style="margin-bottom:12px;">Responder</div>
  <?php if (!empty($erro)): ?>
    <div class="erro"><?php echo View::e((string)$erro); ?></div>
  <?php endif; ?>
  <?php if ($st === 'closed'): ?>
    <div style="font-size:13px;color:#64748b;margin-bottom:16px;">Este ticket está fechado.</div>
    <?php
      $_jaAvaliou = false;
      try {
          $_pdo = \LRV\Core\BancoDeDados::pdo();
          $_s = $_pdo->prepare('SELECT id FROM satisfaction_surveys WHERE type = :t AND reference_id = :r LIMIT 1');
          $_s->execute([':t' => 'ticket', ':r' => (int)($ticket['id'] ?? 0)]);
          $_jaAvaliou = (bool)$_s->fetch();
      } catch (\Throwable) {}
    ?>
    <?php if (!$_jaAvaliou): ?>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;">
        <div style="font-weight:600;font-size:14px;color:#166534;margin-bottom:4px;">Como foi o atendimento?</div>
        <p style="font-size:13px;color:#15803d;margin:0 0 12px;">Avalie este ticket e nos ajude a melhorar.</p>
        <a href="/cliente/avaliar?type=ticket&id=<?php echo (int)($ticket['id'] ?? 0); ?>" class="botao" style="font-size:13px;padding:8px 18px;">Avaliar atendimento</a>
      </div>
    <?php else: ?>
      <div style="font-size:13px;color:#16a34a;">Voce ja avaliou este atendimento. Obrigado!</div>
    <?php endif; ?>
  <?php else: ?>
    <form method="post" action="/cliente/tickets/responder">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <input type="hidden" name="ticket_id" value="<?php echo (int)($ticket['id'] ?? 0); ?>" />
      <textarea class="input" name="message" rows="6" style="margin-bottom:12px;"></textarea>
      <button class="botao" type="submit">Enviar resposta</button>
    </form>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
