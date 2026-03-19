<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeStatusAplicacao(string $st): string
{
    if ($st === 'inactive') {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">Inativa</span>';
    }
    if ($st === 'deploying') {
        return '<span class="badge" style="background:#e0e7ff;color:#1e3a8a;">Deploy</span>';
    }
    if ($st === 'error') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Erro</span>';
    }
    return '<span class="badge">Ativa</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Aplicações</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Aplicações</div>
        <div style="opacity:.9; font-size:13px;">Apps vinculados às VPS e portas reservadas</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/vps">VPS</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="linha" style="justify-content:space-between; margin-bottom:12px;">
      <div class="texto" style="margin:0;">Crie aplicações e reserve portas únicas para expor serviços.</div>
      <a class="botao" href="/equipe/aplicacoes/novo">Nova aplicação</a>
    </div>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">ID</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Tipo</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Domínio</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Porta</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($aplicacoes ?? []) as $a): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($a['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">#<?php echo (int) ($a['vps_id'] ?? 0); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($a['client_email'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($a['type'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($a['domain'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($a['port'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusAplicacao((string) ($a['status'] ?? 'active')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <a href="/equipe/aplicacoes/editar?id=<?php echo (int) ($a['id'] ?? 0); ?>">Editar</a>
                  <span style="opacity:.4; margin:0 6px;">|</span>
                  <form method="post" action="/equipe/aplicacoes/deploy" style="display:inline;" onsubmit="return confirm('Iniciar deploy agora?');">
                    <input type="hidden" name="id" value="<?php echo (int) ($a['id'] ?? 0); ?>" />
                    <button class="botao sec" type="submit" style="padding:6px 10px; border-radius:10px;">Deploy</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($aplicacoes)): ?>
              <tr>
                <td colspan="8" style="padding:12px;">Nenhuma aplicação cadastrada.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
