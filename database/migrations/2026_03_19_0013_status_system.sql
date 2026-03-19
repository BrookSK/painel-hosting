CREATE TABLE IF NOT EXISTS status_services (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(120) NOT NULL,
  name VARCHAR(190) NOT NULL,
  description VARCHAR(255) NULL,
  scope VARCHAR(20) NOT NULL DEFAULT 'public',
  client_id INT UNSIGNED NULL,
  server_id INT UNSIGNED NULL,
  vps_id INT UNSIGNED NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'unknown',
  last_check_at DATETIME NULL,
  last_ok_at DATETIME NULL,
  last_error VARCHAR(255) NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_status_services_key (`key`),
  KEY idx_status_services_scope_status (scope, status),
  KEY idx_status_services_client_id (client_id),
  KEY idx_status_services_server_id (server_id),
  KEY idx_status_services_vps_id (vps_id),
  CONSTRAINT fk_status_services_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_status_services_server FOREIGN KEY (server_id) REFERENCES servers(id),
  CONSTRAINT fk_status_services_vps FOREIGN KEY (vps_id) REFERENCES vps(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS status_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  service_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(30) NOT NULL,
  message VARCHAR(255) NULL,
  metrics_json LONGTEXT NULL,
  checked_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_status_logs_service_id_checked_at (service_id, checked_at),
  KEY idx_status_logs_checked_at (checked_at),
  CONSTRAINT fk_status_logs_service FOREIGN KEY (service_id) REFERENCES status_services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS status_incidents (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(190) NOT NULL,
  status VARCHAR(30) NOT NULL,
  impact VARCHAR(20) NOT NULL,
  scope VARCHAR(20) NOT NULL DEFAULT 'public',
  message LONGTEXT NULL,
  started_at DATETIME NOT NULL,
  resolved_at DATETIME NULL,
  created_by_user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_status_incidents_started_at (started_at),
  KEY idx_status_incidents_status (status),
  CONSTRAINT fk_status_incidents_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS status_incident_updates (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(30) NOT NULL,
  message LONGTEXT NOT NULL,
  created_by_user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_status_incident_updates_incident_id_created_at (incident_id, created_at),
  CONSTRAINT fk_status_incident_updates_incident FOREIGN KEY (incident_id) REFERENCES status_incidents(id) ON DELETE CASCADE,
  CONSTRAINT fk_status_incident_updates_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS status_incident_services (
  incident_id BIGINT UNSIGNED NOT NULL,
  service_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (incident_id, service_id),
  CONSTRAINT fk_status_incident_services_incident FOREIGN KEY (incident_id) REFERENCES status_incidents(id) ON DELETE CASCADE,
  CONSTRAINT fk_status_incident_services_service FOREIGN KEY (service_id) REFERENCES status_services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
