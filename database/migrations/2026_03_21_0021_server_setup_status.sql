ALTER TABLE servers
    ADD COLUMN IF NOT EXISTS setup_status ENUM('pending','initializing','ready','error') NOT NULL DEFAULT 'pending'
    AFTER status;
