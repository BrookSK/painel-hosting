<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeStatusEquipe(string $st): string {
    $map = ['open'=>['Aberto','badge-blue'],'in_progress'=>['Em andamento','badge-blue'],'waiting_client'=>['Aguard. cliente','badge-yellow'],'closed'=>['Fechado','badge-gray']];
    $d = $map[$st] ?? [$st,'badge-gray'];
    return '<span class="badge-new '.$d[1].'">'.View::e($d[0]).'</span>';
}
function badgePrioridadeEquipe(string $p): string {
    if ($p==='high') return '<span class="badge-new badge-red">Alta</span>';
    if ($p==='low')  return '<span class="badge-new badge-green">Baixa</span>';
    return '<span class="badge-new badge-yellow">Média</span>';
}

$filtroStatus = (string)($_GET['status']??'');
$filtroPrio   = (string)($_GET['priority']??'');
$filtroDept   = (string)($_GET['department']??'');
$filtroBusca  = (string)($_GET['q']??'');

$pageTitle = 'Tickets';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Tickets</div>
<div class="page-subtitle">Atendimento e histórico</div>

<div class="card-new" style="margin-bottom:16px;">
  <form method="get" action="/equipe/tickets">
    <div class="linha" style="gap:8px;flex-wrap:wrap;margin-bottom:0;">
      <input class="input" type="text" name="q" placeholder="Buscar assunto ou cliente..." value="<?php echo View::e($filtroBusca); ?>" style="max-width:240px;" />
      <select class="input" name="status" style="max-width:160px;">
        <option value="">Todos os status</option>
        <option value="open" <?php echo $filtroStatus==='open'?'selected':''; ?>>Aberto</option>
        <option value="in_progress" <?php echo $filtroStatus==='in_progress'?'selected':''; ?>>Em andamento</option>
        <option value="waiting_client" <?php echo $filtroStatus==='waiting_client'?'selected':''; ?>>Aguardando cliente</option>
        <option value="closed" <?php echo $filtroStatus==='closed'?'selected':''; ?>>Fechado</option>
      </select>
      <select class="input" name="priority" style="max-width:140px;">
        <option value="">Todas as prioridades</option>
        <option value="high" <?php echo $filtroPrio==='high'?'selected':''; ?>>Alta</option>
        <option value="medium" <?php echo $filtroPrio==='medium'?'selected':''; ?>>Média</option>
        <option value="low" <?php echo $filtroPrio==='low'?'selected':''; ?>>Baixa</option>
      </select>
      <select class="input" name="department" style="max-width:150px;">
        <option value="">Todos os departamentos</option>
        <option value="suporte" <?php echo $filtroDept==='suporte'?'selected':''; ?>>Suporte</option>
        <option value="financeiro" <?php echo $filtroDept==='financeiro'?'selected':''; ?>>Financeiro</option>
        <option value="devops" <?php echo $filtroDept==='devops'?'selected':''; ?>>DevOps</option>
        <option value="comercial" <?php echo $filtroDept==='comercial'?'selected':''; ?>>Comercial</option>
      </select>
      <button class="botao" type="submit">Filtrar</button>
      <?php if ($filtroStatus!==''||$filtroPrio!==''||$filtroDept!==''||$filtroBusca!==''): ?>
        <a href="/equipe/tickets" class="botao sec">Limpar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>#</th><th>Cliente</th><th>Assunto</th><th>Dept.</th><th>Prioridade</th><th>Status</th><th>Atribuído</th><th>Atualizado</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach (($tickets??[]) as $t): ?>
          <tr>
            <td>#<?php echo (int)($t['id']??0); ?></td>
            <td>
              <div><strong><?php echo View::e((string)($t['client_name']??'')); ?></strong></div>
              <div style="font-size:12px;opacity:.8;"><?php echo View::e((string)($t['client_email']??'')); ?></div>
            </td>
            <td><?php echo View::e((string)($t['subject']??'')); ?></td>
            <td><?php echo View::e((string)($t['department']??'')); ?></td>
            <td><?php echo badgePrioridadeEquipe((string)($t['priority']??'medium')); ?></td>
            <td><?php echo badgeStatusEquipe((string)($t['status']??'open')); ?></td>
            <td style="font-size:12px;"><?php echo View::e((string)($t['assigned_name']??'')); ?></td>
            <td style="font-size:12px;"><?php echo View::e((string)($t['updated_at']??'')); ?></td>
            <td><a href="/equipe/tickets/ver?id=<?php echo (int)($t['id']??0); ?>">Abrir</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
          <tr><td colspan="9">Nenhum ticket encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
