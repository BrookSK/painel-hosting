<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle    = I18n::t('ajuda.titulo');
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
  <div class="page-title"><?php echo View::e(I18n::t('ajuda.titulo')); ?></div>
  <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::tf('ajuda.versao', \LRV\Core\SistemaConfig::versao())); ?></div>
</div>

<div class="card-new" style="max-width:760px;">
  <div class="card-new-title" style="margin-bottom:16px;"><?php echo View::e(I18n::t('ajuda.perguntas_frequentes')); ?></div>

  <div class="faq-section"><?php echo View::e(I18n::t('ajuda.secao_vps')); ?></div>

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

  <div class="faq-section"><?php echo View::e(I18n::t('ajuda.secao_email')); ?></div>

  <details>
    <summary>Como criar um e-mail com meu domínio?</summary>
    <p>Acesse <a href="/cliente/emails">Meus E-mails</a>, preencha o usuário, o domínio, uma senha segura e clique em "Criar e-mail".</p>
  </details>

  <details>
    <summary>Como acessar o webmail?</summary>
    <p>Na listagem de e-mails, clique em "Webmail" ao lado do endereço desejado. Você será redirecionado para o webmail do sistema (SOGo). Se você ativou o webmail personalizado no seu domínio, o link apontará para <code>webmail.seudominio.com</code>.</p>
  </details>

  <details>
    <summary>Como configurar meu e-mail no Outlook, Gmail ou Apple Mail?</summary>
    <p>Na tela de <a href="/cliente/emails">Meus E-mails</a>, role até a seção "Configurar em outros apps". Lá você encontra os dados de servidor (IMAP/SMTP) e tutoriais passo a passo para Outlook, Gmail, Apple Mail e Thunderbird, tanto no computador quanto no celular.</p>
  </details>

  <details>
    <summary>O que é a cota de armazenamento?</summary>
    <p>Cada conta de e-mail tem uma cota (espaço em disco). Ao criar um e-mail, você define a cota em MB ou GB. A barra de progresso mostra quanto do total do seu plano já foi usado. Se a cota acabar, você pode fazer upgrade do plano ou reduzir a cota de outras contas.</p>
  </details>

  <details>
    <summary>Como ativar o webmail personalizado (webmail.meudominio.com)?</summary>
    <p>Em <a href="/cliente/emails/dominios">Domínios de E-mail</a>, após o domínio estar ativo, clique em "Ativar webmail personalizado". O sistema pedirá para criar um registro CNAME no seu DNS. Após a propagação, clique em "Verificar webmail" e o acesso será liberado.</p>
  </details>

  <details>
    <summary>Posso usar o Roundcube em vez do webmail padrão?</summary>
    <p>Sim. Acesse o <a href="/cliente/aplicacoes/catalogo">Catálogo de Aplicações</a> e instale o "Roundcube Webmail". Ele será instalado na sua VPS e substituirá o webmail padrão automaticamente. Atenção: o Roundcube consome recursos (CPU, RAM, disco) da sua VPS.</p>
  </details>

  <details>
    <summary>Como alterar a senha de um e-mail?</summary>
    <p>Em <a href="/cliente/emails">Meus E-mails</a>, clique em "Alterar senha" ao lado do endereço. A alteração é aplicada imediatamente no servidor.</p>
  </details>

  <div class="faq-section"><?php echo View::e(I18n::t('ajuda.secao_chat')); ?></div>

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

  <div class="faq-section">Domínios e Subdomínios</div>

  <details>
    <summary>Como funciona o sistema de domínios?</summary>
    <p>Acesse <a href="/cliente/dominios">Domínios</a>. Existem dois tipos: domínios raiz (para e-mail, ex: seudominio.com) e subdomínios (para aplicações e deploys, ex: app.seudominio.com). Primeiro cadastre o domínio raiz, depois adicione subdomínios.</p>
  </details>

  <details>
    <summary>Como verificar um subdomínio?</summary>
    <p>São 2 passos: primeiro crie um registro TXT no seu DNS para provar que o domínio é seu. Depois crie um CNAME apontando para o endereço que o sistema indicar. O IP do servidor nunca é exposto — tudo passa pelo proxy.</p>
  </details>

  <details>
    <summary>Posso usar domínio raiz nas aplicações?</summary>
    <p>Não. Aplicações e deploys usam apenas subdomínios verificados. Isso garante segurança e esconde o IP do servidor. Domínios raiz são usados apenas para criar contas de e-mail.</p>
  </details>

  <details>
    <summary>Onde uso meus subdomínios?</summary>
    <p>Subdomínios verificados aparecem como opção ao instalar aplicações no <a href="/cliente/aplicacoes/catalogo">Catálogo</a> e ao configurar <a href="/cliente/git-deploy">Git Deploy</a>. Cada subdomínio só pode ser usado em um lugar por vez.</p>
  </details>

  <div class="faq-section"><?php echo View::e(I18n::t('ajuda.secao_apps')); ?></div>

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
      <li><strong>Roundcube Webmail</strong> — Webmail moderno, substitui o webmail padrão</li>
    </ul>
  </details>

  <details>
    <summary>Como acompanhar o status da instalação?</summary>
    <p>Após iniciar a instalação, acesse <a href="/cliente/aplicacoes">Minhas Aplicações</a>. O status será atualizado automaticamente: "Instalando" → "Rodando" (ou "Erro" se houver falha).</p>
  </details>

  <div class="faq-section"><?php echo View::e(I18n::t('ajuda.secao_conta')); ?></div>

  <details>
    <summary>Como gerenciar minhas preferências de cookies?</summary>
    <p>Clique em "Cookies" no rodapé de qualquer página ou acesse a <a href="/privacidade#cookies">Política de Privacidade</a>. Você pode ativar ou desativar cookies de analytics, marketing e preferências a qualquer momento. Cookies necessários (sessão e segurança) não podem ser desativados.</p>
  </details>

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
    <p>Acesse <a href="/cliente/assinaturas">Assinaturas</a> para ver suas assinaturas ativas com a VPS vinculada. Para ver o histórico completo de cobranças e faturas, clique em <a href="/cliente/assinaturas/historico">Histórico de cobranças</a>.</p>
  </details>

  <div class="faq-section">Planos e Assinatura</div>

  <details>
    <summary>Como contratar um plano?</summary>
    <p>Na página inicial, escolha o plano desejado e clique em "Contratar". Você será levado a um wizard de 4 passos: detalhes do plano, configuração (quantidade de servidores, período, addons), criação de conta e pagamento. Tudo numa única página, sem precisar de cadastro prévio.</p>
  </details>

  <details>
    <summary>Posso ter mais de uma VPS?</summary>
    <p>Sim. Cada assinatura corresponde a uma VPS. Para ter mais servidores, contrate novas assinaturas. No wizard de contratação, você pode escolher a quantidade de servidores de uma vez.</p>
  </details>

  <details>
    <summary>Existe desconto para períodos maiores?</summary>
    <p>Sim. Ao contratar no período semestral ou anual, você recebe um desconto automático. O desconto é aplicado tanto no plano quanto nos serviços adicionais. Os valores exatos são mostrados no wizard antes de confirmar.</p>
  </details>

  <details>
    <summary>O que são os serviços adicionais?</summary>
    <p>São extras opcionais (como Backup diário ou Suporte WhatsApp) que você pode adicionar ao seu plano. Se você selecionar addons na página inicial, eles já virão marcados no wizard de contratação. O preço dos addons acompanha o período escolhido (mensal, semestral ou anual).</p>
  </details>

  <details>
    <summary>Como ver minhas assinaturas?</summary>
    <p>Acesse <a href="/cliente/assinaturas">Assinaturas</a>. Você verá cards com cada assinatura ativa, a VPS vinculada, status e próximo vencimento. Para ver o histórico de cobranças e faturas, clique em "Histórico de cobranças".</p>
  </details>

  <details>
    <summary>Posso pagar com PIX, Boleto ou Cartão?</summary>
    <p>Sim. Na etapa de pagamento, escolha o método desejado. Para PIX, o QR code e código copia-cola aparecem na própria página. Para Boleto, a linha digitável e link de download aparecem inline. Para Cartão, preencha os dados ali mesmo. Pagamentos em dólar usam Stripe.</p>
  </details>

  <details>
    <summary>Como trocar a moeda de exibição?</summary>
    <p>No topo de qualquer página, ao lado do seletor de idioma, há um seletor de moeda (R$ ou $). Trocar a moeda não altera o idioma e vice-versa. A moeda escolhida define qual gateway de pagamento será usado (Asaas para BRL, Stripe para USD).</p>
  </details>

  <details>
    <summary>Como solicitar reembolso?</summary>
    <p>Acesse <a href="/cliente/assinaturas/historico">Histórico de cobranças</a>. No final da página, expanda a seção "Solicitar reembolso", selecione a assinatura e descreva o motivo. Um ticket será criado automaticamente para a equipe financeira.</p>
  </details>

  <div style="margin-top:24px;padding-top:16px;border-top:1px solid #e5e7eb;">
    <p style="font-size:13px;color:#64748b;margin-bottom:12px;"><?php echo View::e(I18n::t('ajuda.nao_encontrou')); ?></p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a class="botao" href="/cliente/chat"><?php echo View::e(I18n::t('ajuda.chat_vivo')); ?></a>
      <a class="botao" href="/cliente/tickets/novo"><?php echo View::e(I18n::t('ajuda.abrir_ticket')); ?></a>
      <a class="botao ghost" href="/contato"><?php echo View::e(I18n::t('ajuda.fale_conosco')); ?></a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
