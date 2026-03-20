<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Chat — Atendimento</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Chat — Atendimento</div>
        <div style="opacity:.9; font-size:13px;">Rooms abertas aguardando resposta</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/tickets">Tickets</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <h1 class="titulo" style="margin-bottom:14px;">Rooms abertas</h1>
      <?php if (empty($rooms)): ?>
        <p class="texto">Nenhuma room aberta no momento.</p>
      <?php else: ?>
        <div style="overflow:auto;">
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">#</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Agente</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Mensagens</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Aberta em</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ação</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rooms as $r): ?>
                <tr>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo (int) ($r['id'] ?? 0); ?></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                    <?php echo View::e((string) ($r['client_name'] ?? '#' . ($r['client_id'] ?? ''))); ?>
                  </td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                    <?php if (!empty($r['agent_name'])): ?>
                      <span class="badge-verde"><?php echo View::e((string) $r['agent_name']); ?></span>
                    <?php else: ?>
                      <span class="badge-amarelo">Sem agente</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo (int) ($r['total_messages'] ?? 0); ?></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#64748b;">
                    <?php echo View::e((string) ($r['created_at'] ?? '')); ?>
                  </td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                    <a href="/equipe/chat/ver?id=<?php echo (int) ($r['id'] ?? 0); ?>" class="botao sm">Atender</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
