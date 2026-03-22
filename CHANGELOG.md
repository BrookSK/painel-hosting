# Changelog

Todas as mudanças notáveis neste projeto são documentadas aqui.
Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [1.8.0] — 2026-03-22

### Adicionado
- **Mega menu estilo DigitalOcean na navbar pública** — menus "Produtos" e "Recursos" com 4 colunas cada, hover com fade-in no desktop, drawer com accordion no mobile
- **5 landing pages dedicadas em `/solucoes/`** — VPS, Aplicações, DevOps, E-mail, Segurança com layout de alta conversão (8 seções: hero, problema, solução, benefícios, como funciona, prova social, CTA, FAQ)
- `SolucoesController` com 5 métodos e rotas públicas
- Layout compartilhado `app/Views/solucoes/_layout.php` com design gradiente azul→roxo, cards responsivos e FAQ accordion
- **Sistema de cookies e consentimento (LGPD)** — banner fixo no rodapé com 3 opções (aceitar todos, rejeitar opcionais, configurar)
- Modal de configuração granular com toggles por categoria: necessários (sempre ativo), analytics, marketing, preferências
- Tabela `cookie_consents` (migration `0029_cookie_consents.sql`) com user_id, session_id, IP, user_agent, preferences JSON
- `CookieConsentService` com `salvarConsentimento()`, `obterConsentimento()`, `verificarPermissao()`
- `CookieConsentController` com endpoints `POST /cookies/consent` (CSRF + rate limit) e `GET /cookies/consent`
- Cookie `cookie_consent` no navegador com validade de 12 meses, `Secure`, `SameSite=Lax`
- Função JS `ckTemPermissao('categoria')` para bloqueio condicional de scripts
- Evento `cookieConsentUpdated` disparado ao salvar preferências
- Link "Cookies" no footer público abre modal de preferências
- Seção de cookies adicionada à página `/privacidade` com lista de categorias e link para configurar
- Chaves i18n `cookies.*` nos 3 idiomas (pt-BR, en-US, es-ES)

### Alterado
- **Copywriting das landing pages refinado** — tom profissional para decisores (CEOs, gestores), sem linguagem informal, com mais autoridade e profundidade nos 3 idiomas
- Emojis excessivos removidos do layout das landing pages (😩, ✅, ✔) — substituídos por ícones SVG discretos
- Espaçamento entre seções das landing pages aumentado (80px → 100px)
- Mega menu usa `position: fixed` para funcionar com scroll
- Navbar pública reescrita com mega menus: Início, Produtos▾, Infraestrutura, Recursos▾, Planos, Status, Contato
- Chaves `mega.*` adicionadas nos 3 idiomas (~50 chaves cada)
- Chaves `sol.*` reescritas nos 3 idiomas com copy profissional focado em conversão

---

## [1.7.0] — 2026-03-21

