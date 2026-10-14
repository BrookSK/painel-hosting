# Novidades

Acompanhe aqui as novidades, melhorias e correções do painel.

---

## [3.6.2] — 2026-10-14

### Melhorado
- **Mais segurança para a sua conta e os seus serviços**: fizemos uma revisão completa de segurança no painel, na API e nos servidores, reforçando a proteção dos seus dados, arquivos e sites
- **Upgrade de plano e serviços adicionais**: ao mudar para um plano maior ou contratar um serviço adicional, geramos a cobrança na hora e o recurso é liberado automaticamente assim que o pagamento é confirmado
- **Proteção extra no login e na verificação em duas etapas**

### Corrigido
- **Pasta liberada para gravação voltava a ficar bloqueada**: depois de usar "Liberar pasta de arquivos", a liberação se perdia na próxima publicação do site e os uploads paravam de funcionar. Agora o painel lembra das pastas liberadas e mantém a permissão a cada publicação
- **Publicação via Git (Git Deploy) e tarefas no servidor**: para contas novas, esses recursos passam a ser liberados pelo nosso suporte. Se você já usa, nada muda. Se quiser começar a usar, é só abrir um chamado

---

## [3.6.1] — 2026-10-14

### Melhorado
- Melhorias de estabilidade e segurança

---

## [3.6.0] — 2026-10-03

### Adicionado
- **Integração completa por API para parceiros**: sistemas externos conseguem cadastrar clientes, criar servidores e publicar sites a partir do repositório de forma automática
- **Endereço temporário para testes**: ao publicar pela API, dá para pedir um endereço provisório com cadeado de segurança (https) para ver o site antes de usar o domínio definitivo
- **Avisos automáticos (webhooks)**: seu sistema recebe uma notificação quando um servidor fica pronto ou um site é publicado
- **Tela de Webhooks no painel**: crie e acompanhe seus webhooks pelo navegador, escolhendo os eventos que quer receber
- **Repositórios Git pela API**: parceiros podem criar o repositório do projeto e liberar o acesso dos desenvolvedores automaticamente
- **Pagamento por link**: você pode pagar o seu plano por um link enviado pela nossa equipe, escolhendo cartão, boleto ou PIX, sem precisar entrar no painel

### Melhorado
- **Documentação da API ampliada**, com exemplos de uso de cada recurso

---

## [3.5.1] — 2026-09-04

### Adicionado
- **Botão "Regerar SSL"**: se o cadeado de segurança (https) do seu site cair, basta um clique para reemitir o certificado

### Corrigido
- Publicação automática que não terminava em sites maiores
- O painel agora mostra "Publicando..." enquanto a atualização está indo para o ar

---

## [3.5.0] — 2026-09-04

### Adicionado
- **Página de manutenção amigável**: quando um site fica temporariamente indisponível, o visitante vê uma página de "em manutenção" no lugar de um erro

### Melhorado
- **Histórico de publicações mais claro**: cada publicação mostra o título da alteração enviada
- **Publicação automática ao atualizar o repositório** mais estável, com instruções mais claras no painel
- **Reativação automática após pagamento**: ao regularizar o pagamento, seu site volta ao ar sozinho

### Corrigido
- Sites que ficavam com o endereço errado após a publicação
- Cadeado de segurança (SSL) que, em alguns casos, não era ativado automaticamente

---

## [3.4.0] — 2026-07-29

### Adicionado
- **Publicação automática (Auto Deploy)**: sempre que você enviar uma atualização para a branch configurada, o site é atualizado sozinho
- **Integração com GitHub, GitLab e Bitbucket**, com passo a passo dentro do painel
- **Gerenciamento de Armazenamento**: veja quanto espaço cada site, banco de dados e arquivo ocupa, e libere espaço com poucos cliques
- **Apagar arquivos ao remover**: ao remover um site ou aplicação, você escolhe se quer apagar também os arquivos e o banco de dados
- **Migração de WordPress mais segura**: verificamos o espaço disponível antes de começar e mostramos o andamento em tempo real
- **Central de Ajuda completa**: tutoriais passo a passo para todas as funções do painel e soluções para os problemas mais comuns

---

## [3.3.0] — 2026-07-28

### Adicionado
- **Migração de WordPress**: traga seu site WordPress de outra hospedagem direto pelo painel, sem precisar fazer backup manual
- **Endereço temporário**: o site migrado recebe um endereço provisório para você testar antes de apontar o domínio
- **Ativação do domínio com um clique** depois dos testes

### Melhorado
- Migração mais estável para sites grandes, com novas tentativas automáticas em caso de queda

---

## [3.2.0] — 2026-07-09

### Adicionado
- Notificações por e-mail quando respondemos seus chamados e mensagens no chat

