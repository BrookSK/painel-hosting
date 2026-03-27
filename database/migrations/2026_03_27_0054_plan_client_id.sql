-- Planos personalizados: vincula um plano a um cliente específico (NULL = plano público)
ALTER TABLE plans ADD COLUMN client_id INT UNSIGNED NULL;
ALTER TABLE plans ADD CONSTRAINT fk_plans_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL;
