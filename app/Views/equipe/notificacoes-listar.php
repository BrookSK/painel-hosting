<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeLida(int $read): string
{
    if ($read === 1) {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">Lida</span>';
    }
    return '<span class="badge" style="background:#dcfce7;color:#166534;">Nova</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Notificações</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Notificações</div>
        <div style="opacity:.9; font-size:13px;">Últimas 200</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="linha" style="justify-content:space-between; margin-bottom:12px;">
      <div class="texto" style="margin:0;">Alertas internos do painel (billing/tickets/etc).</div>
      <form method="post" action="/equipe/notificacoes/marcar-todas" onsubmit="return confirm('Marcar todas como lidas?');">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <button class="botao" type="submit">Marcar todas como lidas</button>
      </form>
    </div>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Mensagem</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Data</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($notificacoes ?? []) as $n): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeLida((int) ($n['read'] ?? 0)); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($n['message'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($n['created_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php if (((int) ($n['read'] ?? 0)) === 0): ?>
                    <form method="post" action="/equipe/notificacoes/marcar-lida" style="display:inline;">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                      <input type="hidden" name="id" value="<?php echo (int) ($n['id'] ?? 0); ?>" />
                      <button class="botao sec" type="submit">Marcar lida</button>
                    </form>
                  <?php else: ?>
                    <span style="opacity:.7;">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($notificacoes)): ?>
              <tr>
                <td colspan="4" style="padding:12px;">Nenhuma notificação ainda.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
