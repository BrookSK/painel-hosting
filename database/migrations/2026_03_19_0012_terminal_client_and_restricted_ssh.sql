SET @has_terminal_ssh_user := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servers'
    AND COLUMN_NAME = 'terminal_ssh_user'
);

SET @has_terminal_ssh_key_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servers'
    AND COLUMN_NAME = 'terminal_ssh_key_id'
);

SET @sql1 := IF(@has_terminal_ssh_user = 0,
  'ALTER TABLE servers ADD COLUMN terminal_ssh_user VARCHAR(60) NULL AFTER ssh_key_id',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql2 := IF(@has_terminal_ssh_key_id = 0,
  'ALTER TABLE servers ADD COLUMN terminal_ssh_key_id VARCHAR(120) NULL AFTER terminal_ssh_user',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

CREATE TABLE IF NOT EXISTS client_terminal_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_hash CHAR(64) NOT NULL,
  client_id INT UNSIGNED NOT NULL,
  vps_id INT UNSIGNED NOT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  revoked TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_client_terminal_tokens_hash (token_hash),
  KEY idx_client_terminal_tokens_client_id (client_id),
  KEY idx_client_terminal_tokens_vps_id (vps_id),
  CONSTRAINT fk_client_terminal_tokens_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_client_terminal_tokens_vps FOREIGN KEY (vps_id) REFERENCES vps(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS client_terminal_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_uid CHAR(36) NOT NULL,
  client_id INT UNSIGNED NOT NULL,
  vps_id INT UNSIGNED NOT NULL,
  server_id INT UNSIGNED NOT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  started_at DATETIME NOT NULL,
  ended_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_client_terminal_sessions_uid (session_uid),
  KEY idx_client_terminal_sessions_client_id_started_at (client_id, started_at),
  KEY idx_client_terminal_sessions_vps_id_started_at (vps_id, started_at),
  KEY idx_client_terminal_sessions_server_id_started_at (server_id, started_at),
  CONSTRAINT fk_client_terminal_sessions_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_client_terminal_sessions_vps FOREIGN KEY (vps_id) REFERENCES vps(id),
  CONSTRAINT fk_client_terminal_sessions_server FOREIGN KEY (server_id) REFERENCES servers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS client_terminal_session_commands (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_id BIGINT UNSIGNED NOT NULL,
  command LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_client_terminal_cmds_session_id_created_at (session_id, created_at),
  CONSTRAINT fk_client_terminal_cmds_session FOREIGN KEY (session_id) REFERENCES client_terminal_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
