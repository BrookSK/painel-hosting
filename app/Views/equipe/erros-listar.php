<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = I18n::t('eq_erros.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$erros          = $erros ?? [];
$resumo         = $resumo ?? [];
$total          = (int) ($total ?? 0);
$pagina         = (int) ($pagina ?? 1);
$porPagina      = (int) ($porPagina ?? 30);
$filtroCode     = (int) ($filtroCode ?? 0);
$filtroType     = (string) ($filtroType ?? '');
$filtroResolved = (int) ($filtroResolved ?? -1);
$totalPaginas   = max(1, (int) ceil($total / $porPagina));

function erroCorBadge(int $code): string {
    if ($code >= 500) return 'background:#fee2e2;color:#b91c1c;';
    if ($code >= 400) return 'background:#fef3c7;color:#92400e;';
    return 'background:#e0f2fe;color:#0369a1;';
}
?>
<div class="page-title"><?php echo View::e(I18n::t('eq_erros.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_erros.subtitulo')); ?></div>

<?php if (!empty($resumo)): ?>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
  <?php foreach ($resumo as $r): ?>
  <a href="/equipe/erros?code=<?php echo (int)$r['http_code']; ?>"
     style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 18px;text-decoration:none;color:inherit;min-width:100px;text-align:center;">
    <div style="font-size:22px;font-weight:800;<?php echo erroCorBadge((int)$r['http_code']); ?>border-radius:8px;padding:2px 8px;display:inline-block;">
      <?php echo (int)$r['http_code']; ?>
    </div>
    <div style="font-size:12px;color:#64748b;margin-top:4px;"><?php echo (int)$r['total']; ?> <?php echo View::e(I18n::t('eq_erros.total')); ?></div>
    <?php if ((int)$r['pendentes'] > 0): ?>
    <div style="font-size:11px;color:#b91c1c;"><?php echo (int)$r['pendentes']; ?> <?php echo View::e(I18n::t('eq_erros.pendentes')); ?></div>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card-new" style="margin-bottom:16px;">
  <form method="get" action="/equipe/erros" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div>
      <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;"><?php echo View::e(I18n::t('eq_erros.codigo_http')); ?></label>
      <select class="input" name="code" style="width:120px;">
        <option value="0" <?php echo $filtroCode===0?'selected':''; ?>><?php echo View::e(I18n::t('eq_erros.todos')); ?></option>
        <option value="400" <?php echo $filtroCode===400?'selected':''; ?>>400</option>
        <option value="401" <?php echo $filtroCode===401?'selected':''; ?>>401</option>
        <option value="403" <?php echo $filtroCode===403?'selected':''; ?>>403</option>
        <option value="404" <?php echo $filtroCode===404?'selected':''; ?>>404</option>
        <option value="419" <?php echo $filtroCode===419?'selected':''; ?>>419</option>
        <option value="429" <?php echo $filtroCode===429?'selected':''; ?>>429</option>
        <option value="500" <?php echo $filtroCode===500?'selected':''; ?>>500</option>
        <option value="502" <?php echo $filtroCode===502?'selected':''; ?>>502</option>
        <option value="503" <?php echo $filtroCode===503?'selected':''; ?>>503</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;"><?php echo View::e(I18n::t('eq_erros.tipo')); ?></label>
      <input class="input" type="text" name="type" value="<?php echo View::e($filtroType); ?>" placeholder="exception, not_found…" style="width:160px;" />
    </div>
    <div>
      <label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px;"><?php echo View::e(I18n::t('geral.status')); ?></label>
      <select class="input" name="resolved" style="width:130px;">
        <option value="-1" <?php echo $filtroResolved===-1?'selected':''; ?>><?php echo View::e(I18n::t('eq_erros.todos')); ?></option>
        <option value="0"  <?php echo $filtroResolved===0?'selected':''; ?>><?php echo View::e(I18n::t('eq_erros.pendentes')); ?></option>
        <option value="1"  <?php echo $filtroResolved===1?'selected':''; ?>><?php echo View::e(I18n::t('eq_erros.resolvido')); ?>s</option>
      </select>
    </div>
    <button class="botao sm" type="submit"><?php echo View::e(I18n::t('eq_erros.filtrar')); ?></button>
    <a href="/equipe/erros" class="botao ghost sm"><?php echo View::e(I18n::t('eq_erros.limpar')); ?></a>
    <div style="margin-left:auto;display:flex;gap:8px;">
      <button class="botao sec sm" type="button" onclick="limparResolvidos()"><?php echo View::e(I18n::t('eq_erros.limpar_resolvidos')); ?></button>
    </div>
  </form>
</div>

<div class="card-new" style="padding:0;overflow:hidden;">
  <?php if (empty($erros)): ?>
    <div style="padding:40px;text-align:center;color:#64748b;"><?php echo View::e(I18n::t('eq_erros.nenhum')); ?></div>
  <?php else: ?>
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
      <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;">#</th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"><?php echo View::e(I18n::t('eq_erros.codigo')); ?></th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"><?php echo View::e(I18n::t('eq_erros.tipo')); ?></th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"><?php echo View::e(I18n::t('eq_erros.url')); ?></th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"><?php echo View::e(I18n::t('eq_erros.mensagem')); ?></th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"><?php echo View::e(I18n::t('eq_erros.ip')); ?></th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"><?php echo View::e(I18n::t('eq_erros.data')); ?></th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"><?php echo View::e(I18n::t('geral.status')); ?></th>
        <th style="padding:10px 14px;text-align:left;font-weight:600;color:#475569;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($erros as $e): ?>
      <tr style="border-bottom:1px solid #f1f5f9;" id="row-<?php echo (int)$e['id']; ?>">
        <td style="padding:10px 14px;color:#94a3b8;"><?php echo (int)$e['id']; ?></td>
        <td style="padding:10px 14px;">
          <span style="font-size:12px;font-weight:700;padding:3px 8px;border-radius:6px;<?php echo erroCorBadge((int)$e['http_code']); ?>">
            <?php echo (int)$e['http_code']; ?>
          </span>
        </td>
        <td style="padding:10px 14px;color:#475569;"><?php echo View::e((string)($e['error_type']??'')); ?></td>
        <td style="padding:10px 14px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <span title="<?php echo View::e((string)($e['url']??'')); ?>">
            <?php echo View::e((string)($e['method']??'GET')); ?>
            <?php
              $urlShort = (string)($e['url']??'');
              // Mostrar só o path
              $parsed = parse_url($urlShort, PHP_URL_PATH);
              echo View::e($parsed ?: $urlShort);
            ?>
          </span>
        </td>
        <td style="padding:10px 14px;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#475569;">
          <span title="<?php echo View::e((string)($e['message']??'')); ?>">
            <?php echo View::e(substr((string)($e['message']??''), 0, 80)); ?>
          </span>
        </td>
        <td style="padding:10px 14px;color:#94a3b8;font-size:12px;"><?php echo View::e((string)($e['ip_address']??'')); ?></td>
        <td style="padding:10px 14px;color:#94a3b8;font-size:12px;white-space:nowrap;">
          <?php echo View::e(date('d/m/y H:i', strtotime((string)($e['created_at']??'now')))); ?>
        </td>
        <td style="padding:10px 14px;">
          <?php if ((int)($e['resolved']??0) === 1): ?>
            <span style="font-size:11px;background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:999px;"><?php echo View::e(I18n::t('eq_erros.resolvido')); ?></span>
          <?php else: ?>
            <span style="font-size:11px;background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:999px;"><?php echo View::e(I18n::t('eq_erros.pendente')); ?></span>
          <?php endif; ?>
        </td>
        <td style="padding:10px 14px;white-space:nowrap;">
          <a href="/equipe/erros/ver?id=<?php echo (int)$e['id']; ?>" class="botao ghost sm" style="font-size:12px;"><?php echo View::e(I18n::t('geral.ver')); ?></a>
          <?php if ((int)($e['resolved']??0) === 0): ?>
          <button class="botao sm" style="font-size:12px;" onclick="resolverErro(<?php echo (int)$e['id']; ?>)">✓</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php if ($totalPaginas > 1): ?>
<div style="display:flex;gap:8px;justify-content:center;margin-top:16px;flex-wrap:wrap;">
  <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
    <a href="?pagina=<?php echo $p; ?>&code=<?php echo $filtroCode; ?>&type=<?php echo urlencode($filtroType); ?>&resolved=<?php echo $filtroResolved; ?>"
       class="botao <?php echo $p === $pagina ? '' : 'ghost'; ?> sm"
       style="min-width:36px;text-align:center;"><?php echo $p; ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<div style="margin-top:10px;font-size:13px;color:#94a3b8;">
  <?php echo I18n::tf('eq_erros.encontrados', $total); ?>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function resolverErro(id) {
  const r = await fetch('/equipe/erros/resolver', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},
    body: 'id=' + id
  });
  const j = await r.json();
  if (j.ok) {
    const row = document.getElementById('row-' + id);
    if (row) {
      const badge = row.querySelector('span[style*="fee2e2"]');
      if (badge) { badge.style.background='#dcfce7'; badge.style.color='#15803d'; badge.textContent='Resolvido'; }
      const btn = row.querySelector('button');
      if (btn) btn.remove();
    }
  }
}

async function limparResolvidos() {
  if (!confirm('Excluir todos os erros marcados como resolvidos?')) return;
  const r = await fetch('/equipe/erros/limpar-resolvidos', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},
    body: '_csrf=' + csrf
  });
  const j = await r.json();
  if (j.ok) location.reload();
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
