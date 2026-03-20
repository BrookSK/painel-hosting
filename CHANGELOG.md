# Changelog

Todas as mudanças notáveis neste projeto são documentadas aqui.
Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

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
