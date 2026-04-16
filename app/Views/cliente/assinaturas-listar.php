<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\App\Services\Plans\PlanFeatureService;

$pageTitle = I18n::t('assinaturas.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

$ativas = [];
$outras = [];
foreach (($assinaturas ?? []) as $a) {
    $st = strtoupper((string)($a['status'] ?? ''));
    if (in_array($st, ['ACTIVE', 'PENDING', 'OVERDUE'], true)) {
        $ativas[] = $a;
    } else {
        $outras[] = $a;
    }
}

function _badgeSt(string $st): string {
    $map = [
        'ACTIVE'    => ['Ativa',      '#dcfce7','#166534'],
        'active'    => ['Ativa',      '#dcfce7','#166534'],
        'PENDING'   => ['Pendente',   '#fef3c7','#92400e'],
        'OVERDUE'   => ['Em atraso',  '#fee2e2','#991b1b'],
        'SUSPENDED' => ['Suspensa',   '#fee2e2','#991b1b'],
        'EXPIRED'   => ['Expirada',   '#f1f5f9','#64748b'],
        'CANCELED'  => ['Cancelada',  '#f1f5f9','#334155'],
        'inactive'  => ['Inativa',    '#f1f5f9','#334155'],
    ];
    $d = $map[$st] ?? [$st,'#f1f5f9','#334155'];
    return '<span class="badge-new" style="background:'.$d[1].';color:'.$d[2].';">'.View::e($d[0]).'</span>';
}

function _badgeVps(string $st): string {
    $map = [
        'running'              => ['Rodando',     '#dcfce7','#166534'],
        'stopped'              => ['Parada',      '#f1f5f9','#334155'],
        'pending_payment'      => ['Aguardando pagamento', '#fef3c7','#92400e'],
        'pending_provisioning' => ['Provisionando', '#e0e7ff','#1e3a8a'],
        'provisioning'         => ['Provisionando', '#e0e7ff','#1e3a8a'],
        'suspended'            => ['Suspensa',    '#fee2e2','#991b1b'],
    ];
    $d = $map[$st] ?? [$st,'#f1f5f9','#334155'];
    return '<span class="badge-new" style="background:'.$d[1].';color:'.$d[2].';">'.View::e($d[0]).'</span>';
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::t('assinaturas.titulo')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('assinaturas.sub_cada_vps')); ?></div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a class="botao ghost sm" href="/cliente/assinaturas/historico"><?php echo View::e(I18n::t('assinaturas.historico')); ?></a>
    <a class="botao sm" href="/cliente/planos"><?php echo View::e(I18n::t('assinaturas.contratar_nova')); ?></a>
  </div>
</div>

<!-- Info box -->
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
  <span style="font-size:18px;">💡</span>
  <div style="font-size:13px;color:#1e40af;line-height:1.6;">
    <?php echo View::e(I18n::t('assinaturas.info_uma_vps')); ?>
  </div>
</div>

<?php if (empty($assinaturas)): ?>
  <div class="card-new" style="text-align:center;padding:40px 24px;">
    <div style="font-size:36px;margin-bottom:12px;">💳</div>
    <div style="font-size:15px;font-weight:600;margin-bottom:8px;"><?php echo View::e(I18n::t('assinaturas.nenhuma_ainda')); ?></div>
    <div style="font-size:13px;color:#64748b;margin-bottom:16px;"><?php echo View::e(I18n::t('assinaturas.escolha_plano')); ?></div>
    <a class="botao" href="/cliente/planos"><?php echo View::e(I18n::t('assinaturas.ver_planos')); ?></a>
  </div>
<?php else: ?>

  <?php if (!empty($ativas)): ?>
    <div style="margin-bottom:8px;font-size:13px;font-weight:600;color:#334155;"><?php echo View::e(I18n::t('assinaturas.ativas')); ?> (<?php echo count($ativas); ?>)</div>
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;margin-bottom:24px;">
      <?php foreach ($ativas as $a):
        $subId   = (int)($a['id'] ?? 0);
        $vpsId   = (int)($a['vps_id'] ?? 0);
        $vpsSt   = (string)($a['vps_status'] ?? '');
        $cpu     = (int)($a['cpu'] ?? 0);
        $ramGb   = round((int)($a['ram'] ?? 0) / 1024);
        $discoGb = round((int)($a['storage'] ?? 0) / 1024);
        $preco   = (float)($a['price_monthly'] ?? 0);
        $proxVenc = (string)($a['next_due_date'] ?? '—');
        $status  = strtoupper((string)($a['status'] ?? ''));
      ?>
        <div class="card-new">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="font-weight:600;font-size:14px;"><?php echo View::e((string)($a['plan_name'] ?? '')); ?></div>
              <?php
                $planType = (string)($a['plan_type'] ?? 'vps');
                $badge = PlanFeatureService::tipoPlanoBadge($planType);
              ?>
              <span style="background:<?php echo $badge[1]; ?>;color:<?php echo $badge[2]; ?>;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;"><?php echo $badge[3]; ?> <?php echo View::e($badge[0]); ?></span>
            </div>
            <?php echo _badgeSt((string)($a['status'] ?? '')); ?>
          </div>

          <?php if ($vpsId > 0): ?>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-bottom:10px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:12px;font-weight:600;color:#475569;">VPS #<?php echo $vpsId; ?></span>
                <?php echo _badgeVps($vpsSt); ?>
              </div>
              <div style="display:flex;gap:12px;font-size:12px;color:#64748b;">
                <span><?php echo $cpu; ?> vCPU</span>
                <span><?php echo $ramGb; ?> GB RAM</span>
                <span><?php echo $discoGb; ?> GB Disco</span>
              </div>
            </div>
          <?php endif; ?>

          <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:8px;">
            <span><?php echo View::e(I18n::precoPlano($a)); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></span>
            <span><?php echo View::e(I18n::t('assinaturas.prox_vencimento')); ?>: <?php echo View::e($proxVenc); ?></span>
          </div>

          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if ($status === 'PENDING'): ?>
              <a class="botao sm" href="/cliente/pagamento?sub=<?php echo $subId; ?>"><?php echo View::e(I18n::t('pagamento.pagar')); ?></a>
            <?php endif; ?>
            <?php if ($vpsId > 0 && $vpsSt === 'running'): ?>
              <a class="botao ghost sm" href="/cliente/vps"><?php echo View::e(I18n::t('assinaturas.gerenciar_vps')); ?></a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($outras)): ?>
    <div style="margin-bottom:8px;font-size:13px;font-weight:600;color:#334155;"><?php echo View::e(I18n::t('assinaturas.encerradas')); ?> (<?php echo count($outras); ?>)</div>
    <div class="card-new" style="margin-bottom:24px;">
      <div style="overflow:auto;">
        <table style="width:100%;border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">#</th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.plano')); ?></th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.valor')); ?></th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('geral.status')); ?></th>
              <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('assinaturas.criada_em')); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($outras as $a): ?>
              <tr>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;">#<?php echo (int)($a['id'] ?? 0); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($a['plan_name'] ?? '')); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e(I18n::precoPlano($a)); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo _badgeSt((string)($a['status'] ?? '')); ?></td>
                <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($a['created_at'] ?? '')); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- Contratar novo produto -->
  <div class="card-new" style="padding:24px;">
    <div style="text-align:center;margin-bottom:16px;">
      <div style="font-size:24px;margin-bottom:8px;">➕</div>
      <div style="font-size:14px;font-weight:600;margin-bottom:6px;"><?php echo View::e(I18n::t('assinaturas.contratar_nova')); ?></div>
      <div style="font-size:13px;color:#64748b;margin-bottom:14px;">Escolha o tipo de produto que deseja contratar</div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
      <?php
        $produtos = [
          ['vps',       '🖥️', 'VPS',                'Servidor virtual completo com acesso total',       '/cliente/planos?tipo=vps'],
          ['wordpress', '📝', 'WordPress',           'WordPress gerenciado com banco e backups',         '/cliente/planos?tipo=wordpress'],
          ['webhosting','🌐', 'Web Hosting',         'Hospedagem com catálogo de apps e git deploy',     '/cliente/planos?tipo=webhosting'],
          ['nodejs',    '⬢',  'Node.js',             'Deploy de aplicações Node.js com banco de dados',  '/cliente/planos?tipo=nodejs'],
          ['cpp',       '⚙️', 'C/C++',               'Deploy de aplicações compiladas em C/C++',         '/cliente/planos?tipo=cpp'],
          ['php',       '🐘', 'PHP / Laravel',        'Hospedagem PHP com banco, arquivos e git deploy',  '/cliente/planos?tipo=php'],
          ['python',    '🐍', 'Python',               'Deploy de aplicações Python com banco de dados',   '/cliente/planos?tipo=python'],
        ];
        foreach ($produtos as [$pType, $pIcon, $pName, $pDesc, $pHref]):
      ?>
        <a href="<?php echo $pHref; ?>" style="background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:6px;transition:border-color .15s,box-shadow .15s,transform .15s;"
           onmouseover="this.style.borderColor='#4F46E5';this.style.boxShadow='0 4px 16px rgba(79,70,229,.12)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.transform='none'">
          <div style="font-size:28px;"><?php echo $pIcon; ?></div>
          <div style="font-weight:700;font-size:14px;color:#0f172a;"><?php echo View::e($pName); ?></div>
          <div style="font-size:12px;color:#64748b;line-height:1.4;"><?php echo View::e($pDesc); ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
