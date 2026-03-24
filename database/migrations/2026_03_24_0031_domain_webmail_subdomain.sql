ALTER TABLE client_domains
  ADD COLUMN webmail_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN webmail_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER webmail_enabled;
