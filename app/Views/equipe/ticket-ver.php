<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$st = (string) ($ticket['status'] ?? 'open');

function badgeStatusEquipeTicket(string $st): string
{
    $map = [
        'open'           => ['Aberto', '#eef2ff', '#1e3a8a'],
        'in_progress'    => ['Em andamento', '#e0f2fe', '#075985'],
        'waiting_client' => ['Aguardando cliente', '#fef3c7', '#92400e'],
        'closed'         => ['Fechado', '#f1f5f9', '#334155'],
    ];
    $d = $map[$st] ?? [$st, '#f1f5f9', '#334155'];
    return '<span class="badge" style="background:' . $d[1] . ';color:' . $d[2] . ';">' . View::e($d[0]) . '</span>';
}

function badgePrioridadeEquipeTicket(string $p): string
{
    if ($p === 'high') return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Alta</span>';
    if ($p === 'low')  return '<span class="badge" style="background:#dcfce7;color:#166534;">Baixa</span>';
    return '<span class="badge" style="background:#fef3c7;color:#92400e;">Média</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket #<?php echo (int) ($ticket['id'] ?? 0); ?></title>
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
      <div class="linha" style="justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <div>
          <div class="linha" style="gap:8px; margin-bottom:8px;">
            <?php echo badgeStatusEquipeTicket($st); ?>
            <?php echo badgePrioridadeEquipeTicket((string) ($ticket['priority'] ?? 'medium')); ?>
            <span class="badge" style="background:#e0e7ff;color:#1e3a8a;"><?php echo View::e((string) ($ticket['department'] ?? '')); ?></span>
          </div>
          <div style="font-size:13px; opacity:.85;">
            Cliente: <strong><?php echo View::e((string) ($ticket['client_name'] ?? '')); ?></strong>
            (<?php echo View::e((string) ($ticket['client_email'] ?? '')); ?>)
          </div>
          <?php if (!empty($ticket['assigned_name'])): ?>
            <div style="font-size:13px; opacity:.85; margin-top:4px;">
              Atribuído a: <strong><?php echo View::e((string) ($ticket['assigned_name'] ?? '')); ?></strong>
            </div>
          <?php endif; ?>
        </div>
        <div style="font-size:13px; opacity:.8;">Atualizado: <?php echo View::e((string) ($ticket['updated_at'] ?? '')); ?></div>
      </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Mensagens</h2>

      <?php foreach (($mensagens ?? []) as $m): ?>
        <?php $tipo = (string) ($m['sender_type'] ?? ''); ?>
        <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px; margin-bottom:10px; background:<?php echo $tipo === 'team' ? '#f0f4ff' : '#f8fafc'; ?>;">
          <div class="linha" style="justify-content:space-between; margin-bottom:6px;">
            <div style="font-weight:700; font-size:13px; color:<?php echo $tipo === 'team' ? '#4F46E5' : '#0f172a'; ?>;">
              <?php echo $tipo === 'team' ? 'Equipe' : 'Cliente'; ?>
            </div>
            <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($m['created_at'] ?? '')); ?></div>
          </div>
          <div style="white-space:pre-wrap; line-height:1.55; color:#0f172a; font-size:14px;">
            <?php echo View::e((string) ($m['message'] ?? '')); ?>
          </div>
          <?php if (!empty($m['attachment_name'])): ?>
            <div style="margin-top:8px; font-size:12px;">
              📎 <?php echo View::e((string) ($m['attachment_name'] ?? '')); ?>
              <?php if (!empty($m['attachment_size'])): ?>
                (<?php echo View::e((string) round((int) $m['attachment_size'] / 1024, 1)); ?> KB)
              <?php endif; ?>
            </div>
          <?php endif; ?>
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
          <form method="post" action="/equipe/tickets/responder" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <input type="hidden" name="ticket_id" value="<?php echo (int) ($ticket['id'] ?? 0); ?>" />
            <textarea class="input" name="message" rows="6" style="margin-bottom:10px;" placeholder="Sua resposta..."></textarea>
            <div style="margin-bottom:12px;">
              <label style="font-size:13px; display:block; margin-bottom:4px;">Anexo (opcional, máx. 5 MB)</label>
              <input type="file" name="attachment" style="font-size:13px;" />
            </div>
            <button class="botao" type="submit">Enviar resposta</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Ações</h2>

        <?php if ($st !== 'closed'): ?>
          <div style="margin-bottom:14px;">
            <div style="font-size:13px; margin-bottom:6px;">Alterar status</div>
            <form method="post" action="/equipe/tickets/status">
              <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
              <input type="hidden" name="ticket_id" value="<?php echo (int) ($ticket['id'] ?? 0); ?>" />
              <div class="linha" style="gap:6px;">
                <select class="input" name="status" style="max-width:180px;">
                  <option value="open" <?php echo $st === 'open' ? 'selected' : ''; ?>>Aberto</option>
                  <option value="in_progress" <?php echo $st === 'in_progress' ? 'selected' : ''; ?>>Em andamento</option>
                  <option value="waiting_client" <?php echo $st === 'waiting_client' ? 'selected' : ''; ?>>Aguardando cliente</option>
                  <option value="closed">Fechado</option>
                </select>
                <button class="botao" type="submit" style="padding:10px 12px;">Salvar</button>
              </div>
            </form>
          </div>

          <div style="margin-bottom:14px;">
            <div style="font-size:13px; margin-bottom:6px;">Atribuir para</div>
            <form method="post" action="/equipe/tickets/atribuir">
              <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
              <input type="hidden" name="ticket_id" value="<?php echo (int) ($ticket['id'] ?? 0); ?>" />
              <div class="linha" style="gap:6px;">
                <select class="input" name="user_id" style="max-width:180px;">
                  <option value="0">— Ninguém —</option>
                  <?php foreach (($usuarios ?? []) as $u): ?>
                    <option value="<?php echo (int) ($u['id'] ?? 0); ?>" <?php echo (int) ($ticket['assigned_to'] ?? 0) === (int) ($u['id'] ?? 0) ? 'selected' : ''; ?>>
                      <?php echo View::e((string) ($u['name'] ?? '')); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button class="botao sec" type="submit" style="padding:10px 12px;">Atribuir</button>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="texto" style="margin:0;">Nenhuma ação disponível.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
