-- Coluna para ocultar clientes sem deletar
SET @has_col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'hidden_at');
SET @sql := IF(@has_col = 0, 'ALTER TABLE clients ADD COLUMN hidden_at DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
