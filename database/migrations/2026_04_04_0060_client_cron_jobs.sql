-- Cron jobs dos clientes
CREATE TABLE IF NOT EXISTS client_cron_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    vps_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    task_type ENUM('command', 'url', 'php_script') NOT NULL DEFAULT 'command',
    command TEXT NOT NULL COMMENT 'Comando shell, URL ou caminho do script PHP',
    schedule VARCHAR(100) NOT NULL DEFAULT '0 * * * *' COMMENT 'Expressão cron (minuto hora dia mês dia_semana)',
    description VARCHAR(255) NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    last_run_at DATETIME NULL,
    last_status ENUM('success', 'error', 'running') NULL,
    last_output TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client (client_id),
    INDEX idx_vps (vps_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
