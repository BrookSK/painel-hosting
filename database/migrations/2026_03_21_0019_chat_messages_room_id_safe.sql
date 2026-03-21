-- Migration 0019: garante que chat_messages.room_id existe e aponta para chat_rooms
-- Idempotente: usa procedure para verificar antes de alterar

DROP PROCEDURE IF EXISTS _fix_chat_messages;

DELIMITER $$
CREATE PROCEDURE _fix_chat_messages()
BEGIN
    -- Só renomeia se ticket_id ainda existir
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'chat_messages'
          AND COLUMN_NAME  = 'ticket_id'
    ) THEN
        -- Remove FK antiga se existir
        IF EXISTS (
            SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'chat_messages'
              AND CONSTRAINT_NAME = 'fk_chat_messages_ticket'
        ) THEN
            ALTER TABLE chat_messages DROP FOREIGN KEY fk_chat_messages_ticket;
        END IF;

        -- Remove índice antigo se existir
        IF EXISTS (
            SELECT 1 FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'chat_messages'
              AND INDEX_NAME   = 'idx_chat_messages_ticket_id'
        ) THEN
            ALTER TABLE chat_messages DROP INDEX idx_chat_messages_ticket_id;
        END IF;

        -- Renomeia coluna
        ALTER TABLE chat_messages
            CHANGE COLUMN ticket_id room_id BIGINT UNSIGNED NOT NULL;
    END IF;

    -- Garante índice room_id
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'chat_messages'
          AND INDEX_NAME   = 'idx_chat_messages_room'
    ) THEN
        ALTER TABLE chat_messages
            ADD INDEX idx_chat_messages_room (room_id);
    END IF;

    -- Garante FK para chat_rooms
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'chat_messages'
          AND CONSTRAINT_NAME = 'fk_chat_messages_room'
    ) THEN
        ALTER TABLE chat_messages
            ADD CONSTRAINT fk_chat_messages_room
                FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE;
    END IF;
END$$
DELIMITER ;

CALL _fix_chat_messages();
DROP PROCEDURE IF EXISTS _fix_chat_messages;
