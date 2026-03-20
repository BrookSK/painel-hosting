-- Migration: 2026_03_20_0004b
-- Corrige o INSERT com colunas inexistentes da migration 0004
-- A tabela settings só tem: key, value

INSERT IGNORE INTO settings (`key`, `value`)
VALUES ('app.force_https', '0');
