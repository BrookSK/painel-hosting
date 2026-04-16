<?php
/**
 * Seção de planos dinâmica para landing pages de produto.
 * Variáveis: $planos (array), $_accent (cor accent hex), $_plan_type (string), $_cta_base (string)
 */
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$_planos = is_array($planos ?? null) ? $planos : [];
$_accent = $_accent ?? '#4F46E5';
$_plan_type = $_plan_type ?? 'vps';
$_cta_base = $_cta_base ?? '/cliente/planos/checkout?plan_id=';

if (!empty($_planos)):
?>
<section class="lp-plans-section" id="planos">
  <div class="lp-plans-inner">
    <div style="text-align:center;margin-bottom:48px;">
      <div class="lp-plans-label">Planos</div>
      <h2 class="lp-plans-title">Escolha o plano ideal para você</h2>
      <p class="lp-plans-sub">Todos os planos incluem SSL grátis, suporte técnico e painel de controle.</p>
    </div>

    <div class="lp-plans-grid">
      <?php foreach ($_planos as $p):
        $pId = (int)($p['id'] ?? 0);
        $pName = (string)($p['name'] ?? '');
        $pDesc = (string)($p['description'] ?? '');
        $pPrice = (float)($p['price_monthly'] ?? 0);
        $pPriceUsd = (float)($p['price_monthly_usd'] ?? 0);
        $pCur = (string)($p['currency'] ?? 'BRL');
        $pCpu = (int)($p['cpu'] ?? 0);
        $pRam = round((int)($p['ram'] ?? 0) / 1024);
        $pDisco = round((int)($p['storage'] ?? 0) / 1024);
        $pFeatured = (int)($p['is_featured'] ?? 0) === 1;
        $pMaxSites = $p['max_sites'] ?? null;
        $pMaxDbs = $p['max_databases'] ?? null;
        $specs = json_decode((string)($p['specs_json'] ?? ''), true) ?: [];
      ?>
      <div class="lp-plan-card<?php echo $pFeatured ? ' featured' : ''; ?>">
        <?php if ($pFeatured): ?>
          <div class="lp-plan-badge">Popular</div>
        <?php endif; ?>
        <div class="lp-plan-name"><?php echo View::e($pName); ?></div>
        <?php if ($pDesc !== ''): ?>
          <div class="lp-plan-desc"><?php echo View::e($pDesc); ?></div>
        <?php endif; ?>
        <div class="lp-plan-price">
          <span class="lp-plan-currency"><?php echo $pCur === 'USD' ? 'US$' : 'R$'; ?></span>
          <span class="lp-plan-amount"><?php echo number_format($pCur === 'USD' && $pPriceUsd > 0 ? $pPriceUsd : $pPrice, 2, ',', '.'); ?></span>
          <span class="lp-plan-period">/mês</span>
        </div>
        <ul class="lp-plan-features">
          <li>✓ <?php echo $pCpu; ?> vCPU dedicada</li>
          <li>✓ <?php echo $pRam; ?> GB RAM</li>
          <li>✓ <?php echo $pDisco; ?> GB SSD NVMe</li>
          <?php if ($pMaxSites !== null): ?><li>✓ Até <?php echo (int)$pMaxSites; ?> sites</li><?php endif; ?>
          <?php if ($pMaxDbs !== null): ?><li>✓ Até <?php echo (int)$pMaxDbs; ?> bancos de dados</li><?php endif; ?>
          <?php if (!empty($specs['bandwidth'])): ?><li>✓ <?php echo View::e((string)$specs['bandwidth']); ?> banda</li><?php endif; ?>
          <?php if (!empty($specs['sla'])): ?><li>✓ <?php echo View::e((string)$specs['sla']); ?>% SLA</li><?php endif; ?>
          <li>✓ SSL grátis</li>
          <li>✓ Suporte técnico</li>
        </ul>
        <a href="<?php echo $_cta_base . $pId; ?>" class="lp-plan-cta">Começar agora</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<style>
.lp-plans-section{padding:80px 20px;background:#f8fafc}
.lp-plans-inner{max-width:1100px;margin:0 auto}
.lp-plans-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:<?php echo $_accent; ?>;margin-bottom:10px}
.lp-plans-title{font-size:clamp(24px,3.5vw,36px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em}
.lp-plans-sub{font-size:15px;color:#64748b;max-width:500px;margin:0 auto}
.lp-plans-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px}
.lp-plan-card{background:#fff;border:2px solid #e2e8f0;border-radius:18px;padding:32px 24px;position:relative;transition:all .3s;display:flex;flex-direction:column}
.lp-plan-card:hover{transform:translateY(-6px);box-shadow:0 12px 40px rgba(0,0,0,.1);border-color:<?php echo $_accent; ?>}
.lp-plan-card.featured{border-color:<?php echo $_accent; ?>;box-shadow:0 8px 30px <?php echo $_accent; ?>22}
.lp-plan-badge{position:absolute;top:-12px;right:20px;background:<?php echo $_accent; ?>;color:#fff;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
.lp-plan-name{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:6px}
.lp-plan-desc{font-size:13px;color:#64748b;margin-bottom:16px;line-height:1.5}
.lp-plan-price{margin-bottom:20px;padding-bottom:20px;border-bottom:2px solid #f1f5f9}
.lp-plan-currency{font-size:18px;font-weight:700;color:#64748b;vertical-align:top}
.lp-plan-amount{font-size:42px;font-weight:900;color:<?php echo $_accent; ?>;line-height:1}
.lp-plan-period{font-size:14px;color:#94a3b8}
.lp-plan-features{list-style:none;padding:0;margin:0 0 24px;flex:1}
.lp-plan-features li{padding:8px 0;font-size:13px;color:#475569;border-bottom:1px solid #f8fafc}
.lp-plan-cta{display:block;text-align:center;padding:14px;background:<?php echo $_accent; ?>;color:#fff;border-radius:12px;font-weight:700;font-size:14px;text-decoration:none;transition:opacity .15s,transform .1s}
.lp-plan-cta:hover{opacity:.9;transform:translateY(-2px)}
</style>
<?php else: ?>
<!-- Sem planos cadastrados para este tipo — CTA genérico -->
<section style="padding:60px 20px;background:#f8fafc;text-align:center;" id="planos">
  <div style="max-width:600px;margin:0 auto;">
    <h2 style="font-size:28px;font-weight:800;color:#0f172a;margin-bottom:12px;">Planos em breve</h2>
    <p style="font-size:15px;color:#64748b;margin-bottom:24px;">Estamos preparando planos especiais para este produto. Entre em contato para um plano personalizado.</p>
    <a href="/contato" style="display:inline-block;padding:14px 32px;background:<?php echo $_accent; ?>;color:#fff;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;">Falar com a equipe</a>
  </div>
</section>
<?php endif; ?>
