<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$subject = (string) (($form['subject'] ?? '') ?? '');
$priority = (string) (($form['priority'] ?? 'medium') ?? 'medium');
$department = (string) (($form['department'] ?? 'suporte') ?? 'suporte');
$message = (string) (($form['message'] ?? '') ?? '');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Novo ticket</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Tickets</div>
        <div style="opacity:.9; font-size:13px;">Abrir novo ticket</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/tickets">Voltar</a>
        <a href="/cliente/painel">Painel</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo">Novo ticket</h1>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/cliente/tickets/criar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">Assunto</label>
          <input class="input" type="text" name="subject" value="<?php echo View::e($subject); ?>" />
        </div>

        <div class="grid" style="margin-bottom:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Prioridade</label>
            <select class="input" name="priority">
              <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Baixa</option>
              <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Média</option>
              <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>Alta</option>
            </select>
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Departamento</label>
            <select class="input" name="department">
              <option value="suporte" <?php echo $department === 'suporte' ? 'selected' : ''; ?>>Suporte</option>
              <option value="financeiro" <?php echo $department === 'financeiro' ? 'selected' : ''; ?>>Financeiro</option>
              <option value="devops" <?php echo $department === 'devops' ? 'selected' : ''; ?>>DevOps</option>
              <option value="comercial" <?php echo $department === 'comercial' ? 'selected' : ''; ?>>Comercial</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">Mensagem</label>
          <textarea class="input" name="message" rows="8"><?php echo View::e($message); ?></textarea>
        </div>

        <button class="botao" type="submit">Criar ticket</button>
      </form>
    </div>
  </div>
</body>
</html>
