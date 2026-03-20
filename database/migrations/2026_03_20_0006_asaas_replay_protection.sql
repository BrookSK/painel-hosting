-- Migration: proteção contra replay attack no webhook Asaas
-- Rodar manualmente no banco

CREATE TABLE IF NOT EXISTS asaas_processed_events (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id    VARCHAR(191)    NOT NULL,
    received_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_asaas_event_id (event_id),
    INDEX idx_asaas_received_at (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
