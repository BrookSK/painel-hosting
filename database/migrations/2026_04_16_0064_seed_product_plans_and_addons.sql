-- ═══════════════════════════════════════════════════════════════
-- Seed: Planos por tipo de produto + Addons globais
-- ═══════════════════════════════════════════════════════════════

-- ── WORDPRESS GERENCIADO ──

INSERT INTO plans (name, plan_type, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency, max_sites, max_databases, max_cron_jobs, backup_slots, is_featured, specs_json, support_channels, status, created_at) VALUES
('WP Starter',   'wordpress', 'Ideal para blog pessoal ou site institucional', 1, 1024,  10240,  29.90,  3.49, 'BRL', 1,  1,  0, 0, 0, '{"bandwidth":"1TB","sla":"99.5","email_accounts":0,"max_domains":1}',  '["email","ticket"]', 'active', NOW()),
('WP Growth',    'wordpress', 'Para agências e múltiplos projetos WordPress',  2, 2048,  20480,  49.90,  5.99, 'BRL', 3,  3,  0, 1, 1, '{"bandwidth":"3TB","sla":"99.9","email_accounts":3,"max_domains":3}',  '["email","ticket","chat"]', 'active', NOW()),
('WP Business',  'wordpress', 'Performance e escala para negócios em crescimento', 4, 4096,  51200,  89.90,  9.99, 'BRL', 10, 10, 0, 2, 0, '{"bandwidth":"5TB","sla":"99.9","email_accounts":10,"max_domains":5}', '["email","ticket","chat","whatsapp"]', 'active', NOW()),
('WP Scale',     'wordpress', 'Sites ilimitados com infraestrutura robusta',   6, 8192, 102400, 149.90, 14.99, 'BRL', NULL, NULL, 0, 2, 0, '{"bandwidth":"Ilimitada","sla":"99.99","email_accounts":50,"max_domains":10}', '["email","ticket","chat","whatsapp","telefone"]', 'active', NOW());

-- ── WEB HOSTING ──

INSERT INTO plans (name, plan_type, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency, max_sites, max_databases, max_cron_jobs, backup_slots, is_featured, specs_json, support_channels, status, created_at) VALUES
('Hosting Starter', 'webhosting', 'Para seu primeiro site ou projeto pessoal',       1, 1024,  10240,  24.90,  2.99, 'BRL', 1,  1,  2,  0, 0, '{"bandwidth":"1TB","sla":"99.5","max_apps":1,"max_domains":1}',  '["email","ticket"]', 'active', NOW()),
('Hosting Plus',    'webhosting', 'Múltiplos sites com catálogo de apps completo',   2, 2048,  30720,  44.90,  4.99, 'BRL', 5,  5,  5,  1, 1, '{"bandwidth":"3TB","sla":"99.9","max_apps":3,"max_domains":5}',  '["email","ticket","chat"]', 'active', NOW()),
('Hosting Business','webhosting', 'Para agências e projetos profissionais',          4, 4096,  81920,  79.90,  8.99, 'BRL', 15, 15, 10, 2, 0, '{"bandwidth":"5TB","sla":"99.9","max_apps":10,"max_domains":10}','["email","ticket","chat","whatsapp"]', 'active', NOW()),
('Hosting Unlimited','webhosting','Sites e apps ilimitados com recursos generosos',  6, 8192, 153600, 129.90, 12.99, 'BRL', NULL, NULL, NULL, 2, 0, '{"bandwidth":"Ilimitada","sla":"99.99","max_apps":999,"max_domains":50}', '["email","ticket","chat","whatsapp","telefone"]', 'active', NOW());

-- ── NODE.JS ──

INSERT INTO plans (name, plan_type, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency, max_sites, max_databases, max_cron_jobs, backup_slots, is_featured, specs_json, support_channels, status, created_at) VALUES
('Node Dev',     'nodejs', 'Para desenvolvimento e projetos pessoais',       1,  512,   5120,  39.90,  4.99, 'BRL', 1, 1, 0, 0, 0, '{"bandwidth":"1TB","sla":"99.5","max_domains":1}',  '["email","ticket"]', 'active', NOW()),
('Node Starter', 'nodejs', 'Para startups e APIs em produção',               2, 2048,  15360,  59.90,  6.99, 'BRL', 2, 2, 0, 0, 0, '{"bandwidth":"3TB","sla":"99.9","max_domains":2}',  '["email","ticket","chat"]', 'active', NOW()),
('Node Pro',     'nodejs', 'Para aplicações de médio porte com alta demanda', 3, 4096,  40960,  99.90, 10.99, 'BRL', 5, 5, 0, 1, 1, '{"bandwidth":"5TB","sla":"99.9","max_domains":5}',  '["email","ticket","chat","whatsapp"]', 'active', NOW()),
('Node Scale',   'nodejs', 'Para aplicações de grande escala',               6, 8192,  81920, 179.90, 17.99, 'BRL', 10, 10, 0, 2, 0, '{"bandwidth":"Ilimitada","sla":"99.99","max_domains":10}', '["email","ticket","chat","whatsapp","telefone"]', 'active', NOW());

