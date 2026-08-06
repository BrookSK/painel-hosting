<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = I18n::t('dev_workflow.demandas') . ' — ' . View::e((string)($projeto['name'] ?? ''));
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$projetoId = (int)($projeto['id'] ?? 0);
$tempDomain = (string)($projeto['temp_domain'] ?? '');

function badgeStatus(string $s): string {
    $map = [
        'open'        => ['badge-gray',   'Aberta'],
        'in_progress' => ['badge-blue',   'Em progresso'],
        'testing'     => ['badge-purple', 'Testando'],
        'pr_pending'  => ['badge-yellow', 'PR Pendente'],
        'pr_rejected' => ['badge-red',    'PR Devolvido'],
        'merged'      => ['badge-green',  'Mergeado'],
        'closed'      => ['badge-gray',   'Fechada'],
    ];
    $d = $map[$s] ?? ['badge-gray', $s];
    return '<span class="badge-new ' . $d[0] . '">' . htmlspecialchars($d[1], ENT_QUOTES, 'UTF-8') . '</span>';
}

function badgePriority(string $p): string {
    $map = [
        'low'    => ['badge-gray',   'Baixa'],
        'medium' => ['badge-blue',   'Média'],
        'high'   => ['badge-yellow', 'Alta'],
        'urgent' => ['badge-red',    'Urgente'],
    ];
    $d = $map[$p] ?? ['badge-gray', $p];
    return '<span class="badge-new ' . $d[0] . '" style="font-size:10px;">' . htmlspecialchars($d[1], ENT_QUOTES, 'UTF-8') . '</span>';
}
?>
<div class="page-title"><?php echo View::e(I18n::t('dev_workflow.demandas')); ?></div>
<div class="page-subtitle">
  Projeto: <strong><?php echo View::e((string)($projeto['name'] ?? '')); ?></strong>
  <?php if ($tempDomain !== ''): ?>
    — <a href="https://<?php echo View::e($tempDomain); ?>" target="_blank" style="color:#4F46E5;"><?php echo View::e($tempDomain); ?></a>
  <?php endif; ?>
</div>

<a href="/equipe/dev" style="font-size:13px;color:#4F46E5;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:16px;">
  <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
  <?php echo View::e(I18n::t('dev_workflow.voltar_projetos')); ?>
</a>

<!-- Estatísticas -->
<?php $st = $stats ?? []; ?>
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
  <div class="card-new" style="padding:12px 18px;min-width:100px;text-align:center;">
    <div style="font-size:22px;font-weight:800;color:#0f172a;"><?php echo (int)($st['total'] ?? 0); ?></div>
    <div style="font-size:11px;color:#64748b;">Total</div>
  </div>
  <div class="card-new" style="padding:12px 18px;min-width:100px;text-align:center;">
    <div style="font-size:22px;font-weight:800;color:#3b82f6;"><?php echo (int)($st['in_progress'] ?? 0) + (int)($st['open'] ?? 0); ?></div>
    <div style="font-size:11px;color:#64748b;">Em andamento</div>
  </div>
  <div class="card-new" style="padding:12px 18px;min-width:100px;text-align:center;">
    <div style="font-size:22px;font-weight:800;color:#8b5cf6;"><?php echo (int)($st['testing'] ?? 0); ?></div>
    <div style="font-size:11px;color:#64748b;">Testando</div>
  </div>
  <div class="card-new" style="padding:12px 18px;min-width:100px;text-align:center;">
    <div style="font-size:22px;font-weight:800;color:#f59e0b;"><?php echo (int)($st['pr_pending'] ?? 0); ?></div>
    <div style="font-size:11px;color:#64748b;">PR Pendente</div>
  </div>
  <div class="card-new" style="padding:12px 18px;min-width:100px;text-align:center;">
    <div style="font-size:22px;font-weight:800;color:#10b981;"><?php echo (int)($st['merged'] ?? 0); ?></div>
    <div style="font-size:11px;color:#64748b;">Concluídas</div>
  </div>
</div>

