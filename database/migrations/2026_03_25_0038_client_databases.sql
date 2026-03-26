CREATE TABLE IF NOT EXISTS client_databases (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  vps_id INT UNSIGNED NULL,
  name VARCHAR(64) NOT NULL,
  db_name VARCHAR(64) NOT NULL,
  db_user VARCHAR(64) NOT NULL,
  db_password_enc VARCHAR(500) NOT NULL,
  db_host VARCHAR(255) NOT NULL DEFAULT '127.0.0.1',
  db_port INT UNSIGNED NOT NULL DEFAULT 3306,
  container_id VARCHAR(80) NULL,
  status ENUM('creating','active','error') NOT NULL DEFAULT 'creating',
  notes TEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
