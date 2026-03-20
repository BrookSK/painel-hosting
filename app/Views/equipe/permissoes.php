<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
$roles = (array) ($roles ?? []);
$permissoes = (array) ($permissoes ?? []);
$atual = (array) ($atual ?? []);
$ok = (string) ($ok ?? '');
$erro = (string) ($erro ?? '');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Permissões por Role</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    table{width:100%;border-collapse:collapse;}
    th,td{padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:13px;}
    th{background:#f8fafc;font-weight:600;}
    input[type=checkbox]{width:16px;height:16px;cursor:pointer;}
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div><div style="font-size:18px;font-weight:700;">Permissões por Role</div></div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/usuarios">Usuários</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>
  <div class="conteudo">
    <div class="card">
      <?php if ($ok !== ''): ?>
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:10px 12px;border-radius:12px;margin-bottom:12px;"><?php echo View::e($ok); ?></div>
      <?php endif; ?>
      <?php if ($erro !== ''): ?>
        <div class="erro"><?php echo View::e($erro); ?></div>
      <?php endif; ?>
      <form method="post" action="/equipe/permissoes/salvar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div style="overflow:auto;">
          <table>
            <thead>
              <tr>
                <th>Permissão</th>
                <?php foreach ($roles as $role): ?>
                  <th><?php echo View::e($role); ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($permissoes as $perm): ?>
                <?php if (!is_array($perm)) continue; ?>
                <tr>
                  <td>
                    <strong><?php echo View::e((string) ($perm['key'] ?? '')); ?></strong>
                    <?php if (!empty($perm['description'])): ?>
                      <div style="font-size:11px;opacity:.7;"><?php echo View::e((string) ($perm['description'] ?? '')); ?></div>
                    <?php endif; ?>
                  </td>
                  <?php foreach ($roles as $role): ?>
                    <td style="text-align:center;">
                      <input type="checkbox"
                        name="perm_<?php echo View::e($role); ?>_<?php echo View::e((string) ($perm['key'] ?? '')); ?>"
                        value="1"
                        <?php echo !empty($atual[$role][(string) ($perm['key'] ?? '')]) ? 'checked' : ''; ?>
                        <?php echo $role === 'superadmin' ? 'checked disabled' : ''; ?>
                      />
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="margin-top:14px;">
          <button class="botao" type="submit">Salvar permissões</button>
          <span style="font-size:12px;opacity:.7;margin-left:10px;">superadmin sempre tem todas as permissões.</span>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
