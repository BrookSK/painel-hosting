-- Adiciona coluna avatar na tabela clients para armazenar o path do avatar customizado.
-- Se NULL, o sistema usa Gravatar baseado no e-mail do cliente.

ALTER TABLE clients ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER email;
