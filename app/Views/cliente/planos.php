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
        <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#4F46E5;color:#fff;font-size:11px;font-weight:700;padding:3px 14px;border-radius:99px;white-space:nowrap;"><svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;vertical-align:middle;color:#fbbf24;" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> POPULAR</div>
      <?php endif; ?>
      <h2 class="titulo" style="margin-bottom:6px;"><?php echo View::e((string) ($p['name'] ?? '')); ?></h2>
      <p class="texto" style="margin-bottom:12px;"><?php echo View::e((string) ($p['description'] ?? '')); ?></p>

      <div class="linha" style="gap:8px; margin-bottom:12px;">
        <span class="badge-new"><?php echo View::e((string) ($p['cpu'] ?? '')); ?> vCPU</span>
        <span class="badge-new"><?php echo View::e(gb((int) ($p['ram'] ?? 0))); ?> RAM</span>
        <span class="badge-new"><?php echo View::e(gb((int) ($p['storage'] ?? 0))); ?> SSD</span>
      </div>

      <p class="texto" style="font-size:18px; color:#0f172a; margin-bottom:12px;">
        <strong><?php echo View::e(I18n::precoPlano($p)); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></strong>
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
        $channelLabels = ['email' => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> E-mail', 'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> WhatsApp', 'chat' => '🗨️ Chat', 'telefone' => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg> Telefone'];
      ?>
      <?php if (!empty($channels)): ?>
        <div style="margin-bottom:12px;">
          <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Canais de suporte</div>
          <div class="linha" style="gap:6px;">
            <?php foreach ($channels as $ch): ?>
              <span class="badge-new" style="background:#f0fdf4; color:#166534; font-size:11px;">
                <?php echo $channelLabels[$ch] ?? View::e($ch); ?>
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

  <?php if (!(\LRV\Core\Auth::clienteGerenciado() && !\LRV\Core\Auth::estaImpersonando())): ?>
  <!-- Card plano personalizado -->
  <div class="card-new" style="border:2px solid #4F46E5;position:relative;background:linear-gradient(180deg,#fff 0%,#f5f3ff 100%);display:flex;flex-direction:column;">
    <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#0B1C3D,#4F46E5);color:#fff;font-size:11px;font-weight:700;padding:3px 14px;border-radius:99px;white-space:nowrap;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><path d="M9 22v-4h6v4"/></svg> PERSONALIZADO</div>
    <h2 class="titulo" style="margin-bottom:6px;">Plano Sob Medida</h2>
    <p class="texto" style="margin-bottom:12px;">Precisa de mais recursos ou quer que a gente gerencie tudo por você? Montamos um plano exclusivo para o seu projeto.</p>
    <p class="texto" style="font-size:18px;color:#4F46E5;margin-bottom:12px;"><strong>Sob consulta</strong></p>
    <div style="font-size:13px;color:#475569;display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:8px;"><span style="color:#4F46E5;">✓</span> CPU, RAM e disco sob medida</div>
      <div style="display:flex;align-items:center;gap:8px;"><span style="color:#4F46E5;">✓</span> Gerenciamento completo</div>
      <div style="display:flex;align-items:center;gap:8px;"><span style="color:#4F46E5;">✓</span> Deploy, monitoramento e suporte dedicado</div>
      <div style="display:flex;align-items:center;gap:8px;"><span style="color:#4F46E5;">✓</span> Ideal para empresas</div>
    </div>
    <div style="margin-top:auto;display:flex;flex-direction:column;gap:8px;">
      <a href="https://wa.me/5517988093160?text=Ol%C3%A1%2C%20gostaria%20de%20saber%20mais%20sobre%20planos%20personalizados" target="_blank" class="botao" style="display:flex;align-items:center;justify-content:center;gap:8px;background:#25D366;border-color:#25D366;text-align:center;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> WhatsApp Vendas</a>
      <a href="mailto:<?php echo View::e(\LRV\Core\ConfiguracoesSistema::emailAdmin()); ?>?subject=Plano%20Personalizado" class="botao sec" style="display:block;text-align:center;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> Enviar e-mail</a>
    </div>
  </div>
  <?php endif; ?>

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
    <div style="width:48px;height:48px;border-radius:12px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
    <div style="flex:1;min-width:200px;">
      <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Precisa de mais recursos?</div>
      <p style="font-size:13px;color:#64748b;margin:0;line-height:1.6;">
        Seu plano é personalizado. Para alterar recursos, limites ou valores, entre em contato conosco e revisaremos seu plano sob medida.
      </p>
    </div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
    <a href="/cliente/tickets/novo" class="botao sm" style="background:#4F46E5;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg> Abrir ticket</a>
    <a href="mailto:<?php echo View::e(\LRV\Core\ConfiguracoesSistema::emailAdmin()); ?>" class="botao sm sec"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,7 12,13 2,7"/></svg> E-mail</a>
    <a href="https://wa.me/5517988093160" target="_blank" class="botao sm" style="background:#25D366;color:#fff;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> WhatsApp</a>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
