-- Coluna deleted_at para VPS (remoção lógica)
SET @has_vps_deleted_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vps' AND COLUMN_NAME = 'deleted_at'
);
SET @sql_vps_del := IF(@has_vps_deleted_at = 0,
  'ALTER TABLE vps ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE s FROM @sql_vps_del; EXECUTE s; DEALLOCATE PREPARE s;

-- Coluna name para VPS
SET @has_vps_name := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vps' AND COLUMN_NAME = 'name'
);
SET @sql_vps_name := IF(@has_vps_name = 0,
  'ALTER TABLE vps ADD COLUMN name VARCHAR(120) NULL DEFAULT NULL AFTER client_id',
  'SELECT 1'
);
PREPARE s FROM @sql_vps_name; EXECUTE s; DEALLOCATE PREPARE s;

-- Status in_progress e waiting_client para tickets
SET @has_ticket_attachment_size := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_messages' AND COLUMN_NAME = 'attachment_name'
);
SET @sql_att := IF(@has_ticket_attachment_size = 0,
  'ALTER TABLE ticket_messages ADD COLUMN attachment_name VARCHAR(255) NULL DEFAULT NULL AFTER attachment',
  'SELECT 1'
);
PREPARE s FROM @sql_att; EXECUTE s; DEALLOCATE PREPARE s;

SET @has_ticket_attachment_size2 := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_messages' AND COLUMN_NAME = 'attachment_size'
);
SET @sql_att2 := IF(@has_ticket_attachment_size2 = 0,
  'ALTER TABLE ticket_messages ADD COLUMN attachment_size INT UNSIGNED NULL DEFAULT NULL AFTER attachment_name',
  'SELECT 1'
);
PREPARE s FROM @sql_att2; EXECUTE s; DEALLOCATE PREPARE s;

-- Tabela de 2FA TOTP para equipe
CREATE TABLE IF NOT EXISTS user_totp (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  secret VARCHAR(64) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_totp_user_id (user_id),
  CONSTRAINT fk_user_totp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de log de login/logout
CREATE TABLE IF NOT EXISTS auth_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  actor_type VARCHAR(10) NOT NULL COMMENT 'team ou client',
  actor_id INT UNSIGNED NOT NULL,
  action VARCHAR(20) NOT NULL COMMENT 'login, logout, login_failed, 2fa_failed',
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_auth_logs_actor (actor_type, actor_id, created_at),
  KEY idx_auth_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de sessões com expiração por inatividade
SET @has_session_last_activity := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'last_activity_at'
);
SET @sql_la := IF(@has_session_last_activity = 0,
  'ALTER TABLE users ADD COLUMN last_activity_at DATETIME NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE s FROM @sql_la; EXECUTE s; DEALLOCATE PREPARE s;

-- Tabela de permissões customizadas por role (interface)
CREATE TABLE IF NOT EXISTS role_permission_overrides (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role VARCHAR(50) NOT NULL,
  permission_key VARCHAR(120) NOT NULL,
  granted TINYINT(1) NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rpo_role_perm (role, permission_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de incidentes históricos públicos (já existe status_incidents, apenas índice extra)
SET @has_idx_inc_scope := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'status_incidents' AND INDEX_NAME = 'idx_status_incidents_scope_status'
);
SET @sql_idx_inc := IF(@has_idx_inc_scope = 0,
  'CREATE INDEX idx_status_incidents_scope_status ON status_incidents (scope, status, started_at)',
  'SELECT 1'
);
PREPARE s FROM @sql_idx_inc; EXECUTE s; DEALLOCATE PREPARE s;

-- Coluna uptime_pct em status_services para cache de uptime
SET @has_uptime_pct := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'status_services' AND COLUMN_NAME = 'uptime_30d_pct'
);
SET @sql_uptime := IF(@has_uptime_pct = 0,
  'ALTER TABLE status_services ADD COLUMN uptime_30d_pct DECIMAL(5,2) NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE s FROM @sql_uptime; EXECUTE s; DEALLOCATE PREPARE s;

-- Tabela de contato público
CREATE TABLE IF NOT EXISTS contact_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NOT NULL,
  message LONGTEXT NOT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_contact_messages_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coluna support_channels em plans (JSON com canais de suporte)
SET @has_plan_support := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND COLUMN_NAME = 'support_channels'
);
SET @sql_plan_support := IF(@has_plan_support = 0,
  'ALTER TABLE plans ADD COLUMN support_channels LONGTEXT NULL DEFAULT NULL COMMENT ''JSON array: email, whatsapp, chat''',
  'SELECT 1'
);
PREPARE s FROM @sql_plan_support; EXECUTE s; DEALLOCATE PREPARE s;

-- Tabela de chat em tempo real (mensagens)
CREATE TABLE IF NOT EXISTS chat_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticket_id INT UNSIGNED NOT NULL,
  sender_type VARCHAR(10) NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_chat_messages_ticket_id (ticket_id),
  CONSTRAINT fk_chat_messages_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coluna de log de erros de aplicação
CREATE TABLE IF NOT EXISTS app_error_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  level VARCHAR(20) NOT NULL DEFAULT 'error',
  message TEXT NOT NULL,
  context_json LONGTEXT NULL,
  file VARCHAR(255) NULL,
  line INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_app_error_logs_level_created (level, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
