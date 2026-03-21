<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_topo_links = [
    ['href' => '/cliente/entrar', 'label' => 'Entrar'],
    ['href' => '/equipe/entrar',  'label' => 'Equipe'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Criar conta — <?php echo View::e(SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/../_partials/topo-publico.php'; ?>

  <div class="conteudo">
    <div class="card" style="max-width:620px;margin:40px auto 0;">
      <h1 class="titulo">Criar conta</h1>
      <p class="texto">Depois disso você já consegue entrar no painel do cliente.</p>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/cliente/criar-conta">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="grid">
          <div>
            <label style="display:block;font-size:13px;margin-bottom:6px;">Nome</label>
            <input class="input" type="text" name="nome" value="<?php echo View::e((string) ($nome ?? '')); ?>" autocomplete="name" />
          </div>
          <div>
            <label style="display:block;font-size:13px;margin-bottom:6px;">E-mail</label>
            <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" />
          </div>
          <div>
            <label style="display:block;font-size:13px;margin-bottom:6px;">CPF/CNPJ (opcional)</label>
            <input class="input" type="text" name="cpf_cnpj" value="<?php echo View::e((string) ($cpf_cnpj ?? '')); ?>" />
          </div>
          <div>
            <label style="display:block;font-size:13px;margin-bottom:6px;">Telefone (opcional)</label>
            <input class="input" type="text" name="phone" value="<?php echo View::e((string) ($phone ?? '')); ?>" autocomplete="tel" />
          </div>
          <div>
            <label style="display:block;font-size:13px;margin-bottom:6px;">Celular (opcional)</label>
            <input class="input" type="text" name="mobile_phone" value="<?php echo View::e((string) ($mobile_phone ?? '')); ?>" autocomplete="tel" />
          </div>
          <div>
            <label style="display:block;font-size:13px;margin-bottom:6px;">Senha</label>
            <input class="input" type="password" name="senha" autocomplete="new-password" />
          </div>
        </div>

        <div style="margin-top:16px;">
          <button class="botao" type="submit">Criar conta</button>
        </div>
      </form>
    </div>
  </div>

  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
