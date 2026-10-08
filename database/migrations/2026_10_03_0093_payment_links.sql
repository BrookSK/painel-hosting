-- Links públicos de pagamento: o admin gera um link único para um cliente + plano,
-- e o cliente paga por ele SEM precisar logar no painel. O token em claro só aparece
-- na URL; no banco guardamos apenas o hash (SHA-256), como nos resets de senha.
CREATE TABLE IF NOT EXISTS payment_links (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  addons_json JSON NULL,
  periodo INT NOT NULL DEFAULT 1,
  currency VARCHAR(3) NOT NULL DEFAULT 'BRL',
  token_hash CHAR(64) NOT NULL,
  token_hint VARCHAR(8) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  subscription_id INT UNSIGNED NULL,
  created_by INT UNSIGNED NULL,
  expires_at DATETIME NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payment_links_token (token_hash),
  KEY idx_payment_links_client (client_id),
  KEY idx_payment_links_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
