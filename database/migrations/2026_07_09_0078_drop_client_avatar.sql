-- Reverte a migration 0077: remove coluna avatar da tabela clients.

ALTER TABLE clients DROP COLUMN avatar;
