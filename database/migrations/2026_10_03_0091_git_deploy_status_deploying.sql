-- O status de git_deployments era ENUM('active','inactive','error'), o que impedia
-- gravar o estado intermediário 'deploying' (publicação em andamento via fila/worker).
-- Troca para VARCHAR para suportar 'deploying' e futuros estados sem travar o INSERT/UPDATE.
ALTER TABLE git_deployments MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active';
