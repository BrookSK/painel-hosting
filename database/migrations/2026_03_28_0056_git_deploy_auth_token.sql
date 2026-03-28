-- Token de autenticação e deploy key para repositórios privados
ALTER TABLE git_deployments
  ADD COLUMN IF NOT EXISTS auth_token_enc VARCHAR(500) NULL AFTER repo_url,
  ADD COLUMN IF NOT EXISTS deploy_key_public TEXT NULL AFTER auth_token_enc,
  ADD COLUMN IF NOT EXISTS deploy_key_private_enc TEXT NULL AFTER deploy_key_public,
  ADD COLUMN IF NOT EXISTS post_deploy_cmd VARCHAR(1000) NULL AFTER force_overwrite;
