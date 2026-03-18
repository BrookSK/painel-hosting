<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$id = $plano['id'] ?? null;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $id ? 'Editar plano' : 'Novo plano'; ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Planos</div>
        <div style="opacity:.9; font-size:13px;"><?php echo $id ? 'Editar' : 'Novo'; ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/planos">Voltar</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo"><?php echo $id ? 'Editar plano' : 'Novo plano'; ?></h1>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/equipe/planos/salvar">
        <input type="hidden" name="id" value="<?php echo View::e((string) ($plano['id'] ?? '')); ?>" />

        <div class="grid">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Nome</label>
            <input class="input" type="text" name="name" value="<?php echo View::e((string) ($plano['name'] ?? '')); ?>" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Status</label>
            <select class="input" name="status">
              <option value="active" <?php echo ($plano['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
              <option value="inactive" <?php echo ($plano['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
            </select>
          </div>
        </div>

        <div style="margin-top:12px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">Descrição</label>
          <input class="input" type="text" name="description" value="<?php echo View::e((string) ($plano['description'] ?? '')); ?>" />
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">vCPU</label>
            <input class="input" type="number" name="cpu" value="<?php echo View::e((string) ($plano['cpu'] ?? '')); ?>" min="1" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Memória (MB)</label>
            <input class="input" type="number" name="ram" value="<?php echo View::e((string) ($plano['ram'] ?? '')); ?>" min="256" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Exemplo: 4096 = 4 GB</p>
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Armazenamento (MB)</label>
            <input class="input" type="number" name="storage" value="<?php echo View::e((string) ($plano['storage'] ?? '')); ?>" min="1024" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Exemplo: 81920 = 80 GB</p>
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Preço mensal (R$)</label>
            <input class="input" type="text" name="price_monthly" value="<?php echo View::e((string) ($plano['price_monthly'] ?? '')); ?>" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Especificações extras (JSON opcional)</label>
            <input class="input" type="text" name="specs_json" value="<?php echo View::e((string) ($plano['specs_json'] ?? '')); ?>" />
          </div>
        </div>

        <div style="margin-top:14px;">
          <button class="botao" type="submit">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
