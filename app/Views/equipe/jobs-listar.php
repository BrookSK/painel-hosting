<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeStatusJob(string $st): string {
    if ($st==='completed') return '<span class="badge-new badge-green">Concluido</span>';
    if ($st==='failed')    return '<span class="badge-new badge-red">Falhou</span>';
    if ($st==='running')   return '<span class="badge-new badge-blue">Rodando</span>';
    return '<span class="badge-new badge-yellow">Pendente</span>';
}

$pageTitle = 'Jobs';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Jobs</div>
<div class="page-subtitle">Fila e logs (ultimos 200)</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>ID</th><th>Tipo</th><th>Status</th><th>Criado</th><th>Atualizado</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php foreach (($jobs??[]) as $j): ?>
          <tr>
            <td><strong>#<?php echo (int)($j['id']??0); ?></strong></td>
            <td><?php echo View::e((string)($j['type']??'')); ?></td>
            <td><?php echo badgeStatusJob((string)($j['status']??'pending')); ?></td>
            <td><?php echo View::e((string)($j['created_at']??'')); ?></td>
            <td><?php echo View::e((string)($j['updated_at']??'')); ?></td>
            <td><a href="/equipe/jobs/ver?id=<?php echo (int)($j['id']??0); ?>">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($jobs)): ?>
          <tr><td colspan="6">Nenhum job encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<div style="margin-top:12px;" class="texto">Dica: rode <strong>php worker.php</strong> para processar a fila.</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
