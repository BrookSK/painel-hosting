ALTER TABLE jobs
  ADD COLUMN run_at DATETIME NULL AFTER payload;

CREATE INDEX idx_jobs_status_run_at_id ON jobs (status, run_at, id);
