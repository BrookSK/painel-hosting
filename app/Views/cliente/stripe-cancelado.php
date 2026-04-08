<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = 'Pagamento cancelado';
$clienteNome = '';
$clienteEmail = '';

// Tentar pegar dados do cliente logado
$_cid = \LRV\Core\Auth::clienteId();
if ($_cid) {
    try {
        $s = \LRV\Core\BancoDeDados::pdo()->prepare('SELECT name, email FROM clients WHERE id = ?');
        $s->execute([$_cid]);
        $c = $s->fetch();
        if (is_array($c)) { $clienteNome = (string)($c['name'] ?? ''); $clienteEmail = (string)($c['email'] ?? ''); }
    } catch (\Throwable) {}
}

require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="max-width:500px;margin:0 auto;text-align:center;">
  <div style="font-size:48px;margin-bottom:12px;">⚠️</div>
  <div class="page-title">Pagamento cancelado</div>
  <div class="page-subtitle">O checkout foi cancelado. Nenhuma cobrança foi realizada.</div>

  <div class="card-new" style="margin-top:20px;">
    <p style="font-size:14px;color:#475569;margin-bottom:16px;">Você pode tentar novamente a qualquer momento.</p>
    <div style="display:flex;gap:10px;justify-content:center;">
      <a class="botao" href="/cliente/planos">Ver planos</a>
      <a class="botao ghost" href="/cliente/painel">Voltar ao painel</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
