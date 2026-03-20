-- Migration 0008: Branding dinâmico e páginas legais
-- Inserir defaults na tabela settings

INSERT INTO settings (`key`, `value`) VALUES
  ('system.name',           'LRV Cloud Manager'),
  ('system.logo_url',       ''),
  ('system.favicon_url',    ''),
  ('system.company_name',   'LRV Cloud'),
  ('system.copyright_text', ''),
  ('legal.terms_html',      '<h2>Termos de Uso</h2><p>Edite este conteúdo no painel de configurações.</p>'),
  ('legal.privacy_html',    '<h2>Política de Privacidade</h2><p>Edite este conteúdo no painel de configurações.</p>')
ON DUPLICATE KEY UPDATE `key` = `key`;
