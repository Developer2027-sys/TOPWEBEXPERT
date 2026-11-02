<?php
// Branches: multiple office locations with their own address/phone/email
require dirname(__DIR__) . '/includes/db.php';

$conn->query("CREATE TABLE IF NOT EXISTS branches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  address TEXT NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed the head office from existing settings (only once)
$count = (int)$conn->query("SELECT COUNT(*) c FROM branches")->fetch_assoc()['c'];
if ($count === 0) {
    $settings = [];
    $res = $conn->query("SELECT skey, svalue FROM settings");
    while ($r = $res->fetch_assoc()) $settings[$r['skey']] = $r['svalue'];
    $name = 'Head Office';
    $addr = $settings['address'] ?? '';
    $phone = $settings['phone'] ?? '';
    $email = $settings['email'] ?? '';
    $stmt = $conn->prepare("INSERT INTO branches (name, address, phone, email, is_primary, sort_order) VALUES (?,?,?,?,1,1)");
    $stmt->bind_param('ssss', $name, $addr, $phone, $email);
    $stmt->execute();
    echo "head office seeded from settings\n";
}
echo "branches table ready\n";
