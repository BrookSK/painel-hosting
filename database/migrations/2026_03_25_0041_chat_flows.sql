-- Chat Flows: tables, indexes, seeds, and settings
-- ============================================================

-- 1. chat_flows table
CREATE TABLE IF NOT EXISTS `chat_flows` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(500) DEFAULT NULL,
  `trigger_type` ENUM('client_inactive','chat_closed','manual') NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. chat_flow_steps table
CREATE TABLE IF NOT EXISTS `chat_flow_steps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `flow_id` INT NOT NULL,
  `sort_order` INT NOT NULL,
  `step_type` ENUM('message','delay','action') NOT NULL,
  `content` TEXT DEFAULT NULL,
  `delay_seconds` INT DEFAULT NULL,
  `action_type` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_flow_steps_flow` FOREIGN KEY (`flow_id`) REFERENCES `chat_flows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_flow_steps_order` ON `chat_flow_steps` (`flow_id`, `sort_order`);

-- 3. chat_flow_executions table
CREATE TABLE IF NOT EXISTS `chat_flow_executions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `flow_id` INT NOT NULL,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `trigger_source` ENUM('cron','manual','event') NOT NULL,
  `current_step` INT NOT NULL DEFAULT 0,
  `status` ENUM('running','completed','failed') NOT NULL DEFAULT 'running',
  `started_at` DATETIME NOT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `next_run_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_flow_exec_flow` FOREIGN KEY (`flow_id`) REFERENCES `chat_flows`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_flow_exec_room` FOREIGN KEY (`room_id`) REFERENCES `chat_rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_flow_exec_room_status` ON `chat_flow_executions` (`room_id`, `status`);

-- 4. ALTER chat_messages.sender_type to support 'system'
ALTER TABLE `chat_messages` MODIFY COLUMN `sender_type` VARCHAR(20) NOT NULL DEFAULT 'client';

-- 5. Seed default flows
INSERT INTO `chat_flows` (`name`, `description`, `trigger_type`, `active`, `created_at`, `updated_at`)
VALUES ('Still there?', 'Checks if the client is still active after agent message', 'client_inactive', 1, NOW(), NOW());

SET @flow_inactive_id = LAST_INSERT_ID();

INSERT INTO `chat_flow_steps` (`flow_id`, `sort_order`, `step_type`, `content`, `delay_seconds`, `action_type`, `created_at`) VALUES
(@flow_inactive_id, 1, 'message', 'Você ainda está aí? Posso ajudar com mais alguma coisa?', NULL, NULL, NOW()),
(@flow_inactive_id, 2, 'delay', NULL, 300, NULL, NOW()),
(@flow_inactive_id, 3, 'action', NULL, NULL, 'close_chat', NOW());

INSERT INTO `chat_flows` (`name`, `description`, `trigger_type`, `active`, `created_at`, `updated_at`)
VALUES ('Satisfaction', 'Sends satisfaction survey after chat closure', 'chat_closed', 1, NOW(), NOW());

SET @flow_satisfaction_id = LAST_INSERT_ID();

INSERT INTO `chat_flow_steps` (`flow_id`, `sort_order`, `step_type`, `content`, `delay_seconds`, `action_type`, `created_at`) VALUES
(@flow_satisfaction_id, 1, 'message', 'Obrigado pelo contato! Gostaríamos de saber como foi sua experiência.', NULL, NULL, NOW()),
(@flow_satisfaction_id, 2, 'action', NULL, NULL, 'send_satisfaction_link', NOW());

-- 6. Default settings
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('chat_flow.inactivity_minutes', '10');
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('chat_flow.cron_interval_seconds', '60');
