<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

$id = $aplicacao['id'] ?? null;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $id ? 'Editar aplicação' : 'Nova aplicação'; ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Aplicações</div>
        <div style="opacity:.9; font-size:13px;"><?php echo $id ? 'Editar' : 'Nova'; ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/aplicacoes">Voltar</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo"><?php echo $id ? 'Editar aplicação' : 'Nova aplicação'; ?></h1>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/equipe/aplicacoes/salvar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo View::e((string) ($aplicacao['id'] ?? '')); ?>" />

        <div class="grid">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">VPS</label>
            <?php $vpsId = (int) (($aplicacao['vps_id'] ?? 0) ?? 0); ?>
            <select class="input" name="vps_id">
              <option value="">Selecione...</option>
              <?php foreach (($vps ?? []) as $vv): ?>
                <?php $vid = (int) ($vv['id'] ?? 0); ?>
                <option value="<?php echo $vid; ?>" <?php echo $vid === $vpsId ? 'selected' : ''; ?>>#<?php echo $vid; ?> (<?php echo View::e((string) ($vv['client_email'] ?? '')); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Tipo</label>
            <input class="input" type="text" name="type" value="<?php echo View::e((string) ($aplicacao['type'] ?? '')); ?>" placeholder="app" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Domínio (opcional)</label>
            <input class="input" type="text" name="domain" value="<?php echo View::e((string) ($aplicacao['domain'] ?? '')); ?>" placeholder="ex: app.seudominio.com" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Porta (deixe vazio para escolher automaticamente)</label>
            <input class="input" type="number" name="port" value="<?php echo View::e((string) ($aplicacao['port'] ?? '')); ?>" min="1" max="65535" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Repositório (opcional)</label>
            <input class="input" type="text" name="repository" value="<?php echo View::e((string) ($aplicacao['repository'] ?? '')); ?>" placeholder="https://..." />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Status</label>
            <?php $st = (string) ($aplicacao['status'] ?? 'active'); ?>
            <select class="input" name="status">
              <option value="active" <?php echo $st === 'active' ? 'selected' : ''; ?>>Ativa</option>
              <option value="inactive" <?php echo $st === 'inactive' ? 'selected' : ''; ?>>Inativa</option>
              <option value="deploying" <?php echo $st === 'deploying' ? 'selected' : ''; ?>>Deploying</option>
              <option value="error" <?php echo $st === 'error' ? 'selected' : ''; ?>>Erro</option>
            </select>
          </div>
        </div>

        <div style="margin-top:14px;" class="linha">
          <button class="botao" type="submit">Salvar</button>
          <a href="/equipe/aplicacoes">Cancelar</a>
        </div>
      </form>

      <?php if ($id): ?>
        <div style="margin-top:14px;" class="linha">
          <form method="post" action="/equipe/aplicacoes/deploy" onsubmit="return confirm('Iniciar deploy agora?');">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <input type="hidden" name="id" value="<?php echo (int) $id; ?>" />
            <button class="botao sec" type="submit">Deploy</button>
          </form>
        </div>
      <?php endif; ?>

      <?php if ($id): ?>
        <div style="margin-top:14px; border-top:1px solid #e5e7eb; padding-top:14px;">
          <form method="post" action="/equipe/aplicacoes/excluir" onsubmit="return confirm('Excluir aplicação?');">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <input type="hidden" name="id" value="<?php echo (int) $id; ?>" />
            <button class="botao sec" type="submit">Excluir</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
