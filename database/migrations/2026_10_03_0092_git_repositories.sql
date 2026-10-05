-- Repositórios Git criados via API pública (ex.: helpdeskON provisiona na organização).
-- Rastreia o que foi criado no provedor (GitHub/GitLab) para dar visibilidade e idempotência.
CREATE TABLE IF NOT EXISTS git_repositories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  provider VARCHAR(20) NOT NULL DEFAULT 'github',
  org VARCHAR(120) NULL,
  name VARCHAR(140) NOT NULL,
  full_name VARCHAR(280) NULL,
  visibility VARCHAR(10) NOT NULL DEFAULT 'private',
  provider_repo_id VARCHAR(40) NULL,
  html_url VARCHAR(500) NULL,
  clone_url VARCHAR(500) NULL,
  ssh_url VARCHAR(500) NULL,
  description VARCHAR(500) NULL,
  external_ref VARCHAR(120) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_git_repos_client (client_id),
  KEY idx_git_repos_fullname (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS git_repository_collaborators (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  repository_id INT UNSIGNED NOT NULL,
  username VARCHAR(140) NOT NULL,
  permission VARCHAR(20) NOT NULL DEFAULT 'push',
  status VARCHAR(20) NOT NULL DEFAULT 'invited',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_git_collab_repo (repository_id),
  UNIQUE KEY uq_git_collab (repository_id, username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
