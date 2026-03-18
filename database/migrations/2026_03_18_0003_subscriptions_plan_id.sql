ALTER TABLE subscriptions
  ADD COLUMN plan_id INT UNSIGNED NULL AFTER vps_id;

ALTER TABLE subscriptions
  ADD CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans(id);
