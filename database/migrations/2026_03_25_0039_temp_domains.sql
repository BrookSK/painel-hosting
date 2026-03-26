ALTER TABLE git_deployments ADD COLUMN temp_domain VARCHAR(253) NULL AFTER subdomain;
ALTER TABLE applications ADD COLUMN temp_domain VARCHAR(253) NULL;
