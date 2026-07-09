<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$cliente = $cliente ?? [];
$ok      = (string)($ok ?? ($_GET['ok'] ?? ''));
$erro    = (string)($erro ?? '');

$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
$pageTitle    = I18n::t('conta.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

$iniciais = 'C';
$partes = explode(' ', trim($clienteNome));
if (count($partes) >= 2) {
    $iniciais = strtoupper(substr($partes[0], 0, 1) . substr(end($partes), 0, 1));
} elseif ($clienteNome !== '') {
    $iniciais = strtoupper(substr($clienteNome, 0, 1));
}
?>

<div class="page-title"><?php echo View::e(I18n::t('conta.titulo')); ?></div>
<div class="page-subtitle" style="margin-bottom:20px;"><?php echo View::e(I18n::t('conta.subtitulo')); ?></div>

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
    <?php
      $avatarPath = trim((string)($cliente['avatar'] ?? ''));
      $gravatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($clienteEmail))) . '?s=120&d=blank';
      $avatarUrl = $avatarPath !== '' ? View::e($avatarPath) : $gravatarUrl;
      $hasAvatar = $avatarPath !== '';
      // Testar se Gravatar existe (usa d=404 pra check, mas mostramos d=blank como fallback visual)
    ?>
    <div style="position:relative;flex-shrink:0;">
      <div id="avatarContainer" style="width:60px;height:60px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#4F46E5,#7C3AED);font-size:22px;font-weight:700;color:#fff;">
        <?php if ($hasAvatar): ?>
          <img id="avatarImg" src="<?php echo $avatarUrl; ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';document.getElementById('avatarInitials').style.display='flex';" />
          <span id="avatarInitials" style="display:none;"><?php echo View::e($iniciais); ?></span>
        <?php else: ?>
          <img id="avatarImg" src="<?php echo $gravatarUrl; ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;display:none;" onload="if(this.naturalWidth>1)this.style.display='block'" onerror="this.style.display='none'" />
          <span id="avatarInitials" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;"><?php echo View::e($iniciais); ?></span>
        <?php endif; ?>
      </div>
      <label for="avatarInput" style="position:absolute;bottom:-2px;right:-2px;width:22px;height:22px;border-radius:50%;background:#4F46E5;border:2px solid #fff;display:flex;align-items:center;justify-content:center;cursor:pointer;" title="Alterar foto">
        <svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M2 11.5V14h2.5L12.06 6.44l-2.5-2.5L2 11.5z" fill="#fff"/><path d="M14.35 3.15a.5.5 0 000-.7l-1.8-1.8a.5.5 0 00-.7 0L10.58 1.92l2.5 2.5 1.27-1.27z" fill="#fff"/></svg>
      </label>
      <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;" onchange="uploadAvatar(this)" />
    </div>
    <div>
      <div style="font-size:17px;font-weight:700;color:#0f172a;"><?php echo View::e($clienteNome ?: '—'); ?></div>
      <div style="font-size:13px;color:#64748b;margin-top:2px;"><?php echo View::e($clienteEmail); ?></div>
      <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Cliente #<?php echo (int)($cliente['id'] ?? 0); ?> · desde <?php echo View::e(date('d/m/Y', strtotime((string)($cliente['created_at'] ?? 'now')))); ?></div>
      <?php if ($hasAvatar): ?>
        <button type="button" onclick="removerAvatar()" style="margin-top:6px;background:none;border:none;color:#ef4444;font-size:12px;cursor:pointer;padding:0;text-decoration:underline;">Remover foto</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function uploadAvatar(input) {
  if (!input.files || !input.files[0]) return;
  var file = input.files[0];
  if (file.size > 2 * 1024 * 1024) { alert('Arquivo muito grande (máx. 2 MB).'); return; }

  var fd = new FormData();
  fd.append('avatar', file);

  var csrf = document.querySelector('input[name="_csrf"]');
  if (csrf) fd.append('_csrf', csrf.value);

  fetch('/cliente/minha-conta/avatar', { method: 'POST', headers: { 'x-csrf-token': csrf ? csrf.value : '' }, body: fd })
    .then(function(r) { return r.json(); })
    .then(function(json) {
      if (json.ok) {
        location.reload();
      } else {
        alert(json.erro || 'Erro ao enviar avatar.');
      }
    })
    .catch(function() { alert('Erro de rede.'); });
}

