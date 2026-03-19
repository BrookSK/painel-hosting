SET @has_is_online := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servers'
    AND COLUMN_NAME = 'is_online'
);

SET @has_last_check_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servers'
    AND COLUMN_NAME = 'last_check_at'
);

SET @has_last_error := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servers'
    AND COLUMN_NAME = 'last_error'
);

SET @sql1 := IF(@has_is_online = 0,
  'ALTER TABLE servers ADD COLUMN is_online TINYINT(1) NOT NULL DEFAULT 0 AFTER ssh_key_id',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql2 := IF(@has_last_check_at = 0,
  'ALTER TABLE servers ADD COLUMN last_check_at DATETIME NULL AFTER is_online',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @sql3 := IF(@has_last_error = 0,
  'ALTER TABLE servers ADD COLUMN last_error VARCHAR(255) NULL AFTER last_check_at',
  'SELECT 1'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;
