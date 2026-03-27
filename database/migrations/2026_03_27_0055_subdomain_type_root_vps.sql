-- Adiciona 'root_vps' ao enum de type na tabela client_subdomains
ALTER TABLE client_subdomains MODIFY COLUMN type VARCHAR(30) NOT NULL DEFAULT 'subdomain';

-- Trocar status de enum para varchar para suportar pending_dns, pending_txt, etc.
ALTER TABLE client_subdomains MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'pending_cname';

-- Corrigir registros que deveriam ser root_vps mas foram salvos como subdomain por causa do enum antigo
UPDATE client_subdomains SET type = 'root_vps', status = 'pending_dns' WHERE type = 'subdomain' AND (cname_target IS NULL OR cname_target = '') AND root_domain = subdomain;
