SET @has_cpf := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'clients'
    AND COLUMN_NAME = 'cpf_cnpj'
);

SET @sql1 := IF(@has_cpf = 0,
  'ALTER TABLE clients ADD COLUMN cpf_cnpj VARCHAR(20) NULL AFTER email',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @has_phone := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'clients'
    AND COLUMN_NAME = 'phone'
);

SET @sql2 := IF(@has_phone = 0,
  'ALTER TABLE clients ADD COLUMN phone VARCHAR(20) NULL AFTER cpf_cnpj',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @has_mobile := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'clients'
    AND COLUMN_NAME = 'mobile_phone'
);

SET @sql3 := IF(@has_mobile = 0,
  'ALTER TABLE clients ADD COLUMN mobile_phone VARCHAR(20) NULL AFTER phone',
  'SELECT 1'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;
