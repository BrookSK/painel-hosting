-- Pricing multi-período e multi-moeda para planos
-- price_monthly já existe (BRL mensal)

ALTER TABLE plans
  ADD COLUMN IF NOT EXISTS currency VARCHAR(3) NOT NULL DEFAULT 'BRL' AFTER price_monthly,
  ADD COLUMN IF NOT EXISTS price_monthly_usd DECIMAL(10,2) NULL AFTER currency,
  ADD COLUMN IF NOT EXISTS price_semiannual DECIMAL(10,2) NULL AFTER price_monthly_usd,
  ADD COLUMN IF NOT EXISTS price_semiannual_usd DECIMAL(10,2) NULL AFTER price_semiannual,
  ADD COLUMN IF NOT EXISTS price_annual DECIMAL(10,2) NULL AFTER price_semiannual_usd,
  ADD COLUMN IF NOT EXISTS price_annual_usd DECIMAL(10,2) NULL AFTER price_annual,
  ADD COLUMN IF NOT EXISTS price_annual_upfront DECIMAL(10,2) NULL AFTER price_annual_usd,
  ADD COLUMN IF NOT EXISTS price_annual_upfront_usd DECIMAL(10,2) NULL AFTER price_annual_upfront,
  ADD COLUMN IF NOT EXISTS max_installments_annual INT NULL DEFAULT 12 AFTER price_annual_upfront_usd,
  ADD COLUMN IF NOT EXISTS max_installments_semiannual INT NULL DEFAULT 6 AFTER max_installments_annual;

-- Pricing multi-período para addons
ALTER TABLE plan_addons
  ADD COLUMN IF NOT EXISTS price_usd DECIMAL(10,2) NULL AFTER price,
  ADD COLUMN IF NOT EXISTS price_annual DECIMAL(10,2) NULL AFTER price_usd,
  ADD COLUMN IF NOT EXISTS price_annual_usd DECIMAL(10,2) NULL AFTER price_annual;
