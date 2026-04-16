# LRV Cloud Manager

> Versão atual: **3.0.0**

Plataforma de gerenciamento de hospedagem cloud em PHP MVC próprio, sem frameworks externos. Suporta múltiplos tipos de produto: VPS, WordPress Gerenciado, Web Hosting, Node.js, PHP/Laravel, Python, C/C++ e App Genérico.

## Requisitos

- PHP 8.1+
- MySQL 8.0+
- Composer
- SSH access ao servidor de nodes
- (Opcional) Stripe, Asaas para billing
- (Opcional) Mailcow para e-mail por cliente
- (Opcional) Ratchet (já incluso via Composer) para WebSocket (terminal + chat)

## Instalação

### 1. Clonar e instalar dependências

```bash
git clone <repo> lrv-cloud
cd lrv-cloud
composer install
```

### 2. Banco de dados

Crie o banco e rode as migrations em ordem:

```bash
mysql -u root -p lrv_cloud < database/schema.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0001_*.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0002_*.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0003_*.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0004_*.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0005_*.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0006_*.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0007_chat_email.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0008_branding_legal.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_20_0009_seed_permissions.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0010_email_dominios.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0011_trial.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0012_system_errors.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0013_user_avatar.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0014_password_resets.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0024_client_address.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0025_client_totp.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0026_legal_defaults.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0026b_legal_force.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0026c_license_content.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0027_chat_files.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0028_app_templates.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_21_0029_cookie_consents.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_24_0030_plan_backup_slots.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_24_0031_domain_webmail_subdomain.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_24_0032_roundcube_template.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_24_0033_client_webmail_app.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_24_0034_server_role.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_24_0035_subscription_addons.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_24_0036_plan_featured_and_seed.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_25_0037_git_deployments.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_25_0038_client_databases.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_25_0041_chat_flows.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_26_0042_billing_discounts.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_26_0043_client_hidden.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_26_0044_client_last_login.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_26_0045_client_lang_country.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_26_0046_subscription_billing_type.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_26_0047_client_subdomains.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_27_0051_client_databases_application_id.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_27_0052_client_managed.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_27_0053_server_managed_flag.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_27_0054_plan_client_id.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_27_0055_subdomain_type_root_vps.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_28_0056_git_deploy_auth_token.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_31_0057_server_phpmyadmin_url.sql
mysql -u root -p lrv_cloud < database/migrations/2026_03_31_0058_git_deploy_php_config.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_01_0059_git_deploy_app_type.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_04_0060_client_cron_jobs.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_07_0061_plan_multi_pricing.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0062_plan_types_and_limits.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0063_cpp_app_template.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0064_seed_product_plans_and_addons.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0065_subscription_upgrades.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0066_subscription_addons_active.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0067_remove_unimplemented_addons.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0068_addon_slug.sql
mysql -u root -p lrv_cloud < database/migrations/2026_04_16_0069_client_support_priority.sql
```

### 3. Configuração do banco

Edite `core/BancoDeDados.php` com suas credenciais MySQL (DSN, usuário, senha).

### 4. Configurações do sistema

Todas as configurações ficam na tabela `settings`. Acesse `/equipe/inicializacao` após o primeiro acesso para gerar os defaults.

Configurações principais:

| Chave | Descrição |
|---|---|
| `app.url_base` | URL base da aplicação |
| `app.force_https` | `1` para forçar HTTPS |
| `asaas.token` | Token da API Asaas |
| `asaas.webhook_segredo` | Segredo do webhook Asaas |
| `stripe.secret_key` | Chave secreta Stripe |
| `stripe.webhook_secret` | Segredo do webhook Stripe |
| `infra.ssh_key_dir` | Diretório das chaves SSH dos nodes |
| `terminal.ws_internal_port` | Porta interna do WS do terminal (padrão: 8081) |
| `terminal.token_ttl_seconds` | TTL dos tokens de terminal (padrão: 60s) |
| `chat.ws_port` | Porta interna do WS do chat (padrão: 8082) |
| `chat.ws_url` | URL pública do WebSocket do chat (ex: `wss://seudominio.com/ws/chat`) |
| `email.mailcow_url` | URL base do Mailcow (ex: `https://correio.seudominio.com`) |
| `email.mailcow_key` | API key Read-Write do Mailcow |
| `email.webmail_url` | URL do webmail SOGo (ex: `https://correio.seudominio.com/SOGo`) |
| `email.server_ip` | IP do servidor de e-mail (para monitoramento) |
| `email.alert_cpu` | Limite de CPU (%) para alerta do servidor de e-mail |
| `email.alert_ram` | Limite de RAM (%) para alerta |
| `email.alert_disk` | Limite de disco (%) para alerta |
| `asaas.mode` | Ambiente ativo: `sandbox` ou `production` |
| `billing.desconto_6m` | Desconto (%) para contratação semestral (padrão: 5) |
| `billing.desconto_12m` | Desconto (%) para contratação anual (padrão: 10) |
| `stripe.mode` | Ambiente ativo: `sandbox` ou `production` |
| `email.mailcow_key` | API key do Mailcow |
| `email.webmail_url` | URL do webmail (Roundcube/SOGo) — fallback global |
| `alertas.email_admin` | E-mail para alertas |
| `system.name` | Nome do sistema (padrão: `LRV Cloud Manager`) |
| `system.logo_url` | URL do logotipo |
| `system.favicon_url` | URL do favicon |
| `system.company_name` | Nome da empresa (padrão: `LRV Cloud`) |
| `system.copyright_text` | Texto de copyright no footer |
| `legal.terms_html` | HTML dos Termos de Uso (`/termos`) |
| `legal.privacy_html` | HTML da Política de Privacidade (`/privacidade`) |
| `app_url` | URL base usada nos e-mails de reset de senha |

