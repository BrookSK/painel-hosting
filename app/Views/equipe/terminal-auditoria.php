<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Auditoria — Terminal';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Auditoria — Terminal</div>
<div class="page-subtitle">Ultimas 200 sessoes</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>ID</th><th>Usuario</th><th>Node</th><th>Inicio</th><th>Fim</th><th>IP</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php foreach (((array)($sessoes??[])) as $s): ?>
          <tr>
            <td><strong>#<?php echo (int)($s['id']??0); ?></strong></td>
            <td><?php echo View::e((string)($s['user_name']??'')); ?></td>
            <td>
              <?php echo View::e((string)($s['server_hostname']??'')); ?>
              <div style="opacity:.8;font-size:12px;"><code><?php echo View::e((string)($s['server_ip']??'')); ?></code></div>
            </td>
            <td><?php echo View::e((string)($s['started_at']??'')); ?></td>
            <td><?php echo View::e((string)($s['ended_at']??'')); ?></td>
            <td><code><?php echo View::e((string)($s['ip']??'')); ?></code></td>
            <td><a href="/equipe/terminal/auditoria/ver?id=<?php echo (int)($s['id']??0); ?>">Ver comandos</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($sessoes)): ?>
          <tr><td colspan="7">Nenhum registro encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
