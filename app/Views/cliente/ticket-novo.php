<?php
declare(strict_types=1);
use LRV\Core\View;

$subject    = (string)(($form['subject']    ?? '') ?? '');
$priority   = (string)(($form['priority']   ?? 'medium') ?? 'medium');
$department = (string)(($form['department'] ?? 'suporte') ?? 'suporte');
$message    = (string)(($form['message']    ?? '') ?? '');

$pageTitle    = 'Novo ticket';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Novo ticket</div>
    <div class="page-subtitle" style="margin-bottom:0;">Abrir solicitação de suporte</div>
  </div>
  <a href="/cliente/tickets" class="botao ghost sm">← Voltar</a>
</div>

<div class="card-new" style="max-width:760px;">
  <?php if (!empty($erro)): ?>
    <div class="erro"><?php echo View::e((string)$erro); ?></div>
  <?php endif; ?>

  <form method="post" action="/cliente/tickets/criar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Assunto</label>
      <input class="input" type="text" name="subject" value="<?php echo View::e($subject); ?>" required />
    </div>

    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Prioridade</label>
        <select class="input" name="priority">
          <option value="low"    <?php echo $priority === 'low'    ? 'selected' : ''; ?>>Baixa</option>
          <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Média</option>
          <option value="high"   <?php echo $priority === 'high'   ? 'selected' : ''; ?>>Alta</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Departamento</label>
        <select class="input" name="department">
          <option value="suporte"    <?php echo $department === 'suporte'    ? 'selected' : ''; ?>>Suporte</option>
          <option value="financeiro" <?php echo $department === 'financeiro' ? 'selected' : ''; ?>>Financeiro</option>
          <option value="devops"     <?php echo $department === 'devops'     ? 'selected' : ''; ?>>DevOps</option>
          <option value="comercial"  <?php echo $department === 'comercial'  ? 'selected' : ''; ?>>Comercial</option>
        </select>
      </div>
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Mensagem</label>
      <textarea class="input" name="message" rows="8"><?php echo View::e($message); ?></textarea>
    </div>

    <button class="botao" type="submit">Criar ticket</button>
  </form>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
