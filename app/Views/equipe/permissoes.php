<?php
declare(strict_types=1);
use LRV\Core\View;

$roles      = (array)($roles ?? []);
$permissoes = (array)($permissoes ?? []);
$atual      = (array)($atual ?? []);
$ok         = (string)($ok ?? '');
$erro       = (string)($erro ?? '');

$pageTitle = 'Permissões por Role';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Permissões por Role</div>
<div class="page-subtitle">Controle de acesso por função. superadmin sempre tem todas as permissões.</div>

<?php if ($ok !== ''): ?><div class="sucesso"><?php echo View::e($ok); ?></div><?php endif; ?>
<?php if ($erro !== ''): ?><div class="erro"><?php echo View::e($erro); ?></div><?php endif; ?>

<div class="card-new" style="padding:0;overflow:auto;">
  <form method="post" action="/equipe/permissoes/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:600px;">
      <thead>
        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
          <th style="padding:12px 16px;text-align:left;font-weight:600;color:#475569;min-width:200px;">Permissão</th>
          <?php foreach ($roles as $role): ?>
            <th style="padding:12px 10px;text-align:center;font-weight:600;color:#475569;white-space:nowrap;">
              <?php echo View::e($role); ?>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($permissoes as $perm): ?>
          <?php if (!is_array($perm)) continue; ?>
          <?php $key = (string)($perm['key'] ?? ''); ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 16px;">
              <strong style="color:#1e293b;"><?php echo View::e($key); ?></strong>
              <?php if (!empty($perm['description'])): ?>
                <div style="font-size:11px;color:#94a3b8;margin-top:2px;"><?php echo View::e((string)$perm['description']); ?></div>
              <?php endif; ?>
            </td>
            <?php foreach ($roles as $role): ?>
              <td style="padding:10px;text-align:center;">
                <?php $checked = !empty($atual[$role][$key]) || $role === 'superadmin'; ?>
                <input type="checkbox"
                  name="perm_<?php echo View::e($role); ?>_<?php echo View::e($key); ?>"
                  value="1"
                  <?php echo $checked ? 'checked' : ''; ?>
                  <?php echo $role === 'superadmin' ? 'disabled' : ''; ?>
                  style="width:16px;height:16px;accent-color:#4F46E5;cursor:<?php echo $role === 'superadmin' ? 'not-allowed' : 'pointer'; ?>;"
                />
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="padding:16px;border-top:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
      <button class="botao sm" type="submit">Salvar permissões</button>
      <span style="font-size:12px;color:#94a3b8;">superadmin sempre tem todas as permissões.</span>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