-- ── PHP / LARAVEL ──

INSERT INTO plans (name, plan_type, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency, max_sites, max_databases, max_cron_jobs, backup_slots, is_featured, specs_json, support_channels, status, created_at) VALUES
('PHP Starter',  'php', 'Para seu primeiro projeto Laravel ou PHP',       1, 1024,  10240,  34.90,  3.99, 'BRL', 1,  1,  0, 0, 0, '{"bandwidth":"1TB","sla":"99.5","max_domains":1}',  '["email","ticket"]', 'active', NOW()),
('PHP Pro',      'php', 'Para múltiplos projetos PHP em produção',        2, 2048,  30720,  69.90,  7.99, 'BRL', 5,  5,  0, 1, 1, '{"bandwidth":"3TB","sla":"99.9","max_domains":5}',  '["email","ticket","chat"]', 'active', NOW()),
('PHP Business', 'php', 'Para agências e projetos de grande porte',       4, 4096,  81920, 119.90, 12.99, 'BRL', 15, 15, 0, 2, 0, '{"bandwidth":"5TB","sla":"99.9","max_domains":10}', '["email","ticket","chat","whatsapp"]', 'active', NOW()),
('PHP Scale',    'php', 'Apps ilimitados com infraestrutura robusta',      6, 8192, 122880, 179.90, 17.99, 'BRL', NULL, NULL, 0, 2, 0, '{"bandwidth":"Ilimitada","sla":"99.99","max_domains":50}', '["email","ticket","chat","whatsapp","telefone"]', 'active', NOW());

-- ── PYTHON ──

INSERT INTO plans (name, plan_type, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency, max_sites, max_databases, max_cron_jobs, backup_slots, is_featured, specs_json, support_channels, status, created_at) VALUES
('Python Dev',     'python', 'Para desenvolvimento e projetos pessoais',         1, 1024,  10240,  39.90,  4.99, 'BRL', 1, 1, 0, 0, 0, '{"bandwidth":"1TB","sla":"99.5","max_domains":1}',  '["email","ticket"]', 'active', NOW()),
('Python Starter', 'python', 'Para APIs e apps Django/Flask em produção',        2, 2048,  20480,  59.90,  6.99, 'BRL', 2, 2, 0, 0, 0, '{"bandwidth":"3TB","sla":"99.9","max_domains":2}',  '["email","ticket","chat"]', 'active', NOW()),
('Python Pro',     'python', 'Para aplicações de médio porte com alta demanda',  4, 4096,  51200, 109.90, 11.99, 'BRL', 5, 5, 0, 1, 1, '{"bandwidth":"5TB","sla":"99.9","max_domains":5}',  '["email","ticket","chat","whatsapp"]', 'active', NOW()),
('Python Scale',   'python', 'Para aplicações de grande escala e ML/AI',         6, 8192, 102400, 189.90, 18.99, 'BRL', 10, 10, 0, 2, 0, '{"bandwidth":"Ilimitada","sla":"99.99","max_domains":10}', '["email","ticket","chat","whatsapp","telefone"]', 'active', NOW());

-- ── C/C++ ──

INSERT INTO plans (name, plan_type, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency, max_sites, max_databases, max_cron_jobs, backup_slots, is_featured, specs_json, support_channels, status, created_at) VALUES
('C++ Build',       'cpp', 'Para projetos de compilação e testes',              2, 2048,  20480,  79.90,  8.99, 'BRL', 1, 1, 0, 0, 0, '{"bandwidth":"2TB","sla":"99.5","max_domains":1}',  '["email","ticket"]', 'active', NOW()),
('C++ Performance', 'cpp', 'Para aplicações de alta performance em produção',   4, 4096,  51200, 139.90, 13.99, 'BRL', 3, 3, 0, 1, 0, '{"bandwidth":"5TB","sla":"99.9","max_domains":3}',  '["email","ticket","chat"]', 'active', NOW()),
('C++ Compute',     'cpp', 'Para workloads intensivos de computação',           6, 8192, 102400, 219.90, 19.99, 'BRL', 6, 6, 0, 2, 1, '{"bandwidth":"Ilimitada","sla":"99.9","max_domains":6}',  '["email","ticket","chat","whatsapp"]', 'active', NOW()),
('C++ Cluster',     'cpp', 'Para clusters e aplicações de larga escala',        8, 16384, 204800, 349.90, 29.99, 'BRL', NULL, NULL, 0, 2, 0, '{"bandwidth":"Ilimitada","sla":"99.99","max_domains":20}', '["email","ticket","chat","whatsapp","telefone"]', 'active', NOW());

