-- Migration: plan_backup_slots
-- Adiciona campo backup_slots na tabela plans (0 = sem backup, 1 ou 2)

SET @has_col := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'plans'
    AND COLUMN_NAME = 'backup_slots'
);

SET @sql := IF(@has_col = 0,
  'ALTER TABLE plans ADD COLUMN backup_slots TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER price_monthly',
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
