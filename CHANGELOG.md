# Changelog

Todas as mudanças notáveis neste projeto são documentadas aqui.
Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

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
