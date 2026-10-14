-- Segurança: só contas autorizadas pela equipe (parceiros/revenda) podem provisionar
-- recursos com custo pela API pública (criar VPS, criar clientes, criar repositórios).
-- Padrão 0 = bloqueado. Sem isso, qualquer cliente que se cadastrasse podia criar uma
-- API Key e provisionar VPS sem pagamento.
ALTER TABLE clients ADD COLUMN IF NOT EXISTS api_provisioning_enabled TINYINT(1) NOT NULL DEFAULT 0;
