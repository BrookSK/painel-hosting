CREATE TABLE IF NOT EXISTS backups (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  vps_id INT UNSIGNED NOT NULL,
  job_id BIGINT UNSIGNED NULL,
  status VARCHAR(20) NOT NULL,
  file_path VARCHAR(255) NULL,
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  error LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  completed_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_backups_vps_id (vps_id),
  KEY idx_backups_status (status),
  CONSTRAINT fk_backups_vps FOREIGN KEY (vps_id) REFERENCES vps(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
