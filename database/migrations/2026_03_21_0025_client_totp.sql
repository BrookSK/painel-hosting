-- 2FA TOTP para clientes
CREATE TABLE IF NOT EXISTS client_totp (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id  INT UNSIGNED NOT NULL,
    secret     VARCHAR(64)  NOT NULL,
    enabled    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL,
    updated_at DATETIME     NOT NULL,
    UNIQUE KEY uq_client_totp_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
