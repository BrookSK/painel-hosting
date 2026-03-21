-- Migration 0012: Tabela de erros do sistema
-- Rodar manualmente no banco MySQL

CREATE TABLE IF NOT EXISTS system_errors (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  http_code     SMALLINT     NOT NULL DEFAULT 500,
  error_type    VARCHAR(80)  NOT NULL DEFAULT 'exception',
  message       TEXT         NOT NULL,
  url           VARCHAR(1000) NOT NULL DEFAULT '',
  method        VARCHAR(10)  NOT NULL DEFAULT 'GET',
  ip_address    VARCHAR(45)  NULL,
  user_agent    VARCHAR(500) NULL,
  user_type     VARCHAR(20)  NULL COMMENT 'client|team|guest',
  user_id       INT UNSIGNED NULL,
  file          VARCHAR(500) NULL,
  line          INT UNSIGNED NULL,
  trace         TEXT         NULL,
  context_json  JSON         NULL,
  notified      TINYINT(1)   NOT NULL DEFAULT 0,
  resolved      TINYINT(1)   NOT NULL DEFAULT 0,
  resolved_by   INT UNSIGNED NULL,
  resolved_at   DATETIME     NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_http_code  (http_code),
  INDEX idx_created    (created_at),
  INDEX idx_resolved   (resolved),
  INDEX idx_error_type (error_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
