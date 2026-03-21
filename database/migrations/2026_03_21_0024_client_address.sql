-- Endereço do cliente — cada coluna verificada individualmente

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_street')=0,
  'ALTER TABLE clients ADD COLUMN address_street VARCHAR(255) NULL AFTER mobile_phone','SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_number')=0,
  'ALTER TABLE clients ADD COLUMN address_number VARCHAR(20) NULL AFTER address_street','SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_complement')=0,
  'ALTER TABLE clients ADD COLUMN address_complement VARCHAR(100) NULL AFTER address_number','SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_district')=0,
  'ALTER TABLE clients ADD COLUMN address_district VARCHAR(100) NULL AFTER address_complement','SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_city')=0,
  'ALTER TABLE clients ADD COLUMN address_city VARCHAR(100) NULL AFTER address_district','SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_state')=0,
  'ALTER TABLE clients ADD COLUMN address_state VARCHAR(2) NULL AFTER address_city','SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_zip')=0,
  'ALTER TABLE clients ADD COLUMN address_zip VARCHAR(10) NULL AFTER address_state','SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='clients' AND COLUMN_NAME='address_country')=0,
  "ALTER TABLE clients ADD COLUMN address_country VARCHAR(2) NOT NULL DEFAULT 'BR' AFTER address_zip",'SELECT 1');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;
