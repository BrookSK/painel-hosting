CREATE TABLE IF NOT EXISTS git_deployments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  vps_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  repo_url VARCHAR(500) NOT NULL,
  branch VARCHAR(100) NOT NULL DEFAULT 'main',
  subdomain VARCHAR(253) NULL,
  deploy_path VARCHAR(500) NOT NULL DEFAULT '/var/www/html',
  force_overwrite TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('active','inactive','error') NOT NULL DEFAULT 'active',
  last_deployed_at DATETIME NULL,
  last_commit_hash VARCHAR(40) NULL,
  last_commit_message VARCHAR(500) NULL,
  last_commit_author VARCHAR(100) NULL,
  error_message TEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_client (client_id),
  KEY idx_vps (vps_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS git_deploy_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  deployment_id INT UNSIGNED NOT NULL,
  status ENUM('success','error') NOT NULL,
  commit_hash VARCHAR(40) NULL,
  commit_message VARCHAR(500) NULL,
  commit_author VARCHAR(100) NULL,
  output TEXT NULL,
  deployed_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_deployment (deployment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
