<?php
// Official address update — Spaze I-Tech Park, Sector 49, Gurugram (23 September 2026).
//
//   php app/scripts/migrate-official-address.php          apply
//   php app/scripts/migrate-official-address.php --check  only show what would change
//
// Makes one name / address / phone everywhere (site, schema, Google Business Profile):
//  - adds the Gurugram head office as the primary branch
//  - turns off the old Noida and Sapphire 83 (Gurugram) branches — switch back on in Admin > Branches if needed
//  - fills Admin > Settings: address, city / state / PIN for schema, X (Twitter) URL
//  - replaces "Noida" with "Gurugram" in the site-wide texts: footer text and Admin > SEO Manager overrides
// Safe to re-run.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/db.php';

const OFFICE_NAME = 'Head Office - Gurugram';
const OFFICE_ADDRESS = 'Tower B1, Spaze I-Tech Park, 32B & 33A, Badshahpur Sohna Rd, Sector 49, Gurugram, Haryana 122018';
const OFFICE_PHONE = '+91 9050126213';
const OFFICE_EMAIL = 'info@topwebexpert.com';

$check = in_array('--check', $argv, true);
$say = fn($m) => print(($check ? '[check] ' : '') . $m . "\n");

// 1. Branches
$row = $conn->query("SELECT id FROM branches WHERE address LIKE '%Spaze%' LIMIT 1")->fetch_assoc();
$say($row ? "Head office branch exists (id {$row['id']}) — making it primary" : 'Adding head office branch: ' . OFFICE_ADDRESS);
$old = $conn->query("SELECT id, name, address FROM branches WHERE is_active = 1
                     AND (name = 'Noida' OR address LIKE '%Noida%' OR address LIKE '%Sapphire 83%')")->fetch_all(MYSQLI_ASSOC);
foreach ($old as $b) $say("Turning off old branch #{$b['id']} {$b['name']}: {$b['address']}");
if (!$check) {
    if ($row) {
        $id = (int)$row['id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO branches (name, address, phone, email, is_primary, is_active, sort_order) VALUES (?,?,?,?,0,1,0)");
        $name = OFFICE_NAME; $addr = OFFICE_ADDRESS; $phone = OFFICE_PHONE; $email = OFFICE_EMAIL;
        $stmt->bind_param('ssss', $name, $addr, $phone, $email);
        $stmt->execute();
        $id = $conn->insert_id;
    }
    $conn->query("UPDATE branches SET is_primary = 0");
    $conn->query("UPDATE branches SET is_primary = 1, is_active = 1, sort_order = 0 WHERE id = $id");
    foreach ($old as $b) $conn->query("UPDATE branches SET is_active = 0 WHERE id = " . (int)$b['id']);
}

// 2. Settings
$settings = [];
$res = $conn->query("SELECT skey, svalue FROM settings");
while ($r = $res->fetch_assoc()) $settings[$r['skey']] = $r['svalue'];
$set = [
    'address' => OFFICE_ADDRESS,
    'address_locality' => 'Gurugram',
    'address_region' => 'Haryana',
    'postal_code' => '122018',
    'footer_about' => str_replace('Noida', 'Gurugram', $settings['footer_about'] ?? ''),
];
if (empty($settings['twitter'])) $set['twitter'] = 'https://x.com/TopWebexpe62866';
$stmt = $conn->prepare("INSERT INTO settings (skey, svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)");
foreach ($set as $k => $v) {
    if (($settings[$k] ?? null) === $v) continue;
    $say("Setting $k = $v");
    if (!$check) { $stmt->bind_param('ss', $k, $v); $stmt->execute(); }
}

// 3. SEO Manager overrides (page titles / descriptions / keywords)
$rows = $conn->query("SELECT id, route FROM page_seo WHERE CONCAT_WS(' ', meta_title, meta_description, meta_keywords) LIKE '%noida%'")->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) $say("SEO override {$r['route']}: Noida -> Gurugram");
if (!$check && $rows) {
    foreach (['meta_title', 'meta_description', 'meta_keywords'] as $col) {
        $conn->query("UPDATE page_seo SET $col = REPLACE(REPLACE($col, 'Noida', 'Gurugram'), 'noida', 'gurugram') WHERE $col LIKE '%noida%'");
    }
}

$say($check ? 'Nothing changed (--check).' : 'Done. Now update the Google Business Profile, Facebook, Instagram, X, LinkedIn and IndiaMART to the same address.');
