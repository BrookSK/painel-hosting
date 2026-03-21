<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusEquipe(string $st): string {
    $map = ['open'=>[I18n::t('eq_tickets.aberto'),'badge-blue'],'in_progress'=>[I18n::t('eq_tickets.em_andamento'),'badge-blue'],'waiting_client'=>[I18n::t('eq_tickets.aguard_cliente'),'badge-yellow'],'closed'=>[I18n::t('eq_tickets.fechado'),'badge-gray']];
    $d = $map[$st] ?? [$st,'badge-gray'];
    return '<span class="badge-new '.$d[1].'">'.View::e($d[0]).'</span>';
}
function badgePrioridadeEquipe(string $p): string {
    if ($p==='high') return '<span class="badge-new badge-red">'.View::e(I18n::t('eq_tickets.alta')).'</span>';
    if ($p==='low')  return '<span class="badge-new badge-green">'.View::e(I18n::t('eq_tickets.baixa')).'</span>';
    return '<span class="badge-new badge-yellow">'.View::e(I18n::t('eq_tickets.media')).'</span>';
}

$filtroStatus = (string)($_GET['status']??'');
$filtroPrio   = (string)($_GET['priority']??'');
$filtroDept   = (string)($_GET['department']??'');
$filtroBusca  = (string)($_GET['q']??'');

$pageTitle = 'Tickets';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('eq_tickets.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_tickets.subtitulo')); ?></div>

<div class="card-new" style="margin-bottom:16px;">
  <form method="get" action="/equipe/tickets">
    <div class="linha" style="gap:8px;flex-wrap:wrap;margin-bottom:0;">
      <input class="input" type="text" name="q" placeholder="<?php echo View::e(I18n::t('eq_tickets.buscar')); ?>" value="<?php echo View::e($filtroBusca); ?>" style="max-width:240px;" />
      <select class="input" name="status" style="max-width:160px;">
        <option value=""><?php echo View::e(I18n::t('eq_tickets.todos_status')); ?></option>
        <option value="open" <?php echo $filtroStatus==='open'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.aberto')); ?></option>
        <option value="in_progress" <?php echo $filtroStatus==='in_progress'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.em_andamento')); ?></option>
        <option value="waiting_client" <?php echo $filtroStatus==='waiting_client'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.aguard_cliente')); ?></option>
        <option value="closed" <?php echo $filtroStatus==='closed'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.fechado')); ?></option>
      </select>
      <select class="input" name="priority" style="max-width:140px;">
        <option value=""><?php echo View::e(I18n::t('eq_tickets.todas_prioridades')); ?></option>
        <option value="high" <?php echo $filtroPrio==='high'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.alta')); ?></option>
        <option value="medium" <?php echo $filtroPrio==='medium'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.media')); ?></option>
        <option value="low" <?php echo $filtroPrio==='low'?'selected':''; ?>><?php echo View::e(I18n::t('eq_tickets.baixa')); ?></option>
      </select>
      <select class="input" name="department" style="max-width:150px;">
        <option value=""><?php echo View::e(I18n::t('eq_tickets.todos_departamentos')); ?></option>
        <option value="suporte" <?php echo $filtroDept==='suporte'?'selected':''; ?>>Suporte</option>
        <option value="financeiro" <?php echo $filtroDept==='financeiro'?'selected':''; ?>>Financeiro</option>
        <option value="devops" <?php echo $filtroDept==='devops'?'selected':''; ?>>DevOps</option>
        <option value="comercial" <?php echo $filtroDept==='comercial'?'selected':''; ?>>Comercial</option>
      </select>
      <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_tickets.filtrar')); ?></button>
      <?php if ($filtroStatus!==''||$filtroPrio!==''||$filtroDept!==''||$filtroBusca!==''): ?>
        <a href="/equipe/tickets" class="botao sec"><?php echo View::e(I18n::t('eq_tickets.limpar')); ?></a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>#</th><th><?php echo View::e(I18n::t('eq_tickets.cliente')); ?></th><th><?php echo View::e(I18n::t('eq_tickets.assunto')); ?></th><th><?php echo View::e(I18n::t('eq_tickets.dept')); ?></th><th><?php echo View::e(I18n::t('eq_tickets.prioridade')); ?></th><th><?php echo View::e(I18n::t('eq_tickets.status')); ?></th><th><?php echo View::e(I18n::t('eq_tickets.atribuido')); ?></th><th><?php echo View::e(I18n::t('eq_tickets.atualizado')); ?></th><th></th></tr>
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
            <td><a href="/equipe/tickets/ver?id=<?php echo (int)($t['id']??0); ?>"><?php echo View::e(I18n::t('eq_tickets.abrir')); ?></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
          <tr><td colspan="9"><?php echo View::e(I18n::t('eq_tickets.nenhum')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
