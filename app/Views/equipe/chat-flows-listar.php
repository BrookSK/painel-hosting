<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = I18n::t('chat_flows.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

function badgeTrigger(string $t): string {
    $map = [
        'client_inactive' => ['badge-yellow', I18n::t('chat_flows.trigger_inatividade')],
        'chat_closed'     => ['badge-blue',   I18n::t('chat_flows.trigger_encerramento')],
        'manual'          => ['badge-gray',   I18n::t('chat_flows.trigger_manual')],
    ];
    $d = $map[$t] ?? ['badge-gray', $t];
    return '<span class="badge-new ' . $d[0] . '">' . View::e($d[1]) . '</span>';
}
?>
<div class="page-title"><?php echo View::e(I18n::t('chat_flows.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('chat_flows.subtitulo')); ?></div>

<div style="margin-bottom:16px;">
  <a href="/equipe/chat-flows/novo" class="botao"><?php echo View::e(I18n::t('chat_flows.novo_fluxo')); ?></a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo View::e(I18n::t('chat_flows.nome')); ?></th>
          <th><?php echo View::e(I18n::t('chat_flows.descricao')); ?></th>
          <th><?php echo View::e(I18n::t('chat_flows.gatilho')); ?></th>
          <th><?php echo View::e(I18n::t('chat_flows.passos')); ?></th>
          <th><?php echo View::e(I18n::t('geral.status')); ?></th>
          <th><?php echo View::e(I18n::t('geral.acoes')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($flows ?? []) as $f): ?>
          <tr>
            <td>#<?php echo (int)($f['id'] ?? 0); ?></td>
            <td><?php echo View::e((string)($f['name'] ?? '')); ?></td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo View::e((string)($f['description'] ?? '')); ?></td>
            <td><?php echo badgeTrigger((string)($f['trigger_type'] ?? '')); ?></td>
            <td><?php echo (int)($f['step_count'] ?? 0); ?></td>
            <td>
              <?php if ((int)($f['active'] ?? 0) === 1): ?>
                <span class="badge-new badge-green"><?php echo View::e(I18n::t('geral.ativo')); ?></span>
              <?php else: ?>
                <span class="badge-new badge-gray"><?php echo View::e(I18n::t('geral.inativo')); ?></span>
              <?php endif; ?>
            </td>
            <td>
              <a href="/equipe/chat-flows/editar?id=<?php echo (int)($f['id'] ?? 0); ?>"><?php echo View::e(I18n::t('geral.editar')); ?></a>
              &nbsp;
              <form method="post" action="/equipe/chat-flows/excluir" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                <input type="hidden" name="id" value="<?php echo (int)($f['id'] ?? 0); ?>" />
                <button type="submit" class="botao danger sm" onclick="return confirm('<?php echo View::e(I18n::t('chat_flows.confirmar_excluir')); ?>')" style="font-size:12px;"><?php echo View::e(I18n::t('geral.excluir')); ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($flows)): ?>
          <tr><td colspan="7"><?php echo View::e(I18n::t('geral.nenhum_resultado')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
