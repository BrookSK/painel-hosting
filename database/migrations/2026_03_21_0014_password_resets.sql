CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo        ENUM('equipe','cliente') NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    token       VARCHAR(128) NOT NULL UNIQUE,
    expires_at  DATETIME NOT NULL,
    used_at     DATETIME NULL DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_user (tipo, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
