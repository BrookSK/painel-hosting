-- Pular verificação TXT — mover todos pending_txt para pending_cname
UPDATE client_subdomains SET status = 'pending_cname' WHERE status = 'pending_txt';

-- Atualizar enum para remover pending_txt
ALTER TABLE client_subdomains MODIFY COLUMN status ENUM('pending_cname','active','error') NOT NULL DEFAULT 'pending_cname';
