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
        <label style="display:block;font-size:13px;margin-bottom:6px;">Specs extras (JSON opcional)</label>
        <input class="input" type="text" name="specs_json" value="<?php echo View::e((string)($plano['specs_json']??'')); ?>" />
      </div>
    </div>

    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Canais de suporte (JSON, ex: ["email","whatsapp","chat"])</label>
      <input class="input" type="text" name="support_channels" value="<?php echo View::e((string)($plano['support_channels']??'')); ?>" placeholder='["email","whatsapp"]' />
    </div>

    <div style="margin-top:14px;">
      <button class="botao" type="submit">Salvar</button>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
