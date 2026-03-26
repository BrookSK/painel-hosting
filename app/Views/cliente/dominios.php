<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$dominiosRaiz = is_array($dominios_raiz ?? null) ? $dominios_raiz : [];
$subdomains   = is_array($subdomains ?? null) ? $subdomains : [];
$erro         = (string)($erro ?? ($_SESSION['_dominios_erro'] ?? ''));
unset($_SESSION['_dominios_erro']);
$sucesso      = (string)($sucesso ?? '');

$pageTitle = I18n::t('dominios.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

function _badgeSub(string $st): string {
    $map = [
        'pending_txt'   => [I18n::t('dominios.aguardando_txt'),   '#fef3c7','#92400e'],
        'pending_cname'  => [I18n::t('dominios.aguardando_cname'), '#e0e7ff','#1e3a8a'],
        'active'         => [I18n::t('dominios.ativo'),            '#dcfce7','#166534'],
        'error'          => [I18n::t('dominios.erro'),             '#fee2e2','#991b1b'],
        'pending_dns'    => [I18n::t('dominios.aguardando_dns'),   '#fef3c7','#92400e'],
    ];
    $d = $map[$st] ?? [$st,'#f1f5f9','#334155'];
    return '<span class="badge-new" style="background:'.$d[1].';color:'.$d[2].';">'.View::e($d[0]).'</span>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::t('dominios.titulo')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('dominios.subtitulo')); ?></div>
  </div>
</div>

<?php if ($erro !== ''): ?>
  <div class="erro"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if ($sucesso !== ''): ?>
  <div class="sucesso"><?php echo View::e(I18n::t('dominios.sucesso_' . $sucesso)); ?></div>
<?php endif; ?>

<!-- Info -->
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
  <span style="font-size:18px;">💡</span>
  <div style="font-size:13px;color:#1e40af;line-height:1.6;">
    <?php echo View::e(I18n::t('dominios.info')); ?>
  </div>
</div>

<div class="grid" style="grid-template-columns:1fr 340px;gap:16px;align-items:start;">
<div>

<!-- Subdomínios -->
<div class="card-new" style="margin-bottom:16px;">
  <div class="card-new-title" style="margin-bottom:6px;">🌐 <?php echo View::e(I18n::t('dominios.subdomains')); ?></div>
  <p style="font-size:13px;color:#64748b;margin-bottom:14px;"><?php echo View::e(I18n::t('dominios.sub_desc')); ?></p>

  <?php if (empty($subdomains)): ?>
    <div style="text-align:center;padding:24px 0;color:#94a3b8;">
      <div style="font-size:28px;margin-bottom:6px;">🔗</div>
      <div style="font-size:13px;"><?php echo View::e(I18n::t('dominios.nenhum_sub')); ?></div>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($subdomains as $s): $sid = (int)($s['id'] ?? 0); $st = (string)($s['status'] ?? ''); ?>
        <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:6px;">
            <div style="font-weight:600;font-size:14px;"><?php echo View::e((string)($s['subdomain'] ?? '')); ?></div>
            <?php echo _badgeSub($st); ?>
          </div>
          <div style="font-size:12px;color:#94a3b8;margin-bottom:8px;">
            <?php echo View::e(I18n::t('dominios.raiz')); ?>: <?php echo View::e((string)($s['root_domain'] ?? '')); ?>
            <?php if (($s['used_by_type'] ?? null) !== null): ?>
              · <?php echo View::e(I18n::t('dominios.em_uso')); ?>: <?php echo View::e((string)$s['used_by_type']); ?> #<?php echo (int)($s['used_by_id'] ?? 0); ?>
            <?php endif; ?>
          </div>
          <?php if (!empty($s['error_msg'])): ?>
            <div style="font-size:12px;color:#ef4444;background:#fef2f2;padding:6px 10px;border-radius:8px;margin-bottom:8px;"><?php echo View::e((string)$s['error_msg']); ?></div>
          <?php endif; ?>

          <?php if ($st === 'pending_txt'): ?>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:8px;font-size:12px;">
              <div style="font-weight:600;margin-bottom:4px;"><?php echo View::e(I18n::t('dominios.passo1')); ?></div>
              <code style="font-size:11px;word-break:break-all;">_lrv-verify.<?php echo View::e((string)($s['subdomain'] ?? '')); ?> TXT "lrv-verify=<?php echo View::e((string)($s['verify_token'] ?? '')); ?>"</code>
            </div>
            <form method="post" action="/cliente/dominios/verificar-txt" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
              <input type="hidden" name="sub_id" value="<?php echo $sid; ?>"/>
              <button class="botao sm" type="submit"><?php echo View::e(I18n::t('dominios.verificar_txt')); ?></button>
            </form>
          <?php elseif ($st === 'pending_cname'): ?>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:8px;font-size:12px;">
              <div style="font-weight:600;margin-bottom:4px;"><?php echo View::e(I18n::t('dominios.passo2')); ?></div>
              <code style="font-size:11px;word-break:break-all;"><?php echo View::e((string)($s['subdomain'] ?? '')); ?> CNAME <?php echo View::e((string)($s['cname_target'] ?? '')); ?></code>
            </div>
            <form method="post" action="/cliente/dominios/verificar-cname" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
              <input type="hidden" name="sub_id" value="<?php echo $sid; ?>"/>
              <button class="botao sm" type="submit"><?php echo View::e(I18n::t('dominios.verificar_cname')); ?></button>
            </form>
          <?php endif; ?>

          <?php if (($s['used_by_type'] ?? null) === null): ?>
            <form method="post" action="/cliente/dominios/remover-sub" style="display:inline;margin-left:6px;"
                  onsubmit="return confirm('<?php echo View::e(I18n::t('dominios.confirmar_remover')); ?>')">
              <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
              <input type="hidden" name="sub_id" value="<?php echo $sid; ?>"/>
              <button class="botao danger sm" type="submit"><?php echo View::e(I18n::t('geral.remover')); ?></button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Domínios raiz (para email) -->
<div class="card-new">
  <div class="card-new-title" style="margin-bottom:6px;">📧 <?php echo View::e(I18n::t('dominios.raiz_titulo')); ?></div>
  <p style="font-size:13px;color:#64748b;margin-bottom:14px;"><?php echo View::e(I18n::t('dominios.raiz_desc')); ?></p>

  <?php if (empty($dominiosRaiz)): ?>
    <div style="text-align:center;padding:20px 0;color:#94a3b8;font-size:13px;"><?php echo View::e(I18n::t('dominios.nenhum_raiz')); ?></div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <?php foreach ($dominiosRaiz as $d): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px;">
          <div>
            <div style="font-weight:600;font-size:13px;"><?php echo View::e((string)($d['domain'] ?? '')); ?></div>
          </div>
          <div style="display:flex;gap:6px;align-items:center;">
            <?php echo _badgeSub((string)($d['status'] ?? '')); ?>
            <a href="/cliente/emails/dominios/instrucoes?id=<?php echo (int)($d['id'] ?? 0); ?>" class="botao ghost sm" style="font-size:11px;">DNS</a>
            <form method="post" action="/cliente/dominios/remover-raiz" style="display:inline;"
                  onsubmit="return confirm('<?php echo View::e(I18n::t('dominios.confirmar_remover')); ?>')">
              <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
              <input type="hidden" name="dominio_id" value="<?php echo (int)($d['id'] ?? 0); ?>"/>
              <button class="botao danger sm" style="font-size:11px;" type="submit">✕</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

</div><!-- /col left -->

<!-- Sidebar: adicionar -->
<div>
  <div class="card-new" style="margin-bottom:14px;">
    <div class="card-new-title" style="margin-bottom:6px;"><?php echo View::e(I18n::t('dominios.add_raiz')); ?></div>
    <p style="font-size:12px;color:#64748b;margin-bottom:10px;"><?php echo View::e(I18n::t('dominios.add_raiz_desc')); ?></p>
    <form method="post" action="/cliente/dominios/adicionar-raiz">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
      <input class="input" type="text" name="domain" placeholder="meudominio.com.br" required style="margin-bottom:8px;"
             pattern="[a-z0-9][a-z0-9\-\.]*\.[a-z]{2,}"/>
      <button class="botao sm" type="submit" style="width:100%;"><?php echo View::e(I18n::t('dominios.adicionar')); ?></button>
    </form>
  </div>

  <div class="card-new" style="margin-bottom:14px;">
    <div class="card-new-title" style="margin-bottom:6px;"><?php echo View::e(I18n::t('dominios.add_sub')); ?></div>
    <p style="font-size:12px;color:#64748b;margin-bottom:10px;"><?php echo View::e(I18n::t('dominios.add_sub_desc')); ?></p>
    <form method="post" action="/cliente/dominios/adicionar-sub">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
      <input class="input" type="text" name="subdomain" placeholder="app.meudominio.com.br" required style="margin-bottom:8px;"
             pattern="[a-z0-9][a-z0-9\-\.]*\.[a-z]{2,}"/>
      <button class="botao sm" type="submit" style="width:100%;"><?php echo View::e(I18n::t('dominios.adicionar')); ?></button>
    </form>
  </div>

  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:8px;"><?php echo View::e(I18n::t('dominios.como_funciona')); ?></div>
    <ol style="padding-left:18px;color:#475569;font-size:12px;line-height:1.9;">
      <li><?php echo View::e(I18n::t('dominios.passo_1')); ?></li>
      <li><?php echo View::e(I18n::t('dominios.passo_2')); ?></li>
      <li><?php echo View::e(I18n::t('dominios.passo_3')); ?></li>
      <li><?php echo View::e(I18n::t('dominios.passo_4')); ?></li>
      <li><?php echo View::e(I18n::t('dominios.passo_5')); ?></li>
    </ol>
  </div>
</div>

</div><!-- /grid -->

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