### Adicionado
- **Aplicações pré-configuradas (One-Click Install)** — catálogo visual em `/cliente/aplicacoes/catalogo` com 7 templates prontos (WordPress, Node.js, PHP Laravel, MySQL, Redis, Nginx, Site Estático)
- Tabela `app_templates` com seed de templates padrão (migration `0028_app_templates.sql`)
- Colunas `template_id`, `container_id`, `environment_json`, `logs` na tabela `applications`
- `AppInstallService` — provisionamento automático: pull de imagem, configuração de env vars, clone de repo, criação de container com labels, auto-assign de porta
- Job handler `install_app_template` para instalação assíncrona via fila
- Modal de instalação com seleção de VPS, domínio, repositório e variáveis de ambiente
- Status badges nas aplicações: `installing`, `running`, `stopped`, `error`
- Link "Catálogo" como sub-item na sidebar do cliente
- **Chat — emojis, upload de arquivos e polling fallback** em todos os 3 locais de chat (widget FAB, `/cliente/chat`, `/equipe/chat/ver`)
- Emoji picker inline com categorias (smileys, gestos, corações, objetos, natureza)
- Upload de arquivos no chat (imagens, PDF, DOC, TXT até 5 MB) com preview inline
- Polling HTTP fallback: tenta WebSocket 2x, depois cai para polling a cada 3s
- Endpoints de polling e envio HTTP para cliente e equipe (`/cliente/chat/poll`, `/cliente/chat/enviar`, `/equipe/chat/poll`, `/equipe/chat/enviar`)
- Tabela `chat_files` (migration `0027_chat_files.sql`)
- `ChatUploadController` para upload de arquivos no chat
- **Pesquisa de satisfação ao encerrar chat** — ao fechar um chat, e-mail automático é enviado ao cliente com link para `/cliente/avaliar?type=chat&id=X`
- **Nome do agente nas respostas do chat** — mensagens da equipe exibem "Nome · HH:MM" em vez de apenas "Equipe"
- **Separadores de dia no chat** — linha visual com data dd/mm/aaaa entre mensagens de dias diferentes
- **Histórico de chats da equipe** — `/equipe/chat` agora exibe chats abertos e encerrados em seções separadas; chats encerrados renderizam histórico completo server-side
- **WebSocket URL configurável** — campo `chat.ws_url` nas configurações; fallback automático para `ws://host:porta`
- **Auditoria de segurança (GitGuardian)** — correções aplicadas:
  - `config/instalacao.php` removido do Git (`.gitignore` ativado, `git rm --cached`)
  - `config/instalacao.exemplo.php` criado como template
  - `SshCrypto` — fallback de chave hardcoded removido; agora lança exceção se `app.secret_key` não configurado
  - `InicializacaoService` — `app.secret_key` gerado automaticamente na inicialização

### Corrigido
- Header do painel do cliente exibia "Cliente" genérico nas telas de aplicações — controllers `listar()` e `catalogo()` agora passam `$cliente` com `name`/`email`
- SQL no `ChatRoomService` referenciava `hostname` inexistente — corrigido para `ip_address`
- `aplicacoes-listar.php` referenciava `$cliente['name']` e `$cliente['email']` sem receber do controller

### Alterado
- `ChatRoomService` refatorado com `listarAbertas()` e `listarEncerradas()` usando método privado `listarPorStatus()`
- `ChatMessageService::historico()` faz JOIN com `users` para retornar `sender_name`
- `ChatWsApp::onMessage()` busca nome do admin no banco e inclui `sender_name` no broadcast

---

## [1.6.0] — 2026-03-21

### Adicionado
- **2FA (TOTP) para clientes** — configuração em `/cliente/2fa/configurar` com QR code e chave manual; verificação em `/cliente/2fa/verificar` (tela standalone dark); desativação com confirmação de senha
- Tabela `client_totp` (migration `0025_client_totp.sql`) com `secret`, `enabled`, `created_at`
- Item "Segurança" na sidebar do cliente com link para `/cliente/2fa/configurar`
- Card de segurança em `/cliente/minha-conta` com link para configurar 2FA
- **Endereço do cliente** — campos `address_street`, `address_number`, `address_complement`, `address_city`, `address_state`, `address_zip`, `address_country` em `clients` (migration `0024_client_address.sql`)
- Formulário de endereço em `/cliente/minha-conta`
- **Migração completa do painel do cliente para o novo layout** — sidebar dark, header com avatar/dropdown, design system unificado com o painel da equipe
- Todas as views do cliente migradas: `vps-listar`, `monitoramento-listar`, `monitoramento-ver`, `tickets-listar`, `ticket-novo`, `ticket-ver`, `chat`, `emails-listar`, `emails-dominios`, `emails-dominios-instrucoes`, `aplicacoes-listar`, `assinaturas-listar`, `ajuda`, `avaliar`, `assinatura-criada`, `status-listar`, `vps-terminal`, `planos`, `minha-conta`, `painel`
- Partials `layout-cliente-inicio.php` e `layout-cliente-fim.php` com sidebar, header, avatar dropdown e scripts de toggle
- `sidebar-cliente.php` com todos os itens de navegação e suporte a collapse

