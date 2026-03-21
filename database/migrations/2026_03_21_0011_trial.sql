-- Migration 0011: Fluxo de Teste Grátis
-- Rodar manualmente no banco MySQL

-- Settings de configuração do trial
INSERT INTO settings (`key`, `value`) VALUES
  ('trial.enabled',     '0'),
  ('trial.dias',        '7'),
  ('trial.vcpu',        '1'),
  ('trial.ram_mb',      '1024'),
  ('trial.disco_gb',    '20'),
  ('trial.descricao',   'Experimente nossa plataforma gratuitamente por 7 dias, sem cartão de crédito.'),
  ('trial.label_cta',   'Testar grátis por 7 dias')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Tabela de trials de clientes
CREATE TABLE IF NOT EXISTS client_trials (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id    INT UNSIGNED NOT NULL,
  started_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at   DATETIME     NOT NULL,
  vcpu         TINYINT      NOT NULL DEFAULT 1,
  ram_mb       INT          NOT NULL DEFAULT 1024,
  disco_gb     INT          NOT NULL DEFAULT 20,
  status       ENUM('active','expired','converted') NOT NULL DEFAULT 'active',
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_client_trial (client_id),
  INDEX idx_expires (expires_at),
  INDEX idx_status  (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
