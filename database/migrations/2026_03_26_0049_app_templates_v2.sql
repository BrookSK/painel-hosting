-- Atualizar todos os templates para não pedir repositório Git e ter campos amigáveis

-- Node.js: campos de projeto, sem repo
UPDATE app_templates SET
  requires_repo = 0,
  environment_variables = '{"NODE_PROJECT_NAME":"meu-app","NODE_VERSION":"18","APP_PORT":"3000"}',
  docker_command = NULL,
  description = 'Cria um projeto Node.js pronto para uso. Ideal para APIs e apps web.'
WHERE slug = 'nodejs';

-- PHP Laravel: campos de projeto, sem repo, com MySQL automático
UPDATE app_templates SET
  requires_repo = 0,
  environment_variables = '{"LARAVEL_PROJECT_NAME":"meu-projeto","PHP_VERSION":"8.2","DB_DATABASE":"__AUTO__","DB_USERNAME":"__AUTO__","DB_PASSWORD":"__AUTO__","DB_HOST":"__AUTO__"}',
  docker_command = NULL,
  description = 'Cria um projeto Laravel completo com MySQL automático.'
WHERE slug = 'php-laravel';

-- Redis: sem campos, só instalar
UPDATE app_templates SET
  requires_repo = 0,
  environment_variables = NULL,
  description = 'Cache e broker de mensagens em memória. Só clicar em instalar.'
WHERE slug = 'redis';

-- Nginx: sem campos, só instalar
UPDATE app_templates SET
  requires_repo = 0,
  requires_domain = 0,
  environment_variables = NULL,
  description = 'Servidor web de alta performance. Pronto para servir seus arquivos.'
WHERE slug = 'nginx';

-- Site Estático: campo de título, sem repo
UPDATE app_templates SET
  requires_repo = 0,
  environment_variables = '{"SITE_TITLE":"Meu Site"}',
  description = 'Hospede HTML/CSS/JS estático. Cria uma página inicial automaticamente.'
WHERE slug = 'static-site';

-- MySQL: campos de banco
UPDATE app_templates SET
  requires_repo = 0,
  environment_variables = '{"MYSQL_DATABASE":"meu_banco","MYSQL_ROOT_PASSWORD":""}',
  description = 'Banco de dados relacional MySQL 8. Defina o nome do banco e a senha root.'
WHERE slug = 'mysql';

-- Roundcube: sem campos visíveis (tudo auto)
UPDATE app_templates SET
  requires_repo = 0,
  requires_domain = 0,
  description = 'Webmail moderno. Conecta automaticamente ao servidor de e-mail do sistema.'
WHERE slug = 'roundcube';
