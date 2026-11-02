<?php
// Performance migration: indexes for hot queries + IP column for enquiry rate limiting
require dirname(__DIR__) . '/includes/db.php';

$statements = [
    "ALTER TABLE posts ADD INDEX IF NOT EXISTS idx_date (date)",
    "ALTER TABLE city_pages ADD INDEX IF NOT EXISTS idx_svc_active (service_slug, is_active)",
    "ALTER TABLE city_pages ADD INDEX IF NOT EXISTS idx_active (is_active)",
    "ALTER TABLE locations ADD INDEX IF NOT EXISTS idx_active (is_active)",
    "ALTER TABLE inquiries ADD COLUMN IF NOT EXISTS ip VARCHAR(45) NULL",
    "ALTER TABLE inquiries ADD INDEX IF NOT EXISTS idx_ip_time (ip, created_at)",
];
foreach ($statements as $sql) {
    if ($conn->query($sql)) {
        echo "OK: $sql\n";
    } else {
        echo "SKIP/ERR: $sql -> " . $conn->error . "\n";
    }
}
echo "done\n";