function removerAvatar() {
  if (!confirm('Remover foto de perfil?')) return;
  var csrf = document.querySelector('input[name="_csrf"]');
  fetch('/cliente/minha-conta/avatar/remover', { method: 'POST', headers: { 'Content-Type': 'application/json', 'x-csrf-token': csrf ? csrf.value : '' } })
    .then(function(r) { return r.json(); })
    .then(function(json) { if (json.ok) location.reload(); else alert(json.erro || 'Erro.'); })
    .catch(function() { alert('Erro de rede.'); });
}
</script>

<div class="grid">
  <!-- Dados pessoais -->
  <div style="display:flex;flex-direction:column;gap:16px;">
    <div class="card-new">
      <div class="card-new-title"><?php echo View::e(I18n::t('conta.dados_pessoais')); ?></div>
      <form method="POST" action="/cliente/minha-conta/salvar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="aba" value="dados" />

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
          <div style="grid-column:1/-1;">
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('auth.nome')); ?></label>
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
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('wz.pais')); ?></label>
            <select class="input" name="country">
              <?php $cc = strtoupper(trim((string)($cliente['country'] ?? 'BR'))); ?>
              <?php foreach (['BR'=>'🇧🇷 Brasil','US'=>'🇺🇸 United States','PT'=>'🇵🇹 Portugal','ES'=>'🇪🇸 España','AR'=>'🇦🇷 Argentina','CL'=>'🇨🇱 Chile','CO'=>'🇨🇴 Colombia','MX'=>'🇲🇽 México','UY'=>'🇺🇾 Uruguay','PY'=>'🇵🇾 Paraguay','DE'=>'🇩🇪 Deutschland','FR'=>'🇫🇷 France','GB'=>'🇬🇧 United Kingdom','IT'=>'🇮🇹 Italia','JP'=>'🇯🇵 日本'] as $code => $label): ?>
                <option value="<?php echo $code; ?>" <?php echo $cc === $code ? 'selected' : ''; ?>><?php echo $label; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('wz.idioma_preferido')); ?></label>
            <select class="input" name="preferred_lang">
              <?php $pl = trim((string)($cliente['preferred_lang'] ?? I18n::idioma())); ?>
              <option value="pt-BR" <?php echo $pl === 'pt-BR' ? 'selected' : ''; ?>>🇧🇷 Português</option>
              <option value="en-US" <?php echo $pl === 'en-US' ? 'selected' : ''; ?>>🇺🇸 English</option>
              <option value="es-ES" <?php echo $pl === 'es-ES' ? 'selected' : ''; ?>>🇪🇸 Español</option>
            </select>
          </div>
        </div>

        <button type="submit" class="botao"><?php echo View::e(I18n::t('geral.salvar')); ?></button>
      </form>
    </div>

    <!-- Alterar senha -->
    <div class="card-new">
      <div class="card-new-title"><?php echo View::e(I18n::t('conta.alterar_senha')); ?></div>
      <form method="POST" action="/cliente/minha-conta/salvar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="aba" value="senha" />
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:12px;">
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.senha_atual')); ?></label>
            <input class="input" type="password" name="senha_atual" autocomplete="current-password" placeholder="••••••••" />
          </div>
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.nova_senha')); ?></label>
            <input class="input" type="password" name="senha_nova" autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
          </div>
          <div>
            <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.confirmar_senha')); ?></label>
            <input class="input" type="password" name="senha_confirmar" autocomplete="new-password" placeholder="Repita a nova senha" />
          </div>
        </div>
        <button type="submit" class="botao"><?php echo View::e(I18n::t('conta.alterar_senha')); ?></button>
      </form>
    </div>
  </div>

  <!-- Segurança -->
  <div class="card-new" style="margin-bottom:16px;">
    <div class="card-new-title" style="margin-bottom:8px;"><?php echo View::e(I18n::t('2fa.seguranca')); ?></div>
    <p style="font-size:13px;color:#64748b;margin-bottom:14px;line-height:1.5;">
      Ative a autenticação em dois fatores (2FA) para proteger sua conta com uma camada extra de segurança.
    </p>
    <a href="/cliente/2fa/configurar" class="botao"><?php echo View::e(I18n::t('2fa.configurar')); ?></a>
  </div>

  <!-- Endereço -->
  <div class="card-new">
    <div class="card-new-title"><?php echo View::e(I18n::t('conta.endereco')); ?></div>
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
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.cep')); ?></label>
          <input class="input" type="text" name="address_zip" id="cepInput" value="<?php echo View::e((string)($cliente['address_zip'] ?? '')); ?>" placeholder="00000-000" maxlength="9" style="max-width:160px;" />
        </div>
        <div style="grid-column:1/-1;">
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.rua')); ?></label>
          <input class="input" type="text" name="address_street" id="streetInput" value="<?php echo View::e((string)($cliente['address_street'] ?? '')); ?>" placeholder="Rua, Avenida..." />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.numero')); ?></label>
          <input class="input" type="text" name="address_number" value="<?php echo View::e((string)($cliente['address_number'] ?? '')); ?>" placeholder="123" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.complemento')); ?></label>
          <input class="input" type="text" name="address_complement" value="<?php echo View::e((string)($cliente['address_complement'] ?? '')); ?>" placeholder="Apto, Sala..." />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;">Bairro</label>
          <input class="input" type="text" name="address_district" id="districtInput" value="<?php echo View::e((string)($cliente['address_district'] ?? '')); ?>" placeholder="Bairro" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.cidade')); ?></label>
          <input class="input" type="text" name="address_city" id="cityInput" value="<?php echo View::e((string)($cliente['address_city'] ?? '')); ?>" placeholder="São Paulo" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.estado')); ?> (UF)</label>
          <input class="input" type="text" name="address_state" id="stateInput" value="<?php echo View::e((string)($cliente['address_state'] ?? '')); ?>" placeholder="SP" maxlength="2" style="max-width:80px;text-transform:uppercase;" />
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;"><?php echo View::e(I18n::t('conta.pais')); ?></label>
          <input class="input" type="text" name="address_country" value="<?php echo View::e((string)($cliente['address_country'] ?? 'BR')); ?>" placeholder="BR" maxlength="2" style="max-width:80px;text-transform:uppercase;" />
        </div>
      </div>
      <button type="submit" class="botao"><?php echo View::e(I18n::t('geral.salvar')); ?></button>
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

