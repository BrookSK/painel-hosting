-- Migration: 0016_plans_badge_billing_cycle
-- Adiciona colunas badge e billing_cycle à tabela plans

SET @has_badge := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND COLUMN_NAME = 'badge'
);
SET @sql_badge := IF(@has_badge = 0,
  'ALTER TABLE plans ADD COLUMN badge VARCHAR(60) NULL DEFAULT NULL AFTER description',
  'SELECT 1'
);
PREPARE stmt_badge FROM @sql_badge;
EXECUTE stmt_badge;
DEALLOCATE PREPARE stmt_badge;

SET @has_billing_cycle := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND COLUMN_NAME = 'billing_cycle'
);
SET @sql_bc := IF(@has_billing_cycle = 0,
  'ALTER TABLE plans ADD COLUMN billing_cycle VARCHAR(20) NOT NULL DEFAULT ''monthly'' AFTER price_monthly',
  'SELECT 1'
);
PREPARE stmt_bc FROM @sql_bc;
EXECUTE stmt_bc;
DEALLOCATE PREPARE stmt_bc;
