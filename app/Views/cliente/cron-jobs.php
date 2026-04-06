<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'Tarefas Agendadas (Cron)';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Tarefas Agendadas (Cron)</div>
    <div class="page-subtitle" style="margin-bottom:0;">Agende comandos, URLs ou scripts para executar automaticamente</div>
  </div>
  <button class="botao" onclick="document.getElementById('cronForm').style.display=document.getElementById('cronForm').style.display==='none'?'block':'none'">+ Nova tarefa</button>
</div>

<!-- Formulário de criação/edição -->
<div id="cronForm" class="card-new" style="display:none;margin-bottom:20px;max-width:700px;">
  <div style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:14px;">Agendar tarefa</div>
  <form method="post" action="/cliente/cron-jobs/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
    <input type="hidden" name="id" id="cron-edit-id" value="0" />

    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:4px;">Nome</label>
        <input class="input" type="text" name="name" id="cron-name" placeholder="Backup diário" required />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:4px;">VPS</label>
        <select class="input" name="vps_id" id="cron-vps" required>
          <?php foreach (($vpsList ?? []) as $v): ?>
            <option value="<?php echo (int)$v['id']; ?>">VPS #<?php echo (int)$v['id']; ?> — <?php echo (int)$v['cpu']; ?>vCPU / <?php echo round((int)$v['ram']/1024); ?>GB</option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:4px;">Tipo de tarefa</label>
      <div style="display:flex;gap:12px;">
        <label style="display:flex;align-items:center;gap:4px;font-size:13px;cursor:pointer;">
          <input type="radio" name="task_type" value="command" checked onchange="atualizarPlaceholder()" /> Comando
        </label>
        <label style="display:flex;align-items:center;gap:4px;font-size:13px;cursor:pointer;">
          <input type="radio" name="task_type" value="url" onchange="atualizarPlaceholder()" /> URL
        </label>
        <label style="display:flex;align-items:center;gap:4px;font-size:13px;cursor:pointer;">
          <input type="radio" name="task_type" value="php_script" onchange="atualizarPlaceholder()" /> Script PHP
        </label>
      </div>
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:4px;">Comando / URL / Caminho do script</label>
      <textarea class="input" name="command" id="cron-command" rows="2" placeholder="cd /var/www/app && php artisan schedule:run" required style="font-family:monospace;font-size:12px;resize:vertical;"></textarea>
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:4px;">Executar</label>
      <div class="grid" style="gap:8px;">
        <select class="input" name="schedule_type" id="cron-schedule-type" onchange="toggleScheduleFields()" style="font-size:13px;">
          <option value="every_minute">A cada minuto</option>
          <option value="every_5min">A cada 5 minutos</option>
          <option value="every_15min">A cada 15 minutos</option>
          <option value="every_30min">A cada 30 minutos</option>
          <option value="hourly" selected>A cada hora</option>
          <option value="daily">Diário</option>
          <option value="weekly">Semanal (domingo)</option>
          <option value="monthly">Mensal (dia 1)</option>
          <option value="custom">Personalizado (cron)</option>
        </select>
        <div id="scheduleTimeFields" style="display:none;display:flex;gap:6px;align-items:center;">
          <span style="font-size:12px;color:#64748b;">às</span>
          <input class="input" type="number" name="hour" id="cron-hour" value="6" min="0" max="23" style="width:60px;font-size:13px;" />
          <span style="font-size:12px;color:#64748b;">:</span>
          <input class="input" type="number" name="minute" id="cron-minute" value="0" min="0" max="59" style="width:60px;font-size:13px;" />
        </div>
        <div id="scheduleCustomField" style="display:none;">
          <input class="input" type="text" name="schedule" id="cron-schedule" value="0 * * * *" placeholder="* * * * *" style="font-family:monospace;font-size:13px;" />
          <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Formato: minuto hora dia mês dia_semana. Ex: <code>0 3 * * *</code> = todo dia às 3h</p>
        </div>
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:4px;">Descrição <span style="color:#94a3b8;font-weight:400;">(opcional)</span></label>
      <input class="input" type="text" name="description" id="cron-desc" placeholder="Backup automático do banco de dados" />
    </div>

    <div style="display:flex;gap:8px;">
      <button class="botao" type="submit">Salvar tarefa</button>
      <button class="botao ghost" type="button" onclick="document.getElementById('cronForm').style.display='none';resetForm();">Cancelar</button>
    </div>
  </form>
</div>

