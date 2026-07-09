-- Simplifica ícones: usa identificadores curtos em vez de SVG no banco.
-- A view renderiza o SVG baseado no identificador.

UPDATE app_templates SET icon = 'icon-edit' WHERE slug = 'wordpress';
UPDATE app_templates SET icon = 'icon-hexagon' WHERE slug = 'nodejs';
UPDATE app_templates SET icon = 'icon-code' WHERE slug = 'php-laravel';
UPDATE app_templates SET icon = 'icon-database' WHERE slug = 'mysql';
UPDATE app_templates SET icon = 'icon-zap' WHERE slug = 'redis';
UPDATE app_templates SET icon = 'icon-globe' WHERE slug = 'nginx';
UPDATE app_templates SET icon = 'icon-file' WHERE slug = 'static-site';
UPDATE app_templates SET icon = 'icon-settings' WHERE slug = 'cpp';
UPDATE app_templates SET icon = 'icon-mail' WHERE slug = 'roundcube';
