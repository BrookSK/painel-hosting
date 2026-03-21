<?php
declare(strict_types=1);
use LRV\Core\View;

$id = $usuario['id']??null;
$pageTitle = $id?'Editar usuario':'Novo usuario';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo $id?'Editar usuario':'Novo usuario'; ?></div>
<div class="page-subtitle">Usuarios da equipe interna</div>

<div class="card-new" style="max-width:720px;">
  <?php if (!empty($erro)): ?><div class="erro"><?php echo View::e((string)$erro); ?></div><?php endif; ?>

  <form method="post" action="/equipe/usuarios/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo View::e((string)($usuario['id']??'')); ?>" />

    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Nome</label>
        <input class="input" type="text" name="name" value="<?php echo View::e((string)($usuario['name']??'')); ?>" autocomplete="name" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">E-mail</label>
        <input class="input" type="email" name="email" value="<?php echo View::e((string)($usuario['email']??'')); ?>" autocomplete="email" />
      </div>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Role</label>
        <?php $role=(string)($usuario['role']??'admin'); ?>
        <select class="input" name="role">
          <?php foreach (['superadmin','admin','financeiro','devops','programador','suporte'] as $r): ?>
            <option value="<?php echo $r; ?>" <?php echo $role===$r?'selected':''; ?>><?php echo $r; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Status</label>
        <?php $st=(string)($usuario['status']??'active'); ?>
        <select class="input" name="status">
          <option value="active" <?php echo $st==='active'?'selected':''; ?>>Ativo</option>
          <option value="inactive" <?php echo $st==='inactive'?'selected':''; ?>>Inativo</option>
        </select>
      </div>
    </div>

    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Senha <?php echo $id?'(deixe em branco para nao alterar)':''; ?></label>
      <input class="input" type="password" name="password" autocomplete="new-password" />
    </div>

    <div style="margin-top:14px;" class="linha">
      <button class="botao" type="submit">Salvar</button>
      <a href="/equipe/usuarios">Cancelar</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
