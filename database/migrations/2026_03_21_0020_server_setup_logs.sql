CREATE TABLE IF NOT EXISTS server_setup_logs (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    server_id     INT UNSIGNED NOT NULL,
    step          VARCHAR(80)  NOT NULL,
    status        ENUM('ok','error','running') NOT NULL DEFAULT 'running',
    output        TEXT,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_server_setup (server_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
