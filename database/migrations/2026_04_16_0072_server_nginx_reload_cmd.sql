-- Comando customizado para reload do Nginx por servidor
ALTER TABLE servers ADD COLUMN IF NOT EXISTS nginx_reload_cmd VARCHAR(255) NULL AFTER nginx_vhost_path;
