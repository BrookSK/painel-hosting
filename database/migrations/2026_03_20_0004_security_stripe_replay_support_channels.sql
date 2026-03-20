-- Migration: 2026_03_20_0004
-- Segurança: prevenção de replay attack no Stripe, canais de suporte por plano

-- Tabela para armazenar event_ids do Stripe já processados (previne replay attacks)
CREATE TABLE IF NOT EXISTS stripe_processed_events (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    event_id   VARCHAR(120)    NOT NULL,
    event_type VARCHAR(120)    NOT NULL DEFAULT '',
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_stripe_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coluna support_channels na tabela plans (JSON: ex: ["email","whatsapp","chat"])
ALTER TABLE plans
    ADD COLUMN IF NOT EXISTS support_channels JSON NULL COMMENT 'Canais de suporte disponíveis neste plano';

-- Índice para busca de incidentes por data
ALTER TABLE status_incidents
    ADD INDEX IF NOT EXISTS idx_started_at (started_at);

-- Coluna force_https nas settings (inserir default se não existir)
INSERT IGNORE INTO settings (`key`, value, description, updated_at)
VALUES ('app.force_https', '0', 'Forçar redirecionamento HTTP → HTTPS (1=sim, 0=não)', NOW());
