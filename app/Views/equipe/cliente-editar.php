<?php
declare(strict_types=1);
use LRV\Core\View;

$cliente  = $cliente ?? [];
$isNovo   = empty($cliente['id']);
$pageTitle = $isNovo ? 'Novo Cliente' : 'Editar Cliente';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="/equipe/clientes" style="color:#94a3b8;font-size:13px;">← Clientes</a>
  <span style="color:#e2e8f0;">/</span>
  <span style="font-size:13px;color:#475569;"><?php echo $isNovo ? 'Novo cliente' : View::e((string)($cliente['name'] ?? '')); ?></span>
</div>

<div class="page-title"><?php echo $isNovo ? 'Novo cliente' : 'Editar cliente'; ?></div>
<div class="page-subtitle"><?php echo $isNovo ? 'Crie uma conta de acesso para o cliente.' : 'Atualize os dados do cliente.'; ?></div>

<?php if (!empty($erro)): ?><div class="erro"><?php echo View::e($erro); ?></div><?php endif; ?>

<div class="card-new" style="max-width:680px;">
  <form method="POST" action="/equipe/clientes/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo (int)($cliente['id'] ?? 0); ?>" />

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Nome *</label>
        <input type="text" name="name" class="input" value="<?php echo View::e((string)($cliente['name'] ?? '')); ?>" required />
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">E-mail *</label>
        <input type="email" name="email" class="input" value="<?php echo View::e((string)($cliente['email'] ?? '')); ?>" required />
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Telefone</label>
        <input type="text" name="phone" class="input" value="<?php echo View::e((string)($cliente['phone'] ?? '')); ?>" placeholder="+55 11 99999-9999" />
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">CPF / CNPJ</label>
        <input type="text" name="cpf_cnpj" class="input" value="<?php echo View::e((string)($cliente['cpf_cnpj'] ?? '')); ?>" />
      </div>
    </div>

    <div style="border-top:1px solid #f1f5f9;padding-top:16px;margin-bottom:16px;">
      <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">
        Senha <?php echo $isNovo ? '*' : '<span style="font-weight:400;color:#94a3b8;">(deixe em branco para não alterar)</span>'; ?>
      </label>
      <input type="password" name="password" class="input" style="max-width:320px;"
             autocomplete="new-password" minlength="8"
             <?php echo $isNovo ? 'required' : ''; ?> placeholder="••••••••" />
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="/equipe/clientes" class="botao sm sec">Cancelar</a>
      <button type="submit" class="botao"><?php echo $isNovo ? 'Criar cliente' : 'Salvar alterações'; ?></button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
