<?php
declare(strict_types=1);
use LRV\Core\View;

$cliente = $cliente ?? [];
$ok      = (string)($ok ?? ($_GET['ok'] ?? ''));
$erro    = (string)($erro ?? '');

$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
$pageTitle    = 'Minha Conta';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

$iniciais = 'C';
$partes = explode(' ', trim($clienteNome));
if (count($partes) >= 2) {
    $iniciais = strtoupper(substr($partes[0], 0, 1) . substr(end($partes), 0, 1));
} elseif ($clienteNome !== '') {
    $iniciais = strtoupper(substr($clienteNome, 0, 1));
}
?>

<div class="page-title">Minha Conta</div>
<div class="page-subtitle" style="margin-bottom:20px;">Gerencie seus dados pessoais e senha de acesso</div>

<?php if ($ok === 'dados'): ?>
  <div class="sucesso" style="margin-bottom:16px;">Dados atualizados com sucesso.</div>
<?php elseif ($ok === 'senha'): ?>
  <div class="sucesso" style="margin-bottom:16px;">Senha alterada com sucesso.</div>
<?php endif; ?>
<?php if ($erro !== ''): ?>
  <div class="erro" style="margin-bottom:16px;"><?php echo View::e($erro); ?></div>
<?php endif; ?>

<!-- Avatar + info -->
<div class="card-new" style="margin-bottom:16px;">
  <div style="display:flex;align-items:center;gap:16px;">
    <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0;">
      <?php echo View::e($iniciais); ?>
    </div>
    <div>
      <div style="font-size:17px;font-weight:700;color:#0f172a;"><?php echo View::e($clienteNome ?: '—'); ?></div>
      <div style="font-size:13px;color:#64748b;margin-top:2px;"><?php echo View::e($clienteEmail); ?></div>
      <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Cliente #<?php echo (int)($cliente['id'] ?? 0); ?> · desde <?php echo View::e(date('d/m/Y', strtotime((string)($cliente['created_at'] ?? 'now')))); ?></div>
    </div>
  </div>
</div>

