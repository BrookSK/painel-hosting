<?php

declare(strict_types=1);

use LRV\Core\View;

function badgeStatusJob(string $st): string
{
    if ($st === 'completed') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Concluído</span>';
    }

    if ($st === 'failed') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Falhou</span>';
    }

    if ($st === 'running') {
        return '<span class="badge" style="background:#e0e7ff;color:#1e3a8a;">Rodando</span>';
    }

    return '<span class="badge" style="background:#fef3c7;color:#92400e;">Pendente</span>';
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Jobs</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Jobs</div>
        <div style="opacity:.9; font-size:13px;">Fila e logs (últimos 200)</div>
      </div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/vps">VPS</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/tickets">Tickets</a>
        <a href="/equipe/configuracoes">Configurações</a>
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
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Tipo</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Criado</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Atualizado</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($jobs ?? []) as $j): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($j['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($j['type'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusJob((string) ($j['status'] ?? 'pending')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($j['created_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($j['updated_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><a href="/equipe/jobs/ver?id=<?php echo (int) ($j['id'] ?? 0); ?>">Ver</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($jobs)): ?>
              <tr>
                <td colspan="6" style="padding:12px;">Nenhum job encontrado.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div style="margin-top:12px;" class="texto">
      Dica: rode <strong>php worker.php</strong> para processar a fila.
    </div>
  </div>
</body>
</html>
