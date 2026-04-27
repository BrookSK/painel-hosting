-- Suporte a bancos nativos (MySQL do host) para servidores com aaPanel/painel
-- Coluna engine: 'docker' (container dedicado) ou 'native' (MySQL do host)
ALTER TABLE client_databases
  ADD COLUMN IF NOT EXISTS engine ENUM('docker','native') NOT NULL DEFAULT 'docker' AFTER container_id;
