<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'E-mails';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$emails   = is_array($emails ?? null) ? $emails : [];
$dominios = is_array($dominios ?? null) ? $dominios : [];
$webmailUrl = (string) ($webmail_url ?? '');

function badgeEmailStatus(int $active): string {
    return $active
        ? '<span class="badge-new badge-success">Ativo</span>'
        : '<span class="badge-new badge-danger">Inativo</span>';
}

function badgeDominioStatus(string $st): string {
    return match($st) {
        'active'      => '<span class="badge-new badge-success">Ativo</span>',
        'pending_dns' => '<span class="badge-new badge-warning">Aguard. DNS</span>',
        default       => '<span class="badge-new badge-danger">Erro</span>',
    };
}
?>

<div class="page-title">E-mails</div>
<div class="page-subtitle">Contas de e-mail e domínios cadastrados pelos clientes</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e((string) $erro); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['sucesso'])): ?>
  <div class="sucesso">Operação realizada com sucesso.</div>
<?php endif; ?>

<?php if ($webmailUrl !== ''): ?>
  <div style="margin-bottom:16px;">
    <a href="<?php echo View::e($webmailUrl); ?>" target="_blank" rel="noopener" class="botao ghost sm">🌐 Abrir Webmail</a>
  </div>
<?php endif; ?>

<!-- Filtros -->
<form method="get" action="/equipe/emails" class="linha" style="margin-bottom:16px;flex-wrap:wrap;gap:8px;">
  <input class="input" type="text" name="q" placeholder="Buscar e-mail ou cliente..." value="<?php echo View::e((string)($busca??'')); ?>" style="max-width:280px;" />
  <button class="botao sm sec" type="submit">Filtrar</button>
  <?php if (!empty($busca) || ($cliente_id??0) > 0): ?>
    <a href="/equipe/emails" class="botao ghost sm">Limpar</a>
  <?php endif; ?>
</form>

<!-- Contas de e-mail -->
<div class="card-new" style="margin-bottom:20px;">
  <div class="card-header">
    <div class="titulo" style="font-size:15px;margin:0;">Contas de e-mail (<?php echo count($emails); ?>)</div>
  </div>

  <?php if (empty($emails)): ?>
    <p class="texto" style="margin:0;">Nenhuma conta encontrada.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>E-mail</th>
            <th>Cliente</th>
            <th>Quota</th>
            <th>Status</th>
            <th>Criado em</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($emails as $e): ?>
            <?php if (!is_array($e)) continue; ?>
            <tr>
              <td>
                <code style="font-size:13px;"><?php echo View::e((string)($e['email']??'')); ?></code>
              </td>
              <td>
                <a href="/equipe/usuarios?q=<?php echo urlencode((string)($e['client_name']??'')); ?>" style="font-size:13px;">
                  <?php echo View::e((string)($e['client_name']??'')); ?>
                </a>
              </td>
              <td><?php echo View::e((string)($e['quota_mb']??'0')); ?> MB</td>
              <td><?php echo badgeEmailStatus((int)($e['active']??0)); ?></td>
              <td style="font-size:12px;color:#64748b;"><?php echo View::e((string)($e['created_at']??'')); ?></td>
              <td>
                <form method="post" action="/equipe/emails/remover-email" onsubmit="return confirm('Remover esta conta de e-mail?');">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                  <input type="hidden" name="email_id" value="<?php echo (int)($e['id']??0); ?>" />
                  <button class="botao danger sm" type="submit">Remover</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Domínios custom -->
<div class="card-new">
  <div class="card-header">
    <div class="titulo" style="font-size:15px;margin:0;">Domínios custom (<?php echo count($dominios); ?>)</div>
  </div>

  <?php if (empty($dominios)): ?>
    <p class="texto" style="margin:0;">Nenhum domínio cadastrado.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>Domínio</th>
            <th>Cliente</th>
            <th>Status</th>
            <th>Cadastrado em</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dominios as $d): ?>
            <?php if (!is_array($d)) continue; ?>
            <tr>
              <td><code style="font-size:13px;"><?php echo View::e((string)($d['domain']??'')); ?></code></td>
              <td style="font-size:13px;"><?php echo View::e((string)($d['client_name']??'')); ?></td>
              <td><?php echo badgeDominioStatus((string)($d['status']??'')); ?></td>
              <td style="font-size:12px;color:#64748b;"><?php echo View::e((string)($d['created_at']??'')); ?></td>
              <td>
                <form method="post" action="/equipe/emails/remover-dominio" onsubmit="return confirm('Remover este domínio?');">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                  <input type="hidden" name="dominio_id" value="<?php echo (int)($d['id']??0); ?>" />
                  <button class="botao danger sm" type="submit">Remover</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