### Corrigido
- `AssinaturasController` (cliente) referenciava colunas inexistentes `s.billing_type`, `s.gateway`, `s.gateway_subscription_id` — query corrigida para usar apenas colunas reais da tabela `subscriptions`
- `PlanosController` não passava `$cliente` para a view — adicionada busca de `name`/`email` do cliente logado
- `a:hover` com `text-decoration:underline` removido do design system (CSP e consistência visual)
- `overflow-x:hidden` movido de `body` para `html` no `estilo.php`
- Google Fonts removido do CSP e substituído por `system-ui` em todo o design system

### Alterado
- Design system do cliente (`estilo.php`) unificado com o da equipe (`estilo-equipe.php`): `.card-new`, `.badge-new`, `.botao`, `.sucesso`, `.erro`, `.page-title`, `.page-subtitle`, `.page-header`
- `layout-cliente-fim.php` já inclui `chat-widget.php` — removida inclusão duplicada das views individuais

---

## [1.5.0] — 2026-03-21

### Adicionado
- **Minha Conta** (`/equipe/minha-conta`): tela para cada membro da equipe editar nome, e-mail, senha e foto de perfil
- Upload de avatar de perfil (PNG, JPG, WEBP, GIF · máx. 2 MB) salvo em `public/uploads/avatars/`
- Coluna `avatar_url` na tabela `users` (migration `0013_user_avatar.sql`)
- **Reset de senha** para equipe (`/equipe/reset-senha`) e cliente (`/cliente/reset-senha`) — fluxo em 2 etapas: solicitar e-mail → link com token → nova senha
- Tabela `password_resets` com token único, tipo (equipe/cliente), expiração de 1 hora e controle de uso (migration `0014_password_resets.sql`)
- Link "Esqueci minha senha" nas telas de login da equipe e do cliente
- Link "Minha Conta" no dropdown do avatar do header e no footer da sidebar da equipe
- Rate limit nas rotas de reset de senha (5 req/5min para solicitar, 10 req/5min para salvar)

### Corrigido
- Header da equipe não exibia nome, role e avatar em telas que não passavam `$usuario` pelo controller — agora busca diretamente do banco via `Auth::equipeId()`
- Tela de permissões por role (`/equipe/permissoes`) com layout quebrado — reescrita com estilos inline consistentes com o restante do painel
- View `minha-conta.php` usava classes CSS inexistentes (`form-group`, `form-label`, `btn btn-primary`) — corrigida para usar `.botao`, `.input`, `.sucesso`, `.erro` do design system

### Alterado
- `header-equipe.php` agora é autossuficiente: busca `name`, `email`, `role` e `avatar_url` do usuário logado diretamente do banco em uma única query junto com a contagem de notificações
- Avatar no header exibe foto de perfil quando disponível, com fallback para iniciais

---

## [1.4.0] — 2026-03-20

