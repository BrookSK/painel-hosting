-- Adiciona suporte a sudo no servidor de origem da migração WordPress
ALTER TABLE wp_migrations
  ADD COLUMN source_use_sudo TINYINT(1) NOT NULL DEFAULT 0 AFTER source_db_port,
  ADD COLUMN source_sudo_password_enc VARCHAR(500) NOT NULL DEFAULT '' AFTER source_use_sudo;
