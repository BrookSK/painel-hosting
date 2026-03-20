-- Migration: tabela de notificações internas para clientes
-- Criada em: 2026-03-20

CREATE TABLE IF NOT EXISTS `client_notifications` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`  INT UNSIGNED NOT NULL,
    `type`       VARCHAR(60)  NOT NULL DEFAULT 'info',
    `title`      VARCHAR(200) NOT NULL,
    `body`       TEXT         NOT NULL,
    `read_at`    DATETIME     NULL DEFAULT NULL,
    `created_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_cn_client` (`client_id`, `read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