### Adicionado
- **Branding dinâmico**: `system.name`, `system.logo_url`, `system.favicon_url`, `system.company_name`, `system.copyright_text` configuráveis via `/equipe/configuracoes`
- Helper `core/SistemaConfig.php` com métodos estáticos (`nome()`, `logoUrl()`, `faviconUrl()`, `empresaNome()`, `copyrightText()`, `termsHtml()`, `privacyHtml()`)
- **Páginas legais públicas**: `/termos` e `/privacidade` com conteúdo HTML editável pelo admin (`legal.terms_html`, `legal.privacy_html`)
- **Changelog público** em `/changelog` — lê `CHANGELOG.md` e converte Markdown → HTML com parser regex próprio (sem dependência externa)
- Seções "Identidade Visual" e "Páginas Legais" em `/equipe/configuracoes`
- Footer global atualizado com copyright dinâmico, links para Termos, Privacidade, Changelog, Status e Contato
- Favicon dinâmico via `<link rel="icon">` em `estilo.php` (usa `system.favicon_url` se configurado)
- Migration `0008_branding_legal.sql` com defaults para todas as novas chaves de settings
- **Chat em tempo real** via WebSocket (Ratchet) — processo separado `chat-ws.php` na porta configurável (`chat.ws_port`, padrão 8082)
- Módulo `app/Services/Chat/` com `ChatWsApp`, `ChatRoomService`, `ChatMessageService`, `ChatTokensService`
- Tabelas `chat_rooms`, `chat_messages`, `chat_tokens` (migration `0007_chat_email.sql`)
- Tokens de chat de uso único com TTL 120s e proteção contra replay (`FOR UPDATE` + `used_at`)
- Rate limit no WebSocket (30 msgs/10s por IP) e no endpoint HTTP de token (10 req/60s)
- Isolamento por room: cliente só acessa a própria room (validação de `client_id` no `onOpen`)
- Atribuição automática de agente ao entrar na room
- Histórico de mensagens enviado no `onOpen` (últimas 100)
- Painel da equipe: listagem de rooms abertas (`/equipe/chat`) e interface de atendimento (`/equipe/chat/ver`)
- Botão "Encerrar chat" com confirmação
- **Sistema de e-mail via Mailcow** — integração com API REST (`/api/v1/add/mailbox`, `/api/v1/delete/mailbox`, `/api/v1/edit/mailbox`)
- Módulo `app/Services/Email/MailcowService` com `criarEmail()`, `removerEmail()`, `alterarSenha()`, `listar()`, `webmailUrl()`
- Tabela `client_emails` (migration `0007_chat_email.sql`)
- Rotas `/cliente/emails` (listar, criar, remover, alterar-senha)
- Link de webmail dinâmico por domínio (`https://webmail.{domain}`) com fallback para URL global
- Modal de alteração de senha com validação JS de confirmação
- Configurações Mailcow e Chat WS em `/equipe/configuracoes` (`email.mailcow_url`, `email.mailcow_key`, `email.webmail_url`, `chat.ws_port`)
- **Dashboard do cliente** reformulado: stat-cards com dados reais (total VPS, VPS running, tickets abertos, plano ativo)
- Nav-cards com ícones para todas as seções do painel
- **Onboarding modal** para novos clientes (`onboarding_done = 0`): guia de 4 passos, marcado como concluído via `POST /cliente/onboarding/concluir`
- Coluna `onboarding_done` em `clients` (migration `0007_chat_email.sql`)
- **VPS do cliente** reformulada: cards responsivos com dots de status coloridos (verde/amarelo/vermelho), specs em grid, ações contextuais por status
- **Monitoramento** melhorado: cards de resumo da última coleta com cores por threshold (verde < 70%, amarelo 70–90%, vermelho ≥ 90%), auto-refresh a cada 30s
- Versão do sistema exibida no footer de todas as páginas
- Responsividade completa: media queries 768px e 480px em `estilo.php`

### Corrigido
- Bug crítico: form de remoção de email enviava `local_part`/`domain` mas controller esperava `email_id`
- Bug: campo `senha` no form de criação de email enviava `name="password"` mas controller lia `$req->post['senha']`
- Bug: CSRF no chat do cliente era lido via `document.cookie` (frágil) — substituído por `json_encode(Csrf::token())` embutido no PHP
- Bug: `MailcowService` chamava `$this->http->post()` inexistente — corrigido para `requestJson('POST', ...)` e `requestJson('DELETE', ...)`
- Onboarding modal iniciava com `display:flex` no CSS — corrigido para `display:none` + ativação via JS (não trava interface se JS falhar)
- Double-submit em forms de ação de VPS: botão desabilitado após primeiro clique com feedback "Processando..."
- Reconexão do chat do cliente não chamava `setTimeout` em caso de erro no token — corrigido

