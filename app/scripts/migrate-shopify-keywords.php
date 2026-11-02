<?php
// Shopify keyword expansion — CLI runner (September 2026).
//
//   php app/scripts/migrate-shopify-keywords.php          run until complete
//   php app/scripts/migrate-shopify-keywords.php --check  only report what would be added
//
// Safe to re-run: every insert is INSERT IGNORE on a unique key. The same work can be
// done from Admin > City Pages > "Run Shopify keyword expansion" if SSH is unavailable.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
set_time_limit(0);
require dirname(__DIR__) . '/includes/db.php';
require dirname(__DIR__) . '/includes/functions.php';
require dirname(__DIR__) . '/includes/keyword-migration.php';

$problems = keyword_migration_check_slugs($conn);
if ($problems) {
    echo "Slug conflicts — fix before running:\n  " . implode("\n  ", $problems) . "\n";
    exit(1);
}

$countCp = fn() => (int)$conn->query("SELECT COUNT(*) FROM city_pages")->fetch_row()[0];
$countLoc = fn() => (int)$conn->query("SELECT COUNT(*) FROM locations")->fetch_row()[0];
$countSb = fn() => (int)$conn->query("SELECT COUNT(*) FROM service_blogs")->fetch_row()[0];
$before = [$countCp(), $countLoc(), $countSb()];
printf("Before: %d city pages, %d locations, %d guide templates\n", ...$before);

if (in_array('--check', $argv, true)) {
    $svcs = keyword_migration_services();
    $newLocs = count(keyword_migration_locations());
    printf("Would add: %d locations, %d guide templates, up to %d city pages (%d services x %d locations + %d services x %d new locations)\n",
        $newLocs, count(keyword_migration_guides()), count($svcs) * ($before[1] + $newLocs) + (count(services_list()) - count($svcs)) * $newLocs,
        count($svcs), $before[1] + $newLocs, count(services_list()) - count($svcs), $newLocs);
    exit;
}

$totals = ['locations_added' => 0, 'guides_added' => 0, 'pages_added' => 0];
$runs = 0;
do {
    $r = keyword_migration_run($conn, 55, fn($m) => print("  $m\n"));
    foreach ($totals as $k => $_) $totals[$k] += $r[$k];
    foreach ($r['errors'] as $e) echo "  ERROR: $e\n";
    $runs++;
    echo "run $runs: +{$r['pages_added']} pages (total so far {$totals['pages_added']})\n";
} while (!$r['complete'] && $runs < 2000);

$after = [$countCp(), $countLoc(), $countSb()];
printf("\nDone. Added %d locations, %d guide templates, %d city pages.\n", $totals['locations_added'], $totals['guides_added'], $totals['pages_added']);
printf("After:  %d city pages, %d locations, %d guide templates\n", ...$after);
$guideUrls = (int)$conn->query("SELECT COUNT(*) FROM service_blogs sb JOIN city_pages cp ON cp.service_slug = sb.service_slug AND cp.is_active = 1 JOIN locations l ON l.id = cp.location_id AND l.is_active = 1 WHERE sb.is_active = 1")->fetch_row()[0];
printf("Sitemap now lists %d city pages + %d guide pages.\n", $after[0], $guideUrls);
