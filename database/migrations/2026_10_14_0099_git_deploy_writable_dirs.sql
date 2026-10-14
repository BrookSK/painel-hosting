-- Pastas que o cliente liberou para gravação (botão "Liberar pasta de arquivos").
-- Cada novo deploy reaplica a permissão nelas — antes o deploy resetava tudo para 755 e
-- trocava o dono, e o site parava de conseguir gravar uploads.
ALTER TABLE git_deployments ADD COLUMN IF NOT EXISTS writable_dirs TEXT NULL;
