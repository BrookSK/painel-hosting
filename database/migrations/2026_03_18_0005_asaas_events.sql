CREATE TABLE IF NOT EXISTS asaas_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_id VARCHAR(190) NOT NULL,
  event_type VARCHAR(60) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_asaas_events_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
