<?php

declare(strict_types=1);

use LRV\Core\View;

$id = $servidor['id'] ?? null;

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $id ? 'Editar servidor' : 'Novo servidor'; ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Servidores</div>
        <div style="opacity:.9; font-size:13px;"><?php echo $id ? 'Editar' : 'Novo'; ?></div>
      </div>
      <div class="linha">
        <a href="/equipe/servidores">Voltar</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo"><?php echo $id ? 'Editar servidor' : 'Novo servidor'; ?></h1>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/equipe/servidores/salvar">
        <input type="hidden" name="id" value="<?php echo View::e((string) ($servidor['id'] ?? '')); ?>" />

        <div class="grid">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Hostname</label>
            <input class="input" type="text" name="hostname" value="<?php echo View::e((string) ($servidor['hostname'] ?? '')); ?>" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Status</label>
            <select class="input" name="status">
              <option value="active" <?php echo ($servidor['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
              <option value="maintenance" <?php echo ($servidor['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Manutenção</option>
              <option value="inactive" <?php echo ($servidor['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
            </select>
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">IP</label>
            <input class="input" type="text" name="ip_address" value="<?php echo View::e((string) ($servidor['ip_address'] ?? '')); ?>" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Porta SSH</label>
            <input class="input" type="number" name="ssh_port" value="<?php echo View::e((string) ($servidor['ssh_port'] ?? '22')); ?>" min="1" max="65535" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">CPU total</label>
            <input class="input" type="number" name="cpu_total" value="<?php echo View::e((string) ($servidor['cpu_total'] ?? '')); ?>" min="1" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Memória total (MB)</label>
            <input class="input" type="number" name="ram_total" value="<?php echo View::e((string) ($servidor['ram_total'] ?? '')); ?>" min="256" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Exemplo: 65536 = 64 GB</p>
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Armazenamento total (MB)</label>
            <input class="input" type="number" name="storage_total" value="<?php echo View::e((string) ($servidor['storage_total'] ?? '')); ?>" min="1024" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Exemplo: 1024000 = 1000 GB</p>
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
