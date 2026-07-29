-- Auto Deploy: webhook automático ao receber push na branch configurada
ALTER TABLE git_deployments
  ADD COLUMN IF NOT EXISTS auto_deploy TINYINT(1) NOT NULL DEFAULT 0 AFTER app_port,
  ADD COLUMN IF NOT EXISTS webhook_secret VARCHAR(64) NULL AFTER auto_deploy;
