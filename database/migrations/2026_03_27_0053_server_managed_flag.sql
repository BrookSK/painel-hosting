-- Marca servidores dedicados a clientes gerenciados (overselling)
ALTER TABLE servers ADD COLUMN is_managed_server TINYINT(1) NOT NULL DEFAULT 0;
