<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'Bancos de Dados';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Bancos de Dados</div>
    <div class="page-subtitle" style="margin-bottom:0;">Crie e gerencie bancos MySQL nas suas VPS</div>
  </div>
  <a href="/cliente/banco-dados/criar" class="botao">+ Novo banco</a>
</div>

<?php if (empty($bancos)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:40px;margin-bottom:12px;">🗄️</div>
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhum banco de dados</div>
  <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Crie um banco MySQL para usar nas suas aplicações.</div>
  <a href="/cliente/banco-dados/criar" class="botao">Criar banco de dados</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px;">
  <?php foreach ($bancos as $b):
    $bid = (int)($b['id'] ?? 0);
    $status = (string)($b['status'] ?? 'active');
    $statusColor = $status === 'active' ? '#10b981' : ($status === 'error' ? '#ef4444' : '#f59e0b');
    $statusLabel = $status === 'active' ? 'Ativo' : ($status === 'error' ? 'Erro' : 'Criando...');
  ?>
  <div class="card-new">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;"><?php echo View::e((string)($b['name'] ?? '')); ?></div>
        <div style="font-size:12px;color:#64748b;font-family:monospace;margin-top:2px;"><?php echo View::e((string)($b['db_name'] ?? '')); ?></div>
      </div>
      <span style="font-size:11px;padding:3px 10px;border-radius:99px;background:<?php echo $statusColor; ?>20;color:<?php echo $statusColor; ?>;font-weight:600;"><?php echo $statusLabel; ?></span>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="/cliente/banco-dados/ver?id=<?php echo $bid; ?>" class="botao sm">Ver detalhes / SQL</a>
      <form method="post" action="/cliente/banco-dados/excluir" style="display:inline;" onsubmit="return confirm('Remover banco <?php echo View::e((string)($b['name'] ?? '')); ?>? Os dados serão perdidos.')">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo $bid; ?>" />
        <button class="botao danger sm" type="submit">Remover</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
