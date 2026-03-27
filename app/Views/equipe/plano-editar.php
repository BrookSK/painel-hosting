<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

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
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Destaque</label>
        <select class="input" name="is_featured">
          <option value="0" <?php echo ((int)($plano['is_featured']??0))===0?'selected':''; ?>>Normal</option>
          <option value="1" <?php echo ((int)($plano['is_featured']??0))===1?'selected':''; ?>>⭐ Popular / Destaque</option>
        </select>
        <p class="texto" style="font-size:12px;margin-top:4px;">Planos em destaque aparecem com badge "Popular" e borda destacada na página de planos.</p>
      </div>
    </div>

    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Cliente exclusivo (opcional)</label>
      <select class="input" name="client_id">
        <option value="">— Plano público (todos os clientes)</option>
        <?php
          $clientesDoPlan = [];
          try {
              $clientesDoPlan = \LRV\Core\BancoDeDados::pdo()->query("SELECT id, name, email FROM clients ORDER BY name ASC")->fetchAll() ?: [];
          } catch (\Throwable) {}
          $selectedClientId = (int)($plano['client_id'] ?? 0);
          foreach ($clientesDoPlan as $cl):
        ?>
          <option value="<?php echo (int)$cl['id']; ?>" <?php echo (int)$cl['id'] === $selectedClientId ? 'selected' : ''; ?>>
            <?php echo View::e((string)$cl['name']); ?> (<?php echo View::e((string)$cl['email']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <p class="texto" style="font-size:12px;margin-top:4px;">Planos vinculados a um cliente não aparecem na página pública de planos nem ficam disponíveis para outros clientes.</p>
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
        <p class="texto" style="font-size:12px;margin-top:4px;">Valor em BRL. Pra clientes em USD, o sistema converte automaticamente pela taxa configurada.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_planos.backup_slots')); ?></label>
        <select class="input" name="backup_slots">
          <option value="0" <?php echo ((int)($plano['backup_slots']??0))===0?'selected':''; ?>>0 — <?php echo View::e(I18n::t('eq_planos.sem_backup')); ?></option>
          <option value="1" <?php echo ((int)($plano['backup_slots']??0))===1?'selected':''; ?>>1 backup</option>
          <option value="2" <?php echo ((int)($plano['backup_slots']??0))===2?'selected':''; ?>>2 backups</option>
        </select>
        <p class="texto" style="font-size:12px;margin-top:4px;">Limita quantos backups o cliente pode ter. Rotação automática.</p>
      </div>
    </div>
    <input type="hidden" name="stripe_price_id" value="<?php echo View::e((string)($plano['stripe_price_id']??'')); ?>" />
    <p class="texto" style="font-size:12px;margin-top:8px;">O Stripe Price ID é gerado automaticamente ao salvar, se o Stripe estiver configurado.</p>

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
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;">Limites do plano</label>
      <p class="texto" style="font-size:12px;margin:0 0 12px;">Esses valores controlam o que o cliente pode fazer. Deixe 0 ou vazio pra usar o padrão do sistema.</p>

      <div class="grid">
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;color:#475569;">Contas de e-mail (máx)</label>
          <input class="input" type="number" name="spec_email_accounts" value="<?php echo View::e((string)($specsArr['email_accounts'] ?? '5')); ?>" min="0" placeholder="5" />
          <p class="texto" style="font-size:11px;margin-top:3px;">Padrão: 5</p>
        </div>
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;color:#475569;">Cota total de e-mail (MB)</label>
          <input class="input" type="number" name="spec_email_quota_mb" value="<?php echo View::e((string)($specsArr['email_quota_mb'] ?? '5120')); ?>" min="0" placeholder="5120" />
          <p class="texto" style="font-size:11px;margin-top:3px;">5120 = 5 GB. Distribuído entre as contas do cliente.</p>
        </div>
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;color:#475569;">Banda mensal</label>
          <input class="input" type="text" name="spec_bandwidth" value="<?php echo View::e((string)($specsArr['bandwidth'] ?? 'Ilimitada')); ?>" placeholder="Ilimitada" />
          <p class="texto" style="font-size:11px;margin-top:3px;">Informativo. Ex: 1TB, 5TB, Ilimitada</p>
        </div>
      </div>
      <div class="grid" style="margin-top:10px;">
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;color:#475569;">Domínios permitidos</label>
          <input class="input" type="number" name="spec_max_domains" value="<?php echo View::e((string)($specsArr['max_domains'] ?? '3')); ?>" min="0" placeholder="3" />
          <p class="texto" style="font-size:11px;margin-top:3px;">Quantos domínios de e-mail o cliente pode cadastrar.</p>
        </div>
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;color:#475569;">Aplicações (máx)</label>
          <input class="input" type="number" name="spec_max_apps" value="<?php echo View::e((string)($specsArr['max_apps'] ?? '5')); ?>" min="0" placeholder="5" />
          <p class="texto" style="font-size:11px;margin-top:3px;">Quantas aplicações do catálogo o cliente pode instalar.</p>
        </div>
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;color:#475569;">SLA (%)</label>
          <input class="input" type="text" name="spec_sla" value="<?php echo View::e((string)($specsArr['sla'] ?? '99.5')); ?>" placeholder="99.5" />
          <p class="texto" style="font-size:11px;margin-top:3px;">Informativo. Exibido no card do plano.</p>
        </div>
      </div>
      <input type="hidden" name="specs_json" id="specs_json_hidden" value="<?php echo View::e($specsRaw); ?>" />
    </div>

    <div style="margin-top:20px;border-top:1px solid #e2e8f0;padding-top:20px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div>
          <div style="font-size:14px;font-weight:700;color:#0f172a;">Serviços adicionais</div>
          <div style="font-size:12px;color:#64748b;margin-top:2px;">Exibidos na landing page como opções extras. São informativos — não alteram limites do plano. O backup real é configurado em "Backups inclusos" acima. Mas eles são cobrados no checkout — o cliente seleciona quais quer e o preço total é calculado.</div>
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

  // Specs extras — atualiza hidden JSON a partir dos campos visuais
  window.atualizarSpecsJson = function() {
    var obj = {};
    var fields = {
      'spec_email_accounts': 'email_accounts',
      'spec_email_quota_mb': 'email_quota_mb',
      'spec_bandwidth': 'bandwidth',
      'spec_max_domains': 'max_domains',
      'spec_max_apps': 'max_apps',
      'spec_sla': 'sla'
    };
    for (var name in fields) {
      var el = document.querySelector('[name="' + name + '"]');
      if (el && el.value.trim() !== '') {
        var v = el.value.trim();
        // Tentar converter pra número se possível
        if (/^\d+$/.test(v)) v = parseInt(v, 10);
        else if (/^\d+\.\d+$/.test(v)) v = parseFloat(v);
        obj[fields[name]] = v;
      }
    }
    document.getElementById('specs_json_hidden').value = Object.keys(obj).length ? JSON.stringify(obj) : '';
  };

  // Atualiza specs ao digitar em qualquer campo spec_*
  document.querySelectorAll('[name^="spec_"]').forEach(function(el) {
    el.addEventListener('input', window.atualizarSpecsJson);
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
