-- Inativar cliente (bloqueia login, sessões e API sem apagar nada)
ALTER TABLE clients ADD COLUMN IF NOT EXISTS disabled_at DATETIME NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS disabled_reason VARCHAR(255) NULL;

-- Exclusão definitiva em segundo plano (job excluir_cliente)
-- deletion_status: NULL (normal) | pending | running | failed
ALTER TABLE clients ADD COLUMN IF NOT EXISTS deletion_status VARCHAR(20) NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS deletion_error TEXT NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS deletion_requested_at DATETIME NULL;
