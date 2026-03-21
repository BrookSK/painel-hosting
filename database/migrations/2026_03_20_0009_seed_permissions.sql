-- Migration 0009: Seed completo de permissions e role_permissions
-- Insere todas as permissões usadas no sistema e as atribui aos roles corretos

-- ── 1. Permissões ────────────────────────────────────────────────────────────

INSERT IGNORE INTO permissions (`key`, description) VALUES
  ('manage_billing',  'Gerenciar cobrança, planos e assinaturas'),
  ('manage_vps',      'Gerenciar VPS, aplicações e backups'),
  ('manage_servers',  'Gerenciar servidores/nodes e monitoramento'),
  ('manage_users',    'Gerenciar usuários da equipe'),
  ('manage_terminal', 'Acessar terminal SSH'),
  ('view_tickets',    'Visualizar tickets de suporte'),
  ('reply_tickets',   'Responder tickets de suporte'),
  ('close_tickets',   'Fechar tickets de suporte');

-- ── 2. superadmin — todas as permissões ─────────────────────────────────────

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'superadmin', id FROM permissions
WHERE `key` IN (
  'manage_billing', 'manage_vps', 'manage_servers', 'manage_users',
  'manage_terminal', 'view_tickets', 'reply_tickets', 'close_tickets'
);

-- ── 3. admin — tudo exceto manage_users ─────────────────────────────────────

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions
WHERE `key` IN (
  'manage_billing', 'manage_vps', 'manage_servers',
  'manage_terminal', 'view_tickets', 'reply_tickets', 'close_tickets'
);

-- ── 4. devops — infra e terminal ─────────────────────────────────────────────

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'devops', id FROM permissions
WHERE `key` IN (
  'manage_vps', 'manage_servers', 'manage_terminal',
  'view_tickets', 'reply_tickets'
);

-- ── 5. support — tickets ─────────────────────────────────────────────────────

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'support', id FROM permissions
WHERE `key` IN (
  'view_tickets', 'reply_tickets', 'close_tickets'
);

-- ── 6. billing — cobrança ────────────────────────────────────────────────────

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'billing', id FROM permissions
WHERE `key` IN (
  'manage_billing', 'view_tickets'
);
