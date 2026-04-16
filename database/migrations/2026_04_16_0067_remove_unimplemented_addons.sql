-- Remover addons que não têm implementação técnica real
-- Mantém apenas: Storage +10GB, Backup Extra, E-mail Profissional, Domínio Extra, Suporte Prioritário
DELETE FROM plan_addons WHERE name IN ('CDN', 'SSL Wildcard', 'CI/CD Pipeline', 'Staging Environment');
