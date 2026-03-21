-- Catálogo de aplicações pré-configuradas (one-click install)
CREATE TABLE IF NOT EXISTS app_templates (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(60) NOT NULL,
  description TEXT NULL,
  category VARCHAR(40) NOT NULL DEFAULT 'other',
  icon VARCHAR(10) NULL,
  docker_image VARCHAR(255) NOT NULL,
  docker_command VARCHAR(500) NULL,
  default_port INT UNSIGNED NOT NULL DEFAULT 80,
  requires_domain TINYINT(1) NOT NULL DEFAULT 0,
  requires_repo TINYINT(1) NOT NULL DEFAULT 0,
  environment_variables JSON NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_app_templates_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Campos extras na tabela applications
ALTER TABLE applications
  ADD COLUMN IF NOT EXISTS template_id INT UNSIGNED NULL AFTER vps_id,
  ADD COLUMN IF NOT EXISTS container_id VARCHAR(80) NULL AFTER repository,
  ADD COLUMN IF NOT EXISTS environment_json JSON NULL AFTER container_id,
  ADD COLUMN IF NOT EXISTS logs TEXT NULL AFTER environment_json;

-- Seed: templates padrão
INSERT IGNORE INTO app_templates (name, slug, description, category, icon, docker_image, docker_command, default_port, requires_domain, requires_repo, environment_variables, created_at) VALUES
('WordPress', 'wordpress', 'CMS mais popular do mundo. Blog, site ou loja virtual.', 'cms', '📝', 'wordpress:latest', NULL, 80, 1, 0, '{"WORDPRESS_DB_HOST":"","WORDPRESS_DB_USER":"","WORDPRESS_DB_PASSWORD":""}', NOW()),
('Node.js App', 'nodejs', 'Aplicação Node.js com npm. Ideal para APIs e apps web.', 'backend', '🟢', 'node:18', 'sh -c "cd /app && npm install && npm start"', 3000, 0, 1, NULL, NOW()),
('PHP Laravel', 'php-laravel', 'API ou app PHP com Apache. Pronto para Laravel/Symfony.', 'backend', '🐘', 'php:8.2-apache', NULL, 80, 0, 1, NULL, NOW()),
('MySQL', 'mysql', 'Banco de dados relacional MySQL 8.', 'database', '🗄️', 'mysql:8', NULL, 3306, 0, 0, '{"MYSQL_ROOT_PASSWORD":"","MYSQL_DATABASE":""}', NOW()),
('Redis', 'redis', 'Cache e broker de mensagens em memória.', 'database', '⚡', 'redis:latest', NULL, 6379, 0, 0, NULL, NOW()),
('Nginx', 'nginx', 'Servidor web e proxy reverso de alta performance.', 'webserver', '🌐', 'nginx:latest', NULL, 80, 1, 0, NULL, NOW()),
('Site Estático', 'static-site', 'Hospede HTML/CSS/JS estático com Nginx Alpine.', 'webserver', '📄', 'nginx:alpine', NULL, 80, 0, 1, NULL, NOW());
