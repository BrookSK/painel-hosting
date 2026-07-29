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
<!-- GUIA RAPIDO - O QUE VOCE QUER FAZER? -->
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
          <li>Se já tem domínio próprio: vá em <a href="/cliente/dominios">Domínios</a> &rarr; adicione seu subdomínio &rarr; siga a verificação DNS</li>
          <li>Se não tem domínio: não se preocupe, ao criar o Git Deploy você pode gerar um domínio temporário gratuito</li>
        </ul>
      </li>
      <li><strong>Conecte seu repositório</strong>: vá em <a href="/cliente/git-deploy/novo">Git Deploy &rarr; Novo repositório</a>. Preencha nome, VPS, URL do GitHub, branch e tipo de app.</li>
      <li><strong>Configure a Deploy Key</strong> (só para repos privados): copie a chave gerada e adicione no GitHub (Settings &rarr; Deploy keys).</li>
      <li><strong>Faça o primeiro deploy</strong>: clique em "Deploy agora" no card do repositório.</li>
      <li><strong>Acesse seu site</strong>: clique no link do domínio que aparece no card. Pronto!</li>
    </ol>
    <div class="tip">Tempo estimado: 5 a 10 minutos na primeira vez. Depois disso, cada deploy leva segundos.</div>
  </details>

  <details>
    <summary>Quero criar um e-mail profissional (contato@meusite.com)</summary>
    <ol>
      <li>Vá em <a href="/cliente/dominios">Domínios</a> e adicione seu domínio raiz (ex: <code>meusite.com.br</code>).</li>
      <li>Configure os registros DNS (MX, SPF, DKIM) conforme as instruções que o sistema mostra.</li>
      <li>Aguarde a propagação DNS (5 min a 2h).</li>
      <li>Vá em <a href="/cliente/emails">E-mails</a> &rarr; crie a conta (ex: contato@meusite.com.br) com uma senha.</li>
      <li>Acesse pelo webmail ou configure no Outlook/Gmail (veja tutoriais na seção E-mails abaixo).</li>
    </ol>
    <div class="tip">Tempo estimado: 10 a 30 minutos (a maior parte é esperar o DNS propagar).</div>
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
      <li>Vá em <a href="/cliente/aplicacoes/catalogo">Aplicações &rarr; Catálogo</a>.</li>
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
      <li>Use os dados de conexão (host: localhost, porta: 3306, usuario e senha) no arquivo <code>.env</code> ou config do seu projeto.</li>
    </ol>
  </details>

  <details>
    <summary>Quero migrar meu WordPress de outro servidor</summary>
    <ol>
      <li>Vá em <a href="/cliente/migracoes-wp/novo">Migrar WordPress &rarr; Nova Migração</a>.</li>
      <li>Preencha os dados SSH do servidor atual (IP, porta, usuario, senha, caminho do WordPress).</li>
      <li>Preencha os dados do banco MySQL (nome, usuario, senha — estão no wp-config.php).</li>
      <li>Clique em "Iniciar Migração" e aguarde. O sistema copia tudo automaticamente.</li>
      <li>Quando concluir, teste pelo domínio temporário. Depois, ative seu domínio real.</li>
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
      <li>Clique no botao <strong>+ Novo repositorio</strong> (canto superior direito).</li>
      <li>Preencha o <strong>Nome da integracao</strong> — pode ser qualquer nome para voce identificar (ex: "Meu Site", "API Backend").</li>
      <li>Em <strong>VPS</strong>, selecione o servidor onde o projeto vai rodar.</li>
      <li>Em <strong>URL do repositorio</strong>, cole o link do seu repositorio. Para pegar esse link no GitHub:
        <ul>
          <li>Va no seu repositorio no GitHub</li>
          <li>Clique no botao verde <strong>&lt;&gt; Code</strong></li>
          <li>Copie a URL HTTPS (ex: <code>https://github.com/seu-usuario/seu-projeto</code>)</li>
        </ul>
      </li>
      <li>Em <strong>Branch</strong>, coloque o nome da branch que voce usa (geralmente <code>main</code> ou <code>master</code>). Se voce nao sabe, deixe <code>main</code>.</li>
      <li>Em <strong>Subdominio</strong>, selecione um subdominio que voce ja cadastrou em Dominios. Se nao tiver nenhum, marque a opcao <strong>"Gerar dominio temporario"</strong> — o sistema vai criar um endereco gratuito para voce testar (ex: <code>meusite3f2a.apps.seuservidor.com</code>).</li>
      <li>Escolha o <strong>Tipo de aplicacao</strong>:
        <ul>
          <li><strong>PHP / Laravel / WordPress</strong> — para projetos em PHP</li>
          <li><strong>Site estatico</strong> — para HTML/CSS/JS puro</li>
          <li><strong>Node.js</strong> — para projetos com npm/yarn (Express, Next.js, etc.)</li>
          <li><strong>Python</strong> — para Django, Flask, FastAPI</li>
          <li><strong>C/C++</strong> — para aplicacoes compiladas</li>
        </ul>
      </li>
      <li>Se escolheu Node.js ou Python, informe a <strong>Porta</strong> onde sua app roda (ex: 3000 para Node, 8000 para Python).</li>
      <li>Clique em <strong>Salvar</strong>.</li>
    </ol>
    <div class="tip">Apos salvar, o sistema vai gerar uma <strong>Deploy Key</strong> (chave SSH). Voce precisa copiar essa chave e adicionar no GitHub para que o servidor consiga acessar seu codigo. Veja o tutorial abaixo.</div>
  </details>

  <details>
    <summary>Como adicionar a Deploy Key no GitHub (repositorio privado)?</summary>
    <p>Se seu repositorio e <strong>privado</strong>, voce precisa autorizar nosso servidor a acessar o codigo. Faca assim:</p>
    <ol>
      <li>Apos salvar o repositorio, voce sera levado para a tela de edicao. Copie a <strong>Deploy Key</strong> que aparece na caixa azul (clique em "Copiar").</li>
      <li>No GitHub, va no seu repositorio e clique em <strong>Settings</strong> (aba no topo).</li>
      <li>No menu lateral esquerdo, clique em <strong>Deploy keys</strong>.</li>
      <li>Clique em <strong>Add deploy key</strong>.</li>
      <li>Em "Title", coloque um nome qualquer (ex: <code>LRV Cloud</code>).</li>
      <li>Em "Key", cole a chave que voce copiou do painel.</li>
      <li>Clique em <strong>Add key</strong>.</li>
    </ol>
    <p>Pronto! Agora o servidor consegue acessar seu codigo. Se o repositorio for <strong>publico</strong>, voce nao precisa fazer isso.</p>
    <div class="tip">No GitLab: va em Settings &rarr; Repository &rarr; Deploy keys &rarr; cole a chave e salve.</div>
  </details>

  <details>
    <summary>Como fazer o deploy (enviar o codigo para o servidor)?</summary>
    <ol>
      <li>Va em <a href="/cliente/git-deploy">Git Deploy</a>.</li>
      <li>No card do seu repositorio, clique em <strong>&#9654; Deploy agora</strong>.</li>
      <li>Aguarde — o sistema vai conectar no servidor, baixar o codigo do GitHub e configurar tudo.</li>
      <li>Se der certo, o status muda para "Ativo" e aparece o ultimo commit (hash + mensagem).</li>
      <li>Se der erro, a mensagem de erro aparece em vermelho. Os erros mais comuns sao:
        <ul>
          <li><strong>"Permission denied"</strong> — a Deploy Key nao foi adicionada no GitHub. Veja o tutorial acima.</li>
          <li><strong>"Branch not found"</strong> — a branch informada nao existe. Verifique o nome correto no GitHub.</li>
          <li><strong>"Could not resolve host"</strong> — o servidor nao consegue acessar a internet. Abra um ticket.</li>
        </ul>
      </li>
    </ol>
  </details>

  <div class="faq-section">Opcoes do Git Deploy</div>

  <details>
    <summary>O que faz a opcao "Substituir tudo (force overwrite)"?</summary>
    <p><strong>Quando ativada:</strong> cada vez que voce fizer deploy, o servidor descarta qualquer alteracao feita diretamente nos arquivos e substitui tudo pelo que esta no GitHub. E o modo mais seguro para manter o servidor sempre igual ao repositorio.</p>
    <p><strong>Quando desativada:</strong> o servidor tenta preservar alteracoes locais (arquivos que voce editou direto no servidor) fazendo um <code>git stash</code> antes de puxar as novidades. Util se voce edita configs diretamente no servidor.</p>
    <div class="tip">Recomendacao: deixe ativada. Se voce precisa de arquivos diferentes no servidor (como .env), coloque eles no .gitignore — assim o Git nao mexe neles.</div>
  </details>

  <details>
    <summary>O que e o Auto Deploy e como configurar?</summary>
    <p>O <strong>Auto Deploy</strong> faz com que, toda vez que voce der um <code>git push</code> na branch configurada, o servidor atualize automaticamente — sem precisar clicar em "Deploy agora".</p>
    <p><strong>Como ativar:</strong></p>
    <ol>
      <li>Na tela de edicao do repositorio, marque a opcao <strong>Auto Deploy</strong>.</li>
      <li>Salve. O sistema vai gerar uma <strong>URL de Webhook</strong>.</li>
      <li>Copie essa URL (clique em "Copiar").</li>
      <li>Va no GitHub, no seu repositorio:
        <ul>
          <li>Clique em <strong>Settings</strong> (aba no topo)</li>
          <li>No menu lateral, clique em <strong>Webhooks</strong></li>
          <li>Clique em <strong>Add webhook</strong></li>
          <li>Em "Payload URL", cole a URL que voce copiou do painel</li>
          <li>Em "Content type", selecione <strong>application/json</strong></li>
          <li>Em "Which events?", deixe marcado <strong>Just the push event</strong></li>
          <li>Clique em <strong>Add webhook</strong></li>
        </ul>
      </li>
    </ol>
    <p>Pronto! Agora, cada vez que voce fizer <code>git push</code> na branch configurada, o deploy acontece sozinho em poucos segundos.</p>
    <div class="tip">No GitLab: Settings &rarr; Webhooks &rarr; cole a URL &rarr; marque "Push events" &rarr; salve.</div>
    <div class="tip">No Bitbucket: Repository settings &rarr; Webhooks &rarr; Add webhook &rarr; cole a URL &rarr; trigger "Repository push".</div>
  </details>

  <details>
    <summary>O que e o comando pos-deploy?</summary>
    <p>E um comando que roda automaticamente apos cada deploy. Serve para instalar dependencias ou compilar o projeto. Exemplos:</p>
    <ul>
      <li><strong>PHP/Laravel:</strong> <code>composer install --no-dev</code></li>
      <li><strong>Node.js:</strong> <code>npm install && npm run build</code></li>
      <li><strong>Python:</strong> <code>pip install -r requirements.txt</code></li>
    </ul>
    <p>Preencha esse campo na tela de edicao do repositorio. Deixe em branco se nao precisar.</p>
  </details>

  <details>
    <summary>Configuracoes PHP (versao, memory_limit, upload)</summary>
    <p>Se seu projeto e PHP, voce pode configurar:</p>
    <ul>
      <li><strong>Versao do PHP:</strong> 8.1, 8.2 ou 8.3</li>
      <li><strong>memory_limit:</strong> quanta memoria o PHP pode usar (padrao: 256M)</li>
      <li><strong>upload_max_filesize:</strong> tamanho maximo de upload (padrao: 64M)</li>
      <li><strong>post_max_size:</strong> tamanho maximo de um POST (padrao: 64M)</li>
      <li><strong>max_execution_time:</strong> tempo maximo de execucao em segundos (padrao: 300)</li>
    </ul>
    <p>Essas configuracoes ficam na tela de edicao do repositorio, na secao "Configuracoes PHP".</p>
  </details>

  <details>
    <summary>Como usar o Console (executar comandos no servidor)?</summary>
    <ol>
      <li>Na listagem do Git Deploy, clique em <strong>Console</strong> no card do repositorio.</li>
      <li>Um terminal preto vai abrir abaixo do card.</li>
      <li>Digite o comando que deseja executar (ex: <code>ls -la</code>, <code>npm install</code>, <code>php artisan migrate</code>).</li>
      <li>O comando e executado na pasta do seu projeto no servidor.</li>
    </ol>
    <div class="warn">Cuidado: comandos destrutivos (como <code>rm -rf</code>) nao tem confirmacao. Use com responsabilidade.</div>
  </details>

  <details>
    <summary>Como ver os logs de deploy e logs do servidor?</summary>
    <ul>
      <li><strong>Historico de deploys:</strong> clique em "Historico" no card do repositorio. Mostra data, status (sucesso/erro), commit e saida do comando.</li>
      <li><strong>Logs do servidor:</strong> clique em "Logs servidor" para ver logs do Nginx, PHP-FPM e da aplicacao em tempo real.</li>
      <li><strong>Logs PM2 (Node.js):</strong> se o tipo e Node.js, clique em "Logs PM2" para ver o que o processo Node esta imprimindo.</li>
    </ul>
  </details>

  <details>
    <summary>Como editar ou remover um repositorio?</summary>
    <ul>
      <li><strong>Editar:</strong> clique em "Editar" no card do repositorio. Voce pode mudar a URL, branch, subdominio, tipo de app e todas as configuracoes.</li>
      <li><strong>Remover:</strong> clique em "Remover" e confirme. Isso remove a integracao do painel, mas <strong>nao apaga</strong> os arquivos no servidor.</li>
    </ul>
  </details>

  <div class="faq-section">Problemas comuns e como resolver</div>

  <details>
    <summary>Erro "Permission denied" ou "Authentication failed"</summary>
    <p><strong>Causa:</strong> o servidor nao consegue acessar seu repositorio no GitHub porque a Deploy Key nao foi adicionada (ou foi adicionada errada).</p>
    <p><strong>Solucao:</strong></p>
    <ol>
      <li>Va em Git Deploy &rarr; Editar o repositorio.</li>
      <li>Copie a Deploy Key (caixa azul).</li>
      <li>No GitHub, va no repositorio &rarr; Settings &rarr; Deploy keys.</li>
      <li>Se ja existe uma key antiga, remova ela.</li>
      <li>Clique em "Add deploy key" e cole a nova chave.</li>
      <li>Tente o deploy novamente.</li>
    </ol>
    <div class="tip">Se o repositorio for <strong>publico</strong>, esse erro nao deveria acontecer. Nesse caso, verifique se a URL esta correta.</div>
  </details>

  <details>
    <summary>Erro "Branch not found" ou "Remote branch not found"</summary>
    <p><strong>Causa:</strong> a branch informada no painel nao existe no repositorio.</p>
    <p><strong>Solucao:</strong></p>
    <ol>
      <li>No GitHub, clique no seletor de branches (geralmente mostra "main") e veja o nome exato da branch principal.</li>
      <li>Copie o nome (ex: <code>main</code>, <code>master</code>, <code>develop</code>).</li>
      <li>No painel, edite o repositorio e corrija o campo "Branch".</li>
      <li>Salve e tente o deploy novamente.</li>
    </ol>
  </details>

  <details>
    <summary>Erro "Could not resolve host" ou "DNS"</summary>
    <p><strong>Causa:</strong> o servidor nao esta conseguindo acessar a internet (problema de rede/DNS).</p>
    <p><strong>Solucao:</strong> isso e um problema no servidor, nao no seu codigo. Abra um <a href="/cliente/tickets/novo">ticket de suporte</a> informando o erro completo e o ID do repositorio. Resolveremos rapidamente.</p>
  </details>

  <details>
    <summary>Deploy deu certo mas o site nao abre (erro 502 ou pagina em branco)</summary>
    <p><strong>Para PHP/Laravel:</strong></p>
    <ul>
      <li>Verifique se o arquivo <code>.env</code> existe no servidor (use o Gerenciador de Arquivos).</li>
      <li>Execute no Console: <code>php artisan key:generate</code> e depois <code>php artisan config:cache</code>.</li>
      <li>Verifique permissoes: execute <code>chmod -R 775 storage bootstrap/cache</code>.</li>
    </ul>
    <p><strong>Para Node.js:</strong></p>
    <ul>
      <li>Verifique se a porta informada no painel e a mesma que o app usa.</li>
      <li>Clique em "Logs PM2" para ver se o processo iniciou ou se ha erros.</li>
      <li>Se o processo caiu, clique em "Reiniciar".</li>
    </ul>
    <p><strong>Para site estatico:</strong></p>
    <ul>
      <li>Verifique se existe um arquivo <code>index.html</code> na raiz do projeto.</li>
    </ul>
  </details>

  <details>
    <summary>Fiz deploy mas as alteracoes nao aparecem no site</summary>
    <p>Possíveis causas:</p>
    <ul>
      <li><strong>Cache do navegador:</strong> pressione Ctrl+Shift+R (ou Cmd+Shift+R no Mac) para forcar reload sem cache.</li>
      <li><strong>Branch errada:</strong> verifique se voce deu push na mesma branch configurada no painel.</li>
      <li><strong>Build nao rodou:</strong> se seu projeto precisa de <code>npm run build</code>, coloque isso no campo "Comando pos-deploy".</li>
      <li><strong>Cache do servidor (Laravel):</strong> execute no Console: <code>php artisan cache:clear && php artisan view:clear</code>.</li>
    </ul>
  </details>

  <details>
    <summary>Como colocar variaveis de ambiente (.env) no servidor?</summary>
    <p>O arquivo <code>.env</code> geralmente nao vai no Git (ele fica no .gitignore). Entao voce precisa criar manualmente:</p>
    <ol>
      <li>Va em <a href="/cliente/arquivos">Arquivos</a> e navegue ate a pasta do seu projeto.</li>
      <li>Clique em "Novo arquivo" e nomeie como <code>.env</code>.</li>
      <li>Cole o conteudo do seu <code>.env</code> local (adaptando host do banco, URLs, etc.).</li>
      <li>Salve.</li>
    </ol>
    <div class="tip">Com a opcao "Substituir tudo" ativada, o Git nao mexe em arquivos que nao estao no repositorio. Ou seja, seu .env fica seguro entre deploys.</div>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- DOMINIOS E SUBDOMINIOS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Dominios e Subdominios</div>

  <div class="faq-section">Cadastrar e verificar dominios</div>

  <details>
    <summary>Qual a diferenca entre dominio raiz e subdominio?</summary>
    <ul>
      <li><strong>Dominio raiz</strong> (ex: <code>meusite.com.br</code>) — usado para e-mails. Voce aponta via registro MX.</li>
      <li><strong>Subdominio</strong> (ex: <code>app.meusite.com.br</code>) — usado para aplicacoes, Git Deploy e webmail. Voce aponta via CNAME ou registro A.</li>
    </ul>
    <p>Primeiro cadastre o dominio raiz, depois adicione os subdominios que precisar.</p>
  </details>

  <details>
    <summary>Como cadastrar um subdominio (passo a passo)?</summary>
    <ol>
      <li>Va em <a href="/cliente/dominios">Dominios</a>.</li>
      <li>Na secao "Adicionar subdominio", digite o subdominio completo (ex: <code>app.meusite.com.br</code>).</li>
      <li>Clique em <strong>Adicionar</strong>.</li>
      <li>O sistema vai mostrar um <strong>registro TXT</strong> para voce criar no DNS do seu dominio (para provar que o dominio e seu).</li>
      <li>Acesse o painel do seu provedor de DNS (Cloudflare, Registro.br, GoDaddy, etc.) e crie o registro TXT conforme indicado.</li>
      <li>Aguarde a propagacao (pode levar de 5 minutos a 2 horas) e clique em <strong>Verificar TXT</strong>.</li>
      <li>Apos o TXT ser verificado, o sistema pede um <strong>registro CNAME</strong>. Crie no seu DNS apontando para o endereco indicado.</li>
      <li>Clique em <strong>Verificar CNAME</strong>. Se estiver propagado, o subdominio fica "Ativo" e pronto para usar.</li>
    </ol>
  </details>

  <details>
    <summary>Estou usando dominio temporario. Como mudar para meu dominio real?</summary>
    <ol>
      <li>Primeiro, cadastre e verifique seu subdominio real em <a href="/cliente/dominios">Dominios</a> (veja tutorial acima).</li>
      <li>Depois, va em <a href="/cliente/git-deploy">Git Deploy</a> e clique em <strong>Editar</strong> no repositorio.</li>
      <li>No campo "Subdominio", selecione seu subdominio verificado.</li>
      <li>Salve e faca um novo deploy.</li>
    </ol>
    <div class="tip">O dominio temporario continua funcionando apos a troca. Voce pode usar os dois ao mesmo tempo.</div>
  </details>

  <details>
    <summary>Como usar dominio raiz (sem "www" ou subdominio) para meu site?</summary>
    <ol>
      <li>Em <a href="/cliente/dominios">Dominios</a>, adicione o dominio raiz (ex: <code>meusite.com.br</code>).</li>
      <li>No DNS do seu provedor, crie um <strong>registro A</strong> apontando para o IP do seu servidor (o IP e exibido na tela de dominios).</li>
      <li>Verifique e pronto — o dominio raiz fica disponivel para usar em aplicacoes e Git Deploy.</li>
    </ol>
  </details>

  <div class="faq-section">Problemas comuns com dominios</div>

  <details>
    <summary>O DNS nao propaga (verificacao falha)</summary>
    <p><strong>Causas comuns:</strong></p>
    <ul>
      <li><strong>Criou o registro no lugar errado:</strong> o registro TXT/CNAME deve ser criado no provedor que gerencia o DNS do seu dominio (Cloudflare, Registro.br, GoDaddy, etc.). Se nao sabe qual e, use o site <a href="https://who.is" target="_blank" rel="noopener">who.is</a> para verificar.</li>
      <li><strong>Tempo de propagacao:</strong> normalmente leva 5-30 minutos, mas em casos raros pode levar ate 48 horas. Tente novamente depois.</li>
      <li><strong>Proxy do Cloudflare (nuvem laranja):</strong> se usa Cloudflare, desative o proxy (nuvem cinza) no registro CNAME/A. O proxy pode interferir na verificacao.</li>
      <li><strong>Erro de digitacao:</strong> confira se copiou o valor exato que o sistema pediu (sem espacos extras).</li>
    </ul>
  </details>

  <details>
    <summary>Onde encontro o painel de DNS do meu dominio?</summary>
    <p>Depende de onde voce comprou/gerencia o dominio:</p>
    <ul>
      <li><strong>Registro.br:</strong> acesse registro.br &rarr; login &rarr; clique no dominio &rarr; "Editar zona DNS"</li>
      <li><strong>Cloudflare:</strong> painel Cloudflare &rarr; selecione o site &rarr; menu "DNS"</li>
      <li><strong>GoDaddy:</strong> Meus Dominios &rarr; DNS &rarr; Gerenciar</li>
      <li><strong>Hostinger:</strong> hPanel &rarr; Dominios &rarr; DNS/Nameservers</li>
      <li><strong>HostGator:</strong> Portal &rarr; Dominios &rarr; Zona de DNS</li>
    </ul>
    <p>Se nao sabe onde esta, abra um ticket e nos ajudamos a identificar.</p>
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
      <li>Defina um <strong>nome</strong> para o banco e uma <strong>senha</strong> para o usuario.</li>
      <li>Clique em <strong>Criar</strong>.</li>
    </ol>
    <p>O sistema cria o banco MySQL automaticamente no servidor. Os dados de conexao (host, porta, usuario, senha) ficam disponiveis na tela de detalhes do banco.</p>
  </details>

  <details>
    <summary>Como acessar o phpMyAdmin?</summary>
    <ol>
      <li>Em <a href="/cliente/banco-dados">Bancos de Dados</a>, clique no banco desejado.</li>
      <li>Clique no botao <strong>phpMyAdmin</strong>.</li>
      <li>Uma nova aba vai abrir com o phpMyAdmin ja logado no seu banco.</li>
    </ol>
    <div class="tip">Se o phpMyAdmin nao estiver configurado, o botao vai pedir para configurar primeiro. Basta clicar e aguardar.</div>
  </details>

  <details>
    <summary>Como executar comandos SQL?</summary>
    <ol>
      <li>Clique no banco desejado para abrir os detalhes.</li>
      <li>Na secao <strong>Executar SQL</strong>, digite seu comando (ex: <code>SHOW TABLES;</code>).</li>
      <li>Clique em <strong>Executar</strong>. O resultado aparece logo abaixo.</li>
    </ol>
  </details>

  <details>
    <summary>Como conectar minha aplicacao ao banco?</summary>
    <p>Use os dados de conexao exibidos na tela de detalhes do banco:</p>
    <ul>
      <li><strong>Host:</strong> geralmente <code>localhost</code> ou <code>127.0.0.1</code> (se a app roda na mesma VPS)</li>
      <li><strong>Porta:</strong> <code>3306</code></li>
      <li><strong>Banco:</strong> o nome que voce definiu</li>
      <li><strong>Usuario:</strong> o nome de usuario gerado</li>
      <li><strong>Senha:</strong> clique em "Ver senha" para copiar</li>
    </ul>
    <p>Coloque esses dados no arquivo de configuracao da sua aplicacao (<code>.env</code>, <code>config/database.php</code>, etc.).</p>
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
      <li>Selecione a <strong>VPS</strong> no topo da pagina.</li>
      <li>Voce vera as pastas e arquivos do servidor. Navegue clicando nas pastas.</li>
      <li>Para <strong>editar</strong> um arquivo, clique nele. Um editor abre com o conteudo. Faca as alteracoes e clique em "Salvar".</li>
      <li>Para <strong>enviar</strong> um arquivo do seu computador, clique em "Upload" e selecione o arquivo.</li>
      <li>Para <strong>baixar</strong> um arquivo, clique com botao direito (ou no icone de download) e salve no seu computador.</li>
      <li>Para <strong>criar</strong> um novo arquivo ou pasta, use os botoes "Novo arquivo" / "Nova pasta".</li>
      <li>Para <strong>renomear ou excluir</strong>, clique com botao direito no item.</li>
    </ol>
    <div class="tip">O gerenciador de arquivos funciona 100% no navegador. Voce nao precisa instalar nenhum programa de FTP.</div>
  </details>

  <details>
    <summary>Onde ficam os arquivos do meu projeto?</summary>
    <p>Se voce usou o Git Deploy, seus arquivos ficam no caminho exibido no card do repositorio (geralmente <code>/var/www/nome-do-projeto</code>). Voce pode navegar ate la pelo Gerenciador de Arquivos.</p>
    <p>Dica: no Git Deploy, clique em "Arquivos" no card do repositorio para ir direto para a pasta do projeto.</p>
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
      <li>Selecione a VPS desejada (precisa estar com status "Em execucao").</li>
      <li>Clique em <strong>Conectar</strong>.</li>
      <li>Uma tela preta vai abrir — e o terminal do seu servidor. Voce pode digitar qualquer comando Linux.</li>
    </ol>
    <div class="tip">O terminal funciona direto no navegador via WebSocket seguro. Voce nao precisa de PuTTY, Terminal ou qualquer programa externo.</div>
  </details>

  <details>
    <summary>Comandos uteis para iniciantes</summary>
    <ul>
      <li><code>ls</code> — listar arquivos da pasta atual</li>
      <li><code>cd /var/www/meu-projeto</code> — entrar numa pasta</li>
      <li><code>cat arquivo.txt</code> — ver o conteudo de um arquivo</li>
      <li><code>nano arquivo.txt</code> — editar um arquivo (Ctrl+X para sair)</li>
      <li><code>npm install</code> — instalar dependencias Node.js</li>
      <li><code>composer install</code> — instalar dependencias PHP</li>
      <li><code>php artisan migrate</code> — rodar migrations do Laravel</li>
      <li><code>systemctl restart nginx</code> — reiniciar o Nginx</li>
    </ul>
  </details>

  <details>
    <summary>Cenarios praticos: o que executar em cada situacao</summary>
    <p><strong>Meu site Laravel esta dando erro 500:</strong></p>
    <ul>
      <li><code>cd /var/www/meu-projeto</code></li>
      <li><code>php artisan config:cache</code></li>
      <li><code>php artisan route:cache</code></li>
      <li><code>chmod -R 775 storage bootstrap/cache</code></li>
      <li><code>cat storage/logs/laravel.log | tail -50</code> (ver ultimas 50 linhas do log)</li>
    </ul>
    <p><strong>Preciso instalar uma extensao PHP:</strong></p>
    <ul>
      <li><code>apt-get update && apt-get install php8.3-extensao</code> (ex: php8.3-gd, php8.3-curl, php8.3-mbstring)</li>
      <li><code>systemctl restart php8.3-fpm</code></li>
    </ul>
    <p><strong>Meu app Node.js caiu:</strong></p>
    <ul>
      <li><code>pm2 status</code> — ver se o processo esta rodando</li>
      <li><code>pm2 logs</code> — ver os erros</li>
      <li><code>pm2 restart all</code> — reiniciar todos os processos</li>
    </ul>
    <p><strong>Quero ver quanto espaco em disco estou usando:</strong></p>
    <ul>
      <li><code>df -h</code> — espaco total do disco</li>
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
    <summary>O que sao Cron Jobs?</summary>
    <p>Cron Jobs sao tarefas que rodam automaticamente em horarios que voce define. Por exemplo: limpar cache toda meia-noite, enviar e-mails a cada hora, fazer backup do banco todo dia as 3h da manha.</p>
  </details>

  <details>
    <summary>Como criar um Cron Job?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Cron Jobs</strong>.</li>
      <li>Preencha o <strong>comando</strong> que deseja executar (ex: <code>cd /var/www/meu-projeto && php artisan schedule:run</code>).</li>
      <li>Defina a <strong>frequencia</strong> usando os campos de minuto, hora, dia, mes e dia da semana. Exemplos:
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
      <li><strong>Ativar/Desativar:</strong> use o botao de toggle ao lado do cron job. Quando desativado, ele nao roda ate voce ativar de novo.</li>
      <li><strong>Executar agora:</strong> clique em "Executar agora" para rodar o comando imediatamente (sem esperar o horario).</li>
    </ul>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- BACKUPS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Backups</div>

  <details>
    <summary>Como funcionam os backups?</summary>
    <p>O sistema faz backups automaticos diarios da sua VPS. Voce tambem pode criar backups manuais a qualquer momento. Cada backup inclui arquivos e bancos de dados.</p>
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
    <div class="warn">A restauracao substitui os dados atuais. Se voce fez alteracoes depois do backup, elas serao perdidas. Faca um backup manual antes de restaurar, por seguranca.</div>
  </details>

  <details>
    <summary>Como baixar um backup?</summary>
    <p>Na lista de backups, clique em <strong>Baixar</strong> ao lado do backup desejado. O arquivo .tar.gz sera baixado para o seu computador.</p>
  </details>

  <details>
    <summary>Com que frequencia devo fazer backup?</summary>
    <p>O sistema ja faz backups automaticos diarios. Mas recomendamos fazer um backup <strong>manual</strong> antes de:</p>
    <ul>
      <li>Fazer alteracoes grandes no codigo ou banco de dados</li>
      <li>Atualizar plugins ou temas do WordPress</li>
      <li>Mudar configuracoes do servidor</li>
      <li>Migrar de dominio</li>
    </ul>
    <div class="tip">Backup manual + deploy = seguranca. Se algo der errado no deploy, voce restaura o backup e volta ao normal em segundos.</div>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- APLICACOES -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Aplicacoes (Catalogo com 1 clique)</div>

  <details>
    <summary>Como instalar WordPress, Node.js ou outra aplicacao?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Aplicacoes</strong> &rarr; <strong>Catalogo</strong>.</li>
      <li>Escolha o template que deseja instalar (WordPress, Node.js, PHP Laravel, MySQL, Redis, Nginx, etc.).</li>
      <li>Selecione a <strong>VPS</strong> onde vai instalar.</li>
      <li>Preencha os campos obrigatorios (dominio, repositorio, etc. — depende do template).</li>
      <li>Clique em <strong>Instalar</strong>.</li>
      <li>Aguarde — o status vai de "Instalando" para "Rodando" quando concluir.</li>
    </ol>
    <div class="tip">Se a instalacao falhar, o status muda para "Erro". Clique para ver a mensagem de erro e tente novamente.</div>
  </details>

  <details>
    <summary>Quais aplicacoes estao disponiveis?</summary>
    <ul>
      <li><strong>WordPress</strong> — blog, site ou loja virtual</li>
      <li><strong>PHP Laravel</strong> — framework PHP moderno</li>
      <li><strong>Node.js</strong> — APIs e aplicacoes com JavaScript</li>
      <li><strong>Python (Django/Flask)</strong> — aplicacoes Python</li>
      <li><strong>MySQL</strong> — banco de dados relacional</li>
      <li><strong>PostgreSQL</strong> — banco de dados avancado</li>
      <li><strong>Redis</strong> — cache e filas em memoria</li>
      <li><strong>Nginx</strong> — servidor web e proxy reverso</li>
      <li><strong>Site Estatico</strong> — HTML/CSS/JS puro</li>
      <li><strong>Roundcube Webmail</strong> — cliente de e-mail web</li>
    </ul>
  </details>

  <details>
    <summary>Qual a diferenca entre Aplicacoes e Git Deploy?</summary>
    <ul>
      <li><strong>Catalogo de Aplicacoes:</strong> instala templates prontos com 1 clique. Ideal para quem quer algo funcionando rapido sem ter um repositorio Git.</li>
      <li><strong>Git Deploy:</strong> conecta um repositorio seu (GitHub/GitLab) e faz deploy do seu codigo personalizado. Ideal para desenvolvedores que ja tem um projeto.</li>
    </ul>
    <p><strong>Quando usar cada um:</strong></p>
    <ul>
      <li>"Quero um WordPress rapido" &rarr; Catalogo</li>
      <li>"Tenho meu codigo no GitHub e quero publicar" &rarr; Git Deploy</li>
      <li>"Preciso de um banco MySQL" &rarr; Catalogo (ou Bancos de Dados)</li>
      <li>"Quero um site custom com React/Next.js" &rarr; Git Deploy</li>
    </ul>
  </details>

  <details>
    <summary>A instalacao falhou (status "Erro"). O que fazer?</summary>
    <ol>
      <li>Clique no card da aplicacao para ver a <strong>mensagem de erro</strong>.</li>
      <li>Erros comuns:
        <ul>
          <li><strong>"Porta em uso"</strong> — outra aplicacao ja esta usando essa porta. Altere a porta da nova app.</li>
          <li><strong>"Disco cheio"</strong> — sua VPS nao tem espaco suficiente. Apague arquivos desnecessarios ou faca upgrade.</li>
          <li><strong>"Timeout"</strong> — a instalacao demorou demais (servidor lento). Tente novamente.</li>
        </ul>
      </li>
      <li>Se nao entender o erro, copie a mensagem e cole num <a href="/cliente/tickets/novo">ticket</a>.</li>
    </ol>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- EMAILS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">E-mails Profissionais</div>

  <div class="faq-section">Criar e usar e-mails com seu dominio</div>

  <details>
    <summary>Como criar um e-mail com meu dominio (ex: contato@meusite.com)?</summary>
    <ol>
      <li>Primeiro, cadastre seu dominio raiz em <a href="/cliente/dominios">Dominios</a> (veja a secao de Dominios acima).</li>
      <li>Depois, va em <a href="/cliente/emails">E-mails</a>.</li>
      <li>Preencha o <strong>usuario</strong> (parte antes do @, ex: "contato"), selecione o <strong>dominio</strong> e defina uma <strong>senha</strong>.</li>
      <li>Defina a <strong>cota</strong> de armazenamento (quanto espaco essa caixa pode usar).</li>
      <li>Clique em <strong>Criar e-mail</strong>.</li>
    </ol>
    <p>Pronto! Voce ja pode usar o e-mail pelo webmail ou configurar no Outlook/Gmail.</p>
  </details>

  <details>
    <summary>Como configurar no Outlook, Gmail ou celular?</summary>
    <p>Em <a href="/cliente/emails">E-mails</a>, role ate a secao "Configurar em outros apps". La voce encontra:</p>
    <ul>
      <li>Dados do servidor (IMAP, SMTP, portas)</li>
      <li>Tutorial passo a passo para <strong>Outlook</strong> (PC e celular)</li>
      <li>Tutorial passo a passo para <strong>Gmail</strong> (importar no Gmail)</li>
      <li>Tutorial passo a passo para <strong>Apple Mail</strong> (iPhone/Mac)</li>
      <li>Tutorial passo a passo para <strong>Thunderbird</strong></li>
    </ul>
    <p>Resumo rapido dos dados:</p>
    <ul>
      <li><strong>IMAP:</strong> porta 993 com SSL/TLS</li>
      <li><strong>SMTP:</strong> porta 587 com STARTTLS</li>
      <li><strong>Usuario:</strong> seu e-mail completo (ex: contato@meusite.com)</li>
      <li><strong>Senha:</strong> a que voce definiu ao criar</li>
    </ul>
  </details>

  <details>
    <summary>Como acessar pelo webmail (navegador)?</summary>
    <p>Na listagem de e-mails, clique em <strong>Webmail</strong> ao lado do endereco. Voce sera levado para o webmail do sistema.</p>
    <p>Se quiser um endereco personalizado (tipo <code>webmail.meusite.com</code>), va em <a href="/cliente/emails/dominios">Dominios de E-mail</a> e ative o "Webmail personalizado". O sistema vai pedir para criar um registro CNAME no seu DNS.</p>
  </details>

  <details>
    <summary>Como configurar DNS para o e-mail funcionar?</summary>
    <p>Ao adicionar um dominio de e-mail, o sistema mostra exatamente quais registros criar no seu DNS:</p>
    <ul>
      <li><strong>MX</strong> — direciona os e-mails para nosso servidor</li>
      <li><strong>SPF</strong> — autoriza nosso servidor a enviar e-mails em nome do seu dominio</li>
      <li><strong>DKIM</strong> — assina os e-mails para evitar spam</li>
      <li><strong>DMARC</strong> — politica de autenticacao</li>
    </ul>
    <p>Crie esses registros no painel do seu provedor de DNS (Cloudflare, Registro.br, etc.). A verificacao e automatica apos propagacao.</p>
  </details>

  <div class="faq-section">Problemas comuns com e-mail</div>

  <details>
    <summary>Meus e-mails estao indo para o spam do destinatario</summary>
    <p><strong>Causas mais comuns:</strong></p>
    <ul>
      <li><strong>DNS incompleto:</strong> verifique se todos os registros (MX, SPF, DKIM, DMARC) estao configurados corretamente.</li>
      <li><strong>Dominio novo:</strong> dominios recem-registrados tem reputacao baixa. A situacao melhora com o tempo.</li>
      <li><strong>Conteudo do e-mail:</strong> evite usar palavras excessivamente promocionais, imagens sem texto, ou links encurtados.</li>
    </ul>
    <p><strong>O que fazer:</strong></p>
    <ol>
      <li>Em <a href="/cliente/emails/dominios">Dominios de E-mail</a>, verifique se todos os registros mostram status verde (verificado).</li>
      <li>Envie e-mails de teste para o Gmail e verifique o cabecalho ("Mostrar original") — procure por "spf=pass", "dkim=pass" e "dmarc=pass".</li>
      <li>Se algum estiver "fail", revise o registro DNS correspondente.</li>
    </ol>
  </details>

  <details>
    <summary>Nao consigo enviar e-mails (erro de SMTP)</summary>
    <ul>
      <li>Verifique se esta usando a <strong>porta correta</strong>: 587 com STARTTLS (nao use a 25, ela e bloqueada).</li>
      <li>Verifique se o <strong>usuario</strong> e o e-mail completo (com @dominio.com) e a <strong>senha</strong> esta correta.</li>
      <li>Se usa Cloudflare, certifique-se de que o registro MX <strong>nao esta com proxy</strong> (nuvem cinza, nao laranja).</li>
    </ul>
  </details>

  <details>
    <summary>Quero receber e-mails de um dominio no Gmail</summary>
    <p>Voce pode configurar o Gmail para buscar seus e-mails profissionais. Veja o tutorial completo em <a href="/cliente/emails">E-mails</a> &rarr; secao "Configurar em outros apps" &rarr; Gmail.</p>
    <p>Resumo: Gmail &rarr; Configuracoes &rarr; Contas e importacao &rarr; Adicionar conta de e-mail &rarr; IMAP &rarr; use os dados (servidor, porta 993, SSL).</p>
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
      <li>Escreva sua <strong>mensagem</strong> detalhando o que esta acontecendo.</li>
      <li>Se quiser, anexe um arquivo (print de tela, log, etc. — max 5 MB).</li>
      <li>Clique em <strong>Enviar</strong>.</li>
    </ol>
    <p>Voce recebera notificacao por e-mail quando a equipe responder.</p>
  </details>

  <details>
    <summary>Qual a diferenca entre ticket e chat?</summary>
    <ul>
      <li><strong>Chat:</strong> respostas em tempo real. Ideal para duvidas rapidas e urgencias. Funciona em horario comercial.</li>
      <li><strong>Ticket:</strong> registro formal com historico completo. Ideal para problemas tecnicos, solicitacoes e acompanhamento. A equipe responde assim que possivel.</li>
    </ul>
    <div class="tip">Use o chat para coisas rapidas. Use tickets para problemas que precisam de investigacao ou que voce quer ter um historico.</div>
  </details>

  <details>
    <summary>Como usar o chat ao vivo?</summary>
    <ol>
      <li>Clique no icone de chat no canto inferior direito de qualquer pagina.</li>
      <li>Escolha "Falar com atendente".</li>
      <li>Descreva brevemente seu problema e clique em "Iniciar".</li>
      <li>Uma sala sera criada e a equipe sera notificada. Aguarde a resposta (geralmente poucos minutos).</li>
    </ol>
    <p>Voce pode enviar emojis e arquivos (imagens, PDF, documentos ate 5 MB) direto no chat.</p>
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
      <li>Voce vera tres indicadores em tempo real: <strong>CPU</strong>, <strong>RAM</strong> e <strong>Disco</strong>.</li>
      <li>Abaixo, um historico com graficos mostrando a evolucao ao longo do tempo.</li>
    </ol>
    <p>A pagina atualiza automaticamente a cada 30 segundos.</p>
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
      <li>Na secao "Alterar senha", preencha a senha atual, a nova senha e confirme.</li>
      <li>Clique em <strong>Salvar</strong>.</li>
    </ol>
  </details>

  <details>
    <summary>Esqueci minha senha. Como recuperar?</summary>
    <ol>
      <li>Na tela de login, clique em <strong>"Esqueci minha senha"</strong>.</li>
      <li>Informe seu e-mail de cadastro.</li>
      <li>Voce recebera um e-mail com um link para redefinir a senha (valido por 1 hora).</li>
      <li>Clique no link, defina uma nova senha e pronto.</li>
    </ol>
    <div class="tip">Se nao receber o e-mail, verifique a pasta de spam. Se mesmo assim nao chegar, abra um ticket informando o e-mail.</div>
  </details>

  <details>
    <summary>Como ativar a verificacao em dois fatores (2FA)?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Seguranca</strong>.</li>
      <li>Baixe um app autenticador no celular (Google Authenticator, Authy, ou similar — gratuitos na loja de apps).</li>
      <li>No painel, escaneie o <strong>QR Code</strong> com o app autenticador.</li>
      <li>O app vai mostrar um codigo de 6 digitos que muda a cada 30 segundos.</li>
      <li>Digite esse codigo no campo de confirmacao do painel e clique em <strong>Ativar</strong>.</li>
    </ol>
    <p>A partir de agora, toda vez que voce fizer login, alem da senha, vai precisar informar o codigo do app autenticador. Isso protege sua conta mesmo se alguem descobrir sua senha.</p>
  </details>

  <details>
    <summary>Como desativar o 2FA?</summary>
    <ol>
      <li>Va em <strong>Seguranca</strong>.</li>
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
      <li>Voce vera seus planos ativos com VPS vinculada, status e proximo vencimento.</li>
      <li>Para ver o historico de cobracas, clique em <strong>"Historico de cobrancas"</strong>.</li>
    </ol>
  </details>

  <details>
    <summary>Como fazer upgrade do meu plano?</summary>
    <ol>
      <li>Em <strong>Assinaturas</strong>, clique em <strong>"Alterar plano"</strong> na assinatura desejada.</li>
      <li>Escolha o novo plano com mais recursos.</li>
      <li>O upgrade e imediato — os novos recursos sao aplicados automaticamente.</li>
      <li>A diferenca de valor e cobrada proporcionalmente.</li>
    </ol>
  </details>

  <details>
    <summary>Como solicitar reembolso ou cancelar?</summary>
    <p>Acesse <a href="/cliente/assinaturas/historico">Historico de cobrancas</a>. No final da pagina, expanda "Solicitar reembolso", selecione a assinatura e descreva o motivo. Para cancelamento, abra um ticket com o assunto "Cancelamento".</p>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- VPS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">VPS (Servidores Virtuais)</div>

  <details>
    <summary>Como funciona minha VPS?</summary>
    <p>Sua VPS e um servidor virtual com recursos dedicados (CPU, RAM e disco exclusivos para voce). E como ter um computador na nuvem onde voce pode instalar qualquer coisa.</p>
    <p>Apos assinar um plano, a VPS e criada automaticamente e voce pode gerencia-la pelo painel: terminal, arquivos, deploy, backups, monitoramento — tudo pelo navegador.</p>
  </details>

  <details>
    <summary>Onde vejo o status da minha VPS?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>VPS</strong>.</li>
      <li>Voce vera seus servidores com o status atual:
        <ul>
          <li><strong>Em execucao</strong> — funcionando normalmente</li>
          <li><strong>Provisionando</strong> — sendo criada (aguarde alguns minutos)</li>
          <li><strong>Suspensa</strong> — pagamento pendente</li>
          <li><strong>Parada</strong> — desligada</li>
        </ul>
      </li>
    </ol>
  </details>

  <details>
    <summary>Minha VPS esta suspensa. O que fazer?</summary>
    <p>A suspensao acontece por inadimplencia. Para resolver:</p>
    <ol>
      <li>Va em <a href="/cliente/assinaturas">Assinaturas</a> e verifique se ha faturas pendentes.</li>
      <li>Realize o pagamento.</li>
      <li>A reativacao e automatica apos a confirmacao. Se nao reativar em 24h, abra um ticket.</li>
    </ol>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- MIGRACAO WORDPRESS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Migracao de WordPress</div>

  <details>
    <summary>Como migrar meu WordPress de outro servidor para ca?</summary>
    <ol>
      <li>No menu lateral, clique em <strong>Migrar WordPress</strong>.</li>
      <li>Clique em <strong>Nova Migracao</strong>.</li>
      <li>Na secao "Para onde migrar", selecione sua <strong>VPS de destino</strong>.</li>
      <li>Na secao "Servidor de origem", preencha:
        <ul>
          <li><strong>IP ou hostname</strong> do servidor atual (onde o WordPress esta agora)</li>
          <li><strong>Porta SSH</strong> (geralmente 22)</li>
          <li><strong>Usuario</strong> (geralmente root)</li>
          <li><strong>Senha SSH</strong></li>
          <li><strong>Caminho do WordPress</strong> no servidor (ex: <code>/www/wwwroot/meusite.com</code> no AAPanel)</li>
        </ul>
      </li>
      <li>Na secao "Banco de dados", preencha:
        <ul>
          <li><strong>Nome do banco</strong>, <strong>usuario</strong>, <strong>senha</strong> e <strong>host</strong> do MySQL</li>
          <li>Dica: esses dados estao no arquivo <code>wp-config.php</code> do WordPress atual</li>
        </ul>
      </li>
      <li>Clique em <strong>Iniciar Migracao</strong>.</li>
      <li>Aguarde — o sistema copia todos os arquivos e o banco automaticamente.</li>
    </ol>
    <div class="tip">Funciona com qualquer servidor que tenha acesso SSH: AAPanel, cPanel, servidores dedicados, etc. Sites pesados (10GB, 50GB+) migram sem problema.</div>
  </details>

  <details>
    <summary>Como ativar meu dominio real apos a migracao?</summary>
    <ol>
      <li>Apos a migracao concluir, seu site esta rodando com um dominio temporario.</li>
      <li>Teste o site pelo dominio temporario para garantir que esta tudo OK.</li>
      <li>Quando estiver pronto, va na tela de detalhes da migracao.</li>
      <li>Na secao "Dominio", informe seu dominio real (ex: <code>meusite.com.br</code>).</li>
      <li>Clique em <strong>Ativar dominio</strong>.</li>
      <li>O sistema atualiza automaticamente o banco do WordPress (siteurl/home), o wp-config.php e o Nginx.</li>
      <li>Aponte o DNS do seu dominio (registro A) para o IP do nosso servidor.</li>
    </ol>
  </details>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- PERGUNTAS RAPIDAS -->
<!-- ═══════════════════════════════════════════════ -->
<div class="card-new" style="max-width:760px;margin-top:20px;">
  <div class="card-new-title" style="margin-bottom:16px;">Perguntas rapidas</div>

  <details>
    <summary>Nao encontro uma opcao no menu. Por que?</summary>
    <p>O menu mostra apenas as funcionalidades do seu plano. Planos mais simples (como WordPress) nao incluem Terminal ou Monitoramento, por exemplo. Para acesso completo, contrate um plano VPS.</p>
  </details>

  <details>
    <summary>Como trocar o idioma do painel?</summary>
    <p>No topo de qualquer pagina, clique no seletor de idioma (bandeira) e escolha Portugues, English ou Espanol.</p>
  </details>

  <details>
    <summary>Como gerenciar cookies?</summary>
    <p>Clique em "Cookies" no rodape de qualquer pagina. Voce pode ativar ou desativar cookies de analytics, marketing e preferencias. Cookies necessarios (sessao e seguranca) nao podem ser desativados.</p>
  </details>

  <details>
    <summary>Como entrar em contato com o suporte?</summary>
    <p>Voce tem tres opcoes:</p>
    <ul>
      <li><strong>Chat ao vivo:</strong> clique no icone de chat no canto inferior direito</li>
      <li><strong>Ticket:</strong> menu lateral &rarr; Tickets &rarr; Novo Ticket</li>
      <li><strong>E-mail:</strong> envie para o e-mail de suporte da empresa</li>
    </ul>
  </details>

  <details>
    <summary>O que sao API Keys e para que servem?</summary>
    <p>API Keys permitem que voce (ou sistemas externos) se comuniquem com a plataforma de forma automatizada. Por exemplo: um script que cria backups, um bot que abre tickets, ou uma integracao com outro sistema.</p>
    <p>Se voce nao e desenvolvedor e nao vai integrar nada, nao precisa mexer nas API Keys.</p>
    <p>Se precisar: va em <a href="/cliente/api-keys">API Keys</a> no menu lateral &rarr; "Criar API Key" &rarr; escolha um nome e os escopos (permissoes).</p>
  </details>

  <details>
    <summary>Qual a diferenca entre planos VPS, Web Hosting e WordPress?</summary>
    <ul>
      <li><strong>VPS:</strong> acesso completo ao servidor (terminal, monitoramento, tudo). Para quem precisa de controle total.</li>
      <li><strong>Web Hosting:</strong> painel simplificado com catalogo de apps e git deploy. Sem terminal. Para quem quer praticidade.</li>
      <li><strong>WordPress:</strong> focado 100% em WordPress. Instalacao em 1 clique, backups, SSL. O mais simples de todos.</li>
    </ul>
    <p><strong>Nao sabe qual escolher?</strong> Se voce so quer um site WordPress, escolha o plano WordPress. Se tem um projeto custom (Node, PHP, Python), escolha VPS ou Web Hosting.</p>
  </details>

  <details>
    <summary>O painel esta lento ou travando. O que fazer?</summary>
    <ul>
      <li><strong>Limpe o cache do navegador:</strong> Ctrl+Shift+Delete &rarr; limpe dados de navegacao.</li>
      <li><strong>Tente outro navegador:</strong> Chrome, Firefox ou Edge (evite Internet Explorer).</li>
      <li><strong>Conexao lenta:</strong> teste sua internet em <a href="https://fast.com" target="_blank" rel="noopener">fast.com</a>.</li>
      <li>Se o problema persistir, pode ser algo no servidor. Abra um ticket informando o que esta lento.</li>
    </ul>
  </details>

  <details>
    <summary>Posso ter mais de um projeto/site na mesma VPS?</summary>
    <p><strong>Sim!</strong> Voce pode ter quantos projetos quiser na mesma VPS. Basta usar o Git Deploy para cada projeto, cada um com seu proprio subdominio.</p>
    <p>Exemplo: voce pode ter:</p>
    <ul>
      <li><code>site.meudominio.com</code> — seu site principal</li>
      <li><code>api.meudominio.com</code> — sua API backend</li>
      <li><code>blog.meudominio.com</code> — um WordPress</li>
    </ul>
    <p>Todos na mesma VPS, cada um no seu diretorio.</p>
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
