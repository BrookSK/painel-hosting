<?php
declare(strict_types=1);
use LRV\Core\View;

$id = $aplicacao['id']??null;
$pageTitle = $id?'Editar aplicacao':'Nova aplicacao';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo $id?'Editar aplicacao':'Nova aplicacao'; ?></div>
<div class="page-subtitle">Apps vinculados as VPS</div>

<div class="card-new" style="max-width:920px;">
  <?php if (!empty($erro)): ?><div class="erro"><?php echo View::e((string)$erro); ?></div><?php endif; ?>

  <form method="post" action="/equipe/aplicacoes/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo View::e((string)($aplicacao['id']??'')); ?>" />

    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">VPS</label>
        <?php $vpsId=(int)(($aplicacao['vps_id']??0)??0); ?>
        <select class="input" name="vps_id">
          <option value="">Selecione...</option>
          <?php foreach (($vps??[]) as $vv): ?>
            <?php $vid=(int)($vv['id']??0); ?>
            <option value="<?php echo $vid; ?>" <?php echo $vid===$vpsId?'selected':''; ?>>#<?php echo $vid; ?> (<?php echo View::e((string)($vv['client_email']??'')); ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Tipo</label>
        <input class="input" type="text" name="type" value="<?php echo View::e((string)($aplicacao['type']??'')); ?>" placeholder="app" />
      </div>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Dominio (opcional)</label>
        <input class="input" type="text" name="domain" value="<?php echo View::e((string)($aplicacao['domain']??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Porta (vazio = automatico)</label>
        <input class="input" type="number" name="port" value="<?php echo View::e((string)($aplicacao['port']??'')); ?>" min="1" max="65535" />
      </div>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Repositorio (opcional)</label>
        <input class="input" type="text" name="repository" value="<?php echo View::e((string)($aplicacao['repository']??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Status</label>
        <?php $st=(string)($aplicacao['status']??'active'); ?>
        <select class="input" name="status">
          <option value="active" <?php echo $st==='active'?'selected':''; ?>>Ativa</option>
          <option value="inactive" <?php echo $st==='inactive'?'selected':''; ?>>Inativa</option>
          <option value="deploying" <?php echo $st==='deploying'?'selected':''; ?>>Deploying</option>
          <option value="error" <?php echo $st==='error'?'selected':''; ?>>Erro</option>
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
        <input type="hidden" name="id" value="<?php echo (int)$id; ?>" />
        <button class="botao sec" type="submit">Deploy</button>
      </form>
      <form method="post" action="/equipe/aplicacoes/excluir" onsubmit="return confirm('Excluir aplicacao?');">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo (int)$id; ?>" />
        <button class="botao sec" type="submit">Excluir</button>
      </form>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