<div class="grid">
  <!-- Dados pessoais -->
  <div style="display:flex;flex-direction:column;gap:16px;">
    <div class="card-new">
      <div class="card-new-title">Dados pessoais</div>
      <form method="POST" action="/cliente/minha-conta/salvar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="aba" value="dados" />

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
          <div style="grid-column:1/-1;">
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Nome completo</label>
            <input class="input" type="text" name="name" value="<?php echo View::e((string)($cliente['name'] ?? '')); ?>" required />
          </div>
          <div style="grid-column:1/-1;">
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">E-mail</label>
            <input class="input" type="email" value="<?php echo View::e($clienteEmail); ?>" disabled style="opacity:.6;cursor:not-allowed;" />
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Para alterar o e-mail, entre em contato com o suporte.</div>
          </div>
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Telefone</label>
            <input class="input" type="text" name="phone" value="<?php echo View::e((string)($cliente['phone'] ?? '')); ?>" placeholder="(11) 3333-4444" />
          </div>
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Celular</label>
            <input class="input" type="text" name="mobile_phone" value="<?php echo View::e((string)($cliente['mobile_phone'] ?? '')); ?>" placeholder="(11) 99999-8888" />
          </div>
          <div style="grid-column:1/-1;">
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">CPF / CNPJ</label>
            <input class="input" type="text" name="cpf_cnpj" value="<?php echo View::e((string)($cliente['cpf_cnpj'] ?? '')); ?>" placeholder="000.000.000-00" />
          </div>
        </div>

        <button type="submit" class="botao">Salvar dados</button>
      </form>
    </div>

    <!-- Alterar senha -->
    <div class="card-new">
      <div class="card-new-title">Alterar senha</div>
      <form method="POST" action="/cliente/minha-conta/salvar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="aba" value="senha" />
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:12px;">
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Senha atual</label>
            <input class="input" type="password" name="senha_atual" autocomplete="current-password" placeholder="••••••••" />
          </div>
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Nova senha</label>
            <input class="input" type="password" name="senha_nova" autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
          </div>
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Confirmar nova senha</label>
            <input class="input" type="password" name="senha_confirmar" autocomplete="new-password" placeholder="Repita a nova senha" />
          </div>
        </div>
        <button type="submit" class="botao">Alterar senha</button>
      </form>
    </div>
  </div>

  <!-- Endereço -->
  <div class="card-new">
    <div class="card-new-title">Endereço</div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;line-height:1.5;">Opcional. Usado para emissão de notas fiscais e correspondências.</p>
    <form method="POST" action="/cliente/minha-conta/salvar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <input type="hidden" name="aba" value="dados" />
      <!-- campos pessoais ocultos para não perder ao salvar só endereço -->
      <input type="hidden" name="name"         value="<?php echo View::e((string)($cliente['name'] ?? '')); ?>" />
      <input type="hidden" name="phone"        value="<?php echo View::e((string)($cliente['phone'] ?? '')); ?>" />
      <input type="hidden" name="mobile_phone" value="<?php echo View::e((string)($cliente['mobile_phone'] ?? '')); ?>" />
      <input type="hidden" name="cpf_cnpj"     value="<?php echo View::e((string)($cliente['cpf_cnpj'] ?? '')); ?>" />

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div style="grid-column:1/-1;">
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">CEP</label>
          <input class="input" type="text" name="address_zip" id="cepInput" value="<?php echo View::e((string)($cliente['address_zip'] ?? '')); ?>" placeholder="00000-000" maxlength="9" style="max-width:160px;" />
        </div>
        <div style="grid-column:1/-1;">
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Logradouro</label>
          <input class="input" type="text" name="address_street" id="streetInput" value="<?php echo View::e((string)($cliente['address_street'] ?? '')); ?>" placeholder="Rua, Avenida..." />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Número</label>
          <input class="input" type="text" name="address_number" value="<?php echo View::e((string)($cliente['address_number'] ?? '')); ?>" placeholder="123" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Complemento</label>
          <input class="input" type="text" name="address_complement" value="<?php echo View::e((string)($cliente['address_complement'] ?? '')); ?>" placeholder="Apto, Sala..." />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Bairro</label>
          <input class="input" type="text" name="address_district" id="districtInput" value="<?php echo View::e((string)($cliente['address_district'] ?? '')); ?>" placeholder="Bairro" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Cidade</label>
          <input class="input" type="text" name="address_city" id="cityInput" value="<?php echo View::e((string)($cliente['address_city'] ?? '')); ?>" placeholder="São Paulo" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Estado (UF)</label>
          <input class="input" type="text" name="address_state" id="stateInput" value="<?php echo View::e((string)($cliente['address_state'] ?? '')); ?>" placeholder="SP" maxlength="2" style="max-width:80px;text-transform:uppercase;" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">País</label>
          <input class="input" type="text" name="address_country" value="<?php echo View::e((string)($cliente['address_country'] ?? 'BR')); ?>" placeholder="BR" maxlength="2" style="max-width:80px;text-transform:uppercase;" />
        </div>
      </div>
      <button type="submit" class="botao">Salvar endereço</button>
    </form>
  </div>
</div>

<script>
// Auto-preenchimento via ViaCEP
document.getElementById('cepInput').addEventListener('blur', function() {
  var cep = this.value.replace(/\D/g, '');
  if (cep.length !== 8) return;
  fetch('https://viacep.com.br/ws/' + cep + '/json/')
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (d.erro) return;
      var s = document.getElementById('streetInput');
      var di = document.getElementById('districtInput');
      var ci = document.getElementById('cityInput');
      var st = document.getElementById('stateInput');
      if (s && !s.value)  s.value  = d.logradouro || '';
      if (di && !di.value) di.value = d.bairro || '';
      if (ci && !ci.value) ci.value = d.localidade || '';
      if (st && !st.value) st.value = d.uf || '';
    })
    .catch(function(){});
});
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
