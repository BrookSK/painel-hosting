<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$flowId = (int)($flow['id'] ?? 0);
$isNew = $flowId === 0;
$pageTitle = $isNew ? I18n::t('chat_flows.novo_fluxo') : I18n::t('chat_flows.editar_fluxo');
$stepErro = (string)($_GET['step_erro'] ?? '');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e($pageTitle); ?></div>
<div class="page-subtitle"><a href="/equipe/chat-flows">&larr; <?php echo View::e(I18n::t('chat_flows.titulo')); ?></a></div>

<?php if (!empty($erro)): ?>
  <div class="alerta alerta-erro"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if (!empty($sucesso)): ?>
  <div class="alerta alerta-sucesso"><?php echo View::e($sucesso); ?></div>
<?php endif; ?>
<?php if ($stepErro !== ''): ?>
  <div class="alerta alerta-erro"><?php echo View::e($stepErro); ?></div>
<?php endif; ?>

<div class="card-new" style="margin-bottom:20px;">
  <h3 style="margin:0 0 14px;"><?php echo View::e(I18n::t('chat_flows.dados_fluxo')); ?></h3>
  <form method="post" action="/equipe/chat-flows/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo $flowId; ?>" />

    <div class="campo">
      <label class="label"><?php echo View::e(I18n::t('chat_flows.nome')); ?> *</label>
      <input class="input" type="text" name="name" value="<?php echo View::e((string)($flow['name'] ?? '')); ?>" maxlength="100" required />
    </div>
    <div class="campo">
      <label class="label"><?php echo View::e(I18n::t('chat_flows.descricao')); ?></label>
      <input class="input" type="text" name="description" value="<?php echo View::e((string)($flow['description'] ?? '')); ?>" maxlength="500" />
    </div>
    <div class="campo">
      <label class="label"><?php echo View::e(I18n::t('chat_flows.gatilho')); ?> *</label>
      <select class="input" name="trigger_type" id="triggerType" required>
        <option value="client_inactive" <?php echo ($flow['trigger_type'] ?? '') === 'client_inactive' ? 'selected' : ''; ?>><?php echo View::e(I18n::t('chat_flows.trigger_inatividade')); ?></option>
        <option value="chat_closed" <?php echo ($flow['trigger_type'] ?? '') === 'chat_closed' ? 'selected' : ''; ?>><?php echo View::e(I18n::t('chat_flows.trigger_encerramento')); ?></option>
        <option value="manual" <?php echo ($flow['trigger_type'] ?? '') === 'manual' ? 'selected' : ''; ?>><?php echo View::e(I18n::t('chat_flows.trigger_manual')); ?></option>
      </select>
    </div>
    <div class="campo" id="timeoutField" style="<?php echo ($flow['trigger_type'] ?? '') === 'client_inactive' ? '' : 'display:none;'; ?>">
      <label class="label"><?php echo View::e(I18n::t('chat_flows.timeout_inatividade')); ?> (<?php echo View::e(I18n::t('chat_flows.minutos')); ?>)</label>
      <input class="input" type="number" name="inactivity_minutes" value="<?php echo (int)($timeout ?? 10); ?>" min="1" max="120" style="max-width:120px;" />
    </div>
    <div class="campo">
      <label class="label"><?php echo View::e(I18n::t('geral.status')); ?></label>
      <select class="input" name="active" style="max-width:140px;">
        <option value="1" <?php echo (int)($flow['active'] ?? 1) === 1 ? 'selected' : ''; ?>><?php echo View::e(I18n::t('geral.ativo')); ?></option>
        <option value="0" <?php echo (int)($flow['active'] ?? 1) === 0 ? 'selected' : ''; ?>><?php echo View::e(I18n::t('geral.inativo')); ?></option>
      </select>
    </div>
    <button class="botao" type="submit"><?php echo View::e(I18n::t('geral.salvar')); ?></button>
  </form>
</div>

