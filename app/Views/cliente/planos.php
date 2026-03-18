<?php

declare(strict_types=1);

use LRV\Core\View;

function gb(int $mb): string {
    if ($mb <= 0) { return '0 GB'; }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Planos de VPS</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Planos de VPS</div>
        <div style="opacity:.9; font-size:13px;">Escolha um plano e faça a assinatura</div>
      </div>
      <div class="linha">
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="grid">
      <?php foreach (($planos ?? []) as $p): ?>
        <div class="card">
          <h2 class="titulo" style="margin-bottom:6px;"><?php echo View::e((string) ($p['name'] ?? '')); ?></h2>
          <p class="texto" style="margin-bottom:12px;"><?php echo View::e((string) ($p['description'] ?? '')); ?></p>
          <div class="linha" style="gap:8px; margin-bottom:12px;">
            <span class="badge"><?php echo View::e((string) ($p['cpu'] ?? '')); ?> vCPU</span>
            <span class="badge"><?php echo View::e(gb((int) ($p['ram'] ?? 0))); ?> RAM</span>
            <span class="badge"><?php echo View::e(gb((int) ($p['storage'] ?? 0))); ?> SSD</span>
          </div>
          <p class="texto" style="font-size:18px; color:#0f172a; margin-bottom:12px;"><strong>R$ <?php echo View::e((string) ($p['price_monthly'] ?? '0.00')); ?>/mês</strong></p>

          <form method="post" action="/cliente/assinar">
            <input type="hidden" name="plan_id" value="<?php echo (int) ($p['id'] ?? 0); ?>" />

            <label style="display:block; font-size:13px; margin-bottom:6px;">Forma de pagamento</label>
            <select class="input" name="billing_type" style="margin-bottom:12px;">
              <option value="PIX">PIX</option>
              <option value="BOLETO">Boleto</option>
              <option value="CREDIT_CARD">Cartão de crédito</option>
            </select>

            <button class="botao" type="submit">Assinar e gerar cobrança</button>
          </form>
        </div>
      <?php endforeach; ?>

      <?php if (empty($planos)): ?>
        <div class="card">
          <h2 class="titulo">Sem planos disponíveis</h2>
          <p class="texto">No momento não existem planos ativos para assinatura.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
