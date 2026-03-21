<?php declare(strict_types=1);
use LRV\Core\View;
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
<div class="page-header">
  <div>
    <h1 class="page-title">Minha Conta</h1>
    <p class="page-subtitle">Gerencie suas informações pessoais e senha</p>
  </div>
</div>

<?php if (!empty($ok)): ?>
  <div class="alert alert-success"><?php echo View::e($ok); ?></div>
<?php endif; ?>
<?php if (!empty($erro)): ?>
  <div class="alert alert-danger"><?php echo View::e($erro); ?></div>
<?php endif; ?>

<div class="conta-grid">
  <!-- Avatar -->
  <div class="card conta-avatar-card">
    <div class="conta-avatar-wrap">
      <?php if ($_avatar !== ''): ?>
        <img src="<?php echo View::e($_avatar); ?>" alt="Avatar" class="conta-avatar-img" id="avatarPreview" />
      <?php else: ?>
        <div class="conta-avatar-placeholder" id="avatarPreview"><?php echo View::e($_initials); ?></div>
      <?php endif; ?>
    </div>
    <div class="conta-avatar-info">
      <strong><?php echo View::e($_nome); ?></strong>
      <span class="badge-role"><?php echo View::e($_role); ?></span>
    </div>
    <p class="conta-avatar-hint">PNG, JPG, WEBP ou GIF · máx. 2 MB</p>
  </div>

  <!-- Formulário -->
  <div class="card conta-form-card">
    <form method="POST" action="/equipe/minha-conta/salvar" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

      <div class="form-section-title">Informações pessoais</div>

      <div class="form-group">
        <label class="form-label">Foto de perfil</label>
        <div class="avatar-upload-row">
          <label class="btn btn-secondary btn-sm avatar-upload-btn" for="avatarInput">
            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M7.5 2v8M4 5l3.5-3.5L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 11h11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            Escolher foto
          </label>
          <span class="avatar-filename" id="avatarFilename">Nenhum arquivo</span>
          <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none" />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="name">Nome</label>
          <input type="text" id="name" name="name" class="form-control" value="<?php echo View::e($_nome); ?>" required />
        </div>
        <div class="form-group">
          <label class="form-label" for="email">E-mail</label>
          <input type="email" id="email" name="email" class="form-control" value="<?php echo View::e($_email); ?>" required />
        </div>
      </div>

      <div class="form-section-title" style="margin-top:24px;">Alterar senha <span style="font-weight:400;font-size:13px;color:#94a3b8;">(deixe em branco para não alterar)</span></div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="senha_atual">Senha atual</label>
          <input type="password" id="senha_atual" name="senha_atual" class="form-control" autocomplete="current-password" />
        </div>
        <div class="form-group">
          <label class="form-label" for="nova_senha">Nova senha</label>
          <input type="password" id="nova_senha" name="nova_senha" class="form-control" autocomplete="new-password" minlength="8" />
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar alterações</button>
      </div>
    </form>
  </div>
</div>

<style>
.conta-grid { display:grid; grid-template-columns:260px 1fr; gap:24px; align-items:start; }
@media(max-width:768px){ .conta-grid { grid-template-columns:1fr; } }

.conta-avatar-card { display:flex; flex-direction:column; align-items:center; gap:12px; padding:32px 24px; text-align:center; }
.conta-avatar-wrap { width:96px; height:96px; border-radius:50%; overflow:hidden; background:linear-gradient(135deg,#4F46E5,#7C3AED); display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:700; color:#fff; flex-shrink:0; }
.conta-avatar-img { width:100%; height:100%; object-fit:cover; }
.conta-avatar-placeholder { font-size:32px; font-weight:700; color:#fff; }
.conta-avatar-info { display:flex; flex-direction:column; gap:4px; }
.conta-avatar-info strong { font-size:15px; color:#f1f5f9; }
.badge-role { display:inline-block; padding:2px 10px; border-radius:20px; background:rgba(79,70,229,.18); color:#818cf8; font-size:12px; font-weight:500; }
.conta-avatar-hint { font-size:12px; color:#64748b; margin:0; }

.conta-form-card { padding:28px; }
.form-section-title { font-size:13px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; margin-bottom:16px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:600px){ .form-row { grid-template-columns:1fr; } }
.form-actions { margin-top:24px; display:flex; justify-content:flex-end; }

.avatar-upload-row { display:flex; align-items:center; gap:12px; }
.avatar-upload-btn { cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.avatar-filename { font-size:13px; color:#64748b; }
</style>

<script>
(function(){
  var input = document.getElementById('avatarInput');
  var label = document.getElementById('avatarFilename');
  var preview = document.getElementById('avatarPreview');
  if (!input) return;
  input.addEventListener('change', function(){
    var f = this.files[0];
    if (!f) return;
    label.textContent = f.name;
    var reader = new FileReader();
    reader.onload = function(e){
      if (preview.tagName === 'IMG') {
        preview.src = e.target.result;
      } else {
        var img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'conta-avatar-img';
        img.id = 'avatarPreview';
        preview.replaceWith(img);
      }
    };
    reader.readAsDataURL(f);
  });
})();
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
