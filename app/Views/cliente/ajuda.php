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
    summary { cursor:pointer; font-weight:600; font-size:14px; list-style:none; display:flex; justify-content:space-between; align-items:center; }
    summary::after { content:'＋'; font-size:16px; color:#4F46E5; }
    details[open] summary::after { content:'－'; }
    details p, details ul { margin:10px 0 0 0; color:#334155; line-height:1.6; font-size:14px; }
    details ul { padding-left:18px; }
    .section-title { font-size:15px; font-weight:700; color:#0B1C3D; margin:20px 0 10px; padding-bottom:6px; border-bottom:2px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Central de ajuda</div>
        <div style="opacity:.9; font-size:13px;">Dúvidas frequentes — v1.4.0</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/chat">Chat ao vivo</a>
        <a href="/cliente/tickets">Abrir ticket</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:760px; margin:0 auto;">
      <h1 class="titulo">Perguntas frequentes</h1>

      <div class="section-title">🖥️ VPS</div>

      <details>
        <summary>Como provisionar minha VPS?</summary>
        <p>Após assinar um plano, sua VPS é provisionada automaticamente. Acompanhe o status em <a href="/cliente/vps">Minhas VPS</a>. O processo leva alguns minutos. Se ficar em "Provisionando" por mais de 10 minutos, abra um ticket.</p>
      </details>

      <details>
        <summary>Como acessar o terminal da minha VPS?</summary>
        <p>Acesse <a href="/cliente/vps">Minhas VPS</a> e clique em "Terminal" na VPS com status "Em execução". O terminal abre no navegador via WebSocket seguro — sem necessidade de SSH externo.</p>
      </details>

      <details>
        <summary>Minha VPS está suspensa. O que fazer?</summary>
        <p>A suspensão ocorre por inadimplência. Regularize o pagamento em <a href="/cliente/assinaturas">Assinaturas</a> e entre em contato pelo suporte para reativação imediata.</p>
      </details>

      <details>
        <summary>Como monitorar CPU, RAM e disco?</summary>
        <p>Acesse <a href="/cliente/monitoramento">Monitoramento</a>. Você verá gráficos em tempo real com atualização automática a cada 30 segundos e histórico de coletas. Cores indicam o nível de uso: verde (normal), amarelo (atenção), vermelho (crítico).</p>
      </details>

      <details>
        <summary>Como fazer backup da minha VPS?</summary>
        <p>Backups são gerenciados pela equipe. Abra um <a href="/cliente/tickets/novo">ticket de suporte</a> solicitando um backup manual e informe o ID da VPS.</p>
      </details>

      <div class="section-title">📧 E-mail</div>

      <details>
        <summary>Como criar um e-mail com meu domínio?</summary>
        <p>Acesse <a href="/cliente/emails">Meus E-mails</a>, preencha o usuário (ex: <code>contato</code>), o domínio (ex: <code>seudominio.com</code>), uma senha segura e clique em "Criar e-mail". O domínio precisa estar configurado no servidor de e-mail.</p>
      </details>

      <details>
        <summary>Como acessar o webmail?</summary>
        <p>Na listagem de e-mails, clique em "Webmail" ao lado do endereço desejado. Você será redirecionado para o Roundcube/SOGo com o endereço pré-selecionado.</p>
      </details>

      <details>
        <summary>Como alterar a senha de um e-mail?</summary>
        <p>Em <a href="/cliente/emails">Meus E-mails</a>, clique em "Alterar senha" ao lado do endereço. Digite a nova senha (mínimo 8 caracteres) e confirme. A alteração é aplicada imediatamente no servidor.</p>
      </details>

      <div class="section-title">💬 Chat e Suporte</div>

      <details>
        <summary>Como usar o chat ao vivo?</summary>
        <p>Acesse <a href="/cliente/chat">Chat ao vivo</a>. Uma sala é criada automaticamente e nossa equipe será notificada. O chat funciona em tempo real — você verá quando um agente entrar. Use Enter para enviar mensagens.</p>
      </details>

      <details>
        <summary>Qual a diferença entre chat e ticket?</summary>
        <ul>
          <li><strong>Chat:</strong> respostas em tempo real, ideal para dúvidas rápidas e urgências</li>
          <li><strong>Ticket:</strong> registro formal com histórico, ideal para problemas técnicos, solicitações e acompanhamento</li>
        </ul>
      </details>

      <details>
        <summary>Como cancelar minha assinatura?</summary>
        <p>Abra um <a href="/cliente/tickets/novo">ticket de suporte</a> com o assunto "Cancelamento" ou entre em contato pela <a href="/contato">página de contato</a>. O cancelamento é processado em até 1 dia útil.</p>
      </details>

      <div class="section-title">🔐 Conta</div>

      <details>
        <summary>Não consigo fazer login. O que fazer?</summary>
        <p>Verifique e-mail e senha. Após 10 tentativas incorretas, o IP é bloqueado por 30 minutos. Se esqueceu a senha, entre em contato pelo suporte para redefinição.</p>
      </details>

      <details>
        <summary>Como ver minhas assinaturas e cobranças?</summary>
        <p>Acesse <a href="/cliente/assinaturas">Assinaturas</a> para ver o histórico de cobranças, status de pagamento e solicitar reembolso.</p>
      </details>

      <div style="margin-top:24px; padding-top:16px; border-top:1px solid #e5e7eb;">
        <p class="texto">Não encontrou o que procurava?</p>
        <div class="linha" style="gap:10px; margin-top:8px;">
          <a class="botao" href="/cliente/chat">Chat ao vivo</a>
          <a class="botao" href="/cliente/tickets/novo">Abrir ticket</a>
          <a class="botao ghost" href="/contato">Fale conosco</a>
        </div>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
