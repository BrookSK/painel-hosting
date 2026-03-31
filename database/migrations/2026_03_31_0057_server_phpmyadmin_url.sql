-- URL do phpMyAdmin por servidor
ALTER TABLE servers
  ADD COLUMN IF NOT EXISTS phpmyadmin_url VARCHAR(500) NULL;
