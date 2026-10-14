-- Upgrade de plano e serviços adicionais só são liberados DEPOIS do pagamento.
-- Guardamos a cobrança gerada para o webhook do Asaas aplicar a mudança quando for paga.
ALTER TABLE subscription_upgrades ADD COLUMN IF NOT EXISTS asaas_payment_id VARCHAR(80) NULL;
ALTER TABLE subscription_upgrades ADD COLUMN IF NOT EXISTS proration_charge DECIMAL(10,2) NULL;
ALTER TABLE subscription_upgrades ADD INDEX IF NOT EXISTS idx_sub_upgrades_payment (asaas_payment_id);
ALTER TABLE subscription_addon_items ADD INDEX IF NOT EXISTS idx_sub_addon_items_payment (asaas_payment_id);
