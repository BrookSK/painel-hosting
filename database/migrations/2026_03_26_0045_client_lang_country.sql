-- Idioma preferido e país do cliente
SET @has_lang := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'preferred_lang');
SET @sql1 := IF(@has_lang = 0, 'ALTER TABLE clients ADD COLUMN preferred_lang VARCHAR(5) NULL DEFAULT NULL', 'SELECT 1');
PREPARE s1 FROM @sql1; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @has_country := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'country');
SET @sql2 := IF(@has_country = 0, 'ALTER TABLE clients ADD COLUMN country VARCHAR(2) NULL DEFAULT NULL', 'SELECT 1');
PREPARE s2 FROM @sql2; EXECUTE s2; DEALLOCATE PREPARE s2;
