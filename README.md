# LRV Cloud Manager

> Versão atual: **1.4.0**

Plataforma de gerenciamento de VPS em PHP MVC próprio, sem frameworks externos.

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
| `email.mailcow_url` | URL base do Mailcow (ex: `https://mail.seudominio.com`) |
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

### VPS / Provisioning
- Jobs assíncronos: criar, parar, reiniciar, remover VPS
- Seleção automática de node por capacidade disponível
- Métricas de CPU/RAM/Disco coletadas remotamente via SSH

---

## Segurança

- CSRF em todos os formulários POST
- Rate limiting por IP, cliente e equipe
- Bloqueio de IP após 10 tentativas de login falhas em 30 min
- 2FA (TOTP RFC 6238) para usuários da equipe
- Tokens de terminal e chat de uso único com TTL curto
- Validação de propriedade (IDOR) em todos os endpoints sensíveis — retorna 403
- Prevenção de replay attack nos webhooks Stripe e Asaas
- Logs de autenticação em `auth_logs`
- Logs de aplicação em `storage/logs/app-YYYY-MM-DD.log`
- Saída HTML sempre escapada com `View::e()`
- Prepared statements em todas as queries

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
