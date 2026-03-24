<?php
declare(strict_types=1);
use LRV\Core\View;

$id = $plano['id']??null;
$pageTitle = $id?'Editar plano':'Novo plano';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo $id?'Editar plano':'Novo plano'; ?></div>
<div class="page-subtitle">Recursos e precificacao</div>

<div class="card-new" style="max-width:920px;">
  <?php if (!empty($erro)): ?><div class="erro"><?php echo View::e((string)$erro); ?></div><?php endif; ?>

  <form method="post" action="/equipe/planos/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo View::e((string)($plano['id']??'')); ?>" />

    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Nome</label>
        <input class="input" type="text" name="name" value="<?php echo View::e((string)($plano['name']??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Status</label>
        <select class="input" name="status">
          <option value="active" <?php echo ($plano['status']??'')==='active'?'selected':''; ?>>Ativo</option>
          <option value="inactive" <?php echo ($plano['status']??'')==='inactive'?'selected':''; ?>>Inativo</option>
        </select>
      </div>
    </div>

    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Descricao</label>
      <input class="input" type="text" name="description" value="<?php echo View::e((string)($plano['description']??'')); ?>" />
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">vCPU</label>
        <input class="input" type="number" name="cpu" value="<?php echo View::e((string)($plano['cpu']??'')); ?>" min="1" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Memoria (MB)</label>
        <input class="input" type="number" name="ram" value="<?php echo View::e((string)($plano['ram']??'')); ?>" min="256" />
        <p class="texto" style="font-size:13px;margin-top:4px;">Ex: 4096 = 4 GB</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Armazenamento (MB)</label>
        <input class="input" type="number" name="storage" value="<?php echo View::e((string)($plano['storage']??'')); ?>" min="1024" />
        <p class="texto" style="font-size:13px;margin-top:4px;">Ex: 81920 = 80 GB</p>
      </div>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Preco mensal (R$)</label>
        <input class="input" type="text" name="price_monthly" value="<?php echo View::e((string)($plano['price_monthly']??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Stripe Price ID (opcional)</label>
        <input class="input" type="text" name="stripe_price_id" value="<?php echo View::e((string)($plano['stripe_price_id']??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_planos.backup_slots')); ?></label>
        <select class="input" name="backup_slots">
          <option value="0" <?php echo ((int)($plano['backup_slots']??0))===0?'selected':''; ?>>0 — <?php echo View::e(I18n::t('eq_planos.sem_backup')); ?></option>
          <option value="1" <?php echo ((int)($plano['backup_slots']??0))===1?'selected':''; ?>>1 backup</option>
          <option value="2" <?php echo ((int)($plano['backup_slots']??0))===2?'selected':''; ?>>2 backups</option>
        </select>
      </div>
    </div>

    <?php
      // Canais de suporte
      $chRaw = (string)($plano['support_channels'] ?? '');
      $chAtivos = [];
      if ($chRaw !== '') {
          $chDec = json_decode($chRaw, true);
          if (is_array($chDec)) $chAtivos = $chDec;
      }
      $chOpcoes = ['email' => 'E-mail', 'whatsapp' => 'WhatsApp', 'chat' => 'Chat', 'telefone' => 'Telefone', 'ticket' => 'Ticket'];
    ?>
    <div style="margin-top:16px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;">Canais de suporte</label>
      <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php foreach ($chOpcoes as $val => $label): ?>
          <label style="display:flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid <?php echo in_array($val,$chAtivos,true)?'#4F46E5':'#e2e8f0'; ?>;border-radius:10px;cursor:pointer;font-size:13px;background:<?php echo in_array($val,$chAtivos,true)?'#f5f3ff':'#fff'; ?>;transition:all .15s;" class="canal-label">
            <input type="checkbox" name="support_channels_check[]" value="<?php echo $val; ?>" <?php echo in_array($val,$chAtivos,true)?'checked':''; ?> style="accent-color:#4F46E5;width:15px;height:15px;" />
            <?php echo $label; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="support_channels" id="support_channels_json" value="<?php echo View::e($chRaw); ?>" />
    </div>

    <?php
      // Specs extras
      $specsRaw = (string)($plano['specs_json'] ?? '');
      $specsArr = [];
      if ($specsRaw !== '') {
          $specsDec = json_decode($specsRaw, true);
          if (is_array($specsDec)) $specsArr = $specsDec;
      }
    ?>
    <div style="margin-top:16px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;">Specs extras (opcional)</label>
      <div id="specs-lista" style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($specsArr as $k => $v): ?>
          <div class="specs-row" style="display:flex;gap:8px;align-items:center;">
            <input class="input" type="text" placeholder="Chave (ex: bandwidth)" value="<?php echo View::e((string)$k); ?>" style="flex:1;" />
            <input class="input" type="text" placeholder="Valor (ex: 1TB)" value="<?php echo View::e((string)$v); ?>" style="flex:1;" />
            <button type="button" onclick="this.closest('.specs-row').remove();atualizarSpecsJson()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;line-height:1;padding:4px;">×</button>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" id="btn-add-spec" style="margin-top:8px;background:none;border:1.5px dashed #e2e8f0;border-radius:10px;padding:7px 14px;font-size:13px;color:#64748b;cursor:pointer;width:100%;transition:border-color .15s,color .15s;">+ Adicionar spec</button>
      <input type="hidden" name="specs_json" id="specs_json_hidden" value="<?php echo View::e($specsRaw); ?>" />
    </div>

    <div style="margin-top:20px;border-top:1px solid #e2e8f0;padding-top:20px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div>
          <div style="font-size:14px;font-weight:700;color:#0f172a;">Serviços adicionais</div>
          <div style="font-size:12px;color:#64748b;margin-top:2px;">Exibidos na landing page como opções extras ao contratar o plano (ex: Backup, Suporte WhatsApp)</div>
        </div>
        <button type="button" id="btn-add-addon" class="botao" style="font-size:12px;padding:6px 14px;">+ Adicionar</button>
      </div>
      <div id="addons-lista" style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach (($addons ?? []) as $_a): ?>
        <div class="addon-row" style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:8px;align-items:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;">
          <input class="input" type="text" name="addon_name[]" placeholder="Nome (ex: Backup)" value="<?php echo View::e((string)($_a['name']??'')); ?>" style="margin:0;" />
          <input class="input" type="text" name="addon_desc[]" placeholder="Descrição curta" value="<?php echo View::e((string)($_a['description']??'')); ?>" style="margin:0;" />
          <input class="input" type="number" name="addon_price[]" placeholder="Preço R$" value="<?php echo View::e((string)($_a['price']??'0')); ?>" step="0.01" min="0" style="margin:0;width:110px;" />
          <button type="button" onclick="this.closest('.addon-row').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:20px;line-height:1;padding:4px 6px;">×</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="margin-top:14px;">
      <button class="botao" type="submit">Salvar</button>
    </div>
  </form>
