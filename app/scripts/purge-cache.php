<?php
// Deletes every generated page/sitemap cache file under app/cache.
//
// Run on the server over SSH or as a one-off Hostinger cron job:
//   php /home/<account>/domains/topwebexpert.com/public_html/app/scripts/purge-cache.php
//
// Safe to run at any time and as often as you like: the cache is regenerated on
// demand. Needed once after deploying the September 2026 update, to remove the
// ~220,000 HTML files the previous caching scheme left behind (the new code only
// ever keeps a couple of dozen files here).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$root = dirname(__DIR__) . '/cache';
$total = 0;
foreach (['pages' => 'html', 'sitemaps' => 'xml'] as $sub => $ext) {
    $dir = "$root/$sub";
    if (!is_dir($dir)) continue;
    $n = 0;
    foreach (new DirectoryIterator($dir) as $f) {
        if ($f->isFile() && $f->getExtension() === $ext && @unlink($f->getPathname())) {
            $n++;
            if ($n % 10000 === 0) echo "  $sub: $n deleted...\n";
        }
    }
    echo "$sub: $n files deleted\n";
    $total += $n;
}
echo "done — $total files removed from $root\n";
