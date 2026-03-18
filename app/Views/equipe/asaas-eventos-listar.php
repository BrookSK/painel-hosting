<?php

declare(strict_types=1);

use LRV\Core\View;

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Eventos Asaas</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Eventos Asaas</div>
        <div style="opacity:.9; font-size:13px;">Webhook recebido (últimos 300)</div>
      </div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/assinaturas">Assinaturas</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <?php if (!empty($erro)): ?>
      <div class="card" style="border:1px solid #fecaca; background:#fff1f2;">
        <div style="font-weight:700;">Atenção</div>
        <div class="texto" style="margin:6px 0 0 0;"><?php echo View::e((string) $erro); ?></div>
      </div>
      <div style="height:12px;"></div>
    <?php endif; ?>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">ID</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Event ID</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Tipo</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Criado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($eventos ?? []) as $e): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($e['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($e['event_id'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($e['event_type'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($e['created_at'] ?? '')); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($eventos)): ?>
              <tr>
                <td colspan="4" style="padding:12px;">Nenhum evento recebido ainda.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
