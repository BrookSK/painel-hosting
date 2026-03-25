<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$dep = is_array($deployment ?? null) ? $deployment : [];
$isEdit = !empty($dep['id']);
$pageTitle = $isEdit ? 'Editar repositório' : 'Novo repositório Git';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo $isEdit ? 'Editar repositório' : 'Novo repositório Git'; ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">Configure o deploy automático via Git</div>
  </div>
  <a href="/cliente/git-deploy" class="botao ghost sm">← Voltar</a>
</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e((string)$erro); ?></div>
<?php endif; ?>

<div class="card-new" style="max-width:680px;">
  <form method="post" action="/cliente/git-deploy/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo (int)($dep['id'] ?? 0); ?>" />

    <div class="grid" style="margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">Nome da integração</label>
        <input class="input" type="text" name="name" value="<?php echo View::e((string)($dep['name'] ?? '')); ?>" placeholder="Meu site" required />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">VPS</label>
        <select class="input" name="vps_id" required>
          <option value="">Selecione...</option>
          <?php foreach (($vpsList ?? []) as $v): ?>
            <option value="<?php echo (int)$v['id']; ?>" <?php echo ((int)($dep['vps_id'] ?? 0)) === (int)$v['id'] ? 'selected' : ''; ?>>
              VPS #<?php echo (int)$v['id']; ?> — <?php echo (int)$v['cpu']; ?>vCPU / <?php echo round((int)$v['ram']/1024); ?>GB
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">URL do repositório</label>
      <input class="input" type="text" name="repo_url" value="<?php echo View::e((string)($dep['repo_url'] ?? '')); ?>" placeholder="https://github.com/usuario/repositorio.git" required />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">Repositório público ou privado (HTTPS ou SSH). Para privados, use token no URL: <code>https://token@github.com/...</code></p>
    </div>

    <div class="grid" style="margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">Branch</label>
        <input class="input" type="text" name="branch" value="<?php echo View::e((string)($dep['branch'] ?? 'main')); ?>" placeholder="main" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">Subdomínio (opcional)</label>
        <input class="input" type="text" name="subdomain" value="<?php echo View::e((string)($dep['subdomain'] ?? '')); ?>" placeholder="app.meudominio.com" />
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">Caminho de deploy no servidor</label>
      <input class="input" type="text" name="deploy_path" value="<?php echo View::e((string)($dep['deploy_path'] ?? '/var/www/html')); ?>" placeholder="/var/www/html" />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">Diretório onde os arquivos serão colocados na VPS.</p>
    </div>

    <div style="margin-bottom:20px;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
      <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
        <input type="checkbox" name="force_overwrite" value="1" <?php echo ((int)($dep['force_overwrite'] ?? 1)) === 1 ? 'checked' : ''; ?> style="margin-top:2px;accent-color:#4F46E5;width:16px;height:16px;flex-shrink:0;" />
        <div>
          <div style="font-size:13px;font-weight:600;color:#1e293b;">Substituir tudo (force overwrite)</div>
          <div style="font-size:12px;color:#64748b;margin-top:2px;">Quando ativado, qualquer alteração feita diretamente no servidor será descartada e substituída pelo conteúdo do repositório. Recomendado para manter o servidor sempre sincronizado com o Git.<br><br>Quando desativado, o sistema faz <code>git stash</code> antes de puxar, preservando alterações locais.</div>
        </div>
      </label>
    </div>

    <button class="botao" type="submit"><?php echo $isEdit ? 'Salvar alterações' : 'Conectar repositório'; ?></button>
  </form>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