<!-- Botão nova demanda -->
<div style="margin-bottom:16px;">
  <button type="button" class="botao" onclick="document.getElementById('modalNovaDemanda').style.display='flex'">
    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    <?php echo View::e(I18n::t('dev_workflow.nova_demanda')); ?>
  </button>
</div>

<!-- Tabela de demandas -->
<?php if (empty($demandas)): ?>
<div class="card-new" style="padding:40px;text-align:center;">
  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 12px;display:block;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
  <p style="color:#64748b;margin:0;">Nenhuma demanda criada ainda para este projeto.</p>
</div>
<?php else: ?>
<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo View::e(I18n::t('dev_workflow.titulo_demanda')); ?></th>
          <th>Branch</th>
          <th>Prioridade</th>
          <th>Responsável</th>
          <th>Status</th>
          <th>Atualizado</th>
          <th><?php echo View::e(I18n::t('geral.acoes')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($demandas as $d):
          $did = (int)($d['id'] ?? 0);
          $titulo = (string)($d['title'] ?? '');
          $branch = (string)($d['branch_name'] ?? '');
          $priority = (string)($d['priority'] ?? 'medium');
          $status = (string)($d['status'] ?? 'open');
          $assigned = (string)($d['assigned_name'] ?? '—');
          $updatedAt = (string)($d['updated_at'] ?? '');
        ?>
          <tr>
            <td><span style="color:#94a3b8;">#<?php echo $did; ?></span></td>
            <td>
              <a href="/equipe/dev/demanda?id=<?php echo $did; ?>" style="font-weight:600;color:#0f172a;">
                <?php echo View::e($titulo); ?>
              </a>
              <?php if (!empty($d['description'])): ?>
                <br><span style="font-size:11px;color:#64748b;"><?php echo View::e(mb_substr((string)$d['description'], 0, 60)); ?>...</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($branch !== ''): ?>
                <code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;"><?php echo View::e($branch); ?></code>
              <?php else: ?>
                <span style="color:#94a3b8;">—</span>
              <?php endif; ?>
            </td>
            <td><?php echo badgePriority($priority); ?></td>
            <td><span style="font-size:13px;"><?php echo View::e($assigned); ?></span></td>
            <td><?php echo badgeStatus($status); ?></td>
            <td><span style="font-size:12px;color:#64748b;"><?php echo $updatedAt !== '' ? date('d/m H:i', strtotime($updatedAt)) : '—'; ?></span></td>
            <td>
              <a href="/equipe/dev/demanda?id=<?php echo $did; ?>" class="botao botao-sm">Ver</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Modal: Nova Demanda -->
<div id="modalNovaDemanda" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h3 style="margin:0;font-size:18px;font-weight:700;"><?php echo View::e(I18n::t('dev_workflow.nova_demanda')); ?></h3>
      <button type="button" onclick="document.getElementById('modalNovaDemanda').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:20px;color:#64748b;">&times;</button>
    </div>
    <form method="post" action="/equipe/dev/demanda/criar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
      <input type="hidden" name="project_id" value="<?php echo $projetoId; ?>" />

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label">Título da demanda *</label>
        <input type="text" name="title" class="form-input" required maxlength="255" placeholder="Ex: Criar página de contato" />
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label">Descrição</label>
        <textarea name="description" class="form-input" rows="4" placeholder="Descreva o que precisa ser feito..."></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div class="form-group">
          <label class="form-label">Prioridade</label>
          <select name="priority" class="form-input">
            <option value="low">Baixa</option>
            <option value="medium" selected>Média</option>
            <option value="high">Alta</option>
            <option value="urgent">Urgente</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Responsável</label>
          <select name="assigned_to" class="form-input">
            <option value="">— Eu mesmo —</option>
            <?php foreach (($membros ?? []) as $m): ?>
              <option value="<?php echo (int)$m['id']; ?>"><?php echo View::e((string)($m['name'] ?? $m['email'] ?? '')); ?> (<?php echo View::e((string)($m['role'] ?? '')); ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="botao botao-secundario" onclick="document.getElementById('modalNovaDemanda').style.display='none'">Cancelar</button>
        <button type="submit" class="botao">Criar Demanda</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
