<?php declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Minha Conta';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$_nome   = (string)($usuario['name'] ?? '');
$_email  = (string)($usuario['email'] ?? '');
$_role   = (string)($usuario['role'] ?? '');
$_avatar = (string)($usuario['avatar_url'] ?? '');

$_initials = '';
foreach (explode(' ', trim($_nome)) as $w) {
    $_initials .= strtoupper(substr($w, 0, 1));
    if (strlen($_initials) >= 2) break;
}
if ($_initials === '') $_initials = 'U';
?>

<div class="page-title">Minha Conta</div>
<div class="page-subtitle">Gerencie suas informações pessoais e senha</div>

<?php if (!empty($ok)): ?><div class="sucesso"><?php echo View::e($ok); ?></div><?php endif; ?>
<?php if (!empty($erro)): ?><div class="erro"><?php echo View::e($erro); ?></div><?php endif; ?>

<div class="mc-layout">

  <!-- Coluna esquerda: avatar -->
  <div class="card-new mc-avatar-card">
    <div class="mc-avatar-circle" id="avatarWrap">
      <?php if ($_avatar !== ''): ?>
        <img src="<?php echo View::e($_avatar); ?>" alt="avatar" class="mc-avatar-img" />
      <?php else: ?>
        <?php echo View::e($_initials); ?>
      <?php endif; ?>
    </div>
    <div class="mc-avatar-name"><?php echo View::e($_nome); ?></div>
    <span class="mc-role-badge"><?php echo View::e($_role); ?></span>
    <p class="mc-avatar-hint">PNG, JPG, WEBP · máx. 2 MB</p>
  </div>

  <!-- Coluna direita: formulário -->
  <div class="card-new mc-form-card">
    <form method="POST" action="/equipe/minha-conta/salvar" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

      <p class="mc-section-label">Informações pessoais</p>

      <div class="mc-field">
        <label class="mc-label" for="avatarInput">Foto de perfil</label>
        <div class="mc-upload-row">
          <label for="avatarInput" class="botao sm sec" style="cursor:pointer;">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M6.5 1.5v7M3.5 4.5l3-3 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M1.5 10.5h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            Escolher foto
          </label>
          <span class="mc-filename" id="avatarFilename">Nenhum arquivo selecionado</span>
          <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none;" />
        </div>
      </div>

      <div class="mc-row">
        <div class="mc-field">
          <label class="mc-label" for="name">Nome</label>
          <input type="text" id="name" name="name" class="input mc-input" value="<?php echo View::e($_nome); ?>" required />
        </div>
        <div class="mc-field">
          <label class="mc-label" for="email">E-mail</label>
          <input type="email" id="email" name="email" class="input mc-input" value="<?php echo View::e($_email); ?>" required />
        </div>
      </div>

      <div class="mc-divider"></div>
      <p class="mc-section-label">Alterar senha <span class="mc-section-hint">(deixe em branco para não alterar)</span></p>

      <div class="mc-row">
        <div class="mc-field">
          <label class="mc-label" for="senha_atual">Senha atual</label>
          <input type="password" id="senha_atual" name="senha_atual" class="input mc-input" autocomplete="current-password" />
        </div>
        <div class="mc-field">
          <label class="mc-label" for="nova_senha">Nova senha</label>
          <input type="password" id="nova_senha" name="nova_senha" class="input mc-input" autocomplete="new-password" minlength="8" />
        </div>
      </div>

      <div class="mc-actions">
        <button type="submit" class="botao">Salvar alterações</button>
      </div>
    </form>
  </div>

</div>

<style>
.mc-layout {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 20px;
  align-items: start;
}
.mc-avatar-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 28px 20px;
  text-align: center;
}
.mc-avatar-circle {
  width: 84px;
  height: 84px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4F46E5, #7C3AED);
  color: #fff;
  font-size: 28px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.mc-avatar-img { width: 100%; height: 100%; object-fit: cover; }
.mc-avatar-name { font-size: 14px; font-weight: 600; color: #0f172a; }
.mc-role-badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 20px;
  background: #f5f3ff;
  color: #6d28d9;
  font-size: 12px;
  font-weight: 500;
}
.mc-avatar-hint { font-size: 11px; color: #94a3b8; margin: 0; }

.mc-form-card { padding: 24px; }
.mc-section-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: #94a3b8;
  margin-bottom: 16px;
}
.mc-section-hint { font-weight: 400; text-transform: none; letter-spacing: 0; font-size: 12px; }
.mc-divider { border: none; border-top: 1px solid #f1f5f9; margin: 20px 0 16px; }
.mc-field { display: flex; flex-direction: column; gap: 6px; }
.mc-label { font-size: 13px; font-weight: 500; color: #475569; }
.mc-input { width: 100%; }
.mc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.mc-upload-row { display: flex; align-items: center; gap: 10px; }
.mc-filename { font-size: 13px; color: #94a3b8; }
.mc-actions { display: flex; justify-content: flex-end; margin-top: 8px; }

@media (max-width: 860px) { .mc-layout { grid-template-columns: 1fr; } }
@media (max-width: 560px) { .mc-row { grid-template-columns: 1fr; } }
</style>

<script>
(function(){
  var input = document.getElementById('avatarInput');
  var label = document.getElementById('avatarFilename');
  var wrap  = document.getElementById('avatarWrap');
  if (!input) return;
  input.addEventListener('change', function(){
    var f = this.files[0];
    if (!f) return;
    label.textContent = f.name;
    var reader = new FileReader();
    reader.onload = function(e){
      wrap.innerHTML = '<img src="'+e.target.result+'" class="mc-avatar-img" alt="avatar" />';
    };
    reader.readAsDataURL(f);
  });
})();
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
