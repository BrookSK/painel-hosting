-- Caminho base de volumes por servidor (onde os dados das VPS ficam)
-- Permite usar discos/partições diferentes por servidor
ALTER TABLE servers ADD COLUMN IF NOT EXISTS volume_base_path VARCHAR(255) NULL AFTER phpmyadmin_url;
