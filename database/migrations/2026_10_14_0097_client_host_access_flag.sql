-- Segurança: recursos que executam comandos DIRETO no servidor (fora do container do
-- cliente) — Git Deploy, console do deploy, cron no servidor, gerenciador de arquivos
-- em modo "servidor" — só ficam disponíveis para contas liberadas pela equipe.
-- Padrão 0 = bloqueado (contas novas não recebem acesso ao servidor automaticamente).
ALTER TABLE clients ADD COLUMN IF NOT EXISTS host_access_enabled TINYINT(1) NOT NULL DEFAULT 0;

-- Mantém funcionando quem JÁ usa esses recursos hoje (revise a lista depois em
-- SELECT id, name, email FROM clients WHERE host_access_enabled = 1;)
UPDATE clients SET host_access_enabled = 1
WHERE id IN (SELECT client_id FROM git_deployments)
   OR id IN (SELECT client_id FROM client_cron_jobs);
