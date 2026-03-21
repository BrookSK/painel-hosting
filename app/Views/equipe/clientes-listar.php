<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Clientes';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$clientes = $clientes ?? [];
$busca    = (string)($busca ?? '');
?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <div>
    <div class="page-title">Clientes</div>
    <div class="page-subtitle">Contas de clientes cadastradas no sistema</div>
  </div>
  <a href="/equipe/clientes/novo" class="botao">+ Novo cliente</a>
</div>

<!-- Busca -->
<div class="card-new" style="margin-bottom:16px;padding:14px 16px;">
  <form method="get" action="/equipe/clientes" style="display:flex;gap:10px;align-items:center;">
    <input type="text" name="q" class="input" value="<?php echo View::e($busca); ?>"
           placeholder="Buscar por nome ou e-mail..." style="max-width:340px;" />
    <button class="botao sm" type="submit">Buscar</button>
    <?php if ($busca !== ''): ?>
      <a href="/equipe/clientes" class="botao sm sec">Limpar</a>
    <?php endif; ?>
  </form>
</div>

<div class="card-new" style="padding:0;overflow:auto;">
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
      <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
        <th style="padding:11px 16px;text-align:left;font-weight:600;color:#475569;">#</th>
        <th style="padding:11px 16px;text-align:left;font-weight:600;color:#475569;">Nome</th>
        <th style="padding:11px 16px;text-align:left;font-weight:600;color:#475569;">E-mail</th>
        <th style="padding:11px 16px;text-align:left;font-weight:600;color:#475569;">Telefone</th>
        <th style="padding:11px 16px;text-align:center;font-weight:600;color:#475569;">VPS</th>
        <th style="padding:11px 16px;text-align:center;font-weight:600;color:#475569;">Assinaturas</th>
        <th style="padding:11px 16px;text-align:left;font-weight:600;color:#475569;">Cadastro</th>
        <th style="padding:11px 16px;"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($clientes)): ?>
        <tr><td colspan="8" style="padding:40px;text-align:center;color:#94a3b8;">
          <?php echo $busca !== '' ? 'Nenhum cliente encontrado para "' . View::e($busca) . '".' : 'Nenhum cliente cadastrado.'; ?>
        </td></tr>
      <?php else: ?>
        <?php foreach ($clientes as $c): ?>
          <?php
            $ativas = (int)($c['assinaturas_ativas'] ?? 0);
            $totalSubs = (int)($c['total_assinaturas'] ?? 0);
          ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 16px;color:#94a3b8;font-size:12px;">#<?php echo (int)$c['id']; ?></td>
            <td style="padding:10px 16px;font-weight:500;color:#0f172a;"><?php echo View::e((string)($c['name'] ?? '')); ?></td>
            <td style="padding:10px 16px;color:#475569;"><?php echo View::e((string)($c['email'] ?? '')); ?></td>
            <td style="padding:10px 16px;color:#64748b;"><?php echo View::e((string)($c['phone'] ?? '—')); ?></td>
            <td style="padding:10px 16px;text-align:center;">
              <span style="font-weight:600;color:#0f172a;"><?php echo (int)($c['total_vps'] ?? 0); ?></span>
            </td>
            <td style="padding:10px 16px;text-align:center;">
              <?php if ($ativas > 0): ?>
                <span class="badge-new badge-green"><?php echo $ativas; ?> ativa<?php echo $ativas > 1 ? 's' : ''; ?></span>
              <?php elseif ($totalSubs > 0): ?>
                <span class="badge-new badge-yellow"><?php echo $totalSubs; ?> inativa<?php echo $totalSubs > 1 ? 's' : ''; ?></span>
              <?php else: ?>
                <span class="badge-new badge-gray">sem plano</span>
              <?php endif; ?>
            </td>
            <td style="padding:10px 16px;color:#94a3b8;font-size:12px;white-space:nowrap;">
              <?php echo View::e(date('d/m/Y', strtotime((string)($c['created_at'] ?? 'now')))); ?>
            </td>
            <td style="padding:10px 16px;">
              <a href="/equipe/clientes/ver?id=<?php echo (int)$c['id']; ?>" class="botao sm sec">Ver</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div style="margin-top:10px;font-size:13px;color:#94a3b8;"><?php echo count($clientes); ?> cliente(s)</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
