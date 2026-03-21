<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function gb(int $mb): string {
    if ($mb <= 0) { return '0 GB'; }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
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
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <?php if (!empty($erro)): ?>
      <div class="card" style="border:1px solid #fecaca; background:#fff1f2;">
        <div style="font-weight:700;">Atenção</div>
        <div class="texto" style="margin:6px 0 0 0;"><?php echo View::e((string) $erro); ?></div>
      </div>
      <div style="height:12px;"></div>
    <?php endif; ?>

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

          <?php
            $channels = [];
            $chRaw = $p['support_channels'] ?? null;
            if (is_string($chRaw) && $chRaw !== '') {
                $dec = json_decode($chRaw, true);
                if (is_array($dec)) { $channels = $dec; }
            } elseif (is_array($chRaw)) {
                $channels = $chRaw;
            }
            $channelLabels = ['email' => '📧 E-mail', 'whatsapp' => '💬 WhatsApp', 'chat' => '🗨️ Chat', 'telefone' => '📞 Telefone'];
          ?>
          <?php if (!empty($channels)): ?>
            <div style="margin-bottom:12px;">
              <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Canais de suporte</div>
              <div class="linha" style="gap:6px;">
                <?php foreach ($channels as $ch): ?>
                  <span class="badge" style="background:#f0fdf4;color:#166534; font-size:11px;">
                    <?php echo View::e($channelLabels[$ch] ?? $ch); ?>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <form method="post" action="/cliente/assinar">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <input type="hidden" name="plan_id" value="<?php echo (int) ($p['id'] ?? 0); ?>" />

            <label style="display:block; font-size:13px; margin-bottom:6px;">Forma de pagamento</label>
            <select class="input" name="billing_type" style="margin-bottom:12px;">
              <option value="PIX">PIX</option>
              <option value="BOLETO">Boleto</option>
            </select>

            <button class="botao" type="submit">Assinar e gerar cobrança</button>
          </form>

          <?php if (trim((string) ($p['stripe_price_id'] ?? '')) !== ''): ?>
            <div style="height:10px;"></div>

            <form method="post" action="/cliente/assinar">
              <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
              <input type="hidden" name="plan_id" value="<?php echo (int) ($p['id'] ?? 0); ?>" />
              <input type="hidden" name="gateway" value="stripe" />
              <button class="botao" type="submit" style="background:#111827;">Assinar com cartão (Stripe)</button>
            </form>
          <?php endif; ?>
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
  <?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