// Máscaras visuais
(function(){
  function maskCpfCnpj(el){
    el.addEventListener('input',function(){
      var v=this.value.replace(/\D/g,'');
      if(v.length<=11){
        v=v.replace(/(\d{3})(\d)/,'$1.$2');
        v=v.replace(/(\d{3})(\d)/,'$1.$2');
        v=v.replace(/(\d{3})(\d{1,2})$/,'$1-$2');
      }else{
        v=v.substring(0,14);
        v=v.replace(/^(\d{2})(\d)/,'$1.$2');
        v=v.replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3');
        v=v.replace(/\.(\d{3})(\d)/,'.$1/$2');
        v=v.replace(/(\d{4})(\d)/,'$1-$2');
      }
      this.value=v;
    });
  }
  function maskPhone(el){
    el.addEventListener('input',function(){
      var v=this.value.replace(/\D/g,'');
      if(v.length<=10){
        v=v.replace(/^(\d{2})(\d)/,'($1) $2');
        v=v.replace(/(\d{4})(\d)/,'$1-$2');
      }else{
        v=v.substring(0,11);
        v=v.replace(/^(\d{2})(\d)/,'($1) $2');
        v=v.replace(/(\d{5})(\d)/,'$1-$2');
      }
      this.value=v;
    });
  }
  function maskCep(el){
    el.addEventListener('input',function(){
      var v=this.value.replace(/\D/g,'').substring(0,8);
      if(v.length>5) v=v.substring(0,5)+'-'+v.substring(5);
      this.value=v;
    });
  }
  document.querySelectorAll('input[name="cpf_cnpj"]').forEach(function(el){maskCpfCnpj(el);});
  document.querySelectorAll('input[name="phone"]').forEach(function(el){maskPhone(el);});
  document.querySelectorAll('input[name="mobile_phone"]').forEach(function(el){maskPhone(el);});
  var cepEl=document.getElementById('cepInput');if(cepEl)maskCep(cepEl);
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
