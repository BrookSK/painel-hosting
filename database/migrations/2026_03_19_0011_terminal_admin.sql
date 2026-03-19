CREATE TABLE IF NOT EXISTS terminal_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_hash CHAR(64) NOT NULL,
  equipe_id INT UNSIGNED NOT NULL,
  server_id INT UNSIGNED NOT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  revoked TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_terminal_tokens_hash (token_hash),
  KEY idx_terminal_tokens_equipe_id (equipe_id),
  KEY idx_terminal_tokens_server_id (server_id),
  CONSTRAINT fk_terminal_tokens_user FOREIGN KEY (equipe_id) REFERENCES users(id),
  CONSTRAINT fk_terminal_tokens_server FOREIGN KEY (server_id) REFERENCES servers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS terminal_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_uid CHAR(36) NOT NULL,
  equipe_id INT UNSIGNED NOT NULL,
  server_id INT UNSIGNED NOT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  started_at DATETIME NOT NULL,
  ended_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_terminal_sessions_uid (session_uid),
  KEY idx_terminal_sessions_equipe_id_started_at (equipe_id, started_at),
  KEY idx_terminal_sessions_server_id_started_at (server_id, started_at),
  CONSTRAINT fk_terminal_sessions_user FOREIGN KEY (equipe_id) REFERENCES users(id),
  CONSTRAINT fk_terminal_sessions_server FOREIGN KEY (server_id) REFERENCES servers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS terminal_session_commands (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_id BIGINT UNSIGNED NOT NULL,
  command LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_terminal_cmds_session_id_created_at (session_id, created_at),
  CONSTRAINT fk_terminal_cmds_session FOREIGN KEY (session_id) REFERENCES terminal_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO permissions (`key`, description)
SELECT 'manage_terminal', 'Acessar terminal (Admin)'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE `key` = 'manage_terminal');

INSERT INTO role_permissions (role, permission_id)
SELECT 'superadmin', p.id
FROM permissions p
WHERE p.`key` = 'manage_terminal'
  AND NOT EXISTS (
    SELECT 1 FROM role_permissions rp
    WHERE rp.role = 'superadmin' AND rp.permission_id = p.id
  );

INSERT INTO role_permissions (role, permission_id)
SELECT 'devops', p.id
FROM permissions p
WHERE p.`key` = 'manage_terminal'
  AND NOT EXISTS (
    SELECT 1 FROM role_permissions rp
    WHERE rp.role = 'devops' AND rp.permission_id = p.id
  );

INSERT INTO role_permissions (role, permission_id)
SELECT 'admin', p.id
FROM permissions p
WHERE p.`key` = 'manage_terminal'
  AND NOT EXISTS (
    SELECT 1 FROM role_permissions rp
    WHERE rp.role = 'admin' AND rp.permission_id = p.id
  );
