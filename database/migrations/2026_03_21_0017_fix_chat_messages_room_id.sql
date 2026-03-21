-- Migration: corrige chat_messages — renomeia ticket_id para room_id
-- A tabela foi criada pela migration 0003 com coluna ticket_id (para tickets),
-- mas a migration 0007 tentou recriar com room_id (IF NOT EXISTS não executou).
-- Esta migration corrige a estrutura sem perder dados.

-- 1. Remover FK antiga (se existir)
ALTER TABLE chat_messages
    DROP FOREIGN KEY IF EXISTS fk_chat_messages_ticket;

-- 2. Remover índice antigo (se existir)
ALTER TABLE chat_messages
    DROP INDEX IF EXISTS idx_chat_messages_ticket_id;

-- 3. Renomear coluna ticket_id → room_id (só se room_id ainda não existir)
ALTER TABLE chat_messages
    CHANGE COLUMN ticket_id room_id BIGINT UNSIGNED NOT NULL;

-- 4. Adicionar índice e FK corretos
ALTER TABLE chat_messages
    ADD INDEX idx_chat_messages_room (room_id),
    ADD CONSTRAINT fk_chat_messages_room
        FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE;
