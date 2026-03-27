-- Atualizar template WordPress com campos amigáveis e MySQL automático
UPDATE app_templates SET
  environment_variables = '{"WORDPRESS_SITE_TITLE":"","WORDPRESS_ADMIN_USER":"admin","WORDPRESS_ADMIN_PASSWORD":"","WORDPRESS_ADMIN_EMAIL":"","WORDPRESS_TABLE_PREFIX":"wp_","WORDPRESS_DB_HOST":"__AUTO__","WORDPRESS_DB_USER":"__AUTO__","WORDPRESS_DB_PASSWORD":"__AUTO__","WORDPRESS_DB_NAME":"__AUTO__"}',
  docker_command = NULL
WHERE slug = 'wordpress';
