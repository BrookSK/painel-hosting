<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = (string)($demanda['title'] ?? 'Demanda');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$did = (int)($demanda['id'] ?? 0);
$projetoId = (int)($demanda['project_id'] ?? 0);
$projetoNome = (string)($demanda['project_name'] ?? '');
$status = (string)($demanda['status'] ?? 'open');
$branch = (string)($demanda['branch_name'] ?? '');
$tempDomain = (string)($demanda['temp_domain'] ?? '');
$priority = (string)($demanda['priority'] ?? 'medium');
$csrfToken = Csrf::token();

$statusLabels = [
    'open' => ['Aberta', 'badge-gray'],
    'in_progress' => ['Em progresso', 'badge-blue'],
    'testing' => ['Testando', 'badge-purple'],
    'pr_pending' => ['PR Pendente', 'badge-yellow'],
    'pr_rejected' => ['PR Devolvido', 'badge-red'],
    'merged' => ['Mergeado', 'badge-green'],
    'closed' => ['Fechada', 'badge-gray'],
];
$priorityLabels = [
    'low' => ['Baixa', 'badge-gray'],
    'medium' => ['Média', 'badge-blue'],
    'high' => ['Alta', 'badge-yellow'],
    'urgent' => ['Urgente', 'badge-red'],
];
$sl = $statusLabels[$status] ?? ['?', 'badge-gray'];
$pl = $priorityLabels[$priority] ?? ['?', 'badge-gray'];
?>
<a href="/equipe/dev/demandas?projeto=<?php echo $projetoId; ?>" style="font-size:13px;color:#4F46E5;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:12px;">
  <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Voltar para <?php echo View::e($projetoNome); ?>
</a>

<!-- Header da demanda -->
<div class="card-new" style="padding:24px;margin-bottom:20px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
      <h1 style="margin:0 0 8px;font-size:22px;font-weight:800;color:#0f172a;">#<?php echo $did; ?> — <?php echo View::e((string)($demanda['title'] ?? '')); ?></h1>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <span class="badge-new <?php echo $sl[1]; ?>"><?php echo View::e($sl[0]); ?></span>
        <span class="badge-new <?php echo $pl[1]; ?>" style="font-size:10px;"><?php echo View::e($pl[0]); ?></span>
        <?php if ($branch !== ''): ?>
          <code style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:4px;color:#475569;"><?php echo View::e($branch); ?></code>
        <?php endif; ?>
      </div>
    </div>
    <div style="text-align:right;font-size:12px;color:#64748b;">
      <div>Criado por: <strong><?php echo View::e((string)($demanda['creator_name'] ?? '—')); ?></strong></div>
      <div>Responsável: <strong><?php echo View::e((string)($demanda['assigned_name'] ?? '—')); ?></strong></div>
      <div>Criado em: <?php echo date('d/m/Y H:i', strtotime((string)($demanda['created_at'] ?? ''))); ?></div>
    </div>
  </div>

  <?php if (!empty($demanda['description'])): ?>
  <div style="margin-top:16px;padding:14px;background:#f8fafc;border-radius:8px;font-size:14px;color:#334155;line-height:1.6;">
    <?php echo nl2br(View::e((string)$demanda['description'])); ?>
  </div>
  <?php endif; ?>

  <?php if ($tempDomain !== '' && in_array($status, ['testing', 'in_progress', 'pr_pending', 'pr_rejected'])): ?>
  <div style="margin-top:12px;padding:10px 14px;background:#ecfdf5;border-radius:8px;display:flex;align-items:center;gap:8px;">
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="#10b981" stroke-width="1.6"/><path d="M7 10l2 2 4-4" stroke="#10b981" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <span style="font-size:13px;color:#065f46;">Site de teste: <a href="https://<?php echo View::e($tempDomain); ?>" target="_blank" style="color:#047857;font-weight:600;"><?php echo View::e($tempDomain); ?></a></span>
  </div>
  <?php endif; ?>

  <!-- Motivo da rejeição (se PR rejeitado) -->
  <?php if ($status === 'pr_rejected' && !empty($demanda['pr_rejection_reason'])): ?>
  <div style="margin-top:12px;padding:12px 14px;background:#fef2f2;border-radius:8px;border-left:4px solid #ef4444;">
    <div style="font-size:12px;font-weight:700;color:#991b1b;margin-bottom:4px;">Motivo da devolução:</div>
    <div style="font-size:13px;color:#7f1d1d;"><?php echo nl2br(View::e((string)$demanda['pr_rejection_reason'])); ?></div>
    <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Revisado por: <?php echo View::e((string)($demanda['reviewer_name'] ?? '—')); ?> em <?php echo date('d/m/Y H:i', strtotime((string)($demanda['pr_reviewed_at'] ?? ''))); ?></div>
  </div>
  <?php endif; ?>
