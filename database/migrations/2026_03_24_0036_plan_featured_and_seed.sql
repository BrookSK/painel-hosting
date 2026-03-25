ALTER TABLE plans ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

-- Seed: 5 planos
INSERT INTO plans (name, description, cpu, ram, storage, price_monthly, status, is_featured, backup_slots, support_channels, specs_json, created_at) VALUES
('Startup', 'O básico para começar seu projeto', 1, 2048, 20480, 90.00, 'active', 0, 0, '["email","ticket"]', '{"email_accounts":3,"email_quota_mb":2048,"bandwidth":"1 TB","max_domains":1,"max_apps":1,"sla":"99.0"}', NOW()),
('Essential', 'Ideal para startups e projetos em início', 2, 4096, 40960, 297.00, 'active', 1, 1, '["email","chat","ticket"]', '{"email_accounts":5,"email_quota_mb":5120,"bandwidth":"3 TB","max_domains":3,"max_apps":1,"sla":"99.5"}', NOW()),
('Professional', 'Para empresas em crescimento', 4, 8192, 81920, 697.00, 'active', 0, 1, '["email","chat","ticket"]', '{"email_accounts":10,"email_quota_mb":10240,"bandwidth":"5 TB","max_domains":5,"max_apps":3,"sla":"99.9"}', NOW()),
('Business', 'Para operações de alto volume', 8, 16384, 163840, 957.00, 'active', 0, 2, '["email","whatsapp","chat","ticket"]', '{"email_accounts":25,"email_quota_mb":25600,"bandwidth":"10 TB","max_domains":10,"max_apps":999,"sla":"99.9"}', NOW()),
('Enterprise', 'Infraestrutura dedicada customizada', 16, 32768, 307200, 1497.00, 'active', 0, 2, '["email","whatsapp","chat","telefone","ticket"]', '{"email_accounts":50,"email_quota_mb":51200,"bandwidth":"Ilimitada","max_domains":999,"max_apps":999,"sla":"99.99"}', NOW());

-- Seed: addons para cada plano
INSERT INTO plan_addons (plan_id, name, description, price, sort_order, active)
SELECT p.id, 'Backup diário', 'Backup diário automatizado com retenção', 90.00, 0, 1 FROM plans p WHERE p.name IN ('Startup','Essential','Professional','Business','Enterprise')
UNION ALL
SELECT p.id, 'Suporte WhatsApp', 'Atendimento prioritário via WhatsApp', 290.00, 1, 1 FROM plans p WHERE p.name IN ('Startup','Essential','Professional','Business','Enterprise');
