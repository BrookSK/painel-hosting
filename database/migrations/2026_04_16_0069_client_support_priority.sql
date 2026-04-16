-- Flag de suporte prioritário no cliente
ALTER TABLE clients ADD COLUMN IF NOT EXISTS support_priority TINYINT(1) NOT NULL DEFAULT 0 AFTER onboarding_done;
