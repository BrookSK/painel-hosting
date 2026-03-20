-- Migration: chat em tempo real + email por cliente
-- Rodar manualmente no banco

CREATE TABLE IF NOT EXISTS chat_tokens (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    token_hash VARCHAR(64)     NOT NULL,
    client_id  INT UNSIGNED    NULL,
    user_id    INT UNSIGNED    NULL,
    room_id    BIGINT UNSIGNED NULL,
    expires_at DATETIME        NOT NULL,
    used_at    DATETIME        NULL,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_chat_token_hash (token_hash),
    INDEX idx_chat_token_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_rooms (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id  INT UNSIGNED    NOT NULL,
    user_id    INT UNSIGNED    NULL,
    status     ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_chat_rooms_client (client_id),
    INDEX idx_chat_rooms_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_messages (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    room_id     BIGINT UNSIGNED NOT NULL,
    sender_type ENUM('client','admin') NOT NULL,
    sender_id   INT UNSIGNED    NOT NULL,
    message     TEXT            NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_chat_messages_room (room_id),
    CONSTRAINT fk_chat_messages_room FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Emails por cliente (integração Mailcow)
CREATE TABLE IF NOT EXISTS client_emails (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id    INT UNSIGNED    NOT NULL,
    email        VARCHAR(191)    NOT NULL,
    domain       VARCHAR(191)    NOT NULL,
    mailcow_id   VARCHAR(191)    NULL,
    quota_mb     INT UNSIGNED    NOT NULL DEFAULT 1024,
    active       TINYINT(1)      NOT NULL DEFAULT 1,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_client_email (email),
    INDEX idx_client_emails_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Onboarding: marcar se cliente já viu o guia inicial
ALTER TABLE clients ADD COLUMN IF NOT EXISTS onboarding_done TINYINT(1) NOT NULL DEFAULT 0;
