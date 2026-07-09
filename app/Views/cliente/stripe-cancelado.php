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
  <div style="font-size:48px;margin-bottom:12px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;vertical-align:middle;color:#f59e0b;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
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
