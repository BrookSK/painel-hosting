-- Plano sob consulta (custom): não pode ser contratado diretamente
ALTER TABLE plans
  ADD COLUMN IF NOT EXISTS is_custom TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured;
