<?php
// Security migration: rate limiting + IP block tables
require dirname(__DIR__) . '/includes/db.php';

$conn->query("CREATE TABLE IF NOT EXISTS rate_limits (
  ip VARCHAR(45) PRIMARY KEY,
  hits INT NOT NULL DEFAULT 1,
  window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS blocked_ips (
  ip VARCHAR(45) PRIMARY KEY,
  reason VARCHAR(120) NULL,
  until_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "security tables ready\n";
