-- Configuração PHP por deploy
ALTER TABLE git_deployments
  ADD COLUMN IF NOT EXISTS php_version VARCHAR(10) NULL DEFAULT '8.3' AFTER post_deploy_cmd,
  ADD COLUMN IF NOT EXISTS php_settings JSON NULL AFTER php_version;