</div>

<!-- Barra de ações -->
<div class="card-new" style="padding:16px 24px;margin-bottom:20px;">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <?php if (in_array($status, ['in_progress', 'testing', 'pr_rejected'])): ?>
      <!-- Deploy no teste -->
      <button type="button" id="btnDeploy" class="botao" onclick="deployTeste()">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M10 3v10M6 9l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Implementar no Teste
      </button>
    <?php endif; ?>

    <?php if (in_array($status, ['in_progress', 'testing', 'pr_rejected'])): ?>
      <!-- Criar PR -->
      <button type="button" class="botao botao-secundario" onclick="document.getElementById('modalPR').style.display='flex'">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M6 3v14M14 3v14M6 7h8M6 13h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        Fazer Pull Request
      </button>
    <?php endif; ?>

    <?php if ($status === 'pr_pending'): ?>
      <!-- Aprovar PR -->
      <button type="button" class="botao" style="background:#10b981;" onclick="aprovarPR()">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M5 10l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Aprovar PR
      </button>
      <!-- Rejeitar PR -->
      <button type="button" class="botao botao-danger" onclick="document.getElementById('modalRejeitar').style.display='flex'">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Devolver PR
      </button>
      <!-- Ver Diff -->
      <button type="button" class="botao botao-secundario" onclick="verDiff()">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M4 4h12M4 8h8M4 12h12M4 16h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        Ver Alterações
      </button>
    <?php endif; ?>

    <?php if (!in_array($status, ['merged', 'closed'])): ?>
      <form method="post" action="/equipe/dev/demanda/fechar" style="display:inline;" onsubmit="return confirm('Deseja fechar esta demanda?');">
        <input type="hidden" name="_csrf" value="<?php echo View::e($csrfToken); ?>" />
        <input type="hidden" name="id" value="<?php echo $did; ?>" />
        <button type="submit" class="botao botao-sm" style="background:#64748b;color:#fff;">Fechar</button>
      </form>
    <?php endif; ?>

    <?php if (in_array($status, ['closed', 'pr_rejected'])): ?>
      <form method="post" action="/equipe/dev/demanda/reabrir" style="display:inline;">
        <input type="hidden" name="_csrf" value="<?php echo View::e($csrfToken); ?>" />
        <input type="hidden" name="id" value="<?php echo $did; ?>" />
        <button type="submit" class="botao botao-sm botao-secundario">Reabrir</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<!-- Output de deploy -->
<div id="deployOutput" style="display:none;margin-bottom:20px;" class="card-new">
  <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-weight:600;font-size:13px;">Resultado do Deploy</div>
  <div id="deployOutputContent" style="padding:12px 16px;background:#0f172a;color:#e2e8f0;font-family:monospace;font-size:12px;white-space:pre-wrap;max-height:250px;overflow:auto;border-radius:0 0 12px 12px;"></div>
</div>

