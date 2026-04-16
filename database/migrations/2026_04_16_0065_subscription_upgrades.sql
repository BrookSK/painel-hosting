-- Tracking de upgrades/downgrades de plano
CREATE TABLE IF NOT EXISTS subscription_upgrades (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  subscription_id INT UNSIGNED NOT NULL,
  client_id INT UNSIGNED NOT NULL,
  old_plan_id INT UNSIGNED NOT NULL,
  new_plan_id INT UNSIGNED NOT NULL,
  old_price DECIMAL(10,2) NOT NULL,
  new_price DECIMAL(10,2) NOT NULL,
  proration_credit DECIMAL(10,2) NULL DEFAULT 0,
  type VARCHAR(10) NOT NULL DEFAULT 'upgrade',
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL,
  completed_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_sub_upgrades_sub (subscription_id),
  KEY idx_sub_upgrades_client (client_id),
  CONSTRAINT fk_sub_upgrades_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id),
  CONSTRAINT fk_sub_upgrades_client FOREIGN KEY (client_id) REFERENCES clients(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
