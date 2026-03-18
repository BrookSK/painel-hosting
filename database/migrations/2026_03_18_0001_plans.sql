CREATE TABLE IF NOT EXISTS plans (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  description VARCHAR(255) NULL,
  cpu INT UNSIGNED NOT NULL,
  ram BIGINT UNSIGNED NOT NULL,
  storage BIGINT UNSIGNED NOT NULL,
  price_monthly DECIMAL(10,2) NOT NULL,
  specs_json LONGTEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @has_plan_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'vps'
    AND COLUMN_NAME = 'plan_id'
);

SET @sql1 := IF(@has_plan_id = 0,
  'ALTER TABLE vps ADD COLUMN plan_id INT UNSIGNED NULL',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @has_idx_vps_plan := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'vps'
    AND INDEX_NAME = 'idx_vps_plan_id'
);

SET @sql2 := IF(@has_idx_vps_plan = 0,
  'CREATE INDEX idx_vps_plan_id ON vps (plan_id)',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @has_fk_vps_plan := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'vps'
    AND CONSTRAINT_NAME = 'fk_vps_plan'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql3 := IF(@has_fk_vps_plan = 0,
  'ALTER TABLE vps ADD CONSTRAINT fk_vps_plan FOREIGN KEY (plan_id) REFERENCES plans(id)',
  'SELECT 1'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;
