<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function fmtGbPlano(int $mb): string {
    if ($mb<=0) return '0 GB';
    return ((int)round($mb/1024)).' GB';
}

$pageTitle = 'Planos';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Planos</div>
<div class="page-subtitle">CPU, memoria, armazenamento e preco</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;">Crie e gerencie os planos disponibilizados aos clientes.</span>
  <a class="botao" href="/equipe/planos/novo">Novo plano</a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>Nome</th><th>Tipo</th><th>CPU</th><th>Memoria</th><th>Armazenamento</th><th>Preco/mes</th><th>Status</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php
          $typeLabels = [
            'vps'        => ['🖥️ VPS',       '#e0e7ff','#1e3a8a'],
            'wordpress'  => ['📝 WordPress',  '#dbeafe','#1d4ed8'],
            'webhosting' => ['🌐 Web Host',   '#dcfce7','#166534'],
            'nodejs'     => ['⬢ Node.js',     '#fef3c7','#92400e'],
            'cpp'        => ['⚙️ C/C++',      '#fce7f3','#9d174d'],
            'php'        => ['🐘 PHP',        '#fef3c7','#78350f'],
            'python'     => ['🐍 Python',     '#e0f2fe','#075985'],
            'app'        => ['📦 App',         '#f3e8ff','#6b21a8'],
          ];
        ?>
        <?php foreach (($planos??[]) as $p): ?>
          <?php $pt = (string)($p['plan_type'] ?? 'vps'); $tl = $typeLabels[$pt] ?? $typeLabels['vps']; ?>
          <tr>
            <td><?php echo View::e((string)($p['name']??'')); ?>
              <?php if (!empty($p['client_id'])): ?>
                <span class="badge-new" style="font-size:10px;padding:1px 6px;margin-left:4px;background:#dbeafe;color:#1e40af;">🔒 <?php echo View::e((string)($p['client_name'] ?? 'Cliente #' . (int)$p['client_id'])); ?></span>
              <?php endif; ?>
            </td>
            <td><span class="badge-new" style="font-size:10px;padding:2px 8px;background:<?php echo $tl[1]; ?>;color:<?php echo $tl[2]; ?>;"><?php echo $tl[0]; ?></span></td>
            <td><?php echo View::e((string)($p['cpu']??'')); ?></td>
            <td><?php echo View::e(fmtGbPlano((int)($p['ram']??0))); ?></td>
            <td><?php echo View::e(fmtGbPlano((int)($p['storage']??0))); ?></td>
            <td><?php
              $planCur = (string)($p['currency'] ?? 'BRL');
              $planPriceUsd = (float)($p['price_monthly_usd'] ?? 0);
              $planPriceBrl = (float)($p['price_monthly'] ?? 0);
              if ($planCur === 'USD' && $planPriceUsd > 0) {
                  echo 'US$ ' . number_format($planPriceUsd, 2, '.', ',');
              } elseif ($planPriceBrl > 0) {
                  echo View::e(I18n::preco($planPriceBrl));
              } elseif ($planPriceUsd > 0) {
                  echo 'US$ ' . number_format($planPriceUsd, 2, '.', ',');
              } else {
                  echo View::e(I18n::preco(0));
              }
            ?></td>
            <td><?php echo ($p['status']??'')==='active'?'<span class="badge-new badge-green">Ativo</span>':'<span class="badge-new badge-gray">Inativo</span>'; ?></td>
            <td><a href="/equipe/planos/editar?id=<?php echo (int)($p['id']??0); ?>">Editar</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($planos)): ?>
          <tr><td colspan="8">Nenhum plano cadastrado ainda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
