<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeStatusUsuario(string $st): string {
    if ($st === 'inactive') return '<span class="badge badge-new badge-yellow">Inativo</span>';
    return '<span class="badge badge-new badge-green">Ativo</span>';
}

$pageTitle = 'Usuários';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Usuários</div>
<div class="page-subtitle">Acessos internos e permissões por role</div>

<div class="card-new" style="margin-bottom:16px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <span class="card-new-title" style="margin:0;">Lista de usuários</span>
    <a class="btn-sm btn-primary" href="/equipe/usuarios/novo">Novo usuário</a>
  </div>
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Nome</th><th>E-mail</th><th>Role</th><th>Status</th><th>Criado</th><th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($usuarios ?? []) as $u): ?>
          <tr>
            <td><strong>#<?php echo (int)($u['id']??0); ?></strong></td>
            <td><?php echo View::e((string)($u['name']??'')); ?></td>
            <td><?php echo View::e((string)($u['email']??'')); ?></td>
            <td><code><?php echo View::e((string)($u['role']??'')); ?></code></td>
            <td><?php echo badgeStatusUsuario((string)($u['status']??'active')); ?></td>
            <td><?php echo View::e((string)($u['created_at']??'')); ?></td>
            <td><a href="/equipe/usuarios/editar?id=<?php echo (int)($u['id']??0); ?>">Editar</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($usuarios)): ?>
          <tr><td colspan="7">Nenhum usuário encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
