<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Chat — Atendimento';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Chat — Atendimento</div>
<div class="page-subtitle">Gerencie as conversas de suporte</div>

<div class="card-new" style="margin-bottom:24px;">
  <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;color:#1e293b;">💬 Chats abertos</h3>
  <?php if (empty($rooms)): ?>
    <p class="texto">Nenhum chat aberto no momento.</p>
  <?php else: ?>
    <div style="overflow:auto;">
      <table>
        <thead>
          <tr><th>#</th><th>Cliente</th><th>Agente</th><th>Msgs</th><th>Aberto em</th><th>Última ativ.</th><th></th></tr>
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
                  <span class="badge-new badge-yellow">Sem agente</span>
                <?php endif; ?>
              </td>
              <td><?php echo (int)($r['total_messages']??0); ?></td>
              <td style="font-size:13px;"><?php echo View::e((string)($r['created_at']??'')); ?></td>
              <td style="font-size:13px;"><?php echo View::e((string)($r['updated_at']??'')); ?></td>
              <td><a href="/equipe/chat/ver?id=<?php echo (int)($r['id']??0); ?>" class="botao sm">Atender</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="card-new">
  <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;color:#1e293b;">📁 Chats encerrados</h3>
  <?php if (empty($encerradas)): ?>
    <p class="texto">Nenhum chat encerrado.</p>
  <?php else: ?>
    <div style="overflow:auto;">
      <table>
        <thead>
          <tr><th>#</th><th>Cliente</th><th>Agente</th><th>Msgs</th><th>Aberto em</th><th>Encerrado em</th><th></th></tr>
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
