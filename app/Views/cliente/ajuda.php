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
details p,details ul,details ol{margin:10px 0 0 0;color:#334155;line-height:1.7;font-size:14px;}
details ul,details ol{padding-left:20px;}
details ol li{margin-bottom:6px;}
details code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:13px;}
.faq-section{font-size:15px;font-weight:700;color:#0B1C3D;margin:24px 0 10px;padding-bottom:6px;border-bottom:2px solid #e5e7eb;}
.faq-section:first-of-type{margin-top:0;}
.tip{background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:10px 12px;margin-top:10px;font-size:13px;color:#0369a1;}
.warn{background:#fefce8;border:1px solid #fef08a;border-radius:8px;padding:10px 12px;margin-top:10px;font-size:13px;color:#854d0e;}
</style>

<div style="margin-bottom:24px;">
  <div class="page-title"><?php echo View::e(I18n::t('ajuda.titulo')); ?></div>
  <div class="page-subtitle" style="margin-bottom:0;">Tutoriais completos para você usar todas as funcionalidades do painel, mesmo sem experiência técnica.</div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- GUIA RAPIDO - O QUE você QUER FAZER? -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;">
  <div class="card-new-title" style="margin-bottom:6px;">O que você quer fazer?</div>
  <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Clique no seu objetivo e veja o caminho completo, passo a passo.</p>

  <details>
    <summary>Quero colocar meu site/projeto no ar pela primeira vez</summary>
    <p>Se você já tem um projeto no GitHub (ou GitLab), siga este caminho:</p>
    <ol>
      <li><strong>Cadastre um domínio</strong> (ou use um temporário):
        <ul>
          <li>Se já tem domínio próprio: vá em <a href="/cliente/domínios">Domínios</a> &rarr; adicioné seu subdomínio &rarr; siga a verificação DNS</li>
          <li>Se não tem domínio: não se preocupe, ao criar o Git Deploy você pode gerar um domínio temporário gratuito</li>
        </ul>
      </li>
      <li><strong>Conecté seu repositório</strong>: vá em <a href="/cliente/git-deploy/novo">Git Deploy &rarr; Novo repositório</a>. Preencha nome, VPS, URL do GitHub, branch e tipo de app.</li>
      <li><strong>Configure a Deploy Key</strong> (só para repos privados): copie a chave gerada e adicione no GitHub (Settings &rarr; Deploy keys).</li>
      <li><strong>Faça o primeiro deploy</strong>: clique em "Deploy agora" no card do repositório.</li>
      <li><strong>Acessé seu site</strong>: clique no link do domínio que aparece no card. Pronto!</li>
    </ol>
    <div class="tip">Tempo estimado: 5 a 10 minutos na primeira vez. Depois disso, cada deploy leva segundos.</div>
  </details>

  <details>
    <summary>Quero criar um e-mail profissional (contato@meusite.com)</summary>
    <ol>
      <li>Vá em <a href="/cliente/domínios">Domínios</a> e adicioné seu domínio raiz (ex: <code>meusite.com.br</code>).</li>
      <li>Configure os registros DNS (MX, SPF, DKIM) conforme as instruções que o sistema mostra.</li>
      <li>Aguarde a propagação DNS (5 min a 2h).</li>
      <li>Vá em <a href="/cliente/emails">E-mails</a> &rarr; crie a conta (ex: contato@meusite.com.br) com uma senha.</li>
      <li>Acesse pelo webmail ou configure no Outlook/Gmail (veja tutoriais na seção E-mails abaixo).</li>
    </ol>
    <div class="tip">Tempo estimado: 10 a 30 minutos (a maior parte e esperar o DNS propagar).</div>
  </details>

  <details>
    <summary>Quero que meu site atualize automaticamente quando eu der git push</summary>
    <ol>
      <li>Vá em <a href="/cliente/git-deploy">Git Deploy</a> &rarr; clique em "Editar" no seu repositório.</li>
      <li>Marque a opção <strong>Auto Deploy</strong> e salve.</li>
      <li>Copie a <strong>URL do Webhook</strong> que apareceu.</li>
      <li>No GitHub: Settings &rarr; Webhooks &rarr; Add webhook &rarr; cole a URL &rarr; Content type: application/json &rarr; push event &rarr; salve.</li>
      <li>Pronto! Agora toda vez que você fizer <code>git push</code>, o servidor atualiza sozinho.</li>
    </ol>
    <div class="tip">Teste: faça uma alteração pequena no código, dê git push e veja o deploy acontecer automaticamente no painel.</div>
  </details>

  <details>
    <summary>Quero instalar WordPress com 1 clique</summary>
    <ol>
      <li>Vá em <a href="/cliente/aplicações/catalogo">Aplicações &rarr; Catálogo</a>.</li>
      <li>Encontre "WordPress" na lista e clique em <strong>Instalar</strong>.</li>
      <li>Selecione a VPS e o domínio (ou gere um temporário).</li>
      <li>Aguarde a instalação (1-3 minutos).</li>
      <li>Acesse o domínio no navegador — o WordPress já está instalado e pronto para uso.</li>
    </ol>
  </details>

  <details>
    <summary>Quero criar um banco de dados para meu projeto</summary>
    <ol>
      <li>Vá em <a href="/cliente/banco-dados/criar">Bancos de Dados &rarr; Criar</a>.</li>
      <li>Selecione a VPS, defina um nome e uma senha.</li>
      <li>Clique em "Criar".</li>
      <li>Use os dados de conexão (host: localhost, porta: 3306, usuário e senha) no arquivo <code>.env</code> ou config do seu projeto.</li>
    </ol>
  </details>

  <details>
    <summary>Quero migrar meu WordPress de outro servidor</summary>
    <ol>
      <li>Vá em <a href="/cliente/migracoes-wp/novo">Migrar WordPress &rarr; Nova Migração</a>.</li>
      <li>Preencha os dados SSH do servidor atual (IP, porta, usuário, senha, caminho do WordPress).</li>
      <li>Preencha os dados do banco MySQL (nome, usuário, senha — estão no wp-config.php).</li>
      <li>Clique em "Iniciar Migração" e aguarde. O sistema copia tudo automaticamente.</li>
      <li>Quando concluir, teste pelo domínio temporário. Depois, ativé seu domínio real.</li>
    </ol>
    <div class="tip">Funciona com sites de qualquer tamanho (10GB, 50GB+). Não precisa fazer zip ou backup manual.</div>
  </details>

  <details>
    <summary>Quero agendar uma tarefa automática (ex: limpeza de cache)</summary>
    <ol>
      <li>Vá em <a href="/cliente/cron-jobs">Cron Jobs</a>.</li>
      <li>Preencha o comando (ex: <code>cd /var/www/meu-projeto && php artisan schedule:run</code>).</li>
      <li>Escolha a frequência (a cada minuto, hora, dia, etc.).</li>
      <li>Salve. O comando vai rodar automaticamente no horário definido.</li>
    </ol>
  </details>

  <details>
    <summary>Meu site está fora do ar. O que fazer?</summary>
    <ol>
      <li><strong>Verifique o status da VPS</strong>: vá em <a href="/cliente/vps">VPS</a> e veja se está "Em execução". Se estiver suspensa, verifique pagamentos.</li>
      <li><strong>Verifique os logs</strong>: no Git Deploy, clique em "Logs servidor" para ver erros do Nginx ou PHP.</li>
      <li><strong>Verifique o monitoramento</strong>: em <a href="/cliente/monitoramento">Monitoramento</a>, veja se CPU/RAM estão no limite (100%).</li>
      <li><strong>Tente um novo deploy</strong>: às vezes um deploy resolve o problema (código travado, processo parado).</li>
      <li><strong>Reinicie o processo</strong> (Node.js): clique em "Reiniciar" no card do Git Deploy.</li>
      <li>Se nada resolver, abra um <a href="/cliente/tickets/novo">ticket</a> com o erro que está vendo.</li>
    </ol>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- GIT DEPLOY -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;">
  <div class="card-new-title" style="margin-bottom:16px;">Git Deploy — Subindo seu projeto do GitHub/GitLab</div>

  <div class="faq-section">Conectar um repositório (passo a passo)</div>

  <details>
    <summary>Como subir meu projeto do GitHub para o servidor?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Git Deploy</strong>.</li>
      <li>Clique no botão <strong>+ Novo repositório</strong> (canto superior direito).</li>
      <li>Preencha o <strong>Nome da integração</strong> — pode ser qualquer nome para você identificar (ex: "Meu Site", "API Backend").</li>
      <li>Em <strong>VPS</strong>, selecione o servidor onde o projeto vai rodar.</li>
      <li>Em <strong>URL do repositório</strong>, cole o link do seu repositório. Para pegar esse link no GitHub:
        <ul>
          <li>Vá no seu repositório no GitHub</li>
          <li>Clique no botão verde <strong>&lt;&gt; Code</strong></li>
          <li>Copie a URL HTTPS (ex: <code>https://github.com/seu-usuário/seu-projeto</code>)</li>
        </ul>
      </li>
      <li>Em <strong>Branch</strong>, coloque o nome da branch que você usa (geralmente <code>main</code> ou <code>master</code>). Se você não sabe, deixe <code>main</code>.</li>
      <li>Em <strong>Subdomínio</strong>, selecione um subdomínio que você já cadastrou em Domínios. Se não tiver nenhum, marque a opção <strong>"Gerar domínio temporário"</strong> — o sistema vai criar um endereço gratuito para você testar (ex: <code>meusite3f2a.apps.seuservidor.com</code>).</li>
      <li>Escolha o <strong>Tipo de aplicação</strong>:
        <ul>
          <li><strong>PHP / Laravel / WordPress</strong> — para projetos em PHP</li>
          <li><strong>Site estático</strong> — para HTML/CSS/JS puro</li>
          <li><strong>Node.js</strong> — para projetos com npm/yarn (Express, Next.js, etc.)</li>
          <li><strong>Python</strong> — para Django, Flask, FastAPI</li>
          <li><strong>C/C++</strong> — para aplicações compiladas</li>
        </ul>
      </li>
      <li>Se escolheu Node.js ou Python, informe a <strong>Porta</strong> onde sua app roda (ex: 3000 para Node, 8000 para Python).</li>
      <li>Clique em <strong>Salvar</strong>.</li>
    </ol>
    <div class="tip">após salvar, o sistema vai gerar uma <strong>Deploy Key</strong> (chave SSH). você precisa copiar essa chave e adicionar no GitHub para que o servidor consiga acessar seu código. Veja o tutorial abaixo.</div>
  </details>

  <details>
    <summary>Como adicionar a Deploy Key no GitHub (repositório privado)?</summary>
    <p>Sé seu repositório e <strong>privado</strong>, você precisa autorizar nosso servidor a acessar o código. Faça assim:</p>
    <ol>
      <li>após salvar o repositório, você será levado para a tela de edição. Copie a <strong>Deploy Key</strong> que aparece na caixa azul (clique em "Copiar").</li>
      <li>No GitHub, va no seu repositório e clique em <strong>Settings</strong> (aba no topo).</li>
      <li>No menu lateral esquerdo, clique em <strong>Deploy keys</strong>.</li>
      <li>Clique em <strong>Add deploy key</strong>.</li>
      <li>Em "Title", coloque um nome qualquer (ex: <code>LRV Cloud</code>).</li>
      <li>Em "Key", cole a chave que você copiou do painel.</li>
      <li>Clique em <strong>Add key</strong>.</li>
    </ol>
    <p>Pronto! Agora o servidor consegue acessar seu código. Se o repositório for <strong>público</strong>, você não precisa fazer isso.</p>
    <div class="tip">No GitLab: va em Settings &rarr; Repository &rarr; Deploy keys &rarr; cole a chave e salve.</div>
  </details>

  <details>
    <summary>Como fazer o deploy (enviar o código para o servidor)?</summary>
    <ol>
      <li>Vá em <a href="/cliente/git-deploy">Git Deploy</a>.</li>
      <li>No card do seu repositório, clique em <strong>&#9654; Deploy agora</strong>.</li>
      <li>Aguarde — o sistema vai conectar no servidor, baixar o código do GitHub e configurar tudo.</li>
      <li>Se der certo, o status muda para "Ativo" e aparece o ultimo commit (hash + mensagem).</li>
      <li>Se der erro, a mensagem de erro aparece em vermelho. Os erros mais comuns sao:
        <ul>
          <li><strong>"Permission denied"</strong> — a Deploy Key não foi adicionada no GitHub. Veja o tutorial acima.</li>
          <li><strong>"Branch not found"</strong> — a branch informada não existe. Verifique o nome correto no GitHub.</li>
          <li><strong>"Could not resolve host"</strong> — o servidor não consegue acessar a internet. Abra um ticket.</li>
        </ul>
      </li>
    </ol>
  </details>

  <div class="faq-section">opções do Git Deploy</div>

  <details>
    <summary>O que faz a opção "Substituir tudo (force overwrite)"?</summary>
    <p><strong>Quando ativada:</strong> cada vez que você fizer deploy, o servidor descarta qualquer alteração feita diretamente nos arquivos e substitui tudo pelo que está no GitHub. É o modo mais seguro para manter o servidor sempre igual ao repositório.</p>
    <p><strong>Quando desativada:</strong> o servidor tenta preservar alterações locais (arquivos que você editou direto no servidor) fazendo um <code>git stash</code> antes de puxar as novidades. Útil se você edita configs diretamente no servidor.</p>
    <div class="tip">Recomendação: deixe ativada. Se você precisa de arquivos diferentes no servidor (como .env), coloque eles no .gitignore — assim o Git não mexe neles.</div>
  </details>

  <details>
    <summary>O que é o Auto Deploy e como configurar?</summary>
    <p>O <strong>Auto Deploy</strong> faz com que, toda vez que você der um <code>git push</code> na branch configurada, o servidor atualize automaticamente — sem precisar clicar em "Deploy agora".</p>
    <p><strong>Como ativar:</strong></p>
    <ol>
      <li>Na tela de edição do repositório, marque a opção <strong>Auto Deploy</strong>.</li>
      <li>Salve. O sistema vai gerar uma <strong>URL de Webhook</strong>.</li>
      <li>Copie essa URL (clique em "Copiar").</li>
      <li>Vá no GitHub, no seu repositório:
        <ul>
          <li>Clique em <strong>Settings</strong> (aba no topo)</li>
          <li>No menu lateral, clique em <strong>Webhooks</strong></li>
          <li>Clique em <strong>Add webhook</strong></li>
          <li>Em "Payload URL", cole a URL que você copiou do painel</li>
          <li>Em "Content type", selecione <strong>application/json</strong></li>
          <li>Em "Which events?", deixe marcado <strong>Just the push event</strong></li>
          <li>Clique em <strong>Add webhook</strong></li>
        </ul>
      </li>
    </ol>
    <p>Pronto! Agora, cada vez que você fizer <code>git push</code> na branch configurada, o deploy acontece sozinho em poucos segundos.</p>
    <div class="tip">No GitLab: Settings &rarr; Webhooks &rarr; cole a URL &rarr; marque "Push events" &rarr; salve.</div>
    <div class="tip">No Bitbucket: Repository settings &rarr; Webhooks &rarr; Add webhook &rarr; cole a URL &rarr; trigger "Repository push".</div>
  </details>

  <details>
    <summary>O que é o comando pós-deploy?</summary>
    <p>É um comando que roda automaticamente apos cada deploy. Serve para instalar dependências ou compilar o projeto. Exemplos:</p>
    <ul>
      <li><strong>PHP/Laravel:</strong> <code>composer install --no-dev</code></li>
      <li><strong>Node.js:</strong> <code>npm install && npm run build</code></li>
      <li><strong>Python:</strong> <code>pip install -r requirements.txt</code></li>
    </ul>
    <p>Preencha esse campo na tela de edição do repositório. Deixe em branco se não precisar.</p>
  </details>

  <details>
    <summary>Configurações PHP (versão, memory_limit, upload)</summary>
    <p>Se seu projeto é PHP, você pode configurar:</p>
    <ul>
      <li><strong>Versão do PHP:</strong> 8.1, 8.2 ou 8.3</li>
      <li><strong>memory_limit:</strong> quanta memória o PHP pode usar (padrão: 256M)</li>
      <li><strong>upload_max_filesize:</strong> tamanho máximo de upload (padrão: 64M)</li>
      <li><strong>post_max_size:</strong> tamanho máximo de um POST (padrão: 64M)</li>
      <li><strong>max_execution_time:</strong> tempo máximo de execução em segundos (padrão: 300)</li>
    </ul>
    <p>Essas configurações ficam na tela de edição do repositório, na seção "configurações PHP".</p>
  </details>

  <details>
    <summary>Como usar o Console (executar comandos no servidor)?</summary>
    <ol>
      <li>Na listagem do Git Deploy, clique em <strong>Console</strong> no card do repositório.</li>
      <li>Um terminal preto vai abrir abaixo do card.</li>
      <li>Digite o comando que deseja executar (ex: <code>ls -la</code>, <code>npm install</code>, <code>php artisan migrate</code>).</li>
      <li>O comando e executado na pasta do seu projeto no servidor.</li>
    </ol>
    <div class="warn">Cuidado: comandos destrutivos (como <code>rm -rf</code>) não tem confirmação. Use com responsabilidade.</div>
  </details>

  <details>
    <summary>Como ver os logs de deploy e logs do servidor?</summary>
    <ul>
      <li><strong>Historico de deploys:</strong> clique em "Historico" no card do repositório. Mostra data, status (sucesso/erro), commit e saida do comando.</li>
      <li><strong>Logs do servidor:</strong> clique em "Logs servidor" para ver logs do Nginx, PHP-FPM e da aplicação em tempo real.</li>
      <li><strong>Logs PM2 (Node.js):</strong> se o tipo e Node.js, clique em "Logs PM2" para ver o que o processo Node esta imprimindo.</li>
    </ul>
  </details>

  <details>
    <summary>Como editar ou remover um repositório?</summary>
    <ul>
      <li><strong>Editar:</strong> clique em "Editar" no card do repositório. você pode mudar a URL, branch, subdomínio, tipo de app e todas as configurações.</li>
      <li><strong>Remover:</strong> clique em "Remover" no card do repositório. Um dialogo vai aparecer com duas opções:
        <ul>
          <li><strong>Sem marcar o checkbox:</strong> remove apenas a integração do painel (o registro, os logs). Os arquivos do projeto <strong>continuam no servidor</strong> — util se você quer manter o site no ar mas não precisa mais da integração Git.</li>
          <li><strong>Marcando "Também apagar os arquivos do servidor":</strong> alem de remover a integração, o sistema apaga <strong>permanentemente</strong> a pasta inteira do projeto no servidor (ex: <code>/var/www/meu-projeto</code>). Essaação e irreversivel — os arquivos não podem ser recuperados.</li>
        </ul>
      </li>
    </ul>
    <div class="warn">Se você marcar "apagar arquivos", o site sai do ar imediatamente. Faça backup antes se precisar dos arquivos.</div>
    <div class="tip">Se o projeto e Node.js e tem um processo PM2 rodando, o sistema também para o processo automaticamente antes de apagar.</div>
  </details>

  <div class="faq-section">Problemas comuns e como resolver</div>

  <details>
    <summary>Erro "Permission denied" ou "Authentication failed"</summary>
    <p><strong>Causa:</strong> o servidor não consegue acessar seu repositório no GitHub porque a Deploy Key não foi adicionada (ou foi adicionada errada).</p>
    <p><strong>Solucao:</strong></p>
    <ol>
      <li>Vá em Git Deploy &rarr; Editar o repositório.</li>
      <li>Copie a Deploy Key (caixa azul).</li>
      <li>No GitHub, va no repositório &rarr; Settings &rarr; Deploy keys.</li>
      <li>Se já existe uma key antiga, remova ela.</li>
      <li>Clique em "Add deploy key" e cole a nova chave.</li>
      <li>Tente o deploy novamente.</li>
    </ol>
    <div class="tip">Se o repositório for <strong>público</strong>, esse erro não deveria acontecer. Nesse caso, verifique se a URL está correta.</div>
  </details>

  <details>
    <summary>Erro "Branch not found" ou "Remote branch not found"</summary>
    <p><strong>Causa:</strong> a branch informada no painel não existe no repositório.</p>
    <p><strong>Solucao:</strong></p>
    <ol>
      <li>No GitHub, clique no seletor de branches (geralmente mostra "main") e veja o nome exato da branch principal.</li>
      <li>Copie o nome (ex: <code>main</code>, <code>master</code>, <code>develop</code>).</li>
      <li>No painel, edite o repositório e corrija o campo "Branch".</li>
      <li>Salve e tente o deploy novamente.</li>
    </ol>
  </details>

  <details>
    <summary>Erro "Could not resolve host" ou "DNS"</summary>
    <p><strong>Causa:</strong> o servidor não está conseguindo acessar a internet (problema de rede/DNS).</p>
    <p><strong>Solucao:</strong> isso e um problema no servidor, não no seu código. Abra um <a href="/cliente/tickets/novo">ticket de suporte</a> informando o erro completo e o ID do repositório. Resolveremos rapidamente.</p>
  </details>

  <details>
    <summary>Deploy deu certo mas o site não abre (erro 502 ou página em branco)</summary>
    <p><strong>Para PHP/Laravel:</strong></p>
    <ul>
      <li>Verifique se o arquivo <code>.env</code> existe no servidor (use o Gerenciador de Arquivos).</li>
      <li>Execute no Console: <code>php artisan key:generate</code> e depois <code>php artisan config:cache</code>.</li>
      <li>Verifique permissões: execute <code>chmod -R 775 storage bootstrap/cache</code>.</li>
    </ul>
    <p><strong>Para Node.js:</strong></p>
    <ul>
      <li>Verifique se a porta informada no painel é a mesma que o app usa.</li>
      <li>Clique em "Logs PM2" para ver se o processo iniciou ou se há erros.</li>
      <li>Se o processo caiu, clique em "Reiniciar".</li>
    </ul>
    <p><strong>Para site estático:</strong></p>
    <ul>
      <li>Verifique se existe um arquivo <code>index.html</code> na raiz do projeto.</li>
    </ul>
  </details>

  <details>
    <summary>Fiz deploy mas as alterações não aparecem no site</summary>
    <p>Possíveis causas:</p>
    <ul>
      <li><strong>Cache do navegador:</strong> pressione Ctrl+Shift+R (ou Cmd+Shift+R no Mac) para forcar reload sem cache.</li>
      <li><strong>Branch errada:</strong> verifique se você deu push na mesma branch configurada no painel.</li>
      <li><strong>Build não rodou:</strong> sé seu projeto precisa de <code>npm run build</code>, coloque isso no campo "Comando pós-deploy".</li>
      <li><strong>Cache do servidor (Laravel):</strong> execute no Console: <code>php artisan cache:clear && php artisan view:clear</code>.</li>
    </ul>
  </details>

  <details>
    <summary>Como colocar variaveis de ambiente (.env) no servidor?</summary>
    <p>O arquivo <code>.env</code> geralmente não vai no Git (ele fica no .gitignore). Entao você precisa criar manualmente:</p>
    <ol>
      <li>Vá em <a href="/cliente/arquivos">Arquivos</a> e navegue ate a pasta do seu projeto.</li>
      <li>Clique em "Novo arquivo" e nomeie como <code>.env</code>.</li>
      <li>Cole o conteúdo do seu <code>.env</code> local (adaptando host do banco, URLs, etc.).</li>
      <li>Salve.</li>
    </ol>
    <div class="tip">Com a opção "Substituir tudo" ativada, o Git não mexe em arquivos que não estao no repositório. Ou seja, seu .env fica seguro entre deploys.</div>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- DOMINIOS E SUBDOMINIOS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Domínios e Subdomínios</div>

  <div class="faq-section">Cadastrar e verificar domínios</div>

  <details>
    <summary>Qual a diferença entre domínio raiz e subdomínio?</summary>
    <ul>
      <li><strong>Domínio raiz</strong> (ex: <code>meusite.com.br</code>) — usado para e-mails. você aponta via registro MX.</li>
      <li><strong>Subdomínio</strong> (ex: <code>app.meusite.com.br</code>) — usado para aplicações, Git Deploy e webmail. você aponta via CNAME ou registro A.</li>
    </ul>
    <p>Primeiro cadastre o domínio raiz, depois adicione os subdomínios que precisar.</p>
  </details>

  <details>
    <summary>Como cadastrar um subdomínio (passo a passo)?</summary>
    <ol>
      <li>Vá em <a href="/cliente/domínios">Domínios</a>.</li>
      <li>Na seção "Adicionar subdomínio", digite o subdomínio completo (ex: <code>app.meusite.com.br</code>).</li>
      <li>Clique em <strong>Adicionar</strong>.</li>
      <li>O sistema vai mostrar um <strong>registro TXT</strong> para você criar no DNS do seu domínio (para provar que o domínio é seu).</li>
      <li>Acesse o painel do seu provedor de DNS (Cloudflare, Registro.br, GoDaddy, etc.) e crie o registro TXT conforme indicado.</li>
      <li>Aguarde a propagação (pode levar de 5 minutos a 2 horas) e clique em <strong>Verificar TXT</strong>.</li>
      <li>após o TXT ser verificado, o sistema pede um <strong>registro CNAME</strong>. Crie no seu DNS apontando para o endereço indicado.</li>
      <li>Clique em <strong>Verificar CNAME</strong>. Se estiver propagado, o subdomínio fica "Ativo" e pronto para usar.</li>
    </ol>
  </details>

  <details>
    <summary>Estou usando domínio temporário. Como mudar para meu domínio real?</summary>
    <ol>
      <li>Primeiro, cadastre e verifiqué seu subdomínio real em <a href="/cliente/domínios">Domínios</a> (veja tutorial acima).</li>
      <li>Depois, va em <a href="/cliente/git-deploy">Git Deploy</a> e clique em <strong>Editar</strong> no repositório.</li>
      <li>No campo "Subdomínio", selecioné seu subdomínio verificado.</li>
      <li>Salve e faca um novo deploy.</li>
    </ol>
    <div class="tip">O domínio temporário continua funcionando apos a troca. você pode usar os dois ao mesmo tempo.</div>
  </details>

  <details>
    <summary>Como usar domínio raiz (sem "www" ou subdomínio) para meu site?</summary>
    <ol>
      <li>Em <a href="/cliente/domínios">Domínios</a>, adicione o domínio raiz (ex: <code>meusite.com.br</code>).</li>
      <li>No DNS do seu provedor, crie um <strong>registro A</strong> apontando para o IP do seu servidor (o IP e exibido na tela de domínios).</li>
      <li>Verifique e pronto — o domínio raiz fica disponível para usar em aplicações e Git Deploy.</li>
    </ol>
  </details>

  <div class="faq-section">Problemas comuns com domínios</div>

  <details>
    <summary>O DNS não propaga (verificação falha)</summary>
    <p><strong>Causas comuns:</strong></p>
    <ul>
      <li><strong>Criou o registro no lugar errado:</strong> o registro TXT/CNAME deve ser criado no provedor que gerencia o DNS do seu domínio (Cloudflare, Registro.br, GoDaddy, etc.). Se não sabe qual e, use o site <a href="https://who.is" target="_blank" rel="noopener">who.is</a> para verificar.</li>
      <li><strong>Tempo de propagação:</strong> normalmente leva 5-30 minutos, mas em casos raros pode levar ate 48 horas. Tente novamente depois.</li>
      <li><strong>Proxy do Cloudflare (nuvem laranja):</strong> se usa Cloudflare, desative o proxy (nuvem cinza) no registro CNAME/A. O proxy pode interferir na verificação.</li>
      <li><strong>Erro de digitacao:</strong> confira se copiou o valor exato que o sistema pediu (sem espaços extras).</li>
    </ul>
  </details>

  <details>
    <summary>Onde encontro o painel de DNS do meu domínio?</summary>
    <p>Depende de onde você comprou/gerencia o domínio:</p>
    <ul>
      <li><strong>Registro.br:</strong> acesse registro.br &rarr; login &rarr; clique no domínio &rarr; "Editar zona DNS"</li>
      <li><strong>Cloudflare:</strong> painel Cloudflare &rarr; selecione o site &rarr; menu "DNS"</li>
      <li><strong>GoDaddy:</strong> Meus Domínios &rarr; DNS &rarr; Gerenciar</li>
      <li><strong>Hostinger:</strong> hPanel &rarr; Domínios &rarr; DNS/Nameservers</li>
      <li><strong>HostGator:</strong> Portal &rarr; Domínios &rarr; Zona de DNS</li>
    </ul>
    <p>Se não sabe onde esta, abra um ticket e nos ajudamos a identificar.</p>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- BANCO DE DADOS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Bancos de Dados</div>

  <div class="faq-section">Criar e gerenciar bancos MySQL</div>

  <details>
    <summary>Como criar um banco de dados?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Bancos de Dados</strong>.</li>
      <li>Clique em <strong>Criar banco de dados</strong>.</li>
      <li>Selecione a <strong>VPS</strong> onde o banco vai ficar.</li>
      <li>Defina um <strong>nome</strong> para o banco e uma <strong>senha</strong> para o usuário.</li>
      <li>Clique em <strong>Criar</strong>.</li>
    </ol>
    <p>O sistema cria o banco MySQL automaticamente no servidor. Os dados de conexão (host, porta, usuário, senha) ficam disponiveis na tela de detalhes do banco.</p>
  </details>

  <details>
    <summary>Como acessar o phpMyAdmin?</summary>
    <ol>
      <li>Em <a href="/cliente/banco-dados">Bancos de Dados</a>, clique no banco desejado.</li>
      <li>Clique no botão <strong>phpMyAdmin</strong>.</li>
      <li>Uma nova aba vai abrir com o phpMyAdmin já logado no seu banco.</li>
    </ol>
    <div class="tip">Se o phpMyAdmin não estiver configurado, o botão vai pedir para configurar primeiro. Basta clicar e aguardar.</div>
  </details>

  <details>
    <summary>Como executar comandos SQL?</summary>
    <ol>
      <li>Clique no banco desejado para abrir os detalhes.</li>
      <li>Na seção <strong>Executar SQL</strong>, digité seu comando (ex: <code>SHOW TABLES;</code>).</li>
      <li>Clique em <strong>Executar</strong>. O resultado aparece logo abaixo.</li>
    </ol>
  </details>

  <details>
    <summary>Como conectar minha aplicação ao banco?</summary>
    <p>Use os dados de conexão exibidos na tela de detalhes do banco:</p>
    <ul>
      <li><strong>Host:</strong> geralmente <code>localhost</code> ou <code>127.0.0.1</code> (se a app roda na mesma VPS)</li>
      <li><strong>Porta:</strong> <code>3306</code></li>
      <li><strong>Banco:</strong> o nome que você definiu</li>
      <li><strong>Usuario:</strong> o nome de usuário gerado</li>
      <li><strong>Senha:</strong> clique em "Ver senha" para copiar</li>
    </ul>
    <p>Coloque esses dados no arquivo de configuração da sua aplicação (<code>.env</code>, <code>config/database.php</code>, etc.).</p>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- ARQUIVOS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Gerenciador de Arquivos</div>

  <details>
    <summary>Como navegar, editar e enviar arquivos no servidor?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Arquivos</strong>.</li>
      <li>Selecione a <strong>VPS</strong> no topo da página.</li>
      <li>você vera as pastas e arquivos do servidor. Navegue clicando nas pastas.</li>
      <li>Para <strong>editar</strong> um arquivo, clique nele. Um editor abre com o conteúdo. Faça as alterações e clique em "Salvar".</li>
      <li>Para <strong>enviar</strong> um arquivo do seu computador, clique em "Upload" e selecione o arquivo.</li>
      <li>Para <strong>baixar</strong> um arquivo, clique com botão direito (ou no ícone de download) e salve no seu computador.</li>
      <li>Para <strong>criar</strong> um novo arquivo ou pasta, use os botões "Novo arquivo" / "Nova pasta".</li>
      <li>Para <strong>renomear ou excluir</strong>, clique com botão direito no item.</li>
    </ol>
    <div class="tip">O gerenciador de arquivos funciona 100% no navegador. você não precisa instalar nenhum programa de FTP.</div>
  </details>

  <details>
    <summary>Nao encontro a pasta do meu projeto Git Deploy em Arquivos. Por que?</summary>
    <p><strong>Isso é normal.</strong> O Gerenciador de Arquivos (menu lateral &rarr; Arquivos) abre na pasta raiz do servidor, que pode não ser a mesma ondé seu projeto foi deployado.</p>
    <p><strong>Motivo técnico:</strong> o Git Deploy coloca seus arquivos em <code>/var/www/nome-do-projeto</code>, mas o gerenciador de arquivos pode abrir em outro diretório (como <code>/root</code> ou <code>/home</code>).</p>
    <p><strong>Como acessar os arquivos do seu projeto Git Deploy:</strong></p>
    <ul>
      <li><strong>Forma rápida:</strong> Vá em <a href="/cliente/git-deploy">Git Deploy</a> e clique no botão <strong>"Arquivos"</strong> (ícone de pasta) no card do repositório. Isso abre o gerenciador de arquivos já posicionado na pasta correta do projeto.</li>
      <li><strong>Forma manual:</strong> Vá em Arquivos, selecione a VPS, e navegue manualmente até o caminho do projeto (ex: <code>/var/www/meu-projeto</code>). O caminho exato aparece no card do Git Deploy, logo abaixo do nome.</li>
    </ul>
    <div class="tip">Dica: sempre use o botão "Arquivos" dentro do Git Deploy. Ele já te leva direto para a pasta certa, sem precisar ficar navegando.</div>
  </details>

  <details>
    <summary>Onde ficam os arquivos do meu projeto?</summary>
    <p>Depende de como você criou o projeto:</p>
    <ul>
      <li><strong>Git Deploy:</strong> os arquivos ficam em <code>/var/www/nome-do-projeto</code>. O caminho exato aparece no card do repositório na página Git Deploy. Use o botão "Arquivos" no card para ir direto.</li>
      <li><strong>aplicação do Catalogo:</strong> o caminho varia conforme o template instalado. Geralmente fica em <code>/var/www/html</code> ou dentro de um container Docker.</li>
      <li><strong>Upload manual:</strong> onde você colocou via Gerenciador de Arquivos ou Terminal.</li>
    </ul>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- TERMINAL -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Terminal (SSH no navegador)</div>

  <details>
    <summary>Como acessar o terminal da minha VPS?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Terminal</strong>.</li>
      <li>Selecione a VPS desejada (precisa estar com status "Em execução").</li>
      <li>Clique em <strong>Conectar</strong>.</li>
      <li>Uma tela preta vai abrir — e o terminal do seu servidor. você pode digitar qualquer comando Linux.</li>
    </ol>
    <div class="tip">O terminal funciona direto no navegador via WebSocket seguro. você não precisa de PuTTY, Terminal ou qualquer programa externo.</div>
  </details>

  <details>
    <summary>Comandos úteis para iniciantes</summary>
    <ul>
      <li><code>ls</code> — listar arquivos da pasta atual</li>
      <li><code>cd /var/www/meu-projeto</code> — entrar numa pasta</li>
      <li><code>cat arquivo.txt</code> — ver o conteúdo de um arquivo</li>
      <li><code>nano arquivo.txt</code> — editar um arquivo (Ctrl+X para sair)</li>
      <li><code>npm install</code> — instalar dependências Node.js</li>
      <li><code>composer install</code> — instalar dependências PHP</li>
      <li><code>php artisan migrate</code> — rodar migrations do Laravel</li>
      <li><code>systemctl restart nginx</code> — reiniciar o Nginx</li>
    </ul>
  </details>

  <details>
    <summary>Cenários práticos: o que executar em cada situação</summary>
    <p><strong>Meu site Laravel esta dando erro 500:</strong></p>
    <ul>
      <li><code>cd /var/www/meu-projeto</code></li>
      <li><code>php artisan config:cache</code></li>
      <li><code>php artisan route:cache</code></li>
      <li><code>chmod -R 775 storage bootstrap/cache</code></li>
      <li><code>cat storage/logs/laravel.log | tail -50</code> (ver ultimas 50 linhas do log)</li>
    </ul>
    <p><strong>Preciso instalar uma extensão PHP:</strong></p>
    <ul>
      <li><code>apt-get update && apt-get install php8.3-extensão</code> (ex: php8.3-gd, php8.3-curl, php8.3-mbstring)</li>
      <li><code>systemctl restart php8.3-fpm</code></li>
    </ul>
    <p><strong>Meu app Node.js caiu:</strong></p>
    <ul>
      <li><code>pm2 status</code> — ver se o processo está rodando</li>
      <li><code>pm2 logs</code> — ver os erros</li>
      <li><code>pm2 restart all</code> — reiniciar todos os processos</li>
    </ul>
    <p><strong>Quero ver quanto espaço em disco estou usando:</strong></p>
    <ul>
      <li><code>df -h</code> — espaço total do disco</li>
      <li><code>du -sh /var/www/*</code> — tamanho de cada pasta de projeto</li>
    </ul>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- CRON JOBS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Cron Jobs (tarefas agendadas)</div>

  <details>
    <summary>O que são Cron Jobs?</summary>
    <p>Cron Jobs são tarefas que rodam automaticamente em horarios que você define. Por exemplo: limpar cache toda meia-noite, enviar e-mails a cada hora, fazer backup do banco todo dia as 3h da manha.</p>
  </details>

  <details>
    <summary>Como criar um Cron Job?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Cron Jobs</strong>.</li>
      <li>Preencha o <strong>comando</strong> que deseja executar (ex: <code>cd /var/www/meu-projeto && php artisan schedule:run</code>).</li>
      <li>Defina a <strong>frequência</strong> usando os campos de minuto, hora, dia, mes e dia da semana. Exemplos:
        <ul>
          <li><strong>A cada minuto:</strong> * * * * *</li>
          <li><strong>A cada hora:</strong> 0 * * * *</li>
          <li><strong>Todo dia a meia-noite:</strong> 0 0 * * *</li>
          <li><strong>Todo dia as 3h:</strong> 0 3 * * *</li>
        </ul>
      </li>
      <li>Clique em <strong>Salvar</strong>.</li>
    </ol>
  </details>

  <details>
    <summary>Como ativar/desativar ou executar manualmente?</summary>
    <ul>
      <li><strong>Ativar/Desativar:</strong> use o botão de toggle ao lado do cron job. Quando desativado, ele não roda ate você ativar de novo.</li>
      <li><strong>Executar agora:</strong> clique em "Executar agora" para rodar o comando imediatamente (sem esperar o horario).</li>
    </ul>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- ARMAZENAMENTO -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Armazenamento (gerenciar espaço em disco)</div>

  <details>
    <summary>Como ver quanto espaço meu servidor está usando?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Armazenamento</strong>.</li>
      <li>Selecione a VPS desejada e clique em <strong>Escanear disco</strong>.</li>
      <li>O sistema conecta no servidor e mostra em tempo real:
        <ul>
          <li>Barra de uso total (verde = OK, amarelo = atenção, vermelho = quase cheio)</li>
          <li>Tamanho de cada aplicação e deploy instalado</li>
          <li>Espaço usado por arquivos temporários (/tmp)</li>
          <li>Espaço usado por logs do servidor (/var/log)</li>
          <li>Espaço usado por "outros" (sistema, caches, pacotes)</li>
        </ul>
      </li>
    </ol>
  </details>

  <details>
    <summary>Como liberar espaço no servidor?</summary>
    <p>Na tela de Armazenamento, após escanear, você tem várias opções de limpeza:</p>
    <ul>
      <li><strong>Limpar /tmp:</strong> apaga arquivos temporários com mais de 1 dia. Seguro — não afeta suas aplicações.</li>
      <li><strong>Limpar logs antigos:</strong> trunca logs grandes e remove logs compactados. Os logs recomeçam do zero.</li>
      <li><strong>Limpar caches (npm/composer/pip):</strong> remove caches de gerenciadores de pacotes. Na próxima instalação, eles serão recriados automaticamente.</li>
      <li><strong>Apagar uma aplicação/deploy específico:</strong> clique em "Apagar" ao lado do item. Remove permanentemente todos os arquivos daquele projeto.</li>
    </ul>
    <div class="warn">Apagar um projeto é irreversível. Faça backup antes se precisar dos arquivos.</div>
    <div class="tip">Depois de limpar, o sistema escaneia novamente automaticamente para mostrar o espaço liberado.</div>
  </details>

  <details>
    <summary>O disco está quase cheio (vermelho). O que fazer?</summary>
    <ol>
      <li>Escaneie o disco para identificar o que está ocupando mais espaço.</li>
      <li>Geralmente os maiores vilões são: uploads antigos (wp-content/uploads), backups locais, e logs grandes.</li>
      <li>Use as opções de limpeza para remover o que não precisa.</li>
      <li>Se mesmo após limpar continuar cheio, considere fazer upgrade do plano para mais armazenamento.</li>
    </ol>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- BACKUPS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Backups</div>

  <details>
    <summary>Como funcionam os backups?</summary>
    <p>O sistema faz backups automáticos diários da sua VPS. você também pode criar backups manuais a qualquer momento. Cada backup inclui arquivos e bancos de dados.</p>
  </details>

  <details>
    <summary>Como criar um backup manual?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Backups</strong>.</li>
      <li>Clique em <strong>Criar backup agora</strong>.</li>
      <li>Aguarde — o backup pode levar de 1 a 15 minutos dependendo do tamanho dos dados.</li>
      <li>Quando pronto, ele aparece na lista com data/hora e tamanho.</li>
    </ol>
  </details>

  <details>
    <summary>Como restaurar um backup?</summary>
    <ol>
      <li>Na lista de backups, encontre o backup desejado pela data.</li>
      <li>Clique em <strong>Restaurar</strong>.</li>
      <li>Confirme. O sistema vai restaurar todos os arquivos e bancos de dados ao estado daquele backup.</li>
    </ol>
    <div class="warn">A restauracao substitui os dados atuais. Se você fez alterações depois do backup, elas seráo perdidas. Faça um backup manual antes de restaurar, por segurança.</div>
  </details>

  <details>
    <summary>Como baixar um backup?</summary>
    <p>Na lista de backups, clique em <strong>Baixar</strong> ao lado do backup desejado. O arquivo .tar.gz será baixado para o seu computador.</p>
  </details>

  <details>
    <summary>Com que frequência devo fazer backup?</summary>
    <p>O sistema já faz backups automáticos diários. Mas recomendamos fazer um backup <strong>manual</strong> antes de:</p>
    <ul>
      <li>Fazer alterações grandes no código ou banco de dados</li>
      <li>Atualizar plugins ou temas do WordPress</li>
      <li>Mudar configurações do servidor</li>
      <li>Migrar de domínio</li>
    </ul>
    <div class="tip">Backup manual + deploy = segurança. Se algo der errado no deploy, você restaura o backup e volta ao normal em segundos.</div>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- aplicações -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">aplicações (Catalogo com 1 clique)</div>

  <details>
    <summary>Como instalar WordPress, Node.js ou outra aplicação?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>aplicações</strong> &rarr; <strong>Catalogo</strong>.</li>
      <li>Escolha o template que deseja instalar (WordPress, Node.js, PHP Laravel, MySQL, Redis, Nginx, etc.).</li>
      <li>Selecione a <strong>VPS</strong> onde vai instalar.</li>
      <li>Preencha os campos obrigatórios (domínio, repositório, etc. — depende do template).</li>
      <li>Clique em <strong>Instalar</strong>.</li>
      <li>Aguarde — o status vai de "Instalando" para "Rodando" quando concluir.</li>
    </ol>
    <div class="tip">Se a instalação falhar, o status muda para "Erro". Clique para ver a mensagem de erro e tente novamente.</div>
  </details>

  <details>
    <summary>Quais aplicações estao disponiveis?</summary>
    <ul>
      <li><strong>WordPress</strong> — blog, site ou loja virtual</li>
      <li><strong>PHP Laravel</strong> — framework PHP moderno</li>
      <li><strong>Node.js</strong> — APIs e aplicações com JavaScript</li>
      <li><strong>Python (Django/Flask)</strong> — aplicações Python</li>
      <li><strong>MySQL</strong> — banco de dados relacional</li>
      <li><strong>PostgreSQL</strong> — banco de dados avançado</li>
      <li><strong>Redis</strong> — cache e filas em memória</li>
      <li><strong>Nginx</strong> — servidor web e proxy reverso</li>
      <li><strong>Site Estatico</strong> — HTML/CSS/JS puro</li>
      <li><strong>Roundcube Webmail</strong> — cliente de e-mail web</li>
    </ul>
  </details>

  <details>
    <summary>Qual a diferença entre aplicações e Git Deploy?</summary>
    <ul>
      <li><strong>Catalogo de aplicações:</strong> instala templates prontos com 1 clique. Ideal para quem quer algo funcionando rápido sem ter um repositório Git.</li>
      <li><strong>Git Deploy:</strong> conecta um repositório seu (GitHub/GitLab) e faz deploy do seu código personalizado. Ideal para desenvolvedores quejátem um projeto.</li>
    </ul>
    <p><strong>Quando usar cada um:</strong></p>
    <ul>
      <li>"Quero um WordPress rápido" &rarr; Catalogo</li>
      <li>"Tenho meu código no GitHub e quero publicar" &rarr; Git Deploy</li>
      <li>"Preciso de um banco MySQL" &rarr; Catalogo (ou Bancos de Dados)</li>
      <li>"Quero um site custom com React/Next.js" &rarr; Git Deploy</li>
    </ul>
  </details>

  <details>
    <summary>A instalação falhou (status "Erro"). O que fazer?</summary>
    <ol>
      <li>Clique no card da aplicação para ver a <strong>mensagem de erro</strong>.</li>
      <li>Erros comuns:
        <ul>
          <li><strong>"Porta em uso"</strong> — outra aplicação já está usando essa porta. Altere a porta da nova app.</li>
          <li><strong>"Disco cheio"</strong> — sua VPS não tem espaço suficiente. Apague arquivos desnecessários ou faça upgrade.</li>
          <li><strong>"Timeout"</strong> — a instalação demorou demais (servidor lento). Tente novamente.</li>
        </ul>
      </li>
      <li>Se não entender o erro, copie a mensagem e cole num <a href="/cliente/tickets/novo">ticket</a>.</li>
    </ol>
  </details>

  <details>
    <summary>Como deletar uma aplicação e liberar espaço?</summary>
    <p>Ao clicar no botão <strong>✕</strong> ao lado da aplicação, um diálogo aparece com opções:</p>
    <ul>
      <li><strong>Apagar os arquivos do servidor</strong> — remove permanentemente a pasta do projeto no servidor, liberando o espaço em disco (WordPress de 30GB+ volta a zero).</li>
      <li><strong>Apagar o banco de dados vinculado</strong> — dropa o banco MySQL associado. Todos os dados (posts, páginas, usuários, etc.) serão perdidos.</li>
    </ul>
    <p>Se você não marcar nenhuma opção, apenas o registro é removido do painel — os arquivos e o banco continuam no servidor (ocupando espaço).</p>
    <div class="warn">Essas ações são irreversíveis. Faça um backup antes se precisar manter os dados para o futuro.</div>
    <div class="tip">Dica: se quiser apenas "pausar" a aplicação sem apagar nada, você pode alterar o status para "Inativa" ao invés de deletar.</div>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- EMAILS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">E-mails Profissionais</div>

  <div class="faq-section">Criar e usar e-mails com seu domínio</div>

  <details>
    <summary>Como criar um e-mail com meu domínio (ex: contato@meusite.com)?</summary>
    <ol>
      <li>Primeiro, cadastré seu domínio raiz em <a href="/cliente/domínios">Domínios</a> (veja a seção de Domínios acima).</li>
      <li>Depois, va em <a href="/cliente/emails">E-mails</a>.</li>
      <li>Preencha o <strong>usuário</strong> (parte antes do @, ex: "contato"), selecione o <strong>domínio</strong> e defina uma <strong>senha</strong>.</li>
      <li>Defina a <strong>cota</strong> de armazenamento (quanto espaço essa caixa pode usar).</li>
      <li>Clique em <strong>Criar e-mail</strong>.</li>
    </ol>
    <p>Pronto! vocêjápode usar o e-mail pelo webmail ou configurar no Outlook/Gmail.</p>
  </details>

  <details>
    <summary>Como configurar no Outlook, Gmail ou celular?</summary>
    <p>Em <a href="/cliente/emails">E-mails</a>, role ate a seção "Configurar em outros apps". La você encontra:</p>
    <ul>
      <li>Dados do servidor (IMAP, SMTP, portas)</li>
      <li>Tutorial passo a passo para <strong>Outlook</strong> (PC e celular)</li>
      <li>Tutorial passo a passo para <strong>Gmail</strong> (importar no Gmail)</li>
      <li>Tutorial passo a passo para <strong>Apple Mail</strong> (iPhone/Mac)</li>
      <li>Tutorial passo a passo para <strong>Thunderbird</strong></li>
    </ul>
    <p>Resumo rápido dos dados:</p>
    <ul>
      <li><strong>IMAP:</strong> porta 993 com SSL/TLS</li>
      <li><strong>SMTP:</strong> porta 587 com STARTTLS</li>
      <li><strong>Usuario:</strong> seu e-mail completo (ex: contato@meusite.com)</li>
      <li><strong>Senha:</strong> a que você definiu ao criar</li>
    </ul>
  </details>

  <details>
    <summary>Como acessar pelo webmail (navegador)?</summary>
    <p>Na listagem de e-mails, clique em <strong>Webmail</strong> ao lado do endereço. você será levado para o webmail do sistema.</p>
    <p>Se quiser um endereço personalizado (tipo <code>webmail.meusite.com</code>), va em <a href="/cliente/emails/domínios">Domínios de E-mail</a> e ative o "Webmail personalizado". O sistema vai pedir para criar um registro CNAME no seu DNS.</p>
  </details>

  <details>
    <summary>Como configurar DNS para o e-mail funcionar?</summary>
    <p>Ao adicionar um domínio de e-mail, o sistema mostra exatamente quais registros criar no seu DNS:</p>
    <ul>
      <li><strong>MX</strong> — direciona os e-mails para nosso servidor</li>
      <li><strong>SPF</strong> — autoriza nosso servidor a enviar e-mails em nome do seu domínio</li>
      <li><strong>DKIM</strong> — assina os e-mails para evitar spam</li>
      <li><strong>DMARC</strong> — politica de autenticação</li>
    </ul>
    <p>Crie esses registros no painel do seu provedor de DNS (Cloudflare, Registro.br, etc.). A verificação e automática apos propagação.</p>
  </details>

  <div class="faq-section">Problemas comuns com e-mail</div>

  <details>
    <summary>Meus e-mails estao indo para o spam do destinatario</summary>
    <p><strong>Causas mais comuns:</strong></p>
    <ul>
      <li><strong>DNS incompleto:</strong> verifique se todos os registros (MX, SPF, DKIM, DMARC) estao configurados corretamente.</li>
      <li><strong>Domínio novo:</strong> domínios recem-registrados tem reputacao baixa. A situação melhora com o tempo.</li>
      <li><strong>Conteudo do e-mail:</strong> evite usar palavras excessivamente promocionais, imagens sem texto, ou links encurtados.</li>
    </ul>
    <p><strong>O que fazer:</strong></p>
    <ol>
      <li>Em <a href="/cliente/emails/domínios">Domínios de E-mail</a>, verifique se todos os registros mostram status verde (verificado).</li>
      <li>Envie e-mails de teste para o Gmail e verifique o cabecalho ("Mostrar original") — procure por "spf=pass", "dkim=pass" e "dmarc=pass".</li>
      <li>Se algum estiver "fail", revise o registro DNS correspondente.</li>
    </ol>
  </details>

  <details>
    <summary>Nao consigo enviar e-mails (erro de SMTP)</summary>
    <ul>
      <li>Verifique se está usando a <strong>porta correta</strong>: 587 com STARTTLS (não use a 25, ela e bloqueada).</li>
      <li>Verifique se o <strong>usuário</strong> e o e-mail completo (com @domínio.com) e a <strong>senha</strong> está correta.</li>
      <li>Se usa Cloudflare, certifique-se de que o registro MX <strong>não esta com proxy</strong> (nuvem cinza, não laranja).</li>
    </ul>
  </details>

  <details>
    <summary>Quero receber e-mails de um domínio no Gmail</summary>
    <p>você pode configurar o Gmail para buscar seus e-mails profissionais. Veja o tutorial completo em <a href="/cliente/emails">E-mails</a> &rarr; seção "Configurar em outros apps" &rarr; Gmail.</p>
    <p>Resumo: Gmail &rarr; configurações &rarr; Contas e importacao &rarr; Adicionar conta de e-mail &rarr; IMAP &rarr; use os dados (servidor, porta 993, SSL).</p>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- TICKETS E SUPORTE -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Tickets e Suporte</div>

  <details>
    <summary>Como abrir um ticket de suporte?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Tickets</strong>.</li>
      <li>Clique em <strong>Novo Ticket</strong>.</li>
      <li>Preencha o <strong>assunto</strong> (resumo do problema).</li>
      <li>Escolha a <strong>prioridade</strong> (baixa, media ou alta).</li>
      <li>Escreva sua <strong>mensagem</strong> detalhando o que está acontecendo.</li>
      <li>Se quiser, anexe um arquivo (print de tela, log, etc. — max 5 MB).</li>
      <li>Clique em <strong>Enviar</strong>.</li>
    </ol>
    <p>você recebera notificação por e-mail quando a equipe responder.</p>
  </details>

  <details>
    <summary>Qual a diferença entre ticket e chat?</summary>
    <ul>
      <li><strong>Chat:</strong> respostas em tempo real. Ideal para dúvidas rápidas e urgencias. Funciona em horário comercial.</li>
      <li><strong>Ticket:</strong> registro formal com histórico completo. Ideal para problemas técnicos, solicitacoes e acompanhamento. A equipe responde assim que possível.</li>
    </ul>
    <div class="tip">Use o chat para coisas rápidas. Use tickets para problemas que precisam de investigacao ou que você quer ter um histórico.</div>
  </details>

  <details>
    <summary>Como usar o chat ao vivo?</summary>
    <ol>
      <li>Clique no icone de chat no canto inferior direito de qualquer página.</li>
      <li>Escolha "Falar com atendente".</li>
      <li>Descreva brevementé seu problema e clique em "Iniciar".</li>
      <li>Uma sala será criada e a equipe será notificada. Aguarde a resposta (geralmente poucos minutos).</li>
    </ol>
    <p>você pode enviar emojis e arquivos (imagens, PDF, documentos ate 5 MB) direto no chat.</p>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- MONITORAMENTO -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Monitoramento</div>

  <details>
    <summary>Como ver o uso de CPU, RAM e disco da minha VPS?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Monitoramento</strong>.</li>
      <li>Selecione a VPS desejada.</li>
      <li>você vera tres indicadores em tempo real: <strong>CPU</strong>, <strong>RAM</strong> e <strong>Disco</strong>.</li>
      <li>Abaixo, um histórico com graficos mostrando a evolucao ao longo do tempo.</li>
    </ol>
    <p>A página atualiza automaticamente a cada 30 segundos.</p>
    <div class="tip">Se a CPU ou RAM estiverem sempre acima de 80%, pode ser hora de fazer upgrade do plano para mais recursos.</div>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- CONTA E SEGURANCA -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Conta e Seguranca</div>

  <div class="faq-section">Gerenciar sua conta</div>

  <details>
    <summary>Como alterar minha senha?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Minha Conta</strong>.</li>
      <li>Na seção "Alterar senha", preencha a senha atual, a nova senha e confirme.</li>
      <li>Clique em <strong>Salvar</strong>.</li>
    </ol>
  </details>

  <details>
    <summary>Esqueci minha senha. Como recuperar?</summary>
    <ol>
      <li>Na tela de login, clique em <strong>"Esqueci minha senha"</strong>.</li>
      <li>Informé seu e-mail de cadastro.</li>
      <li>você recebera um e-mail com um link para redefinir a senha (valido por 1 hora).</li>
      <li>Clique no link, defina uma nova senha e pronto.</li>
    </ol>
    <div class="tip">Se não receber o e-mail, verifique a pasta de spam. Se mesmo assim não chegar, abra um ticket informando o e-mail.</div>
  </details>

  <details>
    <summary>Como ativar a verificação em dois fatores (2FA)?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Seguranca</strong>.</li>
      <li>Baixe um app autenticador no celular (Google Authenticator, Authy, ou similar — gratuitos na loja de apps).</li>
      <li>No painel, escaneie o <strong>QR Code</strong> com o app autenticador.</li>
      <li>O app vai mostrar um código de 6 dígitos que muda a cada 30 segundos.</li>
      <li>Digite esse código no campo de confirmação do painel e clique em <strong>Ativar</strong>.</li>
    </ol>
    <p>A partir de agora, toda vez que você fizer login, alem da senha, vai precisar informar o código do app autenticador. Isso protege sua conta mesmo se alguem descobrir sua senha.</p>
  </details>

  <details>
    <summary>Como desativar o 2FA?</summary>
    <ol>
      <li>Vá em <strong>Seguranca</strong>.</li>
      <li>Clique em <strong>Desativar 2FA</strong>.</li>
      <li>Confirme com sua senha atual.</li>
    </ol>
  </details>

  <details>
    <summary>Nao consigo fazer login. O que fazer?</summary>
    <ul>
      <li><strong>Senha errada:</strong> use "Esqueci minha senha" para redefinir.</li>
      <li><strong>IP bloqueado:</strong> apos 10 tentativas incorretas, o IP e bloqueado por 30 minutos. Espere e tente novamente.</li>
      <li><strong>2FA e perdi o celular:</strong> entre em contato pelo e-mail de suporte ou abra um ticket por outra conta.</li>
    </ul>
  </details>

  <div class="faq-section">Assinaturas e pagamentos</div>

  <details>
    <summary>Como ver minhas assinaturas e faturas?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Assinaturas</strong>.</li>
      <li>você vera seus planos ativos com VPS vinculada, status e próximo vencimento.</li>
      <li>Para ver o histórico de cobranças, clique em <strong>"Historico de cobrancas"</strong>.</li>
    </ol>
  </details>

  <details>
    <summary>Como fazer upgrade do meu plano?</summary>
    <ol>
      <li>Em <strong>Assinaturas</strong>, clique em <strong>"Alterar plano"</strong> na assinatura desejada.</li>
      <li>Escolha o novo plano com mais recursos.</li>
      <li>O upgrade e imediato — os novos recursos sao aplicados automaticamente.</li>
      <li>A diferença de valor e cobrada proporcionalmente.</li>
    </ol>
  </details>

  <details>
    <summary>Como solicitar reembolso ou cancelar?</summary>
    <p>Acesse <a href="/cliente/assinaturas/histórico">Historico de cobrancas</a>. No final da página, expanda "Solicitar reembolso", selecione a assinatura e descreva o motivo. Para cancelamento, abra um ticket com o assunto "Cancelamento".</p>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- VPS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">VPS (Servidores Virtuais)</div>

  <details>
    <summary>Como funciona minha VPS?</summary>
    <p>Sua VPS e um servidor virtual com recursos dedicados (CPU, RAM e disco exclusivos para você). É como ter um computador na nuvem onde você pode instalar qualquer coisa.</p>
    <p>após assinar um plano, a VPS e criada automaticamente e você pode gerencia-la pelo painel: terminal, arquivos, deploy, backups, monitoramento — tudo pelo navegador.</p>
  </details>

  <details>
    <summary>Onde vejo o status da minha VPS?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>VPS</strong>.</li>
      <li>você vera seus servidores com o status atual:
        <ul>
          <li><strong>Em execução</strong> — funcionando normalmente</li>
          <li><strong>Provisionando</strong> — sendo criada (aguarde alguns minutos)</li>
          <li><strong>Suspensa</strong> — pagamento pendente</li>
          <li><strong>Parada</strong> — desligada</li>
        </ul>
      </li>
    </ol>
  </details>

  <details>
    <summary>Minha VPS esta suspensa. O que fazer?</summary>
    <p>A suspensão acontece por inadimplencia. Para resolver:</p>
    <ol>
      <li>Vá em <a href="/cliente/assinaturas">Assinaturas</a> e verifique se ha faturas pendentes.</li>
      <li>Realize o pagamento.</li>
      <li>A reativação e automática apos a confirmação. Se não reativar em 24h, abra um ticket.</li>
    </ol>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- migração WORDPRESS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Migração de WordPress</div>

  <details>
    <summary>Como migrar meu WordPress de outro servidor para cá?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Migrar WordPress</strong>.</li>
      <li>Clique em <strong>Nova migração</strong>.</li>
      <li>Na seção "Para onde migrar", selecione sua <strong>VPS de destino</strong>.</li>
      <li>Na seção "Servidor de origem", preencha:
        <ul>
          <li><strong>IP ou hostname</strong> do servidor atual (onde o WordPress esta agora)</li>
          <li><strong>Porta SSH</strong> (geralmente 22)</li>
          <li><strong>Usuario</strong> (geralmente root)</li>
          <li><strong>Senha SSH</strong></li>
          <li><strong>Caminho do WordPress</strong> no servidor (ex: <code>/www/wwwroot/meusite.com</code> no AAPanel)</li>
        </ul>
      </li>
      <li>Na seção "Banco de dados", preencha:
        <ul>
          <li><strong>Nome do banco</strong>, <strong>usuário</strong>, <strong>senha</strong> e <strong>host</strong> do MySQL</li>
          <li>Dica: esses dados estao no arquivo <code>wp-config.php</code> do WordPress atual</li>
        </ul>
      </li>
      <li>Clique em <strong>Iniciar migração</strong>.</li>
      <li>Aguarde — o sistema copia todos os arquivos e o banco automaticamente.</li>
    </ol>
    <div class="tip">Funciona com qualquer servidor que tenha acesso SSH: AAPanel, cPanel, servidores dedicados, etc. Sites pesados (10GB, 50GB+) migram sem problema.</div>
  </details>

  <details>
    <summary>Como ativar meu domínio real após a migração?</summary>
    <ol>
      <li>após a migração concluir, seu site está rodando com um domínio temporário.</li>
      <li>Teste o site pelo domínio temporário para garantir que está tudo OK.</li>
      <li>Quando estiver pronto, va na tela de detalhes da migração.</li>
      <li>Na seção "Domínio", informé seu domínio real (ex: <code>meusite.com.br</code>).</li>
      <li>Clique em <strong>Ativar domínio</strong>.</li>
      <li>O sistema atualiza automaticamente o banco do WordPress (siteurl/home), o wp-config.php e o Nginx.</li>
      <li>Aponte o DNS do seu domínio (registro A) para o IP do nosso servidor.</li>
    </ol>
  </details>

  <details>
    <summary>E se o servidor não tiver espaço suficiente para a migração?</summary>
    <p>O sistema verifica automaticamente o espaço disponível no servidor de destino <strong>antes de iniciar</strong> a cópia dos arquivos. Se não couber, a migração é cancelada imediatamente com uma mensagem clara informando:</p>
    <ul>
      <li>O tamanho estimado do site</li>
      <li>Quanto espaço é necessário</li>
      <li>Quanto espaço está disponível</li>
    </ul>
    <p><strong>Se o disco encher durante a migração</strong> (caso raro — o site cresceu entre a verificação e a cópia), o sistema detecta o erro automaticamente, <strong>apaga os arquivos parciais</strong> para liberar o espaço, e marca a migração como "Falhou" com instruções de como resolver.</p>
    <p><strong>Para resolver:</strong></p>
    <ul>
      <li>Apague aplicações, sites ou backups antigos que não usa mais (lembre de marcar "Apagar arquivos do servidor" ao deletar)</li>
      <li>Ou faça upgrade do plano para mais armazenamento</li>
      <li>Depois, tente a migração novamente</li>
    </ul>
    <div class="tip">O sistema nunca deixa o servidor instável. Se faltar espaço, ele limpa tudo e avisa você.</div>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- PERGUNTAS RAPIDAS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Perguntas rápidas</div>

  <details>
    <summary>Não encontro uma opção no menu. Por que?</summary>
    <p>O menu mostra apenas as funcionalidades do seu plano. Planos mais simples (como WordPress) não incluem Terminal ou Monitoramento, por exemplo. Para acesso completo, contrate um plano VPS.</p>
  </details>

  <details>
    <summary>Como trocar o idioma do painel?</summary>
    <p>No topo de qualquer página, clique no seletor de idioma (bandeira) e escolha Portugues, English ou Espanol.</p>
  </details>

  <details>
    <summary>Como gerenciar cookies?</summary>
    <p>Clique em "Cookies" no rodape de qualquer página. você pode ativar ou desativar cookies de analytics, marketing e preferencias. Cookies necessarios (sessao e segurança) não podem ser desativados.</p>
  </details>

  <details>
    <summary>Como entrar em contato com o suporte?</summary>
    <p>você tem tres opções:</p>
    <ul>
      <li><strong>Chat ao vivo:</strong> clique no icone de chat no canto inferior direito</li>
      <li><strong>Ticket:</strong> menu lateral &rarr; Tickets &rarr; Novo Ticket</li>
      <li><strong>E-mail:</strong> envie para o e-mail de suporte da empresa</li>
    </ul>
  </details>

  <details>
    <summary>O que são API Keys e para que servem?</summary>
    <p>API Keys permitem que você (ou sistemas externos) se comuniquem com a plataforma de forma automatizada. Por exemplo: um script que cria backups, um bot que abre tickets, ou uma integração com outro sistema.</p>
    <p>Se você não e desenvolvedor e não vai integrar nada, não precisa mexer nas API Keys.</p>
    <p>Se precisar: va em <a href="/cliente/api-keys">API Keys</a> no menu lateral &rarr; "Criar API Key" &rarr; escolha um nome e os escopos (permissões).</p>
  </details>

  <details>
    <summary>Qual a diferença entre planos VPS, Web Hosting e WordPress?</summary>
    <ul>
      <li><strong>VPS:</strong> acesso completo ao servidor (terminal, monitoramento, tudo). Para quem precisa de controle total.</li>
      <li><strong>Web Hosting:</strong> painel simplificado com catalogo de apps e git deploy. Sem terminal. Para quem quer praticidade.</li>
      <li><strong>WordPress:</strong> focado 100% em WordPress. instalação em 1 clique, backups, SSL. O mais simples de todos.</li>
    </ul>
    <p><strong>Nao sabe qual escolher?</strong> Se você so quer um site WordPress, escolha o plano WordPress. Se tem um projeto custom (Node, PHP, Python), escolha VPS ou Web Hosting.</p>
  </details>

  <details>
    <summary>O painel está lento ou travando. O que fazer?</summary>
    <ul>
      <li><strong>Limpe o cache do navegador:</strong> Ctrl+Shift+Delete &rarr; limpe dados de navegacao.</li>
      <li><strong>Tente outro navegador:</strong> Chrome, Firefox ou Edge (evite Internet Explorer).</li>
      <li><strong>Conexao lenta:</strong> teste sua internet em <a href="https://fast.com" target="_blank" rel="noopener">fast.com</a>.</li>
      <li>Se o problema persistir, pode ser algo no servidor. Abra um ticket informando o que esta lento.</li>
    </ul>
  </details>

  <details>
    <summary>Posso ter mais de um projeto/site na mesma VPS?</summary>
    <p><strong>Sim!</strong> você pode ter quantos projetos quiser na mesma VPS. Basta usar o Git Deploy para cada projeto, cada um com seu próprio subdomínio.</p>
    <p>Exemplo: você pode ter:</p>
    <ul>
      <li><code>site.meudomínio.com</code> — seu site principal</li>
      <li><code>api.meudomínio.com</code> — sua API backend</li>
      <li><code>blog.meudomínio.com</code> — um WordPress</li>
    </ul>
    <p>Todos na mesma VPS, cada um no seu diretório.</p>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- RODAPE AJUDA -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div style="padding-top:4px;">
    <p style="font-size:13px;color:#64748b;margin-bottom:12px;"><?php echo View::e(I18n::t('ajuda.nao_encontrou')); ?></p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a class="botao" href="/cliente/chat"><?php echo View::e(I18n::t('ajuda.chat_vivo')); ?></a>
      <a class="botao" href="/cliente/tickets/novo"><?php echo View::e(I18n::t('ajuda.abrir_ticket')); ?></a>
      <a class="botao ghost" href="/contato"><?php echo View::e(I18n::t('ajuda.fale_conosco')); ?></a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>