### Alterado
- `PainelController` agora busca dados reais: contagem de VPS, VPS running, tickets abertos, assinatura ativa, `onboarding_done`
- `ConfiguracoesController` e view de configurações expandidos com seções Mailcow, Chat WS, Identidade Visual e Páginas Legais
- Footer global (`_partials/footer.php`) usa `SistemaConfig` para copyright e nome dinâmicos
- Módulo `app/Services/Chat/` com `ChatWsApp`, `ChatRoomService`, `ChatMessageService`, `ChatTokensService`
- Tabelas `chat_rooms`, `chat_messages`, `chat_tokens` (migration `0007_chat_email.sql`)
- Tokens de chat de uso único com TTL 120s e proteção contra replay (`FOR UPDATE` + `used_at`)
- Rate limit no WebSocket (30 msgs/10s por IP) e no endpoint HTTP de token (10 req/60s)
- Isolamento por room: cliente só acessa a própria room (validação de `client_id` no `onOpen`)
- Atribuição automática de agente ao entrar na room
- Histórico de mensagens enviado no `onOpen` (últimas 100)
- Painel da equipe: listagem de rooms abertas (`/equipe/chat`) e interface de atendimento (`/equipe/chat/ver`)
- Botão "Encerrar chat" com confirmação
- **Sistema de e-mail via Mailcow** — integração com API REST (`/api/v1/add/mailbox`, `/api/v1/delete/mailbox`, `/api/v1/edit/mailbox`)
- Módulo `app/Services/Email/MailcowService` com `criarEmail()`, `removerEmail()`, `alterarSenha()`, `listar()`, `webmailUrl()`
- Tabela `client_emails` (migration `0007_chat_email.sql`)
- Rotas `/cliente/emails` (listar, criar, remover, alterar-senha)
- Link de webmail dinâmico por domínio (`https://webmail.{domain}`) com fallback para URL global
- Modal de alteração de senha com validação JS de confirmação
- Configurações Mailcow e Chat WS em `/equipe/configuracoes` (`email.mailcow_url`, `email.mailcow_key`, `email.webmail_url`, `chat.ws_port`)
- **Dashboard do cliente** reformulado: stat-cards com dados reais (total VPS, VPS running, tickets abertos, plano ativo)
- Nav-cards com ícones para todas as seções do painel
- **Onboarding modal** para novos clientes (`onboarding_done = 0`): guia de 4 passos, marcado como concluído via `POST /cliente/onboarding/concluir`
- Coluna `onboarding_done` em `clients` (migration `0007_chat_email.sql`)
- **VPS do cliente** reformulada: cards responsivos com dots de status coloridos (verde/amarelo/vermelho), specs em grid, ações contextuais por status
- **Monitoramento** melhorado: cards de resumo da última coleta com cores por threshold (verde < 70%, amarelo 70–90%, vermelho ≥ 90%), auto-refresh a cada 30s
- Versão do sistema exibida no footer de todas as páginas
- Responsividade completa: media queries 768px e 480px em `estilo.php`

### Corrigido
- Bug crítico: form de remoção de email enviava `local_part`/`domain` mas controller esperava `email_id`
- Bug: campo `senha` no form de criação de email enviava `name="password"` mas controller lia `$req->post['senha']`
- Bug: CSRF no chat do cliente era lido via `document.cookie` (frágil) — substituído por `json_encode(Csrf::token())` embutido no PHP
- Bug: `MailcowService` chamava `$this->http->post()` inexistente — corrigido para `requestJson('POST', ...)` e `requestJson('DELETE', ...)`
- Onboarding modal iniciava com `display:flex` no CSS — corrigido para `display:none` + ativação via JS (não trava interface se JS falhar)
- Double-submit em forms de ação de VPS: botão desabilitado após primeiro clique com feedback "Processando..."
- Reconexão do chat do cliente não chamava `setTimeout` em caso de erro no token — corrigido

### Alterado
- `PainelController` agora busca dados reais: contagem de VPS, VPS running, tickets abertos, assinatura ativa, `onboarding_done`
- `ConfiguracoesController` e view de configurações expandidos com seções Mailcow e Chat WS

---

## [Unreleased]

