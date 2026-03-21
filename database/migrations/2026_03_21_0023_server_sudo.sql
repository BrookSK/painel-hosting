-- Suporte a sudo para servidores onde o usuário SSH não é root
ALTER TABLE servers
    ADD COLUMN IF NOT EXISTS use_sudo      TINYINT(1) NOT NULL DEFAULT 0 AFTER ssh_auth_type,
    ADD COLUMN IF NOT EXISTS sudo_password VARCHAR(512) NULL DEFAULT NULL AFTER use_sudo;
-- sudo_password: cifrado via SshCrypto. NULL = usa a mesma senha SSH (ou sudo sem senha).