### Melhorado
- A chave de acesso do Git Deploy aparece logo após criar a integração
- Terminal mostra uma tela amigável quando você ainda não tem VPS ativa

---

## [3.1.0] — 2026-07-08

### Melhorado
- **SSL automático** na instalação de aplicações
- O status de instalação de aplicações atualiza sozinho na tela

---

## [3.0.0] — 2026-04-16

### Adicionado
- **Novos tipos de plano**: VPS, WordPress Gerenciado, Web Hosting, Node.js, PHP/Laravel, Python, C/C++ e App Genérico
- **Painel adaptado ao seu plano**: o menu mostra apenas os recursos incluídos no seu plano, com barras de uso de sites, bancos e tarefas agendadas
- **Upgrade e downgrade de plano** pelo próprio painel
- **Serviços adicionais**: contrate ou cancele extras (mais armazenamento, backup extra, e-mails, suporte prioritário) quando quiser
- **Planos anuais com renovação automática**
- **Aplicações C/C++** com compilação automática
- Novas páginas de produtos no site

---

## [2.4.0] — 2026-03-27

### Adicionado
- **Domínio principal na VPS**: aponte seu domínio (ex.: meudominio.com.br) direto para a sua VPS, com verificação automática
- **Planos personalizados** sob medida para a sua empresa

---

## [2.3.0] — 2026-03-26

### Adicionado
- **Tela única de domínios**: cadastre e verifique seus domínios e subdomínios em um só lugar
- **Endereço automático para a VPS** ao ser criada

### Melhorado
- Aplicações e sites publicados agora usam apenas subdomínios verificados, com mais segurança

---

## [2.2.0] — 2026-03-26

### Adicionado
- **Contratação simplificada**: contrate em poucos passos, escolhendo quantidade de servidores, período e adicionais, e pague por PIX, boleto ou cartão sem sair da página
- **Várias assinaturas na mesma conta**
- **Descontos para planos semestrais e anuais**
- **Escolha de moeda** (R$ ou US$) independente do idioma
- **Histórico de cobranças** com todas as suas faturas

---

## [2.1.0] — 2026-03-25

### Adicionado
- **Planos com adicionais**: escolha extras antes de pagar e veja o valor total atualizado na hora

---

## [2.0.0] — 2026-03-24

### Adicionado
- **E-mail profissional com seu domínio**, com webmail e verificação de DNS pelo painel
- **Webmail no seu próprio endereço** (ex.: webmail.seudominio.com)
- **Tutoriais de configuração de e-mail** para Outlook, Gmail, Apple Mail e Thunderbird
- **Cota de armazenamento por conta de e-mail**
- **Volta para a página certa após o login**

### Melhorado
- Sessão de login mais longa
- Backups conforme o seu plano

---

## [1.8.0] — 2026-03-22

### Adicionado
- Novas páginas de soluções e menu do site reorganizado
- **Preferências de cookies (LGPD)**: escolha quais cookies aceitar

---

## [1.7.0] — 2026-03-21

### Adicionado
- **Instalação de aplicações com um clique**: WordPress, Node.js, PHP Laravel, MySQL, Redis, Nginx e site estático
- **Chat com emojis e envio de arquivos** (imagens, PDF, documentos)
- **Pesquisa de satisfação** ao encerrar um atendimento
- Nome do atendente nas respostas do chat

---

## [1.6.0] — 2026-03-21

### Adicionado
- **Verificação em duas etapas (2FA)** para proteger sua conta
- **Endereço de cobrança** em Minha Conta
- **Novo visual do painel**

---

## [1.5.0] — 2026-03-21

### Adicionado
- **Recuperação de senha** pelo link "Esqueci minha senha"

---

## [1.4.0] — 2026-03-20

### Adicionado
- **Chat em tempo real** com a nossa equipe
- **E-mails profissionais** pelo painel
- **Novo painel inicial** com o resumo dos seus serviços e guia de primeiros passos
- **Tela de VPS e monitoramento renovadas**
- Páginas de Termos de Uso, Privacidade e Novidades

---

## [1.3.0] — 2026-03-20

### Adicionado
- Gráficos de uso no monitoramento
- Página pública de status e histórico de incidentes
- Página de contato
- Acompanhamento de assinaturas e cobranças
- Solicitação de reembolso
- Central de ajuda

### Melhorado
- Melhorias de segurança

---

## [1.1.0] — 2026-03-10

### Adicionado
- Terminal da VPS pelo navegador
- Backups da VPS
- Publicação de aplicações

---

## [1.0.0] — 2026-03-01

### Adicionado
- Lançamento do painel: VPS, chamados de suporte, planos e assinaturas, aplicações, monitoramento e página de status