<?php if (!$isNew): ?>
<div class="card-new" style="margin-bottom:20px;">
  <h3 style="margin:0 0 14px;"><?php echo View::e(I18n::t('chat_flows.passos')); ?></h3>

  <?php if (!empty($steps)): ?>
  <div style="overflow:auto;margin-bottom:14px;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo View::e(I18n::t('chat_flows.tipo')); ?></th>
          <th><?php echo View::e(I18n::t('chat_flows.conteudo')); ?></th>
          <th><?php echo View::e(I18n::t('geral.acoes')); ?></th>
        </tr>
      </thead>
      <tbody id="stepsBody">
        <?php foreach ($steps as $s): ?>
        <tr data-id="<?php echo (int)$s['id']; ?>">
          <td><?php echo (int)$s['sort_order']; ?></td>
          <td>
            <?php
              $typeLabel = match((string)$s['step_type']) {
                'message' => I18n::t('chat_flows.tipo_mensagem'),
                'delay'   => I18n::t('chat_flows.tipo_atraso'),
                'action'  => I18n::t('chat_flows.tipo_acao'),
                default   => $s['step_type'],
              };
              echo View::e($typeLabel);
            ?>
          </td>
          <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            <?php if ($s['step_type'] === 'message'): ?>
              <?php echo View::e((string)($s['content'] ?? '')); ?>
            <?php elseif ($s['step_type'] === 'delay'): ?>
              <?php echo (int)($s['delay_seconds'] ?? 0); ?>s
            <?php elseif ($s['step_type'] === 'action'): ?>
              <?php echo View::e((string)($s['action_type'] ?? '')); ?>
            <?php endif; ?>
          </td>
          <td>
            <form method="post" action="/equipe/chat-flows/passo/remover" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
              <input type="hidden" name="flow_id" value="<?php echo $flowId; ?>" />
              <input type="hidden" name="step_id" value="<?php echo (int)$s['id']; ?>" />
              <button type="submit" class="botao danger sm" style="font-size:12px;" onclick="return confirm('<?php echo View::e(I18n::t('chat_flows.confirmar_remover_passo')); ?>')"><?php echo View::e(I18n::t('geral.excluir')); ?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <p style="color:#94a3b8;font-size:13px;margin-bottom:14px;"><?php echo View::e(I18n::t('chat_flows.nenhum_passo')); ?></p>
  <?php endif; ?>

  <h4 style="margin:0 0 10px;"><?php echo View::e(I18n::t('chat_flows.adicionar_passo')); ?></h4>
  <form method="post" action="/equipe/chat-flows/passo/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
    <input type="hidden" name="flow_id" value="<?php echo $flowId; ?>" />
    <input type="hidden" name="step_id" value="0" />
    <div class="linha" style="gap:8px;flex-wrap:wrap;align-items:flex-end;">
      <div class="campo" style="margin-bottom:0;">
        <label class="label"><?php echo View::e(I18n::t('chat_flows.tipo')); ?></label>
        <select class="input" name="step_type" id="newStepType" style="max-width:140px;">
          <option value="message"><?php echo View::e(I18n::t('chat_flows.tipo_mensagem')); ?></option>
          <option value="delay"><?php echo View::e(I18n::t('chat_flows.tipo_atraso')); ?></option>
          <option value="action"><?php echo View::e(I18n::t('chat_flows.tipo_acao')); ?></option>
        </select>
      </div>
      <div class="campo step-field step-message" style="margin-bottom:0;">
        <label class="label"><?php echo View::e(I18n::t('chat_flows.conteudo')); ?></label>
        <input class="input" type="text" name="content" maxlength="1000" style="min-width:240px;" />
      </div>
      <div class="campo step-field step-delay" style="margin-bottom:0;display:none;">
        <label class="label"><?php echo View::e(I18n::t('chat_flows.segundos')); ?></label>
        <input class="input" type="number" name="delay_seconds" min="1" max="3600" style="max-width:120px;" />
      </div>
      <div class="campo step-field step-action" style="margin-bottom:0;display:none;">
        <label class="label"><?php echo View::e(I18n::t('chat_flows.tipo_acao')); ?></label>
        <select class="input" name="action_type" style="max-width:200px;">
          <option value="close_chat"><?php echo View::e(I18n::t('chat_flows.acao_fechar_chat')); ?></option>
          <option value="send_satisfaction_link"><?php echo View::e(I18n::t('chat_flows.acao_enviar_satisfacao')); ?></option>
        </select>
      </div>
      <button class="botao" type="submit"><?php echo View::e(I18n::t('chat_flows.adicionar')); ?></button>
    </div>
  </form>
</div>

<!-- Execution history -->
<div class="card-new">
  <h3 style="margin:0 0 14px;"><?php echo View::e(I18n::t('chat_flows.historico_execucoes')); ?></h3>
  <?php if (!empty($executions)): ?>
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th><?php echo View::e(I18n::t('chat_flows.sala')); ?></th>
          <th><?php echo View::e(I18n::t('chat_flows.origem')); ?></th>
          <th><?php echo View::e(I18n::t('geral.status')); ?></th>
          <th><?php echo View::e(I18n::t('chat_flows.iniciado_em')); ?></th>
          <th><?php echo View::e(I18n::t('chat_flows.concluido_em')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($executions as $e): ?>
        <tr>
          <td>#<?php echo (int)$e['id']; ?></td>
          <td><a href="/equipe/chat/ver?id=<?php echo (int)$e['room_id']; ?>">#<?php echo (int)$e['room_id']; ?></a></td>
          <td><?php echo View::e((string)$e['trigger_source']); ?></td>
          <td>
            <?php
              $stClass = match((string)$e['status']) {
                'completed' => 'badge-green',
                'failed'    => 'badge-red',
                default     => 'badge-yellow',
              };
            ?>
            <span class="badge-new <?php echo $stClass; ?>"><?php echo View::e((string)$e['status']); ?></span>
          </td>
          <td style="font-size:12px;"><?php echo View::e((string)($e['started_at'] ?? '')); ?></td>
          <td style="font-size:12px;"><?php echo View::e((string)($e['completed_at'] ?? '—')); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <p style="color:#94a3b8;font-size:13px;"><?php echo View::e(I18n::t('chat_flows.nenhuma_execucao')); ?></p>
  <?php endif; ?>
</div>
<?php endif; ?>

<script>
(function(){
  var sel = document.getElementById('newStepType');
  if (sel) {
    sel.addEventListener('change', function(){
      document.querySelectorAll('.step-field').forEach(function(el){ el.style.display='none'; });
      var cls = 'step-' + sel.value;
      document.querySelectorAll('.' + cls).forEach(function(el){ el.style.display=''; });
    });
  }
  var tt = document.getElementById('triggerType');
  if (tt) {
    tt.addEventListener('change', function(){
      var tf = document.getElementById('timeoutField');
      if (tf) tf.style.display = tt.value === 'client_inactive' ? '' : 'none';
    });
  }
})();
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
