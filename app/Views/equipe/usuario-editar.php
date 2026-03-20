<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

$id = $usuario['id'] ?? null;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $id ? 'Editar usuário' : 'Novo usuário'; ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Usuários</div>
        <div style="opacity:.9; font-size:13px;"><?php echo $id ? 'Editar' : 'Novo'; ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/usuarios">Voltar</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo"><?php echo $id ? 'Editar usuário' : 'Novo usuário'; ?></h1>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/equipe/usuarios/salvar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo View::e((string) ($usuario['id'] ?? '')); ?>" />

        <div class="grid">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Nome</label>
            <input class="input" type="text" name="name" value="<?php echo View::e((string) ($usuario['name'] ?? '')); ?>" autocomplete="name" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">E-mail</label>
            <input class="input" type="email" name="email" value="<?php echo View::e((string) ($usuario['email'] ?? '')); ?>" autocomplete="email" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Role</label>
            <?php $role = (string) ($usuario['role'] ?? 'admin'); ?>
            <select class="input" name="role">
              <option value="superadmin" <?php echo $role === 'superadmin' ? 'selected' : ''; ?>>superadmin</option>
              <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>admin</option>
              <option value="financeiro" <?php echo $role === 'financeiro' ? 'selected' : ''; ?>>financeiro</option>
              <option value="devops" <?php echo $role === 'devops' ? 'selected' : ''; ?>>devops</option>
              <option value="programador" <?php echo $role === 'programador' ? 'selected' : ''; ?>>programador</option>
              <option value="suporte" <?php echo $role === 'suporte' ? 'selected' : ''; ?>>suporte</option>
            </select>
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Status</label>
            <?php $st = (string) ($usuario['status'] ?? 'active'); ?>
            <select class="input" name="status">
              <option value="active" <?php echo $st === 'active' ? 'selected' : ''; ?>>Ativo</option>
              <option value="inactive" <?php echo $st === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
            </select>
          </div>
        </div>

        <div style="margin-top:12px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">Senha <?php echo $id ? '(deixe em branco para não alterar)' : ''; ?></label>
          <input class="input" type="password" name="password" autocomplete="new-password" />
        </div>

        <div style="margin-top:14px;" class="linha">
          <button class="botao" type="submit">Salvar</button>
          <a href="/equipe/usuarios">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
