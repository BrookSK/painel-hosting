<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle    = 'Ajuda';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<style>
details{border:1px solid #e5e7eb;border-radius:12px;padding:12px 16px;margin-bottom:10px;}
summary{cursor:pointer;font-weight:600;font-size:14px;list-style:none;display:flex;justify-content:space-between;align-items:center;}
summary::after{content:'＋';font-size:16px;color:#4F46E5;}
details[open] summary::after{content:'－';}
details p,details ul{margin:10px 0 0 0;color:#334155;line-height:1.6;font-size:14px;}
details ul{padding-left:18px;}
.faq-section{font-size:15px;font-weight:700;color:#0B1C3D;margin:20px 0 10px;padding-bottom:6px;border-bottom:2px solid #e5e7eb;}
</style>

<div style="margin-bottom:24px;">
  <div class="page-title">Central de Ajuda</div>
  <div class="page-subtitle" style="margin-bottom:0;">Dúvidas frequentes — v<?php echo View::e(\LRV\Core\SistemaConfig::versao()); ?></div>
</div>

<div class="card-new" style="max-width:760px;">
  <div class="card-new-title" style="margin-bottom:16px;">Perguntas frequentes</div>

  <div class="faq-section">🖥️ VPS</div>

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
    <p>Acesse <a href="/cliente/monitoramento">Monitoramento</a>. Você verá gráficos em tempo real com atualização automática a cada 30 segundos e histórico de coletas.</p>
  </details>

  <details>
    <summary>Como fazer backup da minha VPS?</summary>
    <p>Backups são gerenciados pela equipe. Abra um <a href="/cliente/tickets/novo">ticket de suporte</a> solicitando um backup manual e informe o ID da VPS.</p>
  </details>

  <div class="faq-section">📧 E-mail</div>

  <details>
    <summary>Como criar um e-mail com meu domínio?</summary>
    <p>Acesse <a href="/cliente/emails">Meus E-mails</a>, preencha o usuário, o domínio, uma senha segura e clique em "Criar e-mail".</p>
  </details>

  <details>
    <summary>Como acessar o webmail?</summary>
    <p>Na listagem de e-mails, clique em "Webmail" ao lado do endereço desejado.</p>
  </details>

  <details>
    <summary>Como alterar a senha de um e-mail?</summary>
    <p>Em <a href="/cliente/emails">Meus E-mails</a>, clique em "Alterar senha" ao lado do endereço. A alteração é aplicada imediatamente no servidor.</p>
  </details>

  <div class="faq-section">💬 Chat e Suporte</div>

  <details>
    <summary>Como usar o chat ao vivo?</summary>
    <p>Acesse <a href="/cliente/chat">Chat ao vivo</a>. Uma sala é criada automaticamente e nossa equipe será notificada. Use Enter para enviar mensagens. Você também pode enviar emojis e arquivos (imagens, PDF, DOC até 5 MB).</p>
  </details>

  <details>
    <summary>O chat funciona sem WebSocket?</summary>
    <p>Sim. Se o WebSocket não estiver disponível, o chat automaticamente usa polling HTTP (atualiza a cada 3 segundos). Você não precisa fazer nada — a troca é automática.</p>
  </details>

  <details>
    <summary>Qual a diferença entre chat e ticket?</summary>
    <ul>
      <li><strong>Chat:</strong> respostas em tempo real, ideal para dúvidas rápidas e urgências</li>
      <li><strong>Ticket:</strong> registro formal com histórico, ideal para problemas técnicos e acompanhamento</li>
    </ul>
  </details>

  <details>
    <summary>Como cancelar minha assinatura?</summary>
    <p>Abra um <a href="/cliente/tickets/novo">ticket de suporte</a> com o assunto "Cancelamento". O cancelamento é processado em até 1 dia útil.</p>
  </details>

  <div class="faq-section">📦 Aplicações</div>

  <details>
    <summary>Como instalar uma aplicação com 1 clique?</summary>
    <p>Acesse <a href="/cliente/aplicacoes/catalogo">Catálogo de Aplicações</a>, escolha o template desejado (WordPress, Node.js, MySQL, etc.), selecione a VPS, preencha domínio/repositório se necessário e clique em "Instalar". O sistema cria o container automaticamente.</p>
  </details>

  <details>
    <summary>Quais aplicações estão disponíveis?</summary>
    <ul>
      <li><strong>WordPress</strong> — CMS para blog, site ou loja</li>
      <li><strong>Node.js</strong> — APIs e apps web com npm</li>
      <li><strong>PHP Laravel</strong> — API ou app PHP com Apache</li>
      <li><strong>MySQL</strong> — Banco de dados relacional</li>
      <li><strong>Redis</strong> — Cache em memória</li>
      <li><strong>Nginx</strong> — Servidor web e proxy reverso</li>
      <li><strong>Site Estático</strong> — HTML/CSS/JS com Nginx Alpine</li>
    </ul>
  </details>

  <details>
    <summary>Como acompanhar o status da instalação?</summary>
    <p>Após iniciar a instalação, acesse <a href="/cliente/aplicacoes">Minhas Aplicações</a>. O status será atualizado automaticamente: "Instalando" → "Rodando" (ou "Erro" se houver falha).</p>
  </details>

  <div class="faq-section">🔐 Conta</div>

  <details>
    <summary>Não consigo fazer login. O que fazer?</summary>
    <p>Verifique e-mail e senha. Após 10 tentativas incorretas, o IP é bloqueado por 30 minutos. Se esqueceu a senha, use <a href="/cliente/reset-senha">Esqueci minha senha</a>.</p>
  </details>

  <details>
    <summary>Como redefinir minha senha?</summary>
    <p>Na tela de login, clique em "Esqueci minha senha". Informe seu e-mail e você receberá um link válido por 1 hora.</p>
  </details>

  <details>
    <summary>Como ativar a verificação em dois fatores (2FA)?</summary>
    <p>Acesse <a href="/cliente/2fa/configurar">Segurança → 2FA</a> na sidebar. Escaneie o QR code com um app autenticador (Google Authenticator, Authy, etc.) e confirme com o código gerado. Após ativar, você precisará informar o código a cada login.</p>
  </details>

  <details>
    <summary>Como desativar o 2FA?</summary>
    <p>Acesse <a href="/cliente/2fa/configurar">Segurança → 2FA</a> e clique em "Desativar". Será solicitada sua senha atual para confirmar.</p>
  </details>

  <details>
    <summary>Como atualizar meu endereço?</summary>
    <p>Acesse <a href="/cliente/minha-conta">Minha Conta</a>. Na seção "Endereço", preencha os campos e salve.</p>
  </details>

  <details>
    <summary>Como ver minhas assinaturas e cobranças?</summary>
    <p>Acesse <a href="/cliente/assinaturas">Assinaturas</a> para ver o histórico de cobranças, status de pagamento e solicitar reembolso.</p>
  </details>

  <div style="margin-top:24px;padding-top:16px;border-top:1px solid #e5e7eb;">
    <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Não encontrou o que procurava?</p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a class="botao" href="/cliente/chat">Chat ao vivo</a>
      <a class="botao" href="/cliente/tickets/novo">Abrir ticket</a>
      <a class="botao ghost" href="/contato">Fale conosco</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
