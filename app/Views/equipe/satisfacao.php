<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Satisfação';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$_media  = (float) ($totais['media'] ?? 0);
$_total  = (int)   ($totais['total'] ?? 0);
$_cinco  = (int)   ($totais['cinco'] ?? 0);
$_quatro = (int)   ($totais['quatro'] ?? 0);
$_tres   = (int)   ($totais['tres'] ?? 0);
$_dois   = (int)   ($totais['dois'] ?? 0);
$_um     = (int)   ($totais['um'] ?? 0);

function _star_color(float $m): string {
    if ($m >= 4.5) return '#16a34a';
    if ($m >= 3.5) return '#22c55e';
    if ($m >= 2.5) return '#eab308';
    if ($m >= 1.5) return '#f97316';
    return '#ef4444';
}
function _rating_label(int $r): string {
    return ['', 'Muito ruim', 'Ruim', 'Regular', 'Bom', 'Excelente'][$r] ?? '';
}
function _badge_rating(int $r): string {
    $c = ['', '#ef4444','#f97316','#eab308','#22c55e','#16a34a'][$r] ?? '#94a3b8';
    return "<span style='display:inline-block;background:{$c};color:#fff;border-radius:6px;padding:2px 8px;font-size:12px;font-weight:600;'>{$r}★</span>";
}
?>
<div class="page-title">Satisfação do Suporte</div>
<div class="page-subtitle">Avaliações de tickets e chats encerrados</div>

<!-- Cards de resumo -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
  <div class="card-new" style="text-align:center;padding:20px 16px;">
    <div style="font-size:36px;font-weight:700;color:<?php echo _star_color($_media); ?>;"><?php echo $_total > 0 ? number_format($_media, 1) : '—'; ?></div>
    <div style="font-size:13px;color:#64748b;margin-top:4px;">Média geral</div>
    <?php if ($_total > 0): ?>
      <div style="font-size:20px;color:<?php echo _star_color($_media); ?>;margin-top:2px;"><?php echo str_repeat('★', (int)round($_media)) . str_repeat('☆', 5 - (int)round($_media)); ?></div>
    <?php endif; ?>
  </div>
  <div class="card-new" style="text-align:center;padding:20px 16px;">
    <div style="font-size:36px;font-weight:700;color:#4F46E5;"><?php echo $_total; ?></div>
    <div style="font-size:13px;color:#64748b;margin-top:4px;">Total de avaliações</div>
  </div>
  <?php foreach ($porTipo as $_pt): ?>
  <div class="card-new" style="text-align:center;padding:20px 16px;">
    <div style="font-size:28px;font-weight:700;color:<?php echo _star_color((float)($_pt['media']??0)); ?>;"><?php echo number_format((float)($_pt['media']??0), 1); ?></div>
    <div style="font-size:13px;color:#64748b;margin-top:4px;"><?php echo View::e(ucfirst((string)($_pt['type']??''))); ?> (<?php echo (int)($_pt['total']??0); ?>)</div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Distribuição de notas -->
<?php if ($_total > 0): ?>
<div class="card-new" style="max-width:520px;margin-bottom:24px;">
  <div style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:14px;">Distribuição de notas</div>
  <?php foreach ([5 => $_cinco, 4 => $_quatro, 3 => $_tres, 2 => $_dois, 1 => $_um] as $_n => $_q): ?>
    <?php $_pct = $_total > 0 ? round($_q / $_total * 100) : 0; ?>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
      <span style="width:20px;text-align:right;font-size:13px;color:#475569;"><?php echo $_n; ?>★</span>
      <div style="flex:1;background:#f1f5f9;border-radius:6px;height:10px;overflow:hidden;">
        <div style="width:<?php echo $_pct; ?>%;height:100%;background:<?php echo _star_color((float)$_n); ?>;border-radius:6px;transition:width .3s;"></div>
      </div>
      <span style="width:36px;font-size:12px;color:#64748b;"><?php echo $_q; ?></span>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Por agente -->
<?php if (!empty($porAgente)): ?>
<div class="card-new" style="margin-bottom:24px;">
  <div style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:14px;">Por agente</div>
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
      <tr style="border-bottom:1px solid #e2e8f0;color:#64748b;">
        <th style="text-align:left;padding:6px 8px;">Agente</th>
        <th style="text-align:center;padding:6px 8px;">Avaliações</th>
        <th style="text-align:center;padding:6px 8px;">Média</th>
        <th style="text-align:center;padding:6px 8px;">Positivas (≥4)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($porAgente as $_a): ?>
        <?php $_am = (float)($_a['media']??0); ?>
        <tr style="border-bottom:1px solid #f1f5f9;">
          <td style="padding:8px;"><?php echo View::e((string)($_a['name']??'')); ?></td>
          <td style="text-align:center;padding:8px;"><?php echo (int)($_a['total']??0); ?></td>
          <td style="text-align:center;padding:8px;font-weight:600;color:<?php echo _star_color($_am); ?>;"><?php echo number_format($_am, 1); ?> ★</td>
          <td style="text-align:center;padding:8px;"><?php echo (int)($_a['positivas']??0); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Últimas avaliações -->
<div class="card-new">
  <div style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:14px;">Últimas avaliações</div>
  <?php if (empty($recentes)): ?>
    <p class="texto">Nenhuma avaliação registrada ainda.</p>
  <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="border-bottom:1px solid #e2e8f0;color:#64748b;">
          <th style="text-align:left;padding:6px 8px;">Data</th>
          <th style="text-align:left;padding:6px 8px;">Tipo</th>
          <th style="text-align:left;padding:6px 8px;">Cliente</th>
          <th style="text-align:left;padding:6px 8px;">Agente</th>
          <th style="text-align:center;padding:6px 8px;">Nota</th>
          <th style="text-align:left;padding:6px 8px;">Comentário</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentes as $_r): ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:8px;white-space:nowrap;color:#64748b;"><?php echo View::e(date('d/m/Y H:i', strtotime((string)($_r['created_at']??'')))); ?></td>
            <td style="padding:8px;">
              <span class="badge-new"><?php echo View::e(ucfirst((string)($_r['type']??''))); ?> #<?php echo (int)($_r['reference_id']??0); ?></span>
            </td>
            <td style="padding:8px;"><?php echo View::e((string)($_r['client_name']??'')); ?></td>
            <td style="padding:8px;"><?php echo View::e((string)($_r['agent_name']??'—')); ?></td>
            <td style="text-align:center;padding:8px;"><?php echo _badge_rating((int)($_r['rating']??0)); ?></td>
            <td style="padding:8px;color:#475569;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo View::e((string)($_r['comment']??'')); ?>">
              <?php echo View::e((string)($_r['comment']??'—')); ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
