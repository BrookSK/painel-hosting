-- Proxy de borda (Traefik em outra máquina): para servidores sem IP público próprio
-- (ex.: VPS dentro de um dedicado). O painel grava uma regra por domínio no proxy,
-- que emite o SSL e repassa o tráfego HTTP para o IP interno do servidor.
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_ssh_host VARCHAR(190) NULL;
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_ssh_port INT NULL;
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_ssh_user VARCHAR(64) NULL;
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_ssh_password TEXT NULL;
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_rules_dir VARCHAR(255) NULL;
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_backend_ip VARCHAR(64) NULL;
ALTER TABLE servers ADD COLUMN IF NOT EXISTS edge_cert_resolver VARCHAR(64) NULL;
