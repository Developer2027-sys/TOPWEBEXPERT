<?php
// Tier plan (September 2026) — decides which city pages Google may index.
//
//   php app/scripts/migrate-tiers.php                      apply (uses data/gsc-pages-*.csv)
//   php app/scripts/migrate-tiers.php --check              only report the counts
//   php app/scripts/migrate-tiers.php more-pages.csv ...   also promote pages from newer
//                                                          Search Console "Pages" exports
//
// Tier 1 = every service in the core markets below (rewritten first, indexed)
// Tier 2 = pages Search Console proves: any click, or 20+ impressions at position 20 or better (indexed)
// Tier 3 = everything else: page stays live for visitors, "noindex, follow", not in the sitemap
//
// Only ever promotes (3 -> 2 -> 1), so tiers set by hand in Admin > City Pages are kept.
// Safe to re-run.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
set_time_limit(0);
require dirname(__DIR__) . '/includes/db.php';

// Core markets: head office region, biggest Indian markets, Pakistan cities that already
// get clicks, and the Australia branch.
const TIER1_CITIES = ['gurgaon', 'delhi', 'noida', 'faridabad', 'manesar', 'mumbai', 'bangalore',
                      'pune', 'hyderabad', 'karachi', 'lahore', 'melbourne'];
const TIER2_MIN_IMPRESSIONS = 20;
const TIER2_MAX_POSITION = 20;

$check = in_array('--check', $argv, true);
$csvFiles = array_merge(glob(__DIR__ . '/data/gsc-pages-*.csv') ?: [],
                        array_values(array_filter(array_slice($argv, 1), fn($a) => $a[0] !== '-')));

// 1. Column
if (!$conn->query("SHOW COLUMNS FROM city_pages LIKE 'tier'")->num_rows) {
    echo "Adding city_pages.tier (default 3)\n";
    if (!$check) {
        $conn->query("ALTER TABLE city_pages ADD COLUMN tier TINYINT NOT NULL DEFAULT 3, ADD INDEX idx_tier (tier)");
    }
}
$hasTier = (bool)$conn->query("SHOW COLUMNS FROM city_pages LIKE 'tier'")->num_rows;

// 2. Tier 1: all services in the core cities
$in = "'" . implode("','", TIER1_CITIES) . "'";
$t1 = (int)$conn->query("SELECT COUNT(*) FROM city_pages cp JOIN locations l ON l.id = cp.location_id WHERE l.slug IN ($in)")->fetch_row()[0];
echo "Tier 1: $t1 pages (" . count(TIER1_CITIES) . " cities x all services)\n";
if (!$check) {
    $conn->query("UPDATE city_pages cp JOIN locations l ON l.id = cp.location_id SET cp.tier = 1 WHERE l.slug IN ($in)");
}

// 3. Tier 2: pages proven in Search Console exports
$urlToId = [];
$res = $conn->query("SELECT cp.id, CONCAT(cp.service_slug, '-', l.slug) path FROM city_pages cp JOIN locations l ON l.id = cp.location_id");
while ($r = $res->fetch_assoc()) $urlToId[$r['path']] = (int)$r['id'];

$promote = [];
$other = 0;
foreach ($csvFiles as $file) {
    $fh = fopen($file, 'r');
    if (!$fh) { echo "Cannot read $file\n"; continue; }
    fgetcsv($fh); // header: Top pages, Clicks, Impressions, CTR, Position
    $n = 0;
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 5) continue;
        [$url, $clicks, $impr, , $pos] = $row;
        $proven = (int)$clicks > 0 || ((int)$impr >= TIER2_MIN_IMPRESSIONS && (float)$pos <= TIER2_MAX_POSITION);
        if (!$proven) continue;
        $host = parse_url($url, PHP_URL_HOST);
        $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
        if (!in_array($host, ['topwebexpert.com', 'www.topwebexpert.com'], true)) continue;
        if (isset($urlToId[$path])) { $promote[$urlToId[$path]] = true; $n++; }
        else $other++;
    }
    fclose($fh);
    echo "  " . basename($file) . ": $n proven city pages\n";
}
echo "Tier 2 candidates: " . count($promote) . " city pages ($other other proven URLs are static / blog / guide pages)\n";
if (!$check && $promote) {
    foreach (array_chunk(array_keys($promote), 1000) as $ids) {
        $conn->query("UPDATE city_pages SET tier = 2 WHERE tier > 2 AND id IN (" . implode(',', $ids) . ")");
    }
}

// 4. Summary
if ($hasTier) {
    $res = $conn->query("SELECT tier, COUNT(*) c FROM city_pages GROUP BY tier ORDER BY tier");
    while ($r = $res->fetch_assoc()) echo "  tier {$r['tier']}: {$r['c']}\n";
}
echo $check ? "Nothing changed (--check).\n" : "Done. Clear the sitemap cache (Admin > Dashboard > Clear Page Cache) and resubmit /sitemap.xml in Search Console.\n";
