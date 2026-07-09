-- Tabela para rastrear presença de clientes no WebSocket do chat.
-- Permite que o sistema HTTP saiba se o cliente está online antes de enviar e-mail.

CREATE TABLE IF NOT EXISTS chat_presence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    connected_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_chat_presence_room (room_id),
    INDEX idx_chat_presence_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