-- ── APP GENÉRICO ──

INSERT INTO plans (name, plan_type, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency, max_sites, max_databases, max_cron_jobs, backup_slots, is_featured, specs_json, support_channels, allowed_features, status, created_at) VALUES
('App Basic',    'app', 'Para projetos simples com stack customizada',     1, 1024,  10240,  49.90,  5.99, 'BRL', 1,  1,  0, 0, 0, '{"bandwidth":"1TB","sla":"99.5","max_domains":1}',  '["email","ticket"]', '["aplicacoes","banco_dados","dominios","git_deploy"]', 'active', NOW()),
('App Pro',      'app', 'Para múltiplos projetos com flexibilidade total', 2, 4096,  40960,  99.90, 10.99, 'BRL', 5,  5,  0, 1, 1, '{"bandwidth":"3TB","sla":"99.9","max_domains":5}',  '["email","ticket","chat"]', '["aplicacoes","catalogo","banco_dados","arquivos","dominios","git_deploy","backups"]', 'active', NOW()),
('App Business', 'app', 'Para empresas com necessidades específicas',      4, 8192,  81920, 179.90, 17.99, 'BRL', 10, 10, 0, 2, 0, '{"bandwidth":"5TB","sla":"99.9","max_domains":10}', '["email","ticket","chat","whatsapp"]', '["aplicacoes","catalogo","banco_dados","arquivos","dominios","git_deploy","cron_jobs","backups","emails"]', 'active', NOW()),
('App Custom',   'app', 'Plano sob medida — infraestrutura personalizada conforme sua necessidade. Entre em contato para configuração.', 1, 1024, 10240, 0.00, 0.00, 'BRL', NULL, NULL, NULL, 0, 0, '{"bandwidth":"Sob consulta","sla":"99.99"}', '["email","ticket","chat","whatsapp","telefone"]', '["vps","monitoramento","aplicacoes","catalogo","git_deploy","banco_dados","arquivos","terminal","cron_jobs","backups","emails","dominios"]', 'active', NOW());

-- ═══════════════════════════════════════════════════════════════
-- Addons globais (vinculados a cada plano após criação)
-- Usamos um procedimento para vincular addons a todos os planos
-- ═══════════════════════════════════════════════════════════════

-- Criar addons temporários e vincular a todos os planos ativos
-- Infraestrutura
INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'Storage +10GB', 'Armazenamento SSD adicional de 10GB', 19.90, 3.99, 17.90, 3.49, 1, 1
FROM plans p WHERE p.plan_type IN ('wordpress','webhosting','nodejs','php','python','cpp','app') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'Backup Extra', 'Slot adicional de backup automático diário', 9.90, 1.99, 8.90, 1.49, 2, 1
FROM plans p WHERE p.plan_type IN ('wordpress','webhosting','nodejs','php','python','cpp','app') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

-- Produtividade
INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'E-mail Profissional', '5 contas de e-mail com domínio próprio + webmail', 14.90, 2.99, 12.90, 2.49, 3, 1
FROM plans p WHERE p.plan_type IN ('wordpress','webhosting','php') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'Domínio Extra', 'Domínio adicional com SSL automático', 9.90, 1.99, 8.90, 1.49, 4, 1
FROM plans p WHERE p.plan_type IN ('wordpress','webhosting','nodejs','php','python','cpp','app') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

-- Performance
INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'CDN', 'Cache global via CDN para performance máxima', 19.90, 3.99, 17.90, 3.49, 5, 1
FROM plans p WHERE p.plan_type IN ('wordpress','webhosting','php') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'SSL Wildcard', 'Certificado SSL wildcard (*.seudominio.com)', 29.90, 5.99, 24.90, 4.99, 6, 1
FROM plans p WHERE p.plan_type IN ('wordpress','webhosting','nodejs','php','python','cpp','app') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

-- Dev
INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'CI/CD Pipeline', 'Pipeline automatizado de build, teste e deploy', 24.90, 4.99, 21.90, 4.49, 7, 1
FROM plans p WHERE p.plan_type IN ('nodejs','python','cpp','php') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'Staging Environment', 'Ambiente de testes separado para validação', 19.90, 3.99, 17.90, 3.49, 8, 1
FROM plans p WHERE p.plan_type IN ('wordpress','php') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;

-- Suporte
INSERT INTO plan_addons (plan_id, name, description, price, price_usd, price_annual, price_annual_usd, sort_order, active)
SELECT p.id, 'Suporte Prioritário', 'Atendimento prioritário via WhatsApp e telefone', 29.90, 5.99, 24.90, 4.99, 9, 1
FROM plans p WHERE p.plan_type IN ('wordpress','webhosting','nodejs','php','python','cpp','app') AND p.status = 'active' AND p.client_id IS NULL AND p.price_monthly > 0;
