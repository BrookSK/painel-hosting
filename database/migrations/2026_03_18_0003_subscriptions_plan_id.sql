SET @has_plan_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND COLUMN_NAME = 'plan_id'
);

SET @sql1 := IF(@has_plan_id = 0,
  'ALTER TABLE subscriptions ADD COLUMN plan_id INT UNSIGNED NULL AFTER vps_id',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @has_idx_sub_plan := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND INDEX_NAME = 'idx_subscriptions_plan_id'
);

SET @sql2 := IF(@has_idx_sub_plan = 0,
  'CREATE INDEX idx_subscriptions_plan_id ON subscriptions (plan_id)',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @has_fk_sub_plan := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND CONSTRAINT_NAME = 'fk_subscriptions_plan'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql3 := IF(@has_fk_sub_plan = 0,
  'ALTER TABLE subscriptions ADD CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans(id)',
  'SELECT 1'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;
