<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusEquipe(string $st): string
{
    $map = [
        'open'           => ['Aberto', '#eef2ff', '#1e3a8a'],
        'in_progress'    => ['Em andamento', '#e0f2fe', '#075985'],
        'waiting_client' => ['Aguardando cliente', '#fef3c7', '#92400e'],
        'closed'         => ['Fechado', '#f1f5f9', '#334155'],
    ];
    $d = $map[$st] ?? [$st, '#f1f5f9', '#334155'];
    return '<span class="badge" style="background:' . $d[1] . ';color:' . $d[2] . ';">' . View::e($d[0]) . '</span>';
}

function badgePrioridadeEquipe(string $p): string
{
    if ($p === 'high') return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Alta</span>';
    if ($p === 'low')  return '<span class="badge" style="background:#dcfce7;color:#166534;">Baixa</span>';
    return '<span class="badge" style="background:#fef3c7;color:#92400e;">Média</span>';
}

$filtroStatus = (string) ($_GET['status'] ?? '');
$filtroPrio   = (string) ($_GET['priority'] ?? '');
$filtroDept   = (string) ($_GET['department'] ?? '');
$filtroBusca  = (string) ($_GET['q'] ?? '');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tickets</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Tickets</div>
        <div style="opacity:.9; font-size:13px;">Atendimento e histórico</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <form method="get" action="/equipe/tickets" style="margin-bottom:14px;">
      <div class="linha" style="gap:8px; flex-wrap:wrap;">
        <input class="input" type="text" name="q" placeholder="Buscar assunto ou cliente..." value="<?php echo View::e($filtroBusca); ?>" style="max-width:260px;" />
        <select class="input" name="status" style="max-width:180px;">
          <option value="">Todos os status</option>
          <option value="open" <?php echo $filtroStatus === 'open' ? 'selected' : ''; ?>>Aberto</option>
          <option value="in_progress" <?php echo $filtroStatus === 'in_progress' ? 'selected' : ''; ?>>Em andamento</option>
          <option value="waiting_client" <?php echo $filtroStatus === 'waiting_client' ? 'selected' : ''; ?>>Aguardando cliente</option>
          <option value="closed" <?php echo $filtroStatus === 'closed' ? 'selected' : ''; ?>>Fechado</option>
        </select>
        <select class="input" name="priority" style="max-width:140px;">
          <option value="">Todas as prioridades</option>
          <option value="high" <?php echo $filtroPrio === 'high' ? 'selected' : ''; ?>>Alta</option>
          <option value="medium" <?php echo $filtroPrio === 'medium' ? 'selected' : ''; ?>>Média</option>
          <option value="low" <?php echo $filtroPrio === 'low' ? 'selected' : ''; ?>>Baixa</option>
        </select>
        <select class="input" name="department" style="max-width:160px;">
          <option value="">Todos os departamentos</option>
          <option value="suporte" <?php echo $filtroDept === 'suporte' ? 'selected' : ''; ?>>Suporte</option>
          <option value="financeiro" <?php echo $filtroDept === 'financeiro' ? 'selected' : ''; ?>>Financeiro</option>
          <option value="devops" <?php echo $filtroDept === 'devops' ? 'selected' : ''; ?>>DevOps</option>
          <option value="comercial" <?php echo $filtroDept === 'comercial' ? 'selected' : ''; ?>>Comercial</option>
        </select>
        <button class="botao" type="submit" style="padding:10px 14px;">Filtrar</button>
        <?php if ($filtroStatus !== '' || $filtroPrio !== '' || $filtroDept !== '' || $filtroBusca !== ''): ?>
          <a href="/equipe/tickets" class="botao sec" style="padding:10px 14px;">Limpar</a>
        <?php endif; ?>
      </div>
    </form>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">#</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Assunto</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Dept.</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Prioridade</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Atribuído</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Atualizado</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($tickets ?? []) as $t): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">#<?php echo (int) ($t['id'] ?? 0); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div><strong><?php echo View::e((string) ($t['client_name'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($t['client_email'] ?? '')); ?></div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($t['subject'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($t['department'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgePrioridadeEquipe((string) ($t['priority'] ?? 'medium')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusEquipe((string) ($t['status'] ?? 'open')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9; font-size:12px;"><?php echo View::e((string) ($t['assigned_name'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9; font-size:12px;"><?php echo View::e((string) ($t['updated_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><a href="/equipe/tickets/ver?id=<?php echo (int) ($t['id'] ?? 0); ?>">Abrir</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($tickets)): ?>
              <tr>
                <td colspan="9" style="padding:12px;">Nenhum ticket encontrado.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
