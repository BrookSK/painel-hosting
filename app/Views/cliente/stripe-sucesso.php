<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$assinatura = (array) ($assinatura ?? []);
$vps        = is_array($vps ?? null) ? $vps : null;
$erro       = (string) ($erro ?? '');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Checkout concluído</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Assinatura</div>
        <div style="opacity:.9; font-size:13px;">Stripe</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/vps">VPS</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:640px; margin:0 auto;">

      <?php if ($erro !== ''): ?>
        <div class="erro"><?php echo View::e($erro); ?></div>
        <div style="margin-top:14px;">
          <a class="botao sec" href="/cliente/planos">Tentar novamente</a>
        </div>
      <?php else: ?>
        <div style="text-align:center; padding:12px 0 20px;">
          <div style="font-size:48px; margin-bottom:8px;">✅</div>
          <h1 class="titulo" style="margin-bottom:6px;">Pagamento confirmado</h1>
          <p class="texto" style="margin:0;">Sua assinatura foi ativada com sucesso.</p>
        </div>

        <?php if (!empty($assinatura)): ?>
          <div style="background:#0b1220; border-radius:10px; padding:16px; margin-bottom:16px;">
            <div style="font-size:13px; color:#94a3b8; margin-bottom:10px;">Detalhes da assinatura</div>
            <div class="linha" style="justify-content:space-between; margin-bottom:8px;">
              <span style="color:#94a3b8; font-size:13px;">Plano</span>
              <strong><?php echo View::e((string) ($assinatura['plan_name'] ?? '')); ?></strong>
            </div>
            <div class="linha" style="justify-content:space-between; margin-bottom:8px;">
              <span style="color:#94a3b8; font-size:13px;">Valor</span>
              <span>R$ <?php echo View::e(number_format((float) ($assinatura['price_monthly'] ?? 0), 2, ',', '.')); ?>/mês</span>
            </div>
            <?php if (!empty($assinatura['next_due_date'])): ?>
              <div class="linha" style="justify-content:space-between; margin-bottom:8px;">
                <span style="color:#94a3b8; font-size:13px;">Próx. cobrança</span>
                <span><?php echo View::e((string) $assinatura['next_due_date']); ?></span>
              </div>
            <?php endif; ?>
            <div class="linha" style="justify-content:space-between;">
              <span style="color:#94a3b8; font-size:13px;">Status</span>
              <span class="badge" style="background:#dcfce7; color:#166534;">Ativa</span>
            </div>
          </div>
        <?php endif; ?>

        <?php if (is_array($vps)): ?>
          <div style="background:#0b1220; border-radius:10px; padding:16px; margin-bottom:16px;">
            <div style="font-size:13px; color:#94a3b8; margin-bottom:10px;">VPS vinculada</div>
            <div class="linha" style="justify-content:space-between; margin-bottom:8px;">
              <span style="color:#94a3b8; font-size:13px;">ID</span>
              <span>#<?php echo (int) ($vps['id'] ?? 0); ?></span>
            </div>
            <?php if (!empty($vps['label'])): ?>
              <div class="linha" style="justify-content:space-between; margin-bottom:8px;">
                <span style="color:#94a3b8; font-size:13px;">Label</span>
                <span><?php echo View::e((string) $vps['label']); ?></span>
              </div>
            <?php endif; ?>
            <div class="linha" style="justify-content:space-between;">
              <span style="color:#94a3b8; font-size:13px;">Status</span>
              <span><?php echo View::e((string) ($vps['status'] ?? '')); ?></span>
            </div>
          </div>
        <?php endif; ?>

        <div style="background:#1e1b4b; border-radius:10px; padding:14px; margin-bottom:20px; font-size:13px; color:#a5b4fc;">
          <strong>Próximos passos:</strong>
          <ul style="margin:8px 0 0 16px; padding:0; line-height:1.8;">
            <li>Sua VPS será provisionada automaticamente em alguns minutos.</li>
            <li>Você receberá as credenciais de acesso no painel.</li>
            <li>Em caso de dúvidas, abra um ticket de suporte.</li>
          </ul>
        </div>

        <div class="linha" style="gap:10px;">
          <a class="botao" href="/cliente/vps">Ver minhas VPS</a>
          <a class="botao sec" href="/cliente/assinaturas">Ver assinaturas</a>
        </div>
      <?php endif; ?>

    </div>
  </div>
</body>
</html>
