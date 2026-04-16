-- Tipos de plano e limites por produto
-- plan_type: vps (padrão), wordpress, webhosting, nodejs, app

ALTER TABLE plans
  ADD COLUMN IF NOT EXISTS plan_type VARCHAR(30) NOT NULL DEFAULT 'vps' AFTER name,
  ADD COLUMN IF NOT EXISTS max_sites INT UNSIGNED NULL AFTER specs_json,
  ADD COLUMN IF NOT EXISTS max_databases INT UNSIGNED NULL AFTER max_sites,
  ADD COLUMN IF NOT EXISTS max_storage_per_site_mb INT UNSIGNED NULL AFTER max_databases,
  ADD COLUMN IF NOT EXISTS max_cron_jobs INT UNSIGNED NULL AFTER max_storage_per_site_mb,
  ADD COLUMN IF NOT EXISTS allowed_features JSON NULL AFTER max_cron_jobs;

-- Índice para filtrar planos por tipo
ALTER TABLE plans ADD INDEX IF NOT EXISTS idx_plans_plan_type (plan_type);

-- Todos os planos existentes são VPS (já é o default, mas garantir)
UPDATE plans SET plan_type = 'vps' WHERE plan_type = '' OR plan_type IS NULL;
