<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Clientes';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$clientes    = $clientes ?? [];
$busca       = (string)($busca ?? '');
$showHidden  = !empty($showHidden);
$sortAtual   = (string)($sort ?? '');

$sortLink = function(string $key, string $label) use ($busca, $showHidden, $sortAtual): string {
    $params = [];
    if ($busca !== '') $params['q'] = $busca;
    if ($showHidden) $params['hidden'] = '1';
    $params['sort'] = $key;
    $qs = http_build_query($params);
    $ativo = $sortAtual === $key;
    $style = $ativo ? 'color:#4F46E5;font-weight:700;' : 'color:#475569;';
    return '<a href="/equipe/clientes?' . View::e($qs) . '" style="text-decoration:none;' . $style . '">' . View::e($label) . ($ativo ? ' ▼' : '') . '</a>';
};
?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <div>
    <div class="page-title">Clientes</div>
    <div class="page-subtitle">Contas de clientes cadastradas no sistema</div>
  </div>
  <a href="/equipe/clientes/novo" class="botao">+ Novo cliente</a>
</div>

<!-- Busca + filtros -->
<div class="card-new" style="margin-bottom:16px;padding:14px 16px;">
  <form method="get" action="/equipe/clientes" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <input type="text" name="q" class="input" value="<?php echo View::e($busca); ?>"
           placeholder="Buscar por nome ou e-mail..." style="max-width:340px;" />
    <?php if ($showHidden): ?><input type="hidden" name="hidden" value="1"/><?php endif; ?>
    <?php if ($sortAtual !== ''): ?><input type="hidden" name="sort" value="<?php echo View::e($sortAtual); ?>"/><?php endif; ?>
    <button class="botao sm" type="submit">Buscar</button>
    <?php if ($busca !== ''): ?>
      <a href="/equipe/clientes<?php echo $showHidden ? '?hidden=1' : ''; ?>" class="botao sm sec">Limpar</a>
    <?php endif; ?>
    <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;margin-left:auto;cursor:pointer;">
      <input type="checkbox" name="hidden" value="1" <?php echo $showHidden ? 'checked' : ''; ?> onchange="this.form.submit()" style="accent-color:#4F46E5;"/>
      Mostrar ocultos
    </label>
  </form>
</div>

<!-- Ordenação -->
<div style="display:flex;gap:12px;margin-bottom:12px;font-size:12px;flex-wrap:wrap;align-items:center;">
  <span style="color:#94a3b8;">Ordenar por:</span>
  <?php echo $sortLink('nome', 'Nome'); ?>
  <?php echo $sortLink('cadastro', 'Cadastro'); ?>
  <?php echo $sortLink('atividade', 'Última atividade'); ?>
  <?php echo $sortLink('vps', 'VPS'); ?>
  <?php echo $sortLink('assinaturas', 'Assinaturas'); ?>
  <?php echo $sortLink('recursos', 'Uso de recursos'); ?>
</div>

<div class="card-new" style="padding:0;overflow:auto;">
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
      <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
        <th style="padding:11px 14px;text-align:left;font-weight:600;color:#475569;">#</th>
        <th style="padding:11px 14px;text-align:left;font-weight:600;color:#475569;">Nome</th>
        <th style="padding:11px 14px;text-align:left;font-weight:600;color:#475569;">E-mail</th>
        <th style="padding:11px 14px;text-align:left;font-weight:600;color:#475569;">Celular</th>
        <th style="padding:11px 14px;text-align:center;font-weight:600;color:#475569;">VPS</th>
        <th style="padding:11px 14px;text-align:center;font-weight:600;color:#475569;">Assinaturas</th>
        <th style="padding:11px 14px;text-align:left;font-weight:600;color:#475569;">Última atividade</th>
        <th style="padding:11px 14px;text-align:left;font-weight:600;color:#475569;">Cadastro</th>
        <th style="padding:11px 14px;"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($clientes)): ?>
        <tr><td colspan="9" style="padding:40px;text-align:center;color:#94a3b8;">
          <?php echo $busca !== '' ? 'Nenhum cliente encontrado para "' . View::e($busca) . '".' : 'Nenhum cliente cadastrado.'; ?>
        </td></tr>
      <?php else: ?>
        <?php foreach ($clientes as $c):
          $ativas = (int)($c['assinaturas_ativas'] ?? 0);
          $totalSubs = (int)($c['total_assinaturas'] ?? 0);
          $lastLogin = ($c['last_login_at'] ?? null);
        ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 14px;color:#94a3b8;font-size:12px;">#<?php echo (int)$c['id']; ?></td>
            <td style="padding:10px 14px;font-weight:500;color:#0f172a;">
              <?php echo View::e((string)($c['name'] ?? '')); ?>
              <?php if (($c['hidden_at'] ?? null) !== null): ?>
                <span class="badge-new" style="background:#f1f5f9;color:#94a3b8;font-size:10px;margin-left:4px;">oculto</span>
              <?php endif; ?>
            </td>
            <td style="padding:10px 14px;color:#475569;"><?php echo View::e((string)($c['email'] ?? '')); ?></td>
            <td style="padding:10px 14px;color:#64748b;"><?php echo View::e((string)($c['mobile_phone'] ?? '—')); ?></td>
            <td style="padding:10px 14px;text-align:center;">
              <span style="font-weight:600;color:#0f172a;"><?php echo (int)($c['total_vps'] ?? 0); ?></span>
              <?php $cpuT = (int)($c['total_cpu'] ?? 0); $ramT = (int)($c['total_ram'] ?? 0); ?>
              <?php if ($cpuT > 0): ?>
                <div style="font-size:10px;color:#94a3b8;"><?php echo $cpuT; ?>cpu / <?php echo round($ramT/1024); ?>GB</div>
              <?php endif; ?>
            </td>
            <td style="padding:10px 14px;text-align:center;">
              <?php if ($ativas > 0): ?>
                <span class="badge-new badge-green"><?php echo $ativas; ?> ativa<?php echo $ativas > 1 ? 's' : ''; ?></span>
              <?php elseif ($totalSubs > 0): ?>
                <span class="badge-new badge-yellow"><?php echo $totalSubs; ?> inativa<?php echo $totalSubs > 1 ? 's' : ''; ?></span>
              <?php else: ?>
                <span class="badge-new badge-gray">sem plano</span>
              <?php endif; ?>
            </td>
            <td style="padding:10px 14px;color:#94a3b8;font-size:12px;white-space:nowrap;">
              <?php if ($lastLogin): ?>
                <?php echo View::e(date('d/m/Y H:i', strtotime((string)$lastLogin))); ?>
              <?php else: ?>
                <span style="color:#cbd5e1;">nunca</span>
              <?php endif; ?>
            </td>
            <td style="padding:10px 14px;color:#94a3b8;font-size:12px;white-space:nowrap;">
              <?php echo View::e(date('d/m/Y', strtotime((string)($c['created_at'] ?? 'now')))); ?>
            </td>
            <td style="padding:10px 14px;">
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
