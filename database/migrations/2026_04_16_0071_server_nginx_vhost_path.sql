-- Caminho customizado para vhosts Nginx por servidor
-- Permite usar o diretório do aaPanel ou outro painel
ALTER TABLE servers ADD COLUMN IF NOT EXISTS nginx_vhost_path VARCHAR(255) NULL AFTER volume_base_path;
