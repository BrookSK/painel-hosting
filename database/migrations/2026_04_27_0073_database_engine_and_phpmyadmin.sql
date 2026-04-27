-- Suporte a bancos nativos (MySQL do host) para servidores com aaPanel/painel
-- Coluna engine: 'docker' (container dedicado) ou 'native' (MySQL do host)
ALTER TABLE client_databases
  ADD COLUMN IF NOT EXISTS engine ENUM('docker','native') NOT NULL DEFAULT 'docker' AFTER container_id;

-- Senha root do MySQL por servidor (cifrada)
-- Necessária para criar/excluir bancos no MySQL nativo (aaPanel, etc)
ALTER TABLE servers
  ADD COLUMN IF NOT EXISTS mysql_root_password VARCHAR(500) NULL AFTER phpmyadmin_url;
