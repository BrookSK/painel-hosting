<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function gb(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int) round($mb / 1024)) . ' GB';
}

$pageTitle   = 'Planos de VPS';
$clienteNome  = $cliente['name']  ?? '';
$clienteEmail = $cliente['email'] ?? '';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Planos de VPS</h1>
    <p class="page-subtitle">Escolha um plano e faça a assinatura</p>
  </div>
</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e((string) $erro); ?></div>
<?php endif; ?>

<div class="grid">
  <?php foreach (($planos ?? []) as $p): ?>
    <div class="card-new">
      <h2 class="titulo" style="margin-bottom:6px;"><?php echo View::e((string) ($p['name'] ?? '')); ?></h2>
      <p class="texto" style="margin-bottom:12px;"><?php echo View::e((string) ($p['description'] ?? '')); ?></p>

      <div class="linha" style="gap:8px; margin-bottom:12px;">
        <span class="badge-new"><?php echo View::e((string) ($p['cpu'] ?? '')); ?> vCPU</span>
        <span class="badge-new"><?php echo View::e(gb((int) ($p['ram'] ?? 0))); ?> RAM</span>
        <span class="badge-new"><?php echo View::e(gb((int) ($p['storage'] ?? 0))); ?> SSD</span>
      </div>

      <p class="texto" style="font-size:18px; color:#0f172a; margin-bottom:12px;">
        <strong><?php echo View::e(I18n::preco((float)($p['price_monthly'] ?? 0))); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></strong>
      </p>

      <?php
        $channels = [];
        $chRaw = $p['support_channels'] ?? null;
        if (is_string($chRaw) && $chRaw !== '') {
            $dec = json_decode($chRaw, true);
            if (is_array($dec)) $channels = $dec;
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
              <span class="badge-new" style="background:#f0fdf4; color:#166534; font-size:11px;">
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
    <div class="card-new">
      <h2 class="titulo">Sem planos disponíveis</h2>
      <p class="texto">No momento não existem planos ativos para assinatura.</p>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
