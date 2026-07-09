-- ============================================================
-- MÓDULO: API PÚBLICA - FUNDAÇÃO
-- Tabelas: api_keys, api_tokens, api_logs, api_webhooks, api_webhook_events, api_webhook_deliveries
-- ============================================================

-- ── API Keys ──
CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL COMMENT 'Nome da aplicação',
    description VARCHAR(500) DEFAULT NULL,
    prefix VARCHAR(10) NOT NULL COMMENT 'Prefixo visível (ex: lrv_live_)',
    key_hash VARCHAR(128) NOT NULL COMMENT 'SHA-256 hash da chave',
    key_hint VARCHAR(8) NOT NULL COMMENT 'Últimos 4 chars para identificação',
    environment ENUM('sandbox','production') NOT NULL DEFAULT 'production',
    scopes JSON NOT NULL COMMENT '["clients.read","hosting.write",...]',
    status ENUM('active','revoked','expired') NOT NULL DEFAULT 'active',
    rate_limit_per_minute INT UNSIGNED NOT NULL DEFAULT 60,
    last_used_at DATETIME DEFAULT NULL,
    last_used_ip VARCHAR(45) DEFAULT NULL,
    request_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME DEFAULT NULL,
    revoked_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_api_keys_client (client_id),
    INDEX idx_api_keys_prefix (prefix),
    INDEX idx_api_keys_hash (key_hash),
    INDEX idx_api_keys_status (status),
    INDEX idx_api_keys_env (environment),
    CONSTRAINT fk_api_keys_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── API Personal Access Tokens (JWT/Bearer) ──
CREATE TABLE IF NOT EXISTS api_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(128) NOT NULL COMMENT 'SHA-256 hash do token',
    token_hint VARCHAR(8) NOT NULL,
    type ENUM('access','refresh') NOT NULL DEFAULT 'access',
    scopes JSON DEFAULT NULL COMMENT 'Subconjunto dos scopes da key',
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME DEFAULT NULL,
    last_used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_tokens_key (api_key_id),
    INDEX idx_api_tokens_hash (token_hash),
    INDEX idx_api_tokens_expires (expires_at),
    CONSTRAINT fk_api_tokens_key FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── API Request Logs ──
CREATE TABLE IF NOT EXISTS api_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT UNSIGNED DEFAULT NULL,
    client_id INT UNSIGNED DEFAULT NULL,
    method VARCHAR(10) NOT NULL,
    endpoint VARCHAR(500) NOT NULL,
    status_code SMALLINT UNSIGNED NOT NULL,
    request_body TEXT DEFAULT NULL,
    response_body TEXT DEFAULT NULL,
    ip VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    execution_time_ms INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_logs_key (api_key_id),
    INDEX idx_api_logs_client (client_id),
    INDEX idx_api_logs_created (created_at),
    INDEX idx_api_logs_endpoint (endpoint(100)),
    INDEX idx_api_logs_status (status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Webhooks (configurações do cliente) ──
CREATE TABLE IF NOT EXISTS api_webhooks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    url VARCHAR(1000) NOT NULL,
    secret VARCHAR(128) NOT NULL COMMENT 'HMAC secret para assinatura',
    events JSON NOT NULL COMMENT '["client.created","ticket.created",...]',
    status ENUM('active','paused','disabled') NOT NULL DEFAULT 'active',
    max_retries TINYINT UNSIGNED NOT NULL DEFAULT 5,
    timeout_seconds TINYINT UNSIGNED NOT NULL DEFAULT 30,
    last_triggered_at DATETIME DEFAULT NULL,
    last_success_at DATETIME DEFAULT NULL,
    last_failure_at DATETIME DEFAULT NULL,
    failure_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_api_webhooks_client (client_id),
    INDEX idx_api_webhooks_status (status),
    CONSTRAINT fk_api_webhooks_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Webhook Delivery History ──
CREATE TABLE IF NOT EXISTS api_webhook_deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webhook_id INT UNSIGNED NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    response_status SMALLINT UNSIGNED DEFAULT NULL,
    response_body TEXT DEFAULT NULL,
    attempt TINYINT UNSIGNED NOT NULL DEFAULT 1,
    duration_ms INT UNSIGNED DEFAULT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    error_message VARCHAR(1000) DEFAULT NULL,
    delivered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhook_del_webhook (webhook_id),
    INDEX idx_webhook_del_event (event_type),
    INDEX idx_webhook_del_date (delivered_at),
    INDEX idx_webhook_del_success (success),
    CONSTRAINT fk_webhook_del_webhook FOREIGN KEY (webhook_id) REFERENCES api_webhooks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Scopes Registry (para interface admin) ──
CREATE TABLE IF NOT EXISTS api_scopes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scope VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(200) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'general',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed: escopos iniciais ──
INSERT INTO api_scopes (scope, description, category) VALUES
('clients.read', 'Visualizar dados de clientes', 'clients'),
('clients.write', 'Criar e editar clientes', 'clients'),
('hosting.read', 'Visualizar VPS e hospedagem', 'hosting'),
('hosting.write', 'Gerenciar VPS e hospedagem', 'hosting'),
('tickets.read', 'Visualizar tickets de suporte', 'tickets'),
('tickets.write', 'Criar e responder tickets', 'tickets'),
('domains.read', 'Visualizar domínios', 'domains'),
('domains.write', 'Gerenciar domínios', 'domains'),
('billing.read', 'Visualizar assinaturas e faturas', 'billing'),
('billing.write', 'Gerenciar assinaturas', 'billing'),
('backups.read', 'Visualizar backups', 'backups'),
('backups.write', 'Criar e restaurar backups', 'backups'),
('monitoring.read', 'Visualizar métricas e monitoramento', 'monitoring'),
('webhooks.read', 'Visualizar webhooks configurados', 'webhooks'),
('webhooks.write', 'Gerenciar webhooks', 'webhooks'),
('applications.read', 'Visualizar aplicações instaladas', 'applications'),
('applications.write', 'Instalar e gerenciar aplicações', 'applications'),
('databases.read', 'Visualizar bancos de dados', 'databases'),
('databases.write', 'Gerenciar bancos de dados', 'databases'),
('emails.read', 'Visualizar contas de email', 'emails'),
('emails.write', 'Gerenciar contas de email', 'emails');
