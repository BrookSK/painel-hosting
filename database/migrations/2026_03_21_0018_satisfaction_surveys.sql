-- Migration: pesquisa de satisfação para tickets e chats
-- Tipos: ticket, chat

CREATE TABLE IF NOT EXISTS satisfaction_surveys (
    id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    type         ENUM('ticket','chat') NOT NULL,
    reference_id BIGINT UNSIGNED  NOT NULL COMMENT 'ticket.id ou chat_rooms.id',
    client_id    INT UNSIGNED     NOT NULL,
    rating       TINYINT UNSIGNED NOT NULL COMMENT '1 a 5',
    comment      TEXT             NULL,
    agent_id     INT UNSIGNED     NULL COMMENT 'users.id do agente avaliado',
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_survey (type, reference_id),
    INDEX idx_survey_client (client_id),
    INDEX idx_survey_agent  (agent_id),
    INDEX idx_survey_rating (rating),
    INDEX idx_survey_type   (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
