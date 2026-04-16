-- Addons contratados individualmente (separado do plano)
CREATE TABLE IF NOT EXISTS subscription_addon_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  subscription_id INT UNSIGNED NOT NULL,
  client_id INT UNSIGNED NOT NULL,
  addon_id INT UNSIGNED NOT NULL,
  addon_name VARCHAR(120) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  price_usd DECIMAL(10,2) NULL,
  asaas_payment_id VARCHAR(80) NULL,
  stripe_invoice_id VARCHAR(80) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  canceled_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_sub_addon_items_sub (subscription_id),
  KEY idx_sub_addon_items_client (client_id),
  CONSTRAINT fk_sub_addon_items_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id),
  CONSTRAINT fk_sub_addon_items_client FOREIGN KEY (client_id) REFERENCES clients(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
