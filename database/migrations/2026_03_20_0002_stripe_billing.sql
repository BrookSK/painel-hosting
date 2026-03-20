SET @has_stripe_customer_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'clients'
    AND COLUMN_NAME = 'stripe_customer_id'
);

SET @sql1 := IF(@has_stripe_customer_id = 0,
  'ALTER TABLE clients ADD COLUMN stripe_customer_id VARCHAR(80) NULL AFTER asaas_customer_id',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @has_idx_clients_stripe_customer := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'clients'
    AND INDEX_NAME = 'idx_clients_stripe_customer_id'
);

SET @sql2 := IF(@has_idx_clients_stripe_customer = 0,
  'CREATE INDEX idx_clients_stripe_customer_id ON clients (stripe_customer_id)',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @has_stripe_price_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'plans'
    AND COLUMN_NAME = 'stripe_price_id'
);

SET @sql3 := IF(@has_stripe_price_id = 0,
  'ALTER TABLE plans ADD COLUMN stripe_price_id VARCHAR(80) NULL AFTER price_monthly',
  'SELECT 1'
);

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SET @has_asaas_subscription_id_notnull := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND COLUMN_NAME = 'asaas_subscription_id'
    AND IS_NULLABLE = 'NO'
);

SET @sql4 := IF(@has_asaas_subscription_id_notnull > 0,
  'ALTER TABLE subscriptions MODIFY COLUMN asaas_subscription_id VARCHAR(80) NULL',
  'SELECT 1'
);

PREPARE stmt4 FROM @sql4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

SET @has_stripe_subscription_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND COLUMN_NAME = 'stripe_subscription_id'
);

SET @sql5 := IF(@has_stripe_subscription_id = 0,
  'ALTER TABLE subscriptions ADD COLUMN stripe_subscription_id VARCHAR(80) NULL AFTER asaas_subscription_id',
  'SELECT 1'
);

PREPARE stmt5 FROM @sql5;
EXECUTE stmt5;
DEALLOCATE PREPARE stmt5;

SET @has_stripe_checkout_session_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND COLUMN_NAME = 'stripe_checkout_session_id'
);

SET @sql6 := IF(@has_stripe_checkout_session_id = 0,
  'ALTER TABLE subscriptions ADD COLUMN stripe_checkout_session_id VARCHAR(120) NULL AFTER stripe_subscription_id',
  'SELECT 1'
);

PREPARE stmt6 FROM @sql6;
EXECUTE stmt6;
DEALLOCATE PREPARE stmt6;

SET @has_idx_sub_stripe_sub := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND INDEX_NAME = 'idx_subscriptions_stripe_subscription_id'
);

SET @sql7 := IF(@has_idx_sub_stripe_sub = 0,
  'CREATE INDEX idx_subscriptions_stripe_subscription_id ON subscriptions (stripe_subscription_id)',
  'SELECT 1'
);

PREPARE stmt7 FROM @sql7;
EXECUTE stmt7;
DEALLOCATE PREPARE stmt7;

SET @has_idx_sub_stripe_session := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'subscriptions'
    AND INDEX_NAME = 'idx_subscriptions_stripe_checkout_session_id'
);

SET @sql8 := IF(@has_idx_sub_stripe_session = 0,
  'CREATE INDEX idx_subscriptions_stripe_checkout_session_id ON subscriptions (stripe_checkout_session_id)',
  'SELECT 1'
);

PREPARE stmt8 FROM @sql8;
EXECUTE stmt8;
DEALLOCATE PREPARE stmt8;

CREATE TABLE IF NOT EXISTS stripe_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_id VARCHAR(190) NOT NULL,
  event_type VARCHAR(80) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_stripe_events_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
