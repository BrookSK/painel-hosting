-- Token de autenticação para repositórios privados (cifrado)
ALTER TABLE git_deployments
  ADD COLUMN IF NOT EXISTS auth_token_enc VARCHAR(500) NULL AFTER repo_url;
