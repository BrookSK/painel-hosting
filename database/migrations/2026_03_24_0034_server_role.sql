ALTER TABLE servers ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'vps' AFTER status;
UPDATE servers SET role = 'email' WHERE hostname = 'email-server';
