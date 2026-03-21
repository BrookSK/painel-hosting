<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = I18n::t('chat_equipe.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('chat_equipe.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('chat_equipe.subtitulo')); ?></div>

<div class="card-new" style="margin-bottom:24px;">
  <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;color:#1e293b;">💬 <?php echo View::e(I18n::t('chat_equipe.abertos')); ?></h3>
  <?php if (empty($rooms)): ?>
    <p class="texto"><?php echo View::e(I18n::t('chat_equipe.nenhum_aberto')); ?></p>
  <?php else: ?>
    <div style="overflow:auto;">
      <table>
        <thead>
          <tr><th>#</th><th><?php echo View::e(I18n::t('chat_equipe.cliente')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.agente')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.msgs')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.aberto_em')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.ultima_ativ')); ?></th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($rooms as $r): ?>
            <tr>
              <td><?php echo (int)($r['id']??0); ?></td>
              <td><?php echo View::e((string)($r['client_name']??'#'.($r['client_id']??''))); ?></td>
              <td>
                <?php if (!empty($r['agent_name'])): ?>
                  <span class="badge-new badge-green"><?php echo View::e((string)$r['agent_name']); ?></span>
                <?php else: ?>
                  <span class="badge-new badge-yellow"><?php echo View::e(I18n::t('chat_equipe.sem_agente')); ?></span>
                <?php endif; ?>
              </td>
              <td><?php echo (int)($r['total_messages']??0); ?></td>
              <td style="font-size:13px;"><?php echo View::e((string)($r['created_at']??'')); ?></td>
              <td style="font-size:13px;"><?php echo View::e((string)($r['updated_at']??'')); ?></td>
              <td><a href="/equipe/chat/ver?id=<?php echo (int)($r['id']??0); ?>" class="botao sm"><?php echo View::e(I18n::t('chat_equipe.atender')); ?></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="card-new">
  <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;color:#1e293b;">📁 <?php echo View::e(I18n::t('chat_equipe.encerrados')); ?></h3>
  <?php if (empty($encerradas)): ?>
    <p class="texto"><?php echo View::e(I18n::t('chat_equipe.nenhum_encerrado')); ?></p>
  <?php else: ?>
    <div style="overflow:auto;">
      <table>
        <thead>
          <tr><th>#</th><th><?php echo View::e(I18n::t('chat_equipe.cliente')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.agente')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.msgs')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.aberto_em')); ?></th><th><?php echo View::e(I18n::t('chat_equipe.encerrado_em')); ?></th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($encerradas as $r): ?>
            <tr>
              <td><?php echo (int)($r['id']??0); ?></td>
              <td><?php echo View::e((string)($r['client_name']??'#'.($r['client_id']??''))); ?></td>
              <td>
                <?php if (!empty($r['agent_name'])): ?>
                  <span class="badge-new"><?php echo View::e((string)$r['agent_name']); ?></span>
                <?php else: ?>
                  <span class="badge-new badge-yellow">—</span>
                <?php endif; ?>
              </td>
              <td><?php echo (int)($r['total_messages']??0); ?></td>
              <td style="font-size:13px;"><?php echo View::e((string)($r['created_at']??'')); ?></td>
              <td style="font-size:13px;"><?php echo View::e((string)($r['updated_at']??'')); ?></td>
              <td><a href="/equipe/chat/ver?id=<?php echo (int)($r['id']??0); ?>" class="botao ghost sm">Ver</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
