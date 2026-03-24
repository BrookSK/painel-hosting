ALTER TABLE client_domains
  ADD COLUMN webmail_app_id INT UNSIGNED NULL AFTER webmail_verified;
