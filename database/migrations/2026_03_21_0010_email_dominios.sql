-- Migration 0010: Sistema de domínios de email (custom domain)

CREATE TABLE IF NOT EXISTS client_domains (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id   INT UNSIGNED NOT NULL,
    domain      VARCHAR(191) NOT NULL,
    status      ENUM('pending_dns','active','error') NOT NULL DEFAULT 'pending_dns',
    error_msg   TEXT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_domain (domain),
    KEY idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings padrão para o novo sistema de email
INSERT IGNORE INTO settings (`key`, `value`) VALUES
    ('email.default_domain',            ''),
    ('email.webmail_mode',              'global'),
    ('email.max_accounts_per_plan',     '5'),
    ('email.dns_instructions_template', 'Adicione os seguintes registros DNS no seu provedor:\n\nMX (prioridade 10):\n  mail.{domain}\n\nTXT (SPF):\n  v=spf1 mx ~all\n\nTXT (DKIM):\n  (gerado automaticamente pelo servidor — clique em "Ver instruções" para obter o valor atual)\n\nApós configurar, clique em "Verificar DNS" para confirmar.');
