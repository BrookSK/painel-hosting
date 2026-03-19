CREATE TABLE IF NOT EXISTS server_metrics (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  server_id INT UNSIGNED NOT NULL,
  cpu_usage DECIMAL(5,2) NOT NULL,
  ram_usage DECIMAL(5,2) NOT NULL,
  disk_usage DECIMAL(5,2) NOT NULL,
  timestamp DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_server_metrics_server_id_timestamp (server_id, timestamp),
  CONSTRAINT fk_server_metrics_server FOREIGN KEY (server_id) REFERENCES servers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
