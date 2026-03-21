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
    400 => '⚠️',
    401 => '🔒',
    403 => '🚫',
    404 => '🔍',
    419 => '⏱️',
    429 => '🚦',
    500 => '💥',
    502 => '🔌',
    503 => '🛠️',
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
$_icone    = $_icones[$_code]    ?? '❌';
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
