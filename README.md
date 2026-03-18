# LRV Cloud Manager

Este projeto é o painel (SaaS) para gerenciar VPS em containers Docker, provisionamento automático, aplicações, monitoramento e cluster multi-servidor.

## Regras importantes

- O sistema não usa `.env`.
- O arquivo inicial do banco é `database/schema.sql`.
- Depois da primeira criação do banco, alterações devem ser feitas por migrations em `database/migrations`.
- O sistema deve ser isolado e não interferir no AAPanel e serviços existentes.

## Estrutura

- `public/` front controller
- `app/` módulos do sistema
- `core/` base do framework MVC
- `config/` configurações locais
- `database/` schema e migrations
- `routes/` rotas

## Instalação (desenvolvimento)

1. Instale as dependências:

   `composer install`

2. Crie `config/instalacao.php` com base em `config/instalacao.exemplo.php`.

3. Crie o banco e importe `database/schema.sql` manualmente.

4. Execute as migrations (após o schema inicial):

   `php migrar.php`

5. Acesse:

- Painel: `/`
- Saúde da API: `/api/saude`
