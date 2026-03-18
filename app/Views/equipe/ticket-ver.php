<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$st = (string) ($ticket['status'] ?? 'open');

function badgeStatusEquipeTicket(string $st): string
{
    if ($st === 'closed') {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">Fechado</span>';
    }
    return '<span class="badge">Aberto</span>';
}

function badgePrioridadeEquipeTicket(string $p): string
{
    if ($p === 'high') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Alta</span>';
    }
    if ($p === 'low') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Baixa</span>';
    }
    return '<span class="badge" style="background:#fef3c7;color:#92400e;">Média</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Ticket #<?php echo (int) ($ticket['id'] ?? 0); ?></div>
        <div style="opacity:.9; font-size:13px;"><?php echo View::e((string) ($ticket['subject'] ?? '')); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/tickets">Tickets</a>
        <a href="/equipe/painel">Painel</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="margin-bottom:14px;">
      <div class="linha" style="justify-content:space-between;">
        <div>
          <div class="linha" style="gap:8px; margin-bottom:8px;">
            <?php echo badgeStatusEquipeTicket((string) ($ticket['status'] ?? 'open')); ?>
            <?php echo badgePrioridadeEquipeTicket((string) ($ticket['priority'] ?? 'medium')); ?>
            <span class="badge" style="background:#e0e7ff;color:#1e3a8a;"><?php echo View::e((string) ($ticket['department'] ?? '')); ?></span>
          </div>
          <div style="font-size:13px; opacity:.85;">Cliente: <strong><?php echo View::e((string) ($ticket['client_name'] ?? '')); ?></strong> (<?php echo View::e((string) ($ticket['client_email'] ?? '')); ?>)</div>
        </div>
        <div style="font-size:13px; opacity:.8;">Atualizado: <?php echo View::e((string) ($ticket['updated_at'] ?? '')); ?></div>
      </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Mensagens</h2>

      <?php foreach (($mensagens ?? []) as $m): ?>
        <?php $tipo = (string) ($m['sender_type'] ?? ''); ?>
        <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px; margin-bottom:10px; background:<?php echo $tipo === 'team' ? '#ffffff' : '#f8fafc'; ?>;">
          <div class="linha" style="justify-content:space-between; margin-bottom:6px;">
            <div style="font-weight:700; font-size:13px;">
              <?php echo $tipo === 'team' ? 'Equipe' : 'Cliente'; ?>
            </div>
            <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($m['created_at'] ?? '')); ?></div>
          </div>
          <div style="white-space:pre-wrap; line-height:1.55; color:#0f172a; font-size:14px;">
            <?php echo View::e((string) ($m['message'] ?? '')); ?>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($mensagens)): ?>
        <div class="texto" style="margin:0;">Nenhuma mensagem ainda.</div>
      <?php endif; ?>
    </div>

    <div class="grid">
      <div class="card">
        <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Responder</h2>

        <?php if (!empty($erro)): ?>
          <div class="erro"><?php echo View::e((string) $erro); ?></div>
        <?php endif; ?>

        <?php if ($st === 'closed'): ?>
          <div class="texto" style="margin:0;">Ticket fechado.</div>
        <?php else: ?>
          <form method="post" action="/equipe/tickets/responder">
            <input type="hidden" name="ticket_id" value="<?php echo (int) ($ticket['id'] ?? 0); ?>" />
            <textarea class="input" name="message" rows="6" style="margin-bottom:12px;"></textarea>
            <button class="botao" type="submit">Enviar resposta</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Ações</h2>

        <?php if ($st === 'closed'): ?>
          <div class="texto" style="margin:0;">Nenhuma ação disponível.</div>
        <?php else: ?>
          <form method="post" action="/equipe/tickets/fechar">
            <input type="hidden" name="ticket_id" value="<?php echo (int) ($ticket['id'] ?? 0); ?>" />
            <button class="botao sec" type="submit">Fechar ticket</button>
          </form>
        <?php endif; ?>

        <div style="margin-top:12px; font-size:13px; opacity:.8;">Atribuído: <?php echo View::e((string) (($ticket['assigned_to'] ?? '') === null ? '' : (string) ($ticket['assigned_to'] ?? ''))); ?></div>
      </div>
    </div>
  </div>
</body>
</html>
