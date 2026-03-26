-- Tabela centralizada de subdomínios do cliente
CREATE TABLE IF NOT EXISTS client_subdomains (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  subdomain VARCHAR(253) NOT NULL,
  root_domain VARCHAR(191) NOT NULL,
  type ENUM('subdomain','root') NOT NULL DEFAULT 'subdomain',
  verify_token VARCHAR(64) NULL,
  cname_target VARCHAR(253) NULL,
  status ENUM('pending_txt','pending_cname','active','error') NOT NULL DEFAULT 'pending_txt',
  used_by_type VARCHAR(20) NULL,
  used_by_id INT UNSIGNED NULL,
  error_msg TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_subdomain (subdomain),
  KEY idx_client (client_id),
  KEY idx_root (client_id, root_domain),
  KEY idx_status (status),
  KEY idx_used (used_by_type, used_by_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subdomínio automático na VPS
SET @has_col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vps' AND COLUMN_NAME = 'temp_subdomain');
SET @sql := IF(@has_col = 0, 'ALTER TABLE vps ADD COLUMN temp_subdomain VARCHAR(253) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
