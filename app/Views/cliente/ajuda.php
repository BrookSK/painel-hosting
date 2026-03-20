<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ajuda</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    details { border:1px solid #e5e7eb; border-radius:12px; padding:12px 16px; margin-bottom:10px; }
    summary { cursor:pointer; font-weight:600; font-size:14px; }
    details p { margin:10px 0 0 0; color:#334155; line-height:1.6; font-size:14px; }
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Central de ajuda</div>
        <div style="opacity:.9; font-size:13px;">Dúvidas frequentes</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/tickets">Abrir ticket</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:760px; margin:0 auto;">
      <h1 class="titulo">Perguntas frequentes</h1>

      <details>
        <summary>Como provisionar minha VPS?</summary>
        <p>Após assinar um plano, sua VPS será provisionada automaticamente. Você pode acompanhar o status em <a href="/cliente/vps">Minhas VPS</a>. O processo leva alguns minutos.</p>
      </details>

      <details>
        <summary>Como acessar o terminal da minha VPS?</summary>
        <p>Acesse <a href="/cliente/vps">Minhas VPS</a>, clique em "Terminal" na VPS desejada. O terminal abre diretamente no navegador via WebSocket seguro.</p>
      </details>

      <details>
        <summary>Como cancelar minha assinatura?</summary>
        <p>Entre em contato pela <a href="/contato">página de contato</a> ou abra um <a href="/cliente/tickets/novo">ticket de suporte</a> solicitando o cancelamento.</p>
      </details>

      <details>
        <summary>Minha VPS está suspensa. O que fazer?</summary>
        <p>A suspensão ocorre por inadimplência. Regularize o pagamento e entre em contato pelo suporte para reativação.</p>
      </details>

      <details>
        <summary>Como faço backup da minha VPS?</summary>
        <p>Backups são gerenciados pela equipe. Entre em contato pelo suporte para solicitar um backup manual.</p>
      </details>

      <details>
        <summary>Não consigo fazer login. O que fazer?</summary>
        <p>Verifique seu e-mail e senha. Se esqueceu a senha, entre em contato pelo suporte para redefinição.</p>
      </details>

      <details>
        <summary>Como monitorar o uso de recursos da minha VPS?</summary>
        <p>Acesse <a href="/cliente/monitoramento">Monitoramento</a> para ver gráficos de CPU, RAM e disco em tempo real.</p>
      </details>

      <div style="margin-top:20px; padding-top:16px; border-top:1px solid #e5e7eb;">
        <div class="texto">Não encontrou o que procurava?</div>
        <div class="linha" style="gap:10px; margin-top:8px;">
          <a class="botao" href="/cliente/tickets/novo">Abrir ticket de suporte</a>
          <a class="botao sec" href="/contato">Fale conosco</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
