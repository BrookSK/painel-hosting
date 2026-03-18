SET @has_run_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'jobs'
    AND COLUMN_NAME = 'run_at'
);

SET @sql1 := IF(@has_run_at = 0,
  'ALTER TABLE jobs ADD COLUMN run_at DATETIME NULL AFTER payload',
  'SELECT 1'
);

PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @has_idx_jobs_run_at := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'jobs'
    AND INDEX_NAME = 'idx_jobs_status_run_at_id'
);

SET @sql2 := IF(@has_idx_jobs_run_at = 0,
  'CREATE INDEX idx_jobs_status_run_at_id ON jobs (status, run_at, id)',
  'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
