<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$_code    = (int) ($code ?? 500);
$_msg     = (string) ($mensagem ?? '');
$_nome    = SistemaConfig::nome();
$_logo    = SistemaConfig::logoUrl();

$_titulos = [
    400 => 'Requisição inválida',
    401 => 'Não autorizado',
    403 => 'Acesso negado',
    404 => 'Página não encontrada',
    419 => 'Sessão expirada',
    429 => 'Muitas requisições',
    500 => 'Erro interno do servidor',
    502 => 'Gateway inválido',
    503 => 'Serviço indisponível',
];

$_icones = [
    400 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#f59e0b;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    401 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    403 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>',
    404 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
    419 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    429 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#f59e0b;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    500 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
    502 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v6"/><path d="M18.4 6.6L15.5 9.5"/><path d="M20 12h-6"/><path d="M18.4 17.4l-2.9-2.9"/><path d="M12 22v-6"/><path d="M5.6 17.4L8.5 14.5"/><path d="M4 12h6"/><path d="M5.6 6.6l2.9 2.9"/></svg>',
    503 => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
];

$_descricoes = [
    400 => 'Os dados enviados não são válidos.',
    401 => 'Você precisa estar autenticado para acessar esta página.',
    403 => 'Você não tem permissão para acessar este recurso.',
    404 => 'A página que você procura não existe ou foi movida.',
    419 => 'Sua sessão expirou. Recarregue a página e tente novamente.',
    429 => 'Você fez muitas requisições em pouco tempo. Aguarde um momento.',
    500 => 'Algo deu errado no servidor. Nossa equipe foi notificada.',
    502 => 'O servidor não conseguiu se comunicar com o serviço upstream.',
    503 => 'O serviço está temporariamente indisponível. Tente novamente em breve.',
];

$_titulo   = $_titulos[$_code]   ?? 'Erro ' . $_code;
$_icone    = $_icones[$_code]    ?? '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
$_descricao = $_descricoes[$_code] ?? 'Ocorreu um erro inesperado.';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo View::e($_code . ' — ' . $_titulo); ?> · <?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body { background: #f8fafc; display: flex; flex-direction: column; min-height: 100vh; }
    .erro-wrap {
      flex: 1; display: flex; align-items: center; justify-content: center;
      padding: 40px 18px;
    }
    .erro-card {
      background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
      padding: 48px 40px; max-width: 520px; width: 100%; text-align: center;
      box-shadow: 0 4px 24px rgba(15,23,42,.06);
    }
    .erro-code {
      font-size: 80px; font-weight: 900; line-height: 1;
      background: linear-gradient(135deg, #0B1C3D, #4F46E5, #7C3AED);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text; margin-bottom: 8px;
    }
    .erro-icone { font-size: 40px; margin-bottom: 12px; }
    .erro-titulo { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
    .erro-desc   { font-size: 15px; color: #64748b; line-height: 1.65; margin-bottom: 28px; }
    .erro-acoes  { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    .erro-id     { margin-top: 24px; font-size: 12px; color: #94a3b8; }
  </style>
</head>
<body>
  <div class="erro-wrap">
    <div class="erro-card">
      <div class="erro-code"><?php echo $_code; ?></div>
      <div class="erro-icone"><?php echo $_icone; ?></div>
      <h1 class="erro-titulo"><?php echo View::e($_titulo); ?></h1>
      <p class="erro-desc"><?php echo View::e($_descricao); ?></p>
      <div class="erro-acoes">
        <a href="/" class="botao">Ir para o início</a>
        <a href="javascript:history.back()" class="botao ghost">Voltar</a>
        <?php if ($_code >= 500): ?>
          <a href="/status" class="botao sec">Ver status do sistema</a>
        <?php endif; ?>
      </div>
      <?php if (!empty($errorId)): ?>
        <div class="erro-id">Referência: #<?php echo (int) $errorId; ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
