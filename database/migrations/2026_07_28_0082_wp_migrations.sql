-- Tabela para rastrear migrações de WordPress de servidores externos via SSH
CREATE TABLE IF NOT EXISTS wp_migrations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  vps_id INT UNSIGNED NOT NULL,
  application_id INT UNSIGNED NULL,
  database_id INT UNSIGNED NULL,

  -- Dados do servidor de origem (remoto)
  source_host VARCHAR(255) NOT NULL,
  source_port INT UNSIGNED NOT NULL DEFAULT 22,
  source_user VARCHAR(100) NOT NULL DEFAULT 'root',
  source_password_enc VARCHAR(500) NOT NULL DEFAULT '',
  source_key_enc TEXT NULL,

  -- Caminhos no servidor de origem
  source_wp_path VARCHAR(500) NOT NULL COMMENT 'Caminho absoluto do WordPress no servidor de origem (ex: /www/wwwroot/site.com)',
  source_db_name VARCHAR(100) NOT NULL COMMENT 'Nome do banco MySQL no servidor de origem',
  source_db_user VARCHAR(100) NOT NULL DEFAULT 'root',
  source_db_password_enc VARCHAR(500) NOT NULL DEFAULT '',
  source_db_host VARCHAR(255) NOT NULL DEFAULT 'localhost',
  source_db_port INT UNSIGNED NOT NULL DEFAULT 3306,

  -- Dados do destino (nosso servidor)
  dest_domain VARCHAR(255) NULL COMMENT 'Dominio final do WordPress migrado',
  dest_wp_path VARCHAR(500) NULL COMMENT 'Caminho onde o WP foi instalado no destino',
  dest_db_name VARCHAR(100) NULL,

  -- Controle de progresso
  status ENUM('pending','connecting','syncing_files','dumping_db','importing_db','configuring','finalizing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  current_step VARCHAR(60) NULL,
  progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
  job_id INT UNSIGNED NULL,

  -- Logs e metadados
  logs LONGTEXT NULL,
  error_message TEXT NULL,
  files_size_bytes BIGINT UNSIGNED NULL COMMENT 'Tamanho total dos arquivos sincronizados',
  db_size_bytes BIGINT UNSIGNED NULL COMMENT 'Tamanho do dump SQL',
  started_at DATETIME NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  created_by INT UNSIGNED NULL COMMENT 'ID do usuario da equipe que iniciou',

  PRIMARY KEY (id),
  KEY idx_wp_migrations_client (client_id),
  KEY idx_wp_migrations_status (status),
  CONSTRAINT fk_wp_migrations_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_wp_migrations_vps FOREIGN KEY (vps_id) REFERENCES vps(id),
  CONSTRAINT fk_wp_migrations_application FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
  CONSTRAINT fk_wp_migrations_database FOREIGN KEY (database_id) REFERENCES client_databases(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
