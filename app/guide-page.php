<?php
// Frontend renderer for city guide articles: /{guide-slug}-{city-slug}
// A guide is a template (service_blogs row); placeholders {city}/{country}/{city_slug}
// are replaced per city. Guides render only for cities that have the matching
// service city-page, so every internal link resolves.
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/seo.php';

$guideSlug = $_GET['guide_slug'] ?? '';
$citySlug = $_GET['city_slug'] ?? '';

$stmt = $conn->prepare("SELECT sb.*, l.city, l.country, l.slug AS city_slug, cp.service_name
                        FROM service_blogs sb
                        JOIN locations l ON l.slug = ? AND l.is_active = 1
                        JOIN city_pages cp ON cp.service_slug = sb.service_slug AND cp.location_id = l.id AND cp.is_active = 1
                        WHERE sb.slug = ? AND sb.is_active = 1 LIMIT 1");
$stmt->bind_param('ss', $citySlug, $guideSlug);
$stmt->execute();
$g = $stmt->get_result()->fetch_assoc();

if (!$g) {
    http_response_code(404);
    $pageTitle = 'Page Not Found - TopWebExpert';
    $noindex = true;
    require __DIR__ . '/includes/header.php';
    echo '<section class="py-24 text-center"><h1 class="section-title">Page not found</h1><a href="/our-services" class="btn-primary mt-8">Our Services</a></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// "{city}, {country}" collapses to just the name on country-level pages (e.g. "Australia")
$fill = fn($s) => str_replace(['{city}, {country}', '{city}', '{country}', '{city_slug}'],
    [location_label($g['city'], $g['country']), $g['city'], $g['country'], $g['city_slug']], (string)$s);

// Tier plan (Sept 2026): every guide x city copy is Tier 3 — live for visitors, hidden from
// Google (one article repeated for 1,472 cities is what Google treats as doorway pages).
$noindex = 'follow';

$title = $fill($g['title']);
$content = $fill($g['content']);
$pageTitle = $fill($g['meta_title']) ?: $title . ' | TopWebExpert';
$metaDescription = $fill($g['meta_description']) ?: excerpt(strip_tags($content), 158);
$metaKeywords = $fill($g['meta_keywords'] ?? '');
$ogType = 'article';
$servicePageUrl = '/' . $g['service_slug'] . '-' . $g['city_slug'];
$guideUrl = '/' . $g['slug'] . '-' . $g['city_slug'];

// Other guides for the same service (rendered for this city)
$stmt = $conn->prepare("SELECT slug, title FROM service_blogs WHERE service_slug = ? AND slug != ? AND is_active = 1 ORDER BY sort_order, id LIMIT 8");
$stmt->bind_param('ss', $g['service_slug'], $g['slug']);
$stmt->execute();
$otherGuides = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Nearby cities for this same guide (coordinates-ranked, same as city pages)
$nearby = [];
$res = @$conn->query("SELECT lat, lng FROM locations WHERE slug = '" . $conn->real_escape_string($citySlug) . "' AND lat IS NOT NULL AND lng IS NOT NULL");
if ($res && ($c = $res->fetch_assoc())) {
    $lat = (float)$c['lat']; $lng = (float)$c['lng'];
    $lngScale = cos(deg2rad($lat));
    $stmt = $conn->prepare("SELECT l.city, l.slug FROM city_pages cp JOIN locations l ON l.id = cp.location_id
                            WHERE cp.service_slug = ? AND cp.is_active = 1 AND l.is_active = 1 AND l.slug != ?
                              AND l.lat IS NOT NULL AND l.lng IS NOT NULL
                            ORDER BY POW(l.lat - ?, 2) + POW((l.lng - ?) * ?, 2) LIMIT 10");
    $stmt->bind_param('ssddd', $g['service_slug'], $citySlug, $lat, $lng, $lngScale);
    $stmt->execute();
    $nearby = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$base = rtrim(site_settings($conn)['site_url'], '/');
$jsonLd = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => $metaDescription,
        'author' => ['@type' => 'Organization', 'name' => 'TopWebExpert', 'url' => $base],
        'publisher' => ['@type' => 'Organization', 'name' => 'TopWebExpert', 'logo' => ['@type' => 'ImageObject', 'url' => $base . '/assets/logo.png']],
        'dateModified' => date('Y-m-d', strtotime($g['updated_at'])),
        'mainEntityOfPage' => $base . $guideUrl,
    ],
    breadcrumb_schema($conn, [
        'Home' => '/',
        $g['service_name'] . ' in ' . $g['city'] => $servicePageUrl,
        $title => $guideUrl,
    ]),
];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero-grad text-white py-14 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-white/10"></div>
  <div class="max-w-4xl mx-auto px-4 relative">
    <nav class="text-white/70 text-xs uppercase tracking-widest" aria-label="Breadcrumb">
      <a href="/" class="hover:text-white">Home</a> /
      <a href="<?= e($servicePageUrl) ?>" class="hover:text-white"><?= e($g['service_name']) ?> in <?= e($g['city']) ?></a> /
      <span class="text-white">Guide</span>
    </nav>
    <h1 class="text-3xl md:text-4xl font-bold mt-5 drop-shadow leading-tight"><?= e($title) ?></h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mt-6"></div>
    <p class="mt-4 text-white/90 text-sm"><?= e($g['service_name']) ?> insights for businesses in <?= e(location_label($g['city'], $g['country'])) ?></p>
  </div>
</section>

<!-- Article -->
<section class="bg-white py-14">
  <div class="max-w-6xl mx-auto px-4 grid lg:grid-cols-3 gap-12">
    <article class="lg:col-span-2 prose-mono">
      <?= $content /* trusted HTML managed via Admin > City Guides */ ?>
    </article>
    <aside class="space-y-6 self-start lg:sticky lg:top-24">
      <div class="hero-grad text-white rounded-2xl p-6 relative overflow-hidden">
        <div class="absolute inset-0 hero-dots opacity-30"></div>
        <div class="relative">
          <h2 class="font-bold text-lg leading-snug"><?= e($g['service_name']) ?> in <?= e($g['city']) ?></h2>
          <p class="mt-2 text-white/90 text-sm">Get a free consultation and a custom plan for your business.</p>
          <a href="<?= e($servicePageUrl) ?>" class="btn-light mt-4 !px-6 !py-2.5 text-sm">View Service</a>
        </div>
      </div>
      <?php if ($otherGuides): ?>
      <div class="bg-accent-soft rounded-2xl p-6">
        <h2 class="font-bold">More guides for <?= e($g['city']) ?></h2>
        <ul class="mt-3 space-y-2.5 text-sm">
          <?php foreach ($otherGuides as $og): ?>
            <li><a href="/<?= e($og['slug']) ?>-<?= e($g['city_slug']) ?>" class="text-accent-dark hover:underline leading-snug inline-block"><?= e($fill($og['title'])) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <?php if ($nearby): ?>
      <div class="bg-mist rounded-2xl p-6">
        <h2 class="font-bold text-sm uppercase tracking-wide">This guide for nearby cities</h2>
        <ul class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <?php foreach ($nearby as $n): ?>
            <li><a href="/<?= e($g['slug']) ?>-<?= e($n['slug']) ?>" class="text-accent-dark hover:underline"><?= e($n['city']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</section>

<!-- CTA -->
<section class="hero-grad text-white py-14 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center relative">
    <h2 class="text-3xl font-bold drop-shadow">Ready to grow in <?= e($g['city']) ?>?</h2>
    <p class="mt-3 text-white/90">Talk to our <?= e($g['service_name']) ?> experts — free consultation, no obligation.</p>
    <a href="/contact-us" class="btn-light mt-7">Contact Us</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
