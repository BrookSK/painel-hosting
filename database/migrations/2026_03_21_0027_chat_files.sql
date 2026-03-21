-- Migration: 0027 — Suporte a arquivos no chat
-- Adiciona colunas file_url e file_name na tabela chat_messages

ALTER TABLE chat_messages
    ADD COLUMN file_url  VARCHAR(500) NULL DEFAULT NULL AFTER message,
    ADD COLUMN file_name VARCHAR(255) NULL DEFAULT NULL AFTER file_url;
