ALTER TABLE clients ADD COLUMN managed_show_all_tabs TINYINT(1) NOT NULL DEFAULT 0 AFTER is_managed;
