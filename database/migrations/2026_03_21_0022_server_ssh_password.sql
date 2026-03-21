-- Adiciona suporte a autenticação SSH por senha (além de chave)
-- ssh_password é armazenado criptografado via AES-256-CBC pela aplicação
ALTER TABLE servers
    ADD COLUMN IF NOT EXISTS ssh_password VARCHAR(512) NULL DEFAULT NULL AFTER ssh_key_id,
    ADD COLUMN IF NOT EXISTS ssh_auth_type ENUM('key','password') NOT NULL DEFAULT 'key' AFTER ssh_password;

-- Servidores existentes sem chave ficam como 'key' (comportamento anterior)
-- Servidores novos com senha terão ssh_auth_type = 'password'