<!-- Diff viewer -->
<div id="diffOutput" style="display:none;margin-bottom:20px;" class="card-new">
  <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-weight:600;font-size:13px;display:flex;justify-content:space-between;align-items:center;">
    <span>Alterações (diff)</span>
    <button type="button" onclick="document.getElementById('diffOutput').style.display='none'" style="background:none;border:none;cursor:pointer;color:#64748b;">&times;</button>
  </div>
  <div id="diffOutputContent" style="padding:12px 16px;background:#fafafa;font-family:monospace;font-size:12px;white-space:pre-wrap;max-height:400px;overflow:auto;border-radius:0 0 12px 12px;"></div>
</div>

<!-- Commits da branch -->
<?php if (!empty($commits)): ?>
<div class="card-new" style="margin-bottom:20px;">
  <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-weight:600;font-size:13px;">
    Commits na branch (<?php echo count($commits); ?>)
  </div>
  <div style="padding:8px 16px;max-height:200px;overflow:auto;">
    <?php foreach ($commits as $c): ?>
    <div style="padding:6px 0;border-bottom:1px solid #f1f5f9;display:flex;gap:10px;align-items:flex-start;">
      <code style="font-size:11px;color:#6366f1;white-space:nowrap;"><?php echo View::e(substr((string)($c['hash'] ?? ''), 0, 7)); ?></code>
      <div style="flex:1;min-width:0;">
        <div style="font-size:13px;color:#0f172a;"><?php echo View::e((string)($c['message'] ?? '')); ?></div>
        <div style="font-size:11px;color:#94a3b8;"><?php echo View::e((string)($c['author'] ?? '')); ?> — <?php echo View::e((string)($c['date'] ?? '')); ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Histórico / Comentários -->
<div class="card-new" style="margin-bottom:20px;">
  <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-weight:600;font-size:13px;">
    Histórico e Comentários
  </div>
  <div style="padding:16px;max-height:400px;overflow:auto;" id="comentariosContainer">
    <?php if (empty($comentarios)): ?>
      <p style="color:#94a3b8;font-size:13px;text-align:center;">Nenhum comentário ainda.</p>
    <?php else: ?>
      <?php foreach ($comentarios as $c):
        $tipo = (string)($c['type'] ?? 'comment');
        $isSystem = $tipo !== 'comment';
        $iconColors = [
            'branch_created' => '#6366f1',
            'deploy' => '#10b981',
            'pr_created' => '#f59e0b',
            'pr_approved' => '#10b981',
            'pr_rejected' => '#ef4444',
            'status_change' => '#64748b',
        ];
        $iconColor = $iconColors[$tipo] ?? '#4F46E5';
      ?>
      <div style="display:flex;gap:10px;margin-bottom:14px;<?php echo $isSystem ? 'opacity:.85;' : ''; ?>">
        <div style="width:28px;height:28px;border-radius:50%;background:<?php echo $isSystem ? '#f1f5f9' : '#4F46E5'; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <?php if ($isSystem): ?>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="6" stroke="<?php echo $iconColor; ?>" stroke-width="1.5"/><path d="M10 7v3l2 1" stroke="<?php echo $iconColor; ?>" stroke-width="1.5" stroke-linecap="round"/></svg>
          <?php else: ?>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="7" r="3" stroke="#fff" stroke-width="1.5"/><path d="M5 16c0-2.8 2.2-5 5-5s5 2.2 5 5" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
          <?php endif; ?>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:12px;color:#64748b;">
            <strong style="color:#0f172a;"><?php echo View::e((string)($c['user_name'] ?? 'Sistema')); ?></strong>
            — <?php echo date('d/m/Y H:i', strtotime((string)($c['created_at'] ?? ''))); ?>
          </div>
          <div style="margin-top:4px;font-size:13px;color:#334155;<?php echo $isSystem ? 'font-style:italic;' : ''; ?>">
            <?php echo nl2br(View::e((string)($c['comment'] ?? ''))); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Formulário de comentário -->
  <div style="padding:12px 16px;border-top:1px solid #e2e8f0;">
    <form id="formComentario" onsubmit="enviarComentario(event)" style="display:flex;gap:8px;">
      <input type="text" id="inputComentario" class="form-input" placeholder="Adicionar comentário..." style="flex:1;" required />
      <button type="submit" class="botao botao-sm">Enviar</button>
    </form>
  </div>
