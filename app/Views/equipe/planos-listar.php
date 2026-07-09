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
            'vps'        => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg> VPS',       '#e0e7ff','#1e3a8a'],
            'wordpress'  => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> WordPress',  '#dbeafe','#1d4ed8'],
            'webhosting' => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Web Host',   '#dcfce7','#166534'],
            'nodejs'     => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1.5l9.5 5.5v11L12 23.5 2.5 18V7z"/></svg> Node.js',     '#fef3c7','#92400e'],
            'cpp'        => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.08z"/></svg> C/C++',      '#fce7f3','#9d174d'],
            'php'        => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> PHP',        '#fef3c7','#78350f'],
            'python'     => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="12" y1="2" x2="12" y2="22"/></svg> Python',     '#e0f2fe','#075985'],
            'app'        => ['<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> App',         '#f3e8ff','#6b21a8'],
          ];
        ?>
        <?php foreach (($planos??[]) as $p): ?>
          <?php $pt = (string)($p['plan_type'] ?? 'vps'); $tl = $typeLabels[$pt] ?? $typeLabels['vps']; ?>
          <tr>
            <td><?php echo View::e((string)($p['name']??'')); ?>
              <?php if (!empty($p['client_id'])): ?>
                <span class="badge-new" style="font-size:10px;padding:1px 6px;margin-left:4px;background:#dbeafe;color:#1e40af;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> <?php echo View::e((string)($p['client_name'] ?? 'Cliente #' . (int)$p['client_id'])); ?></span>
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