<!-- Lista de cron jobs -->
<?php if (empty($crons)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:40px;margin-bottom:12px;">⏰</div>
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhuma tarefa agendada</div>
  <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Crie tarefas cron para executar comandos, acessar URLs ou rodar scripts automaticamente.</div>
  <button class="botao" onclick="document.getElementById('cronForm').style.display='block'">Criar tarefa</button>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px;">
  <?php foreach ($crons as $c):
    $cid = (int)($c['id'] ?? 0);
    $enabled = (int)($c['enabled'] ?? 1);
    $lastStatus = (string)($c['last_status'] ?? '');
    $statusColor = match($lastStatus) { 'success' => '#10b981', 'error' => '#ef4444', 'running' => '#f59e0b', default => '#94a3b8' };
    $statusLabel = match($lastStatus) { 'success' => '✓ OK', 'error' => '✘ Erro', 'running' => '⏳ Rodando', default => '—' };
    $typeIcon = match((string)($c['task_type'] ?? '')) { 'url' => '🌐', 'php_script' => '🐘', default => '💻' };
    $typeLabel = match((string)($c['task_type'] ?? '')) { 'url' => 'URL', 'php_script' => 'PHP', default => 'Comando' };
  ?>
  <div class="card-new" style="<?php echo !$enabled ? 'opacity:0.6;' : ''; ?>">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:8px;">
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;">
          <?php echo $typeIcon; ?> <?php echo View::e((string)($c['name'] ?? '')); ?>
          <span style="font-size:11px;font-weight:500;color:#64748b;"><?php echo $typeLabel; ?></span>
        </div>
        <div style="font-size:12px;color:#64748b;font-family:monospace;margin-top:2px;">
          <span style="color:#4F46E5;"><?php echo View::e((string)($c['schedule'] ?? '')); ?></span>
          · VPS #<?php echo (int)($c['vps_id'] ?? 0); ?>
          <?php if (!empty($c['description'])): ?> · <?php echo View::e((string)$c['description']); ?><?php endif; ?>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:11px;color:<?php echo $statusColor; ?>;font-weight:600;"><?php echo $statusLabel; ?></span>
        <?php if (!$enabled): ?><span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#f1f5f9;color:#64748b;">Pausado</span><?php endif; ?>
      </div>
    </div>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;margin-bottom:10px;font-family:monospace;font-size:12px;color:#1e293b;word-break:break-all;">
      <?php echo View::e((string)($c['command'] ?? '')); ?>
    </div>

    <?php if (!empty($c['last_run_at'])): ?>
    <div style="font-size:11px;color:#94a3b8;margin-bottom:8px;">Última execução: <?php echo View::e((string)$c['last_run_at']); ?></div>
    <?php endif; ?>

    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <button class="botao sm" onclick="executarCron(<?php echo $cid; ?>)" id="btn-run-<?php echo $cid; ?>">▶ Executar agora</button>
      <button class="botao sm ghost" onclick="toggleCron(<?php echo $cid; ?>)" id="btn-toggle-<?php echo $cid; ?>"><?php echo $enabled ? '⏸ Pausar' : '▶ Ativar'; ?></button>
      <button class="botao sm ghost" onclick="verOutput(<?php echo $cid; ?>)">📋 Último log</button>
      <form method="post" action="/cliente/cron-jobs/excluir" style="display:inline;" onsubmit="return confirm('Remover esta tarefa?')">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo $cid; ?>" />
        <button class="botao danger sm" type="submit">🗑</button>
      </form>
      <span id="cron-status-<?php echo $cid; ?>" style="font-size:12px;color:#64748b;"></span>
    </div>

    <!-- Output panel -->
    <div id="cron-output-<?php echo $cid; ?>" style="display:none;margin-top:10px;background:#0b1020;border-radius:8px;padding:10px;font-family:monospace;font-size:11px;">
      <pre style="color:#e2e8f0;white-space:pre-wrap;max-height:250px;overflow-y:auto;margin:0;"><?php echo View::e((string)($c['last_output'] ?? '(sem output)')); ?></pre>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
var _csrf = '<?php echo View::e(Csrf::token()); ?>';

function toggleScheduleFields() {
  var type = document.getElementById('cron-schedule-type').value;
  var timeFields = document.getElementById('scheduleTimeFields');
  var customField = document.getElementById('scheduleCustomField');
  timeFields.style.display = ['daily','weekly','monthly'].includes(type) ? 'flex' : 'none';
  customField.style.display = type === 'custom' ? 'block' : 'none';
}

function atualizarPlaceholder() {
  var type = document.querySelector('input[name="task_type"]:checked').value;
  var cmd = document.getElementById('cron-command');
  if (type === 'url') cmd.placeholder = 'https://meusite.com/cron-run?token=abc123';
  else if (type === 'php_script') cmd.placeholder = '/var/www/app/artisan schedule:run';
  else cmd.placeholder = 'cd /var/www/app && php artisan schedule:run';
}

function resetForm() {
  document.getElementById('cron-edit-id').value = '0';
  document.getElementById('cron-name').value = '';
  document.getElementById('cron-command').value = '';
  document.getElementById('cron-desc').value = '';
}

function executarCron(id) {
  var btn = document.getElementById('btn-run-' + id);
  var st = document.getElementById('cron-status-' + id);
  btn.disabled = true; btn.textContent = '⏳ Executando...';
  st.textContent = '';

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);

  fetch('/cliente/cron-jobs/executar', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.disabled = false; btn.textContent = '▶ Executar agora';
      if (d.ok) {
        st.textContent = '✓ Executado'; st.style.color = '#10b981';
        var out = document.getElementById('cron-output-' + id);
        out.style.display = 'block';
        out.querySelector('pre').textContent = d.output || '(sem output)';
      } else {
        st.textContent = '✘ ' + (d.erro || 'Erro'); st.style.color = '#ef4444';
      }
    })
    .catch(function() {
      btn.disabled = false; btn.textContent = '▶ Executar agora';
      st.textContent = '✘ Erro de rede'; st.style.color = '#ef4444';
    });
}

function toggleCron(id) {
  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);

  fetch('/cliente/cron-jobs/toggle', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) location.reload();
    });
}

function verOutput(id) {
  var el = document.getElementById('cron-output-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

toggleScheduleFields();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
