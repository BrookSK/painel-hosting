-- Tipo de aplicação e porta para Node.js/Python deploys
ALTER TABLE git_deployments
  ADD COLUMN IF NOT EXISTS app_type VARCHAR(20) NULL DEFAULT 'php' AFTER php_settings,
  ADD COLUMN IF NOT EXISTS app_port INT NULL DEFAULT NULL AFTER app_type;
