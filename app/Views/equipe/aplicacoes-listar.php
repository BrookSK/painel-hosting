<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeStatusAplicacao(string $st): string {
    if ($st==='inactive')  return '<span class="badge-new badge-gray">Inativa</span>';
    if ($st==='deploying') return '<span class="badge-new badge-blue">Deploy</span>';
    if ($st==='error')     return '<span class="badge-new badge-red">Erro</span>';
    return '<span class="badge-new badge-green">Ativa</span>';
}

$pageTitle = 'Aplicacoes';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Aplicacoes</div>
<div class="page-subtitle">Apps vinculados as VPS e portas reservadas</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;">Crie aplicacoes e reserve portas unicas para expor servicos.</span>
  <a class="botao" href="/equipe/aplicacoes/novo">Nova aplicacao</a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>ID</th><th>VPS</th><th>Cliente</th><th>Tipo</th><th>Dominio</th><th>Porta</th><th>Status</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php foreach (($aplicacoes??[]) as $a): ?>
          <tr>
            <td><strong>#<?php echo (int)($a['id']??0); ?></strong></td>
            <td>#<?php echo (int)($a['vps_id']??0); ?></td>
            <td><?php echo View::e((string)($a['client_email']??'')); ?></td>
            <td><code><?php echo View::e((string)($a['type']??'')); ?></code></td>
            <td><?php echo View::e((string)($a['domain']??'')); ?></td>
            <td><code><?php echo View::e((string)($a['port']??'')); ?></code></td>
            <td><?php echo badgeStatusAplicacao((string)($a['status']??'active')); ?></td>
            <td>
              <a href="/equipe/aplicacoes/editar?id=<?php echo (int)($a['id']??0); ?>">Editar</a> |
              <form method="post" action="/equipe/aplicacoes/deploy" style="display:inline;" onsubmit="return confirm('Iniciar deploy agora?');">
                <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                <input type="hidden" name="id" value="<?php echo (int)($a['id']??0); ?>" />
                <button class="botao sec" type="submit" style="padding:4px 8px;font-size:12px;">Deploy</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($aplicacoes)): ?>
          <tr><td colspan="8">Nenhuma aplicacao cadastrada.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
