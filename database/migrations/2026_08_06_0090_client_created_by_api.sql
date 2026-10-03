-- Permite que um cliente (revenda/parceiro, ex.: helpdeskON) crie e gerencie
-- outros clientes via API pública. created_by_client_id aponta para o cliente
-- "dono" (quem criou pela API). NULL = cliente comum, cadastrado diretamente.
ALTER TABLE clients ADD COLUMN IF NOT EXISTS created_by_client_id INT UNSIGNED NULL DEFAULT NULL AFTER is_managed;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS external_ref VARCHAR(120) NULL DEFAULT NULL AFTER created_by_client_id;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER external_ref;
ALTER TABLE clients ADD INDEX IF NOT EXISTS idx_clients_created_by (created_by_client_id);