</div>

<!-- Deploy Logs -->
<?php if (!empty($deployLogs)): ?>
<div class="card-new" style="margin-bottom:20px;">
  <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-weight:600;font-size:13px;">
    Histórico de Deploys
  </div>
  <div style="overflow:auto;">
    <table style="font-size:13px;">
      <thead>
        <tr><th>Data</th><th>Commit</th><th>Mensagem</th><th>Por</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($deployLogs as $log): ?>
        <tr>
          <td style="white-space:nowrap;"><?php echo date('d/m H:i', strtotime((string)($log['deployed_at'] ?? ''))); ?></td>
          <td><code style="font-size:11px;"><?php echo View::e(substr((string)($log['commit_hash'] ?? ''), 0, 7)); ?></code></td>
          <td><?php echo View::e(mb_substr((string)($log['commit_message'] ?? ''), 0, 50)); ?></td>
          <td><?php echo View::e((string)($log['deployed_by_name'] ?? '—')); ?></td>
          <td>
            <?php if (($log['status'] ?? '') === 'success'): ?>
              <span class="badge-new badge-green" style="font-size:10px;">OK</span>
            <?php else: ?>
              <span class="badge-new badge-red" style="font-size:10px;">Erro</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Modal: Criar PR -->
<div id="modalPR" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
    <h3 style="margin:0 0 16px;font-size:18px;font-weight:700;">Criar Pull Request</h3>
    <p style="font-size:13px;color:#64748b;margin:0 0 16px;">O PR será enviado para aprovação do admin. Após aprovado, será feito o merge na branch principal.</p>
    <div class="form-group" style="margin-bottom:12px;">
      <label class="form-label">Título do PR</label>
      <input type="text" id="prTitle" class="form-input" value="<?php echo View::e((string)($demanda['title'] ?? '')); ?>" />
    </div>
    <div class="form-group" style="margin-bottom:16px;">
      <label class="form-label">Descrição (opcional)</label>
      <textarea id="prDescription" class="form-input" rows="3" placeholder="Descreva o que foi implementado..."><?php echo View::e((string)($demanda['description'] ?? '')); ?></textarea>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <button type="button" class="botao botao-secundario" onclick="document.getElementById('modalPR').style.display='none'">Cancelar</button>
      <button type="button" class="botao" onclick="enviarPR()">Criar PR</button>
    </div>
  </div>
</div>

<!-- Modal: Rejeitar PR -->
<div id="modalRejeitar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
    <h3 style="margin:0 0 16px;font-size:18px;font-weight:700;color:#dc2626;">Devolver Pull Request</h3>
    <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Informe o motivo da devolução. O desenvolvedor receberá uma notificação.</p>
    <div class="form-group" style="margin-bottom:16px;">
      <label class="form-label">Motivo *</label>
      <textarea id="rejectReason" class="form-input" rows="4" placeholder="Ex: O código no arquivo X pode causar conflito com..." required></textarea>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <button type="button" class="botao botao-secundario" onclick="document.getElementById('modalRejeitar').style.display='none'">Cancelar</button>
      <button type="button" class="botao botao-danger" onclick="rejeitarPR()">Devolver</button>
    </div>
  </div>
</div>

<script>
var csrf = '<?php echo View::e($csrfToken); ?>';
var demandId = <?php echo $did; ?>;