### 5. Diretórios de armazenamento

```bash
mkdir -p storage/logs storage/attachments storage/backups
chmod 775 storage storage/logs storage/attachments storage/backups
```

### 6. Servidor web

Configure o Apache/Nginx para apontar o `DocumentRoot` para a pasta `public/` e redirecionar todas as requisições para `index.php`.

Exemplo `.htaccess` (já incluso):
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

### 7. Primeiro acesso

Acesse `/equipe/primeiro-acesso` para criar o usuário administrador inicial.

---

## Terminal WebSocket

O terminal usa Ratchet. Para iniciar o daemon:

```bash
php terminal-ws.php
```

Porta configurável via `terminal.ws_internal_port` (padrão: 8081). Configure o proxy reverso para apontar `/ws/terminal` para `127.0.0.1:8081`.

## Chat WebSocket

O chat usa Ratchet em processo separado:

```bash
php chat-ws.php
```

Porta configurável via `chat.ws_port` (padrão: 8082). Configure o proxy reverso para apontar `/ws/chat` para `127.0.0.1:8082`.

## Jobs / Worker

```bash
php worker.php          # loop contínuo
php worker.php --once   # processa um job e sai
```

Ou via painel em `/equipe/inicializacao`.

---

## Módulos

### Billing
- **Asaas** (BRL): criação de clientes, assinaturas, webhooks em `/webhooks/asaas`
- **Stripe** (USD): checkout, webhooks em `/webhooks/stripe`
- Pagamento confirmado → ativa VPS | Inadimplência → suspende VPS

### Chat em tempo real
- WebSocket Ratchet, processo separado (`chat-ws.php`)
- Tokens de uso único (SHA-256, TTL 120s, replay protection)
- Rate limit: 30 msgs/10s por IP no WS + 10 req/60s no endpoint HTTP
- Isolamento por room: cliente só acessa a própria room
- Emoji picker inline e upload de arquivos (imagens, PDF, DOC, TXT até 5 MB)
- Polling HTTP fallback automático (tenta WS 2x, depois polling a cada 3s)
- Nome do agente exibido nas respostas e separadores visuais de dia
- Pesquisa de satisfação enviada por e-mail ao encerrar chat
- Histórico de chats abertos e encerrados no painel da equipe

### Aplicações pré-configuradas (One-Click Install)
- Catálogo visual em `/cliente/aplicacoes/catalogo` com 7 templates prontos
- Instalação automática: pull de imagem Docker, env vars, clone de repo, criação de container
- Auto-assign de porta, labels Docker (`lrv.app_id`, `lrv.client_id`)
- Job assíncrono `install_app_template` para provisionamento via fila
- Templates: WordPress, Node.js, PHP Laravel, MySQL, Redis, Nginx, Site Estático

### E-mail (Mailcow)
- Integração via API REST do Mailcow
- Criar, listar, remover e alterar senha de mailboxes
- Webmail dinâmico por domínio (`https://webmail.{domain}`) com fallback global
- Sem SMTP/IMAP manual — usa infraestrutura Mailcow existente

### Branding e Páginas Legais
- Nome, logo, favicon e copyright configuráveis via `/equipe/configuracoes`
- Páginas públicas `/termos` e `/privacidade` com HTML editável pelo admin
- Changelog público em `/changelog` gerado a partir do `CHANGELOG.md`
- Helper `SistemaConfig` com cache via `Settings`

### Terminal
- SSH via WebSocket, isolado por cliente (docker exec no container)
- Auditoria de sessões, upload/download de arquivos
- Modo seguro configurável (bloqueia comandos perigosos)

