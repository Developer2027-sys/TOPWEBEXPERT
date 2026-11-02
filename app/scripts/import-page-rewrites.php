<?php
// Imports hand-reviewed rewrites of city pages (Tier plan, September 2026).
//
//   php app/scripts/import-page-rewrites.php                  import app/scripts/data/rewrites/*.json
//   php app/scripts/import-page-rewrites.php --check          only validate the files
//   php app/scripts/import-page-rewrites.php --sql out.sql    also write the UPDATEs as SQL
//                                                             (for phpMyAdmin > Import on the live site)
//
// Each file: {"service_slug", "city_slug", "title", "meta_title", "meta_description",
//             "meta_keywords", "content"}. The page is set to Tier 1 (indexed). Safe to re-run.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/db.php';

$check = in_array('--check', $argv, true);
$sqlOut = null;
if (($i = array_search('--sql', $argv, true)) !== false) $sqlOut = $argv[$i + 1] ?? null;

$files = glob(__DIR__ . '/data/rewrites/*.json') ?: [];
if (!$files) { echo "No files in app/scripts/data/rewrites/\n"; exit(1); }
$hasTier = (bool)$conn->query("SHOW COLUMNS FROM city_pages LIKE 'tier'")->num_rows;

$find = $conn->prepare("SELECT cp.id FROM city_pages cp JOIN locations l ON l.id = cp.location_id
                        WHERE cp.service_slug = ? AND l.slug = ? LIMIT 1");
$update = $conn->prepare("UPDATE city_pages SET title=?, content=?, meta_title=?, meta_description=?, meta_keywords=?,
                          updated_at=NOW()" . ($hasTier ? ", tier=1" : "") . " WHERE id=?");
$sql = ["-- TopWebExpert page rewrites (" . date('Y-m-d H:i') . ")", "SET NAMES utf8mb4;"];
$ok = 0;
foreach ($files as $file) {
    $p = json_decode(file_get_contents($file), true);
    $name = basename($file);
    $missing = array_diff(['service_slug', 'city_slug', 'title', 'meta_title', 'meta_description', 'meta_keywords', 'content'],
                          array_keys($p ?: []));
    if (!$p || $missing) { echo "SKIP $name: invalid JSON or missing " . implode(', ', $missing) . "\n"; continue; }
    if (preg_match('/<h1|style=|<script/i', $p['content'])) { echo "SKIP $name: h1 / inline style / script in content\n"; continue; }
    $find->bind_param('ss', $p['service_slug'], $p['city_slug']);
    $find->execute();
    $row = $find->get_result()->fetch_assoc();
    if (!$row) { echo "SKIP $name: no city page /{$p['service_slug']}-{$p['city_slug']}\n"; continue; }
    $id = (int)$row['id'];
    $words = str_word_count(strip_tags($p['content']));
    echo ($check ? '[check] ' : '') . "#$id /{$p['service_slug']}-{$p['city_slug']} ($words words)\n";
    if (!$check) {
        $update->bind_param('sssssi', $p['title'], $p['content'], $p['meta_title'], $p['meta_description'], $p['meta_keywords'], $id);
        $update->execute();
    }
    $q = fn($v) => "'" . $conn->real_escape_string($v) . "'";
    $sql[] = "UPDATE city_pages SET title=" . $q($p['title']) . ", content=" . $q($p['content'])
           . ", meta_title=" . $q($p['meta_title']) . ", meta_description=" . $q($p['meta_description'])
           . ", meta_keywords=" . $q($p['meta_keywords']) . ", updated_at=NOW(), tier=1"
           . " WHERE service_slug=" . $q($p['service_slug'])
           . " AND location_id=(SELECT id FROM locations WHERE slug=" . $q($p['city_slug']) . " LIMIT 1);";
    $ok++;
}
if ($sqlOut) {
    file_put_contents($sqlOut, implode("\n", $sql) . "\n");
    echo "SQL written to $sqlOut (run migrate-tiers.php on the live site first: it adds the tier column)\n";
}
echo "$ok page(s) " . ($check ? 'valid' : 'imported') . ".\n";