function deployTeste() {
  var btn = document.getElementById('btnDeploy');
  var out = document.getElementById('deployOutput');
  var content = document.getElementById('deployOutputContent');
  btn.disabled = true; btn.textContent = 'Deployando...';
  out.style.display = 'block'; content.textContent = 'Executando deploy...';
  fetch('/equipe/dev/demanda/deploy', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': csrf},
    body: 'id=' + demandId + '&_csrf=' + encodeURIComponent(csrf)
  }).then(r => r.json()).then(d => {
    btn.disabled = false; btn.textContent = 'Implementar no Teste';
    if (d.ok) {
      content.textContent = 'Deploy concluído!\nCommit: ' + (d.commit || '?') + '\n\n' + (d.output || '');
      setTimeout(() => location.reload(), 2000);
    } else {
      content.textContent = 'ERRO: ' + (d.erro || 'Desconhecido');
    }
  }).catch(() => { btn.disabled = false; btn.textContent = 'Implementar no Teste'; content.textContent = 'Erro de conexão.'; });
}

function enviarPR() {
  var title = document.getElementById('prTitle').value;
  var desc = document.getElementById('prDescription').value;
  fetch('/equipe/dev/demanda/pr', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': csrf},
    body: 'id=' + demandId + '&pr_title=' + encodeURIComponent(title) + '&pr_description=' + encodeURIComponent(desc) + '&_csrf=' + encodeURIComponent(csrf)
  }).then(r => r.json()).then(d => {
    if (d.ok) { location.reload(); } else { alert('Erro: ' + (d.erro || 'Desconhecido')); }
  }).catch(() => alert('Erro de conexão.'));
}

function aprovarPR() {
  if (!confirm('Confirma a aprovação do PR? O merge será realizado na branch principal.')) return;
  fetch('/equipe/dev/demanda/aprovar', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': csrf},
    body: 'id=' + demandId + '&_csrf=' + encodeURIComponent(csrf)
  }).then(r => r.json()).then(d => {
    if (d.ok) { alert('PR aprovado! Merge concluído.'); location.reload(); } else { alert('Erro: ' + (d.erro || 'Desconhecido')); }
  }).catch(() => alert('Erro de conexão.'));
}

function rejeitarPR() {
  var motivo = document.getElementById('rejectReason').value.trim();
  if (!motivo) { alert('Informe o motivo.'); return; }
  fetch('/equipe/dev/demanda/rejeitar', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': csrf},
    body: 'id=' + demandId + '&motivo=' + encodeURIComponent(motivo) + '&_csrf=' + encodeURIComponent(csrf)
  }).then(r => r.json()).then(d => {
    if (d.ok) { location.reload(); } else { alert('Erro: ' + (d.erro || 'Desconhecido')); }
  }).catch(() => alert('Erro de conexão.'));
}

function verDiff() {
  var out = document.getElementById('diffOutput');
  var content = document.getElementById('diffOutputContent');
  out.style.display = 'block'; content.textContent = 'Carregando diff...';
  fetch('/equipe/dev/demanda/diff?id=' + demandId)
    .then(r => r.json()).then(d => {
      if (d.ok) {
        content.textContent = d.diff || '(nenhuma alteração)';
      } else {
        content.textContent = 'Erro: ' + (d.erro || 'Desconhecido');
      }
    }).catch(() => { content.textContent = 'Erro de conexão.'; });
}

function enviarComentario(e) {
  e.preventDefault();
  var input = document.getElementById('inputComentario');
  var comment = input.value.trim();
  if (!comment) return;
  fetch('/equipe/dev/demanda/comentar', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': csrf},
    body: 'demand_id=' + demandId + '&comment=' + encodeURIComponent(comment) + '&_csrf=' + encodeURIComponent(csrf)
  }).then(r => r.json()).then(d => {
    if (d.ok) { input.value = ''; location.reload(); } else { alert('Erro: ' + (d.erro || '')); }
  }).catch(() => alert('Erro de conexão.'));
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
