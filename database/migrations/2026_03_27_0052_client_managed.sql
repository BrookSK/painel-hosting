-- Adiciona flag is_managed para clientes gerenciados (plano personalizado/manual)
ALTER TABLE clients ADD COLUMN is_managed TINYINT(1) NOT NULL DEFAULT 0;
