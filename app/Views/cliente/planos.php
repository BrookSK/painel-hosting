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
    <?php $featured = (int)($p['is_featured'] ?? 0) === 1; ?>
    <div class="card-new" style="<?php echo $featured ? 'border:2px solid #4F46E5;position:relative;' : ''; ?>">
      <?php if ($featured): ?>
        <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#4F46E5;color:#fff;font-size:11px;font-weight:700;padding:3px 14px;border-radius:99px;white-space:nowrap;">⭐ POPULAR</div>
      <?php endif; ?>
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

      <?php
        $planAddons = is_array($p['addons'] ?? null) ? $p['addons'] : [];
        if (!empty($planAddons)):
      ?>
        <div style="margin-bottom:12px;font-size:12px;color:#64748b;">
          <div style="margin-bottom:4px;">Serviços adicionais disponíveis:</div>
          <?php foreach ($planAddons as $pa): ?>
            <div style="display:flex;justify-content:space-between;padding:2px 0;">
              <span><?php echo View::e((string)($pa['name'] ?? '')); ?></span>
              <span style="color:#4F46E5;font-weight:600;">+<?php echo View::e(I18n::preco((float)($pa['price'] ?? 0))); ?>/mês</span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php
        // Cliente gerenciado que já tem assinatura: não mostrar botão de contratar
        $_isManagedView = \LRV\Core\Auth::clienteGerenciado() && !\LRV\Core\Auth::estaImpersonando();
        if (!$_isManagedView):
      ?>
      <a href="/cliente/planos/checkout?plan_id=<?php echo (int)($p['id'] ?? 0); ?>" class="botao" style="display:block;text-align:center;">Contratar este plano</a>
      <?php else: ?>
      <div style="text-align:center;padding:10px 0;font-size:13px;color:#64748b;border-top:1px solid #f1f5f9;margin-top:8px;">✓ Seu plano personalizado</div>
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

<?php if (\LRV\Core\Auth::clienteGerenciado() && !\LRV\Core\Auth::estaImpersonando()): ?>
<div class="card-new" style="margin-top:20px;border-left:4px solid #4F46E5;">
  <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
    <div style="width:48px;height:48px;border-radius:12px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;">💬</div>
    <div style="flex:1;min-width:200px;">
      <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Precisa de mais recursos?</div>
      <p style="font-size:13px;color:#64748b;margin:0;line-height:1.6;">
        Seu plano é personalizado. Para alterar recursos, limites ou valores, entre em contato conosco e revisaremos seu plano sob medida.
      </p>
    </div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
    <a href="/cliente/tickets/novo" class="botao sm" style="background:#4F46E5;">🎫 Abrir ticket</a>
    <a href="mailto:<?php echo View::e(\LRV\Core\ConfiguracoesSistema::emailAdmin()); ?>" class="botao sm sec">📧 E-mail</a>
    <a href="https://wa.me/5517988093160" target="_blank" class="botao sm" style="background:#25D366;color:#fff;">💬 WhatsApp</a>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
