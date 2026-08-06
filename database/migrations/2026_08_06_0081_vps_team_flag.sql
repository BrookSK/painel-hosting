-- Adiciona flag is_team_vps para identificar VPS internas da equipe (sem client_id)
SET @has_is_team_vps := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vps' AND COLUMN_NAME = 'is_team_vps'
);
SET @sql_team_vps := IF(@has_is_team_vps = 0,
  'ALTER TABLE vps ADD COLUMN is_team_vps TINYINT(1) NOT NULL DEFAULT 0 AFTER status, ADD COLUMN team_vps_name VARCHAR(150) NULL AFTER is_team_vps',
  'SELECT 1'
);
PREPARE s FROM @sql_team_vps; EXECUTE s; DEALLOCATE PREPARE s;

-- Permitir client_id NULL para VPS da equipe
SET @has_client_nullable := (
  SELECT IS_NULLABLE FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vps' AND COLUMN_NAME = 'client_id'
);
SET @sql_nullable := IF(@has_client_nullable = 'NO',
  'ALTER TABLE vps MODIFY COLUMN client_id INT UNSIGNED NULL',
  'SELECT 1'
);
PREPARE s FROM @sql_nullable; EXECUTE s; DEALLOCATE PREPARE s;

-- Índice para buscar VPS da equipe rapidamente
SET @has_idx_team := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vps' AND INDEX_NAME = 'idx_vps_team'
);
SET @sql_idx := IF(@has_idx_team = 0,
  'CREATE INDEX idx_vps_team ON vps (is_team_vps, status)',
  'SELECT 1'
);
PREPARE s FROM @sql_idx; EXECUTE s; DEALLOCATE PREPARE s;
