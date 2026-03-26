<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'Novo banco de dados';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Novo banco de dados</div>
    <div class="page-subtitle" style="margin-bottom:0;">MySQL criado automaticamente na sua VPS</div>
  </div>
  <a href="/cliente/banco-dados" class="botao ghost sm">← Voltar</a>
</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e((string)$erro); ?></div>
<?php endif; ?>

<div class="card-new" style="max-width:560px;">
  <form method="post" action="/cliente/banco-dados/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">Nome do banco</label>
      <input class="input" type="text" name="name" placeholder="meu_projeto" required pattern="[a-zA-Z0-9_\-]+" />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">Apenas letras, números, hífens e underscores. O nome real no servidor será prefixado com seu ID.</p>
    </div>

    <div style="margin-bottom:20px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">VPS</label>
      <?php if (empty($vpsList)): ?>
        <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:10px 12px;border-radius:8px;font-size:13px;">Nenhuma VPS ativa encontrada. Você precisa de uma VPS em execução para criar um banco de dados.</div>
      <?php else: ?>
        <select class="input" name="vps_id" required>
          <option value="">Selecione a VPS...</option>
          <?php foreach ($vpsList as $v): ?>
            <option value="<?php echo (int)$v['id']; ?>">VPS #<?php echo (int)$v['id']; ?> — <?php echo (int)$v['cpu']; ?>vCPU / <?php echo round((int)$v['ram']/1024); ?>GB</option>
          <?php endforeach; ?>
        </select>
        <p style="font-size:12px;color:#64748b;margin-top:4px;">O MySQL será criado dentro desta VPS via Docker.</p>
      <?php endif; ?>
    </div>

    <button class="botao" type="submit" <?php echo empty($vpsList) ? 'disabled' : ''; ?>>Criar banco de dados</button>
  </form>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
