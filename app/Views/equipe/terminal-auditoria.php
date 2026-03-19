<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Auditoria - Terminal</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Auditoria - Terminal</div>
        <div style="opacity:.9; font-size:13px;">Últimas 200 sessões</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/terminal">Terminal</a>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">ID</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Usuário</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Node</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Início</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Fim</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">IP</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (((array) ($sessoes ?? [])) as $s): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($s['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo View::e((string) ($s['user_name'] ?? '')); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo View::e((string) ($s['server_hostname'] ?? '')); ?>
                  <div style="opacity:.8; font-size:12px;"><code><?php echo View::e((string) ($s['server_ip'] ?? '')); ?></code></div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['started_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['ended_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($s['ip'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><a href="/equipe/terminal/auditoria/ver?id=<?php echo (int) ($s['id'] ?? 0); ?>">Ver comandos</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($sessoes)): ?>
              <tr>
                <td colspan="7" style="padding:12px;">Nenhum registro encontrado (ou migrations não aplicadas).</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
