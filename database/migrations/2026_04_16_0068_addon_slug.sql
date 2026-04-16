-- Adicionar slug nos addons pra identificar o efeito técnico
ALTER TABLE plan_addons ADD COLUMN IF NOT EXISTS slug VARCHAR(60) NULL AFTER name;

-- Atualizar slugs dos addons existentes
UPDATE plan_addons SET slug = 'storage_10gb' WHERE name = 'Storage +10GB';
UPDATE plan_addons SET slug = 'backup_extra' WHERE name = 'Backup Extra';
UPDATE plan_addons SET slug = 'email_pro' WHERE name = 'E-mail Profissional';
UPDATE plan_addons SET slug = 'domain_extra' WHERE name = 'Domínio Extra';
UPDATE plan_addons SET slug = 'support_priority' WHERE name = 'Suporte Prioritário';
