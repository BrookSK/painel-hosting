-- ============================================================================
-- Dev Workflow: Projetos internos, demandas, branches, deploy e PRs
-- ============================================================================

-- Tabela de projetos de desenvolvimento (internos da empresa)
CREATE TABLE IF NOT EXISTS dev_projects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  repo_url VARCHAR(500) NOT NULL COMMENT 'URL do repositório Git (SSH ou HTTPS)',
  default_branch VARCHAR(100) NOT NULL DEFAULT 'main',
  vps_id INT UNSIGNED NULL COMMENT 'VPS da equipe vinculada ao projeto',
  deploy_path VARCHAR(500) NULL COMMENT 'Caminho de deploy no servidor de teste',
  temp_domain VARCHAR(255) NULL COMMENT 'Domínio temporário .lrvweb para testes',
  app_type VARCHAR(20) NOT NULL DEFAULT 'php' COMMENT 'php, nodejs, python, static',
  app_port INT UNSIGNED NULL COMMENT 'Porta para apps Node/Python',
  php_version VARCHAR(10) NULL DEFAULT '8.3',
  post_deploy_cmd TEXT NULL COMMENT 'Comando pós-deploy (ex: composer install)',
  auth_token_enc TEXT NULL COMMENT 'Token de autenticação cifrado (HTTPS repos)',
  deploy_key_public TEXT NULL,
  deploy_key_private_enc TEXT NULL,
  webhook_secret VARCHAR(128) NULL,
  status ENUM('active','archived') NOT NULL DEFAULT 'active',
  created_by INT UNSIGNED NULL COMMENT 'ID do user (equipe) que criou',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_dev_projects_status (status),
  KEY idx_dev_projects_vps (vps_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de demandas (tarefas de desenvolvimento)
CREATE TABLE IF NOT EXISTS dev_demands (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  branch_name VARCHAR(200) NULL COMMENT 'Branch criada automaticamente para esta demanda',
  status ENUM('open','in_progress','testing','pr_pending','pr_rejected','merged','closed') NOT NULL DEFAULT 'open',
  priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  assigned_to INT UNSIGNED NULL COMMENT 'ID do user (equipe) responsável',
  created_by INT UNSIGNED NOT NULL COMMENT 'ID do user (equipe) que criou',
  pr_title VARCHAR(255) NULL,
  pr_description TEXT NULL,
  pr_created_at DATETIME NULL,
  pr_reviewed_by INT UNSIGNED NULL COMMENT 'ID do admin que revisou o PR',
  pr_reviewed_at DATETIME NULL,
  pr_rejection_reason TEXT NULL,
  last_deploy_at DATETIME NULL,
  last_deploy_commit VARCHAR(64) NULL,
  last_deploy_output TEXT NULL,
  merged_at DATETIME NULL,
  closed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_dev_demands_project (project_id, status),
  KEY idx_dev_demands_assigned (assigned_to),
  KEY idx_dev_demands_status (status),
  CONSTRAINT fk_dev_demands_project FOREIGN KEY (project_id) REFERENCES dev_projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comentários/histórico nas demandas
CREATE TABLE IF NOT EXISTS dev_demand_comments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  demand_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL COMMENT 'ID do membro da equipe',
  comment TEXT NOT NULL,
  type ENUM('comment','status_change','deploy','pr_created','pr_approved','pr_rejected','branch_created') NOT NULL DEFAULT 'comment',
  metadata JSON NULL COMMENT 'Dados extras (commit hash, branch, etc)',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_dev_demand_comments_demand (demand_id, created_at),
  CONSTRAINT fk_dev_demand_comments_demand FOREIGN KEY (demand_id) REFERENCES dev_demands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Log de deploys no ambiente de teste
CREATE TABLE IF NOT EXISTS dev_deploy_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  demand_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NOT NULL,
  branch VARCHAR(200) NOT NULL,
  commit_hash VARCHAR(64) NULL,
  commit_message VARCHAR(500) NULL,
  commit_author VARCHAR(150) NULL,
  status ENUM('success','error') NOT NULL,
  output TEXT NULL,
  deployed_by INT UNSIGNED NOT NULL,
  deployed_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_dev_deploy_logs_demand (demand_id),
  KEY idx_dev_deploy_logs_project (project_id),
  CONSTRAINT fk_dev_deploy_logs_demand FOREIGN KEY (demand_id) REFERENCES dev_demands(id) ON DELETE CASCADE,
  CONSTRAINT fk_dev_deploy_logs_project FOREIGN KEY (project_id) REFERENCES dev_projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
