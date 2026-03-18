SET @has_ssh_user := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servers'
    AND COLUMN_NAME = 'ssh_user'
);

SET @has_ssh_key_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'servers'
    AND COLUMN_NAME = 'ssh_key_id'
);

SET @sql1 := IF(@has_ssh_user = 0,
  'ALTER TABLE servers ADD COLUMN ssh_user VARCHAR(60) NULL AFTER ssh_port',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql2 := IF(@has_ssh_key_id = 0,
  'ALTER TABLE servers ADD COLUMN ssh_key_id VARCHAR(120) NULL AFTER ssh_user',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