### Adicionado
- Upload e download de arquivos via terminal (SCP + docker cp para VPS de clientes)
- Swagger/OpenAPI em `public/api/openapi.yaml` com UI em `public/api/docs.html`
- Resize de terminal via `ResizeObserver` (envia `{"type":"resize","cols":X,"rows":Y}` ao backend)
- Notificações internas para clientes quando a equipe responde a um ticket (`client_notifications`)
- Exibição de notificações não lidas no painel do cliente
- Bloqueio de IP por excesso de tentativas de login no portal do cliente (`LoginBlocker`)
- Rota `POST /cliente/assinaturas/reembolso` adicionada
- View de sucesso do Stripe melhorada com detalhes do plano, VPS e próximos passos

---

## [1.3.0] — 2026-03-20

### Adicionado
- 2FA (TOTP RFC 6238) para usuários da equipe
- Log de login/logout da equipe com `auth_logs`
- Expiração de sessão por inatividade (30 min equipe, 60 min cliente)
- Interface de permissões por role (`/equipe/permissoes`)
- Filtros e busca na listagem de tickets da equipe
- Atribuição manual de ticket para usuário específico
- Status intermediários de ticket: `in_progress`, `waiting_client`
- Suporte a anexos em respostas de ticket (upload de arquivo)
- Dashboard da equipe com métricas reais (VPS, clientes, tickets, jobs, nodes, receita)
- Página pública de histórico de incidentes (`/status/incidentes`)
- Gráficos de métricas históricas no painel do cliente (Chart.js)
- Página de contato pública (`/contato`)
- Exibição de canais de suporte por plano
- Campo `support_channels` em planos
- Listagem de assinaturas e cobranças Asaas para o cliente
- Solicitação de reembolso via ticket automático
- Página de ajuda/FAQ para clientes
- README detalhado do projeto

### Corrigido
- IDOR em todos os endpoints de VPS, tickets, assinaturas e terminal
- Prevenção de replay attack no webhook do Stripe (`stripe_processed_events`)
- Path traversal no download de backups
- HTTPS forçado via `Settings::obter('app.force_https')`

---

## [1.2.0] — 2026-03-15

### Adicionado
- Jobs `reiniciar_vps` e `remover_vps` implementados
- Botões de reiniciar e remover VPS no painel da equipe
- Suporte a resize de terminal via JSON `{"type":"resize",...}` no `TerminalWsApp`
- `AppLogger` com logs em `storage/logs/app-YYYY-MM-DD.log`
- `LoginBlocker` para bloqueio de IP após 10 falhas em 30 min (equipe)
- `core/Totp.php` — implementação TOTP RFC 6238

### Alterado
- `Bootstrap.php` integrado com `AppLogger` no exception handler
- `Middlewares.php` com expiração de sessão por inatividade

---

## [1.1.0] — 2026-03-10

### Adicionado
- Sistema de terminal SSH via WebSocket (Ratchet)
- Auditoria de sessões de terminal
- Tokens de terminal de uso único com TTL configurável
- Terminal seguro para clientes (isolado no contêiner Docker)
- Monitoramento de nodes com `NodeHealthService`
- Coleta de status de serviços com `StatusCollectorService`
- Backups de VPS com `VpsBackupService`
- Deploy de aplicações com `AplicacaoDeployService`
- Webhooks Stripe e Asaas
- Sistema de notificações internas para equipe

---

## [1.0.0] — 2026-03-01

### Adicionado
- Estrutura inicial do projeto (PHP MVC próprio, sem framework)
- Autenticação de equipe e cliente com sessão PHP
- RBAC com roles e permissões
- Gerenciamento de VPS (provisionar, suspender, reativar)
- Sistema de tickets de suporte
- Planos e assinaturas (Asaas + Stripe)
- Gerenciamento de servidores/nodes
- Aplicações e deploy
- Monitoramento básico
- Página de status pública
- Migrations SQL incrementais
- Rate limiting por IP, cliente e equipe
- CSRF em todos os formulários POST
- Prepared statements em todas as queries
- Saída HTML sempre escapada com `View::e()`
