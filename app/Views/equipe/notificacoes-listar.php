<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Notificacoes';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Notificacoes</div>
<div class="page-subtitle">Ultimas 200</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;">Alertas internos do painel.</span>
  <form method="post" action="/equipe/notificacoes/marcar-todas" onsubmit="return confirm('Marcar todas como lidas?');">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <button class="botao" type="submit">Marcar todas como lidas</button>
  </form>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>Status</th><th>Mensagem</th><th>Data</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php foreach (($notificacoes??[]) as $n): ?>
          <tr>
            <td><?php echo (int)($n['read']??0)===1?'<span class="badge-new badge-gray">Lida</span>':'<span class="badge-new badge-green">Nova</span>'; ?></td>
            <td><?php echo View::e((string)($n['message']??'')); ?></td>
            <td><?php echo View::e((string)($n['created_at']??'')); ?></td>
            <td>
              <?php if ((int)($n['read']??0)===0): ?>
                <form method="post" action="/equipe/notificacoes/marcar-lida" style="display:inline;">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                  <input type="hidden" name="id" value="<?php echo (int)($n['id']??0); ?>" />
                  <button class="botao sec" type="submit" style="padding:4px 8px;font-size:12px;">Marcar lida</button>
                </form>
              <?php else: ?>
                <span style="opacity:.5;">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($notificacoes)): ?>
          <tr><td colspan="4">Nenhuma notificacao ainda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
