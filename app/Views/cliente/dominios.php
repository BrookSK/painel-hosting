<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$dominiosRaiz = is_array($dominios_raiz ?? null) ? $dominios_raiz : [];
$subdomains   = is_array($subdomains ?? null) ? $subdomains : [];
$vpsIp        = (string)($vps_ip ?? '');
$erro         = (string)($erro ?? '');
if ($erro === '' && !empty($_SESSION['_dominios_erro'])) {
    $erro = (string)$_SESSION['_dominios_erro'];
}
unset($_SESSION['_dominios_erro']);
$sucesso      = (string)($sucesso ?? '');

// Unificar todos os domínios numa lista só
$todosDominios = [];
foreach ($dominiosRaiz as $d) {
    $todosDominios[] = ['tipo' => 'raiz_email', 'nome' => (string)($d['domain'] ?? ''), 'status' => (string)($d['status'] ?? ''), 'id' => (int)($d['id'] ?? 0), 'data' => $d];
}
foreach ($subdomains as $s) {
    $tipo = ((string)($s['type'] ?? '')) === 'root_vps' ? 'raiz_vps' : 'subdominio';
    $todosDominios[] = ['tipo' => $tipo, 'nome' => (string)($s['subdomain'] ?? ''), 'status' => (string)($s['status'] ?? ''), 'id' => (int)($s['id'] ?? 0), 'data' => $s];
}

