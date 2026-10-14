-- Confirmação de e-mail no cadastro feito pelo próprio cliente.
-- email_verification_pending = 1 só para quem se cadastra sozinho; contas criadas pela
-- equipe, pela API ou já existentes continuam liberadas (0).
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_verification_pending TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_verify_token_hash CHAR(64) NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_verify_expires_at DATETIME NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_verify_sent_at DATETIME NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_verified_at DATETIME NULL;
ALTER TABLE clients ADD INDEX IF NOT EXISTS idx_clients_email_verify (email_verify_token_hash);

-- Webhook do Git Deploy: código secreto para assinatura (HMAC) do GitHub/GitLab/Bitbucket.
ALTER TABLE git_deployments ADD COLUMN IF NOT EXISTS webhook_hmac_secret VARCHAR(80) NULL;
ALTER TABLE git_deployments ADD COLUMN IF NOT EXISTS webhook_hmac_required TINYINT(1) NOT NULL DEFAULT 1;
-- Webhooks que já existem continuam funcionando sem assinatura até o cliente configurar o
-- Secret (na primeira entrega assinada o sistema passa a exigir a assinatura sozinho).
UPDATE git_deployments SET webhook_hmac_required = 0 WHERE webhook_secret IS NOT NULL AND webhook_secret <> '';

-- Tokens de redefinição de senha agora são guardados com hash: os antigos (em texto) deixam de valer.
DELETE FROM password_resets WHERE used_at IS NULL;