### Cookies e Consentimento (LGPD)
- Banner de cookies com 3 opções: aceitar todos, rejeitar opcionais, configurar
- Modal de configuração granular com 4 categorias: necessários, analytics, marketing, preferências
- Persistência no navegador (cookie 12 meses) e no banco de dados (usuários logados)
- `CookieConsentService` com `verificarPermissao('categoria')` para bloqueio condicional de scripts
- Função JS `ckTemPermissao('analytics')` para carregar scripts apenas com consentimento
- Endpoints `POST /cookies/consent` (CSRF + rate limit) e `GET /cookies/consent`
- Integrado à página `/privacidade` e ao footer público

### Landing Pages de Soluções
- 5 páginas de alta conversão em `/solucoes/` (VPS, Aplicações, DevOps, E-mail, Segurança)
- Layout compartilhado com 8 seções: hero, problema, solução, benefícios, como funciona, prova social, CTA, FAQ
- Copywriting profissional nos 3 idiomas, focado em decisores (CEOs, gestores)
- Design gradiente azul→roxo, responsivo (mobile 1 col, tablet 2 col, desktop 3 col)

### Mega Menu
- Navbar pública com mega menus "Produtos" (4 colunas) e "Recursos" (4 colunas)
- Desktop: hover com fade-in, 250ms delay no close
- Mobile: drawer com accordion expandível
- Links apontam para landing pages dedicadas em `/solucoes/`

### VPS / Provisioning
- Jobs assíncronos: criar, parar, reiniciar, remover VPS
- Seleção automática de node por capacidade disponível
- Métricas de CPU/RAM/Disco coletadas remotamente via SSH

### Clientes Gerenciados (Hospedagem Gerenciada)
- Tipo de cliente `is_managed` com painel restrito (VPS, Monitoramento, Tickets, Assinaturas, Minha Conta, Segurança)
- Planos exclusivos por cliente (`plans.client_id`) — não aparecem para outros clientes
- Servidores dedicados (`is_managed_server`) com overselling — containers sem limites de CPU/RAM
- Impersonação: equipe loga como cliente com acesso completo, botão "Voltar para equipe"
- Monitoramento mostra percentuais relativos ao plano (não ao host)
- Alertas automáticos quando uso real do servidor gerenciado ou da VPS individual está alto
- Listagem de servidores mostra ratio de overselling (vendido vs real)
- Validações de segurança impedem mistura de clientes normais e gerenciados no mesmo servidor

---

## Segurança

- CSRF em todos os formulários POST
- Rate limiting por IP, cliente e equipe
- Bloqueio de IP após 10 tentativas de login falhas em 30 min
- 2FA (TOTP RFC 6238) para usuários da equipe
- 2FA (TOTP RFC 6238) para clientes (`/cliente/2fa/configurar`)
- Chave SSH de fallback removida — `SshCrypto` exige `app.secret_key` configurado
- `config/instalacao.php` removido do Git (credenciais não versionadas)
- Upload de chave SSH no formulário de servidores (substitui input de texto)
- SSH com senha via ext-ssh2 ou proc_open com pty (sem dependência de `sshpass`)
- Tokens de terminal e chat de uso único com TTL curto
- Validação de propriedade (IDOR) em todos os endpoints sensíveis — retorna 403
- Prevenção de replay attack nos webhooks Stripe e Asaas
- Logs de autenticação em `auth_logs`
- Logs de aplicação em `storage/logs/app-YYYY-MM-DD.log`
- Saída HTML sempre escapada com `View::e()`
- Prepared statements em todas as queries
- Reset de senha via token com expiração de 1 hora (equipe e cliente)

---

## Estrutura

```
app/
  Controllers/
    Cliente/      # Painel do cliente
    Equipe/       # Painel da equipe
    Api/          # Endpoints internos
    Webhooks/     # Asaas, Stripe
  Services/
    Billing/      # Asaas, Stripe
    Chat/         # WebSocket chat
    Cookies/      # Consentimento LGPD
    Email/        # Mailcow
    Infra/        # SSH, NodeHealth
    Provisioning/ # Docker, VPS
    Terminal/     # WebSocket terminal
    ...
  Views/
    cliente/      # Templates do cliente
    equipe/       # Templates da equipe
    _partials/    # estilo.php, footer.php, idioma.php
  Jobs/           # Handlers de jobs assíncronos
core/             # Framework próprio (Router, Auth, DB, CSRF, etc.)
database/
  schema.sql      # Schema inicial
  migrations/     # Migrations incrementais
routes/
  web.php         # Todas as rotas
storage/          # Logs, anexos, backups (não versionar)
public/           # DocumentRoot (index.php)
chat-ws.php       # Daemon WebSocket do chat
terminal-ws.php   # Daemon WebSocket do terminal
worker.php        # Worker de jobs
```

---

## Versões

Veja o [CHANGELOG.md](CHANGELOG.md) para histórico completo de versões.