$pageTitle = I18n::t('dominios.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

function _badgeDom(string $st): string {
    $map = [
        'pending_txt'   => ['Aguardando TXT',   '#fef3c7','#92400e'],
        'pending_cname'  => ['Aguardando CNAME', '#e0e7ff','#1e3a8a'],
        'pending_dns'    => ['Aguardando DNS',   '#fef3c7','#92400e'],
        'active'         => ['Ativo',            '#dcfce7','#166534'],
        'error'          => ['Erro',             '#fee2e2','#991b1b'],
    ];
    $d = $map[$st] ?? [$st,'#f1f5f9','#334155'];
    return '<span class="badge-new" style="background:'.$d[1].';color:'.$d[2].';">'.View::e($d[0]).'</span>';
}
function _tipoBadge(string $tipo): string {
    return match($tipo) {
        'raiz_email' => '<span class="badge-new" style="background:#e0e7ff;color:#3730a3;font-size:10px;">📧 E-mail</span>',
        'raiz_vps'   => '<span class="badge-new" style="background:#dcfce7;color:#166534;font-size:10px;">🖥️ VPS</span>',
        'subdominio'  => '<span class="badge-new" style="background:#f1f5f9;color:#475569;font-size:10px;">🔗 Sub</span>',
        default       => '',
    };
}
?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::t('dominios.titulo')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">Gerencie seus domínios e subdomínios</div>
  </div>
</div>

<?php if ($erro !== ''): ?>
  <div class="erro"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if ($sucesso !== ''): ?>
  <div class="sucesso">Operação realizada.</div>
<?php endif; ?>

<?php /* IP do servidor só aparece inline nas instruções de registro A dos domínios pendentes */ ?>

<div class="grid" style="grid-template-columns:1fr 340px;gap:16px;align-items:start;">
<div>

<!-- Todos os domínios -->
<div class="card-new">
  <div class="card-new-title" style="margin-bottom:6px;">🌐 Meus domínios</div>
  <p style="font-size:13px;color:#64748b;margin-bottom:14px;">Domínios raiz e subdomínios configurados para sua VPS, aplicações e e-mails.</p>

  <?php if (empty($todosDominios)): ?>
    <div style="text-align:center;padding:32px 0;color:#94a3b8;">
      <div style="font-size:32px;margin-bottom:8px;">🔗</div>
      <div style="font-size:14px;font-weight:600;margin-bottom:4px;">Nenhum domínio cadastrado</div>
      <div style="font-size:13px;">Adicione seu domínio ao lado para começar.</div>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($todosDominios as $dom):
        $dId = $dom['id'];
        $dNome = $dom['nome'];
        $dStatus = $dom['status'];
        $dTipo = $dom['tipo'];
        $dData = $dom['data'];
      ?>
        <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:6px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="font-weight:600;font-size:14px;"><?php echo View::e($dNome); ?></div>
              <?php echo _tipoBadge($dTipo); ?>
            </div>
            <?php echo _badgeDom($dStatus); ?>
          </div>

          <?php if (!empty($dData['error_msg'])): ?>
            <div style="font-size:12px;color:#ef4444;background:#fef2f2;padding:6px 10px;border-radius:8px;margin-bottom:8px;"><?php echo View::e((string)$dData['error_msg']); ?></div>
          <?php endif; ?>

          <?php if (($dData['used_by_type'] ?? null) !== null): ?>
            <div style="font-size:12px;color:#94a3b8;margin-bottom:8px;">Em uso: <?php echo View::e((string)$dData['used_by_type']); ?> #<?php echo (int)($dData['used_by_id'] ?? 0); ?></div>
          <?php endif; ?>

          <?php if ($dStatus === 'pending_cname'): ?>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:8px;font-size:12px;">
              <div style="font-weight:600;margin-bottom:4px;">Crie este registro CNAME no seu DNS:</div>
              <code style="font-size:11px;word-break:break-all;"><?php echo View::e($dNome); ?> CNAME <?php echo View::e((string)($dData['cname_target'] ?? '')); ?></code>
            </div>
            <form method="post" action="/cliente/dominios/verificar-cname" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
              <input type="hidden" name="sub_id" value="<?php echo $dId; ?>"/>
              <button class="botao sm" type="submit">Verificar CNAME</button>
            </form>
          <?php endif; ?>

          <?php if ($dStatus === 'pending_dns'): ?>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px;margin-bottom:8px;font-size:12px;">
              <div style="font-weight:600;margin-bottom:4px;">Crie este registro A no seu DNS:</div>
              <code style="font-size:11px;word-break:break-all;"><?php echo View::e($dNome); ?> A <strong style="cursor:pointer;" onclick="navigator.clipboard.writeText('<?php echo View::e($vpsIp); ?>');this.textContent='Copiado!';setTimeout(()=>this.textContent='<?php echo View::e($vpsIp); ?>',1500)" title="Clique para copiar"><?php echo View::e($vpsIp); ?></strong></code>
            </div>
            <form method="post" action="/cliente/dominios/verificar-a" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
              <input type="hidden" name="sub_id" value="<?php echo $dId; ?>"/>
              <button class="botao sm" type="submit">Verificar registro A</button>
            </form>
          <?php endif; ?>

          <!-- Ações -->
          <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
            <?php if ($dTipo === 'raiz_email'): ?>
              <a href="/cliente/emails/dominios/instrucoes?id=<?php echo $dId; ?>" class="botao ghost sm" style="font-size:11px;">📋 DNS E-mail</a>
              <form method="post" action="/cliente/dominios/remover-raiz" style="display:inline;" onsubmit="return confirm('Remover este domínio?')">
                <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
                <input type="hidden" name="dominio_id" value="<?php echo $dId; ?>"/>
                <button class="botao danger sm" style="font-size:11px;" type="submit">✕ Remover</button>
              </form>
            <?php else: ?>
              <?php if (($dData['used_by_type'] ?? null) === null): ?>
              <form method="post" action="/cliente/dominios/remover-sub" style="display:inline;" onsubmit="return confirm('Remover este domínio?')">
                <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
                <input type="hidden" name="sub_id" value="<?php echo $dId; ?>"/>
                <button class="botao danger sm" style="font-size:11px;" type="submit">✕ Remover</button>
              </form>
              <?php endif; ?>
            <?php endif; ?>
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
    <div class="card-new-title" style="margin-bottom:6px;">Adicionar domínio</div>
    <p style="font-size:12px;color:#64748b;margin-bottom:10px;">Adicione um domínio raiz ou subdomínio. Domínios raiz usam registro A, subdomínios usam CNAME.</p>
    <form method="post" action="/cliente/dominios/adicionar-sub">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
      <input class="input" type="text" name="subdomain" placeholder="meudominio.com.br ou app.meudominio.com.br" required style="margin-bottom:8px;"
             pattern="[a-z0-9][a-z0-9\-\.]*\.[a-z]{2,}"/>
      <button class="botao sm" type="submit" style="width:100%;">Adicionar</button>
    </form>
  </div>

  <div class="card-new" style="margin-bottom:14px;">
    <div class="card-new-title" style="margin-bottom:6px;">Adicionar domínio para e-mail</div>
    <p style="font-size:12px;color:#64748b;margin-bottom:10px;">Para criar contas de e-mail, cadastre o domínio aqui. Você receberá instruções DNS (MX, SPF, DKIM).</p>
    <form method="post" action="/cliente/dominios/adicionar-raiz">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>"/>
      <input class="input" type="text" name="domain" placeholder="meudominio.com.br" required style="margin-bottom:8px;"
             pattern="[a-z0-9][a-z0-9\-\.]*\.[a-z]{2,}"/>
      <button class="botao sm" type="submit" style="width:100%;">Adicionar para e-mail</button>
    </form>
  </div>

  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:8px;">Como funciona?</div>
    <ol style="padding-left:18px;color:#475569;font-size:12px;line-height:1.9;">
      <li>Adicione seu domínio (raiz ou subdomínio)</li>
      <li>Configure o DNS conforme as instruções</li>
      <li>Clique em "Verificar" para confirmar</li>
      <li>Use o domínio em aplicações, deploys ou e-mails</li>
    </ol>
  </div>
</div>

</div><!-- /grid -->

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
