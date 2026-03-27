-- Vincular bancos de dados criados automaticamente às aplicações
ALTER TABLE client_databases
  ADD COLUMN IF NOT EXISTS application_id INT UNSIGNED NULL AFTER vps_id,
  ADD KEY IF NOT EXISTS idx_client_db_app (application_id);
