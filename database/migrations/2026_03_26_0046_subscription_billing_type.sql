-- Adicionar billing_type e last_reminder_at na tabela subscriptions
ALTER TABLE subscriptions
  ADD COLUMN billing_type VARCHAR(20) NULL AFTER stripe_checkout_session_id,
  ADD COLUMN last_reminder_at DATETIME NULL AFTER next_due_date;
