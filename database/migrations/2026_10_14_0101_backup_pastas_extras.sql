-- Pastas extras e banco no backup (3.6.2)
ALTER TABLE vps ADD COLUMN IF NOT EXISTS backup_extra_paths TEXT NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS backup_extra_roots TEXT NULL;
ALTER TABLE backups ADD COLUMN IF NOT EXISTS manifest TEXT NULL;
ALTER TABLE backups ADD COLUMN IF NOT EXISTS warnings TEXT NULL;
