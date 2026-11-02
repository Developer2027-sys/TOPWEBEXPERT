<?php
// Sitemap index + parts, cached to disk in app/cache/sitemaps (SITEMAP_CACHE_TTL) —
// the only generated files the site keeps on disk; pages are never cached as files.
// /sitemap.xml           -> index of parts
// /sitemap-pages.xml     -> static routes
// /sitemap-blogs.xml     -> published blog posts
// /sitemap-cities-N.xml  -> Tier 1 + Tier 2 city pages, 10,000 per chunk
// Tier 3 city pages and all guide x city pages are noindex (Sept 2026 tier plan), so they
// are left out; old /sitemap-guides-N.xml URLs now answer 404.
require_once __DIR__ . '/app/includes/db.php';
require_once __DIR__ . '/app/includes/settings.php';
require_once __DIR__ . '/app/includes/cache.php';

header('Content-Type: application/xml; charset=utf-8');

$part = $_GET['part'] ?? 'index';
$chunk = max(1, (int)($_GET['chunk'] ?? 1));
$base = rtrim(site_settings($conn)['site_url'], '/');
$perChunk = 10000;

// Tier filter only once scripts/migrate-tiers.php has added the column
$tierSql = @$conn->query("SHOW COLUMNS FROM city_pages LIKE 'tier'")->num_rows ? ' AND cp.tier <= 2' : '';

$cacheDir = cache_root() . '/sitemaps';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
$cacheFile = $cacheDir . '/' . preg_replace('/[^a-z0-9-]/', '', $part . '-' . $chunk) . '.xml';

if (is_file($cacheFile) && time() - filemtime($cacheFile) < SITEMAP_CACHE_TTL) {
    readfile($cacheFile);
    exit;
}

function urlset($urls) {
    $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $out .= "  <url>\n    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
        if (!empty($u['lastmod'])) $out .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
        $out .= "    <changefreq>{$u['changefreq']}</changefreq>\n    <priority>{$u['priority']}</priority>\n  </url>\n";
    }
    return $out . '</urlset>';
}

if ($part === 'index') {
    $cityCount = (int)$conn->query("SELECT COUNT(*) c FROM city_pages cp JOIN locations l ON l.id = cp.location_id WHERE cp.is_active = 1 AND l.is_active = 1" . $tierSql)->fetch_assoc()['c'];
    $chunks = max(1, (int)ceil($cityCount / $perChunk));
    // Real modification dates per part. (Stamping "today" on every part on every
    // request told search engines the whole site changed daily, so they ignored it.)
    $today = date('Y-m-d');
    $lm = function ($sql) use ($conn, $today) {
        $r = @$conn->query($sql);
        $v = $r ? $r->fetch_row()[0] : null;
        return $v ? substr($v, 0, 10) : $today;
    };
    $lastmod = [
        'pages' => $today,
        'blogs' => $lm("SELECT MAX(date) FROM posts WHERE date <= CURDATE()"),
        'cities' => $lm("SELECT MAX(updated_at) FROM city_pages WHERE is_active = 1"),
    ];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $parts = ['/sitemap-pages.xml' => 'pages', '/sitemap-blogs.xml' => 'blogs'];
    for ($i = 1; $i <= $chunks; $i++) $parts["/sitemap-cities-{$i}.xml"] = 'cities';
    foreach ($parts as $p => $kind) {
        $xml .= "  <sitemap>\n    <loc>" . htmlspecialchars($base . $p, ENT_XML1) . "</loc>\n    <lastmod>{$lastmod[$kind]}</lastmod>\n  </sitemap>\n";
    }
    $xml .= '</sitemapindex>';
} elseif ($part === 'pages') {
    $staticRoutes = [
        '/' => '1.0', '/about-us' => '0.8', '/our-services' => '0.9', '/our-portfolio' => '0.7',
        '/blogs' => '0.8', '/contact-us' => '0.8',
        '/terms-and-conditions' => '0.3', '/privacy-policy' => '0.3',
        '/web-development-company' => '0.9', '/website-maintenance-services' => '0.9',
        '/ecommerce-website-development-company' => '0.9', '/search-engine-optimization' => '0.9',
        '/social-media-optimization' => '0.9', '/google-marketing' => '0.9', '/facebook-marketing' => '0.9',
        '/content-marketing' => '0.9', '/pay-per-click' => '0.9', '/sms-marketing' => '0.9',
        '/whatsapp-marketing' => '0.9', '/graphic-designing' => '0.9', '/shopify-services' => '0.9',
    ];
    $urls = [];
    foreach ($staticRoutes as $route => $priority) {
        $urls[] = ['loc' => $base . $route, 'priority' => $priority, 'changefreq' => 'weekly'];
    }
    $xml = urlset($urls);
} elseif ($part === 'blogs') {
    $urls = [];
    $res = $conn->query("SELECT slug, date FROM posts WHERE date <= CURDATE() ORDER BY date DESC");
    while ($r = $res->fetch_assoc()) {
        $urls[] = ['loc' => $base . '/blog/' . rawurlencode($r['slug']), 'lastmod' => $r['date'], 'priority' => '0.7', 'changefreq' => 'monthly'];
    }
    $xml = urlset($urls);
} elseif ($part === 'cities') {
    $offset = ($chunk - 1) * $perChunk;
    $urls = [];
    $stmt = $conn->prepare("SELECT cp.service_slug, l.slug AS city_slug, DATE(cp.updated_at) lastmod
                            FROM city_pages cp JOIN locations l ON l.id = cp.location_id
                            WHERE cp.is_active = 1 AND l.is_active = 1$tierSql ORDER BY cp.id LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $perChunk, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $urls[] = ['loc' => $base . '/' . $r['service_slug'] . '-' . $r['city_slug'], 'lastmod' => $r['lastmod'], 'priority' => '0.6', 'changefreq' => 'monthly'];
    }
    $xml = urlset($urls);
} else {
    http_response_code(404);
    exit;
}

@file_put_contents($cacheFile, $xml, LOCK_EX);
echo $xml;
