<?php
declare(strict_types=1);
use LRV\Core\View;

$roles = (array)($roles??[]);
$permissoes = (array)($permissoes??[]);
$atual = (array)($atual??[]);
$ok = (string)($ok??'');
$erro = (string)($erro??'');

$pageTitle = 'Permissoes por Role';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Permissoes por Role</div>
<div class="page-subtitle">Controle de acesso por funcao</div>

<div class="card-new">
  <?php if ($ok!==''): ?><div class="sucesso"><?php echo View::e($ok); ?></div><?php endif; ?>
  <?php if ($erro!==''): ?><div class="erro"><?php echo View::e($erro); ?></div><?php endif; ?>

  <form method="post" action="/equipe/permissoes/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <div style="overflow:auto;">
      <table>
        <thead>
          <tr>
            <th>Permissao</th>
            <?php foreach ($roles as $role): ?><th><?php echo View::e($role); ?></th><?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($permissoes as $perm): ?>
            <?php if (!is_array($perm)) continue; ?>
            <tr>
              <td>
                <strong><?php echo View::e((string)($perm['key']??'')); ?></strong>
                <?php if (!empty($perm['description'])): ?>
                  <div style="font-size:11px;opacity:.7;"><?php echo View::e((string)($perm['description']??'')); ?></div>
                <?php endif; ?>
              </td>
              <?php foreach ($roles as $role): ?>
                <td style="text-align:center;">
                  <input type="checkbox"
                    name="perm_<?php echo View::e($role); ?>_<?php echo View::e((string)($perm['key']??'')); ?>"
                    value="1"
                    <?php echo !empty($atual[$role][(string)($perm['key']??'')])?'checked':''; ?>
                    <?php echo $role==='superadmin'?'checked disabled':''; ?>
                  />
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:14px;">
      <button class="botao" type="submit">Salvar permissoes</button>
      <span style="font-size:12px;opacity:.7;margin-left:10px;">superadmin sempre tem todas as permissoes.</span>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
