-- Migration: 0015_plan_addons
-- Serviços adicionais por plano (ex: backup, suporte WhatsApp)

CREATE TABLE IF NOT EXISTS plan_addons (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id     INT UNSIGNED NOT NULL,
    name        VARCHAR(120) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plan_addons_plan (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