</div>

<script>
(function () {
  // Canais de suporte — atualiza hidden ao mudar checkbox
  function atualizarCanaisJson() {
    var checks = document.querySelectorAll('input[name="support_channels_check[]"]:checked');
    var vals = Array.from(checks).map(function(c){ return c.value; });
    document.getElementById('support_channels_json').value = vals.length ? JSON.stringify(vals) : '';
    // Atualiza estilo dos labels
    document.querySelectorAll('.canal-label').forEach(function(lbl) {
      var cb = lbl.querySelector('input[type=checkbox]');
      if (cb.checked) {
        lbl.style.borderColor = '#4F46E5';
        lbl.style.background = '#f5f3ff';
      } else {
        lbl.style.borderColor = '#e2e8f0';
        lbl.style.background = '#fff';
      }
    });
  }
  document.querySelectorAll('input[name="support_channels_check[]"]').forEach(function(cb) {
    cb.addEventListener('change', atualizarCanaisJson);
  });

  // Specs extras — atualiza hidden JSON
  window.atualizarSpecsJson = function() {
    var rows = document.querySelectorAll('#specs-lista .specs-row');
    var obj = {};
    rows.forEach(function(row) {
      var inputs = row.querySelectorAll('input[type=text]');
      var k = inputs[0] ? inputs[0].value.trim() : '';
      var v = inputs[1] ? inputs[1].value.trim() : '';
      if (k !== '') obj[k] = v;
    });
    var keys = Object.keys(obj);
    document.getElementById('specs_json_hidden').value = keys.length ? JSON.stringify(obj) : '';
  };

  // Atualiza specs ao digitar
  document.getElementById('specs-lista').addEventListener('input', window.atualizarSpecsJson);

  // Adicionar nova linha de spec
  document.getElementById('btn-add-spec').addEventListener('click', function() {
    var div = document.createElement('div');
    div.className = 'specs-row';
    div.style.cssText = 'display:flex;gap:8px;align-items:center;';
    div.innerHTML = '<input class="input" type="text" placeholder="Chave (ex: bandwidth)" style="flex:1;" />'
      + '<input class="input" type="text" placeholder="Valor (ex: 1TB)" style="flex:1;" />'
      + '<button type="button" onclick="this.closest(\'.specs-row\').remove();atualizarSpecsJson()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;line-height:1;padding:4px;">×</button>';
    document.getElementById('specs-lista').appendChild(div);
    div.querySelector('input').focus();
  });

  // Adicionar addon
  document.getElementById('btn-add-addon').addEventListener('click', function() {
    var div = document.createElement('div');
    div.className = 'addon-row';
    div.style.cssText = 'display:grid;grid-template-columns:1fr 1fr auto auto;gap:8px;align-items:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;';
    div.innerHTML = '<input class="input" type="text" name="addon_name[]" placeholder="Nome (ex: Backup)" style="margin:0;" />'
      + '<input class="input" type="text" name="addon_desc[]" placeholder="Descrição curta" style="margin:0;" />'
      + '<input class="input" type="number" name="addon_price[]" placeholder="Preço R$" value="0" step="0.01" min="0" style="margin:0;width:110px;" />'
      + '<button type="button" onclick="this.closest(\'.addon-row\').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:20px;line-height:1;padding:4px 6px;">×</button>';
    document.getElementById('addons-lista').appendChild(div);
    div.querySelector('input').focus();
  });

  // Garantir que o JSON está atualizado antes do submit
  document.querySelector('form').addEventListener('submit', function() {
    atualizarCanaisJson();
    window.atualizarSpecsJson();
  });
})();
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
