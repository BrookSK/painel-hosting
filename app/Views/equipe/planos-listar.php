<?php

declare(strict_types=1);

use LRV\Core\View;

function formatarGb(int $mb): string {
    if ($mb <= 0) { return '0 GB'; }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

function formatarGbStorage(int $mb): string {
    if ($mb <= 0) { return '0 GB'; }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Planos</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Planos</div>
        <div style="opacity:.9; font-size:13px;">CPU, memória, armazenamento e preço</div>
      </div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="linha" style="justify-content:space-between; margin-bottom:12px;">
      <div class="texto" style="margin:0;">Aqui você cria e gerencia os planos. O que estiver no plano é o que libera para o cliente.</div>
      <a class="botao" href="/equipe/planos/novo">Novo plano</a>
    </div>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Nome</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Memória</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Armazenamento</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Preço/mês</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($planos ?? []) as $p): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($p['name'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($p['cpu'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e(formatarGb((int) ($p['ram'] ?? 0))); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e(formatarGbStorage((int) ($p['storage'] ?? 0))); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">R$ <?php echo View::e((string) ($p['price_monthly'] ?? '0.00')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo ($p['status'] ?? '') === 'active' ? '<span class="badge">Ativo</span>' : '<span class="badge" style="background:#f1f5f9;color:#334155;">Inativo</span>'; ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <a href="/equipe/planos/editar?id=<?php echo (int) ($p['id'] ?? 0); ?>">Editar</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($planos)): ?>
              <tr>
                <td colspan="7" style="padding:12px;">Nenhum plano cadastrado ainda.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
