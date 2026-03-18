CREATE TABLE IF NOT EXISTS migrations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file_name VARCHAR(255) NOT NULL,
  executed_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_migrations_file_name (file_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(190) NOT NULL,
  `value` LONGTEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clients (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password VARCHAR(255) NOT NULL,
  asaas_customer_id VARCHAR(80) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_clients_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL,
  status VARCHAR(20) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS permissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(120) NOT NULL,
  description VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
  role VARCHAR(50) NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role, permission_id),
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS servers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  hostname VARCHAR(190) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  ssh_port INT UNSIGNED NOT NULL,
  ssh_user VARCHAR(60) NULL,
  ssh_key_id VARCHAR(120) NULL,
  ram_total BIGINT UNSIGNED NOT NULL,
  ram_used BIGINT UNSIGNED NOT NULL DEFAULT 0,
  cpu_total INT UNSIGNED NOT NULL,
  cpu_used INT UNSIGNED NOT NULL DEFAULT 0,
  storage_total BIGINT UNSIGNED NOT NULL,
  storage_used BIGINT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS vps (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  server_id INT UNSIGNED NULL,
  container_id VARCHAR(120) NULL,
  cpu INT UNSIGNED NOT NULL,
  ram BIGINT UNSIGNED NOT NULL,
  storage BIGINT UNSIGNED NOT NULL,
  status VARCHAR(30) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_vps_client_id (client_id),
  KEY idx_vps_server_id (server_id),
  CONSTRAINT fk_vps_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_vps_server FOREIGN KEY (server_id) REFERENCES servers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS applications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  vps_id INT UNSIGNED NOT NULL,
  type VARCHAR(30) NOT NULL,
  domain VARCHAR(190) NULL,
  port INT UNSIGNED NULL,
  status VARCHAR(30) NOT NULL,
  repository VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_applications_vps_id (vps_id),
  CONSTRAINT fk_applications_vps FOREIGN KEY (vps_id) REFERENCES vps(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ports (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  port INT UNSIGNED NOT NULL,
  status VARCHAR(20) NOT NULL,
  application_id INT UNSIGNED NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ports_port (port),
  KEY idx_ports_application_id (application_id),
  CONSTRAINT fk_ports_application FOREIGN KEY (application_id) REFERENCES applications(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subscriptions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  vps_id INT UNSIGNED NULL,
  asaas_subscription_id VARCHAR(80) NOT NULL,
  status VARCHAR(30) NOT NULL,
  next_due_date DATE NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_subscriptions_client_id (client_id),
  KEY idx_subscriptions_vps_id (vps_id),
  CONSTRAINT fk_subscriptions_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_subscriptions_vps FOREIGN KEY (vps_id) REFERENCES vps(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jobs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  type VARCHAR(60) NOT NULL,
  payload LONGTEXT NOT NULL,
  status VARCHAR(20) NOT NULL,
  log LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_jobs_status_created_at (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tickets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  subject VARCHAR(190) NOT NULL,
  status VARCHAR(30) NOT NULL,
  priority VARCHAR(20) NOT NULL,
  department VARCHAR(30) NOT NULL,
  assigned_to INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_tickets_client_id (client_id),
  KEY idx_tickets_assigned_to (assigned_to),
  CONSTRAINT fk_tickets_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_tickets_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticket_id INT UNSIGNED NOT NULL,
  sender_type VARCHAR(10) NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  message LONGTEXT NOT NULL,
  attachment VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_ticket_messages_ticket_id (ticket_id),
  CONSTRAINT fk_ticket_messages_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  message VARCHAR(255) NOT NULL,
  `read` TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_notifications_user_id_read (user_id, `read`),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
