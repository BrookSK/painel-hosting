# LRV Cloud Manager

Plataforma de gerenciamento de VPS em PHP MVC próprio, sem frameworks externos.

## Requisitos

- PHP 8.1+
- MySQL 8.0+
- Composer
- SSH access ao servidor de nodes
- (Opcional) Stripe, Asaas para billing

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
| `terminal.token_ttl_seconds` | TTL dos tokens de terminal (padrão: 60s) |
| `alertas.email_admin` | E-mail para alertas |

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

## Terminal WebSocket

O terminal usa Ratchet (WebSocket). Para iniciar o daemon:

```bash
php bin/terminal-server.php
```

Ou via painel em `/equipe/inicializacao`.

## Jobs

Os jobs são processados via worker. Para processar manualmente:

```bash
php bin/worker.php
```

Ou via painel em `/equipe/inicializacao`.

## Segurança

- CSRF em todos os formulários POST
- Rate limiting por IP e por usuário
- Bloqueio de IP após 10 tentativas de login falhas em 30 min
- 2FA (TOTP) disponível para usuários da equipe
- Tokens de terminal de uso único com TTL curto (padrão 60s)
- Validação de propriedade (IDOR) em todos os endpoints sensíveis
- Prevenção de replay attack nos webhooks Stripe
- Logs de autenticação em `auth_logs`
- Logs de aplicação em `storage/logs/app-YYYY-MM-DD.log`

## Estrutura

```
app/
  Controllers/    # Controllers MVC
  Services/       # Lógica de negócio
  Views/          # Templates PHP
  Jobs/           # Handlers de jobs
core/             # Framework próprio (Router, Auth, DB, etc.)
database/         # Schema e migrations
routes/           # Definição de rotas
storage/          # Logs, anexos, backups (não versionar)
public/           # DocumentRoot (index.php)
```
