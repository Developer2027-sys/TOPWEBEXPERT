<?php
// Frontend renderer for service+city pages: /{service-slug}/{city-slug}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/services-config.php';

$serviceSlug = $_GET['service_slug'] ?? '';
$citySlug = $_GET['city_slug'] ?? '';

$stmt = $conn->prepare("SELECT cp.*, l.city, l.country, l.slug AS city_slug FROM city_pages cp
                        JOIN locations l ON l.id = cp.location_id
                        WHERE cp.service_slug = ? AND l.slug = ? AND cp.is_active = 1 AND l.is_active = 1 LIMIT 1");
$stmt->bind_param('ss', $serviceSlug, $citySlug);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

if (!$page) {
    http_response_code(404);
    $pageTitle = 'Page Not Found - TopWebExpert';
    $noindex = true;
    require __DIR__ . '/includes/header.php';
    echo '<section class="py-24 text-center"><h1 class="section-title">Page not found</h1><a href="/our-services" class="btn-primary mt-8">Our Services</a></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Nearby cities for the same service — ordered by real distance from the current
// city (locations.lat/lng, seeded via scripts/migrate-nearby.php). If the current
// city has no coordinates (or the migration has not run yet), fall back to
// same-country cities first so the list still matches the city.
$nearbyLimit = 12;
$otherCities = [];
$coords = null;
$res = @$conn->query("SELECT lat, lng FROM locations WHERE id = " . (int)$page['location_id']);
if ($res && ($r = $res->fetch_assoc()) && $r['lat'] !== null && $r['lng'] !== null) $coords = $r;

if ($coords) {
    $lat = (float)$coords['lat']; $lng = (float)$coords['lng'];
    $lngScale = cos(deg2rad($lat)); // longitude degrees shrink with latitude; scale so distances rank correctly
    $stmt = $conn->prepare("SELECT l.city, l.country, l.slug FROM city_pages cp JOIN locations l ON l.id = cp.location_id
                            WHERE cp.service_slug = ? AND cp.is_active = 1 AND l.is_active = 1 AND l.slug != ?
                              AND l.lat IS NOT NULL AND l.lng IS NOT NULL
                            ORDER BY POW(l.lat - ?, 2) + POW((l.lng - ?) * ?, 2) LIMIT ?");
    $stmt->bind_param('ssdddi', $serviceSlug, $citySlug, $lat, $lng, $lngScale, $nearbyLimit);
    $stmt->execute();
    $otherCities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if (count($otherCities) < $nearbyLimit) {
    // Fill up — same-country cities first, then the rest alphabetically
    $have = array_column($otherCities, 'slug');
    $have[] = $citySlug;
    $fetch = $nearbyLimit + count($have);
    $stmt = $conn->prepare("SELECT l.city, l.country, l.slug FROM city_pages cp JOIN locations l ON l.id = cp.location_id
                            WHERE cp.service_slug = ? AND cp.is_active = 1 AND l.is_active = 1
                            ORDER BY (l.country = ?) DESC, l.city LIMIT ?");
    $stmt->bind_param('ssi', $serviceSlug, $page['country'], $fetch);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
        if (in_array($r['slug'], $have, true)) continue;
        $otherCities[] = $r;
        if (count($otherCities) >= $nearbyLimit) break;
    }
}

// Tier plan: link the indexed pages to each other so Google finds and values them —
// the same service in the core markets, and (on core-market pages) the other services here.
$coreCities = [];
$cityServices = [];
if (isset($page['tier'])) {
    $stmt = $conn->prepare("SELECT l.city, l.slug FROM city_pages cp JOIN locations l ON l.id = cp.location_id
                            WHERE cp.service_slug = ? AND cp.tier = 1 AND cp.is_active = 1 AND l.is_active = 1 AND l.slug != ?
                            ORDER BY l.id LIMIT 12");
    $stmt->bind_param('ss', $serviceSlug, $citySlug);
    $stmt->execute();
    $coreCities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if ((int)$page['tier'] === 1 && !is_shopify_service($serviceSlug)) {
        $stmt = $conn->prepare("SELECT service_slug, service_name FROM city_pages
                                WHERE location_id = ? AND tier = 1 AND is_active = 1 AND service_slug != ? ORDER BY id");
        $stmt->bind_param('is', $page['location_id'], $serviceSlug);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            if (!is_shopify_service($r['service_slug'])) $cityServices[$r['service_slug']] = $r['service_name'];
        }
    }
}

// Guides for this service, rendered for this city (managed in Admin > City Guides)
$cityGuides = [];
$gstmt = @$conn->prepare("SELECT slug, title FROM service_blogs WHERE service_slug = ? AND is_active = 1 ORDER BY sort_order, id");
if ($gstmt) {
    $gstmt->bind_param('s', $serviceSlug);
    $gstmt->execute();
    $cityGuides = $gstmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Tier plan (Sept 2026): Tier 1 (core markets) and Tier 2 (proven in Search Console) are
// indexed; Tier 3 stays live for visitors but is hidden from Google. Set per page in
// Admin > City Pages, bulk via scripts/migrate-tiers.php.
if ((int)($page['tier'] ?? 1) >= 3) $noindex = 'follow';

$pageTitle = $page['meta_title'] ?: $page['title'] . ' | TopWebExpert';
$metaDescription = $page['meta_description'] ?: excerpt(strip_tags($page['content']), 158);
$metaKeywords = $page['meta_keywords'] ?? '';
$ogImage = $page['image'] ? '/assets/img/' . $page['image'] : null;
$servicePageUrl = service_hub_url($page['service_slug']);
// Sibling Shopify services for this city (cross-links the 35 Shopify services per city)
$siblingServices = [];
if (is_shopify_service($page['service_slug'])) {
    foreach (services_list() as $ss => [$sn, $si]) {
        if ($ss !== $page['service_slug'] && is_shopify_service($ss)) $siblingServices[$ss] = $sn;
    }
}
$pageUrl = '/' . $page['service_slug'] . '-' . $page['city_slug'];
$jsonLd = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $page['service_name'],
        'provider' => ['@type' => 'Organization', 'name' => 'TopWebExpert'],
        'areaServed' => ['@type' => 'City', 'name' => $page['city'], 'containedInPlace' => ['@type' => 'Country', 'name' => $page['country']]],
        'description' => $metaDescription,
    ],
    breadcrumb_schema($conn, [
        'Home' => '/',
        'Our Services' => '/our-services',
        $page['service_name'] => $servicePageUrl,
        $page['city'] => $pageUrl,
    ]),
];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-white/10"></div>
  <div class="max-w-5xl mx-auto px-4 text-center relative">
    <nav class="text-white/70 text-xs uppercase tracking-widest" aria-label="Breadcrumb">
      <a href="/" class="hover:text-white">Home</a> / <a href="<?= e($servicePageUrl) ?>" class="hover:text-white"><?= e($page['service_name']) ?></a> / <span class="text-white"><?= e($page['city']) ?></span>
    </nav>
    <h1 class="text-3xl md:text-5xl font-bold mt-5 drop-shadow"><?= e($page['title']) ?></h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
    <p class="mt-5 text-white/90"><?= e($page['service_name']) ?> for businesses in <?= e(location_label($page['city'], $page['country'])) ?></p>
    <a href="/contact-us" class="btn-light mt-8">Get Free Consultation</a>
  </div>
</section>

<!-- Trust band -->
<section class="bg-white border-b border-line">
  <div class="max-w-6xl mx-auto px-4 py-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
    <?php foreach ([['7+', 'Years Experience'], ['150+', 'Happy Clients'], ['75K', 'Leads Generated'], ['500+', 'Projects Delivered']] as [$n, $l]): ?>
    <div class="flex items-center justify-center gap-3">
      <span class="text-3xl font-bold text-accent-dark"><?= e($n) ?></span>
      <span class="text-xs uppercase tracking-wide text-ink-soft text-left leading-tight"><?= e($l) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Content + image -->
<section class="bg-white py-16">
  <div class="max-w-6xl mx-auto px-4 grid lg:grid-cols-3 gap-12">
    <article class="lg:col-span-2 prose-mono">
      <?= $page['content'] /* trusted HTML managed via admin panel */ ?>
      <a href="/contact-us" class="btn-primary mt-4">Contact Us Today</a>
    </article>
    <aside class="space-y-8 self-start">
      <?php if ($page['image']): ?>
      <div class="relative">
        <div class="absolute -inset-3 rounded-3xl bg-accent/15 rotate-2"></div>
        <?php $pageImg = '/assets/img/' . $page['image']; ?>
        <img src="<?= e($pageImg) ?>" alt="<?= e($page['title']) ?>" class="relative w-full h-auto img-card"<?= img_size_attrs($pageImg) ?> loading="lazy" decoding="async">
      </div>
      <?php endif; ?>
      <div class="bg-accent-soft rounded-2xl p-6">
        <h2 class="font-bold text-lg"><?= e($page['service_name']) ?> in nearby cities</h2>
        <ul class="mt-4 grid grid-cols-2 gap-2 text-sm">
          <?php foreach ($otherCities as $c): ?>
            <li><a href="/<?= e($page['service_slug']) ?>-<?= e($c['slug']) ?>" class="text-accent-dark hover:underline"><?= e($c['city']) ?></a></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= e($servicePageUrl) ?>" class="inline-block mt-4 text-sm font-semibold text-ink hover:text-accent-dark">About this service &rarr;</a>
      </div>
      <?php if ($coreCities): ?>
      <div class="bg-mist rounded-2xl p-6">
        <h2 class="font-bold text-lg"><?= e($page['service_name']) ?> in our main markets</h2>
        <ul class="mt-4 grid grid-cols-2 gap-2 text-sm">
          <?php foreach ($coreCities as $c): ?>
            <li><a href="/<?= e($page['service_slug']) ?>-<?= e($c['slug']) ?>" class="text-accent-dark hover:underline"><?= e($c['city']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <?php if ($cityServices): ?>
      <div class="bg-mist rounded-2xl p-6">
        <h2 class="font-bold text-lg">More services in <?= e($page['city']) ?></h2>
        <ul class="mt-4 space-y-1.5 text-sm">
          <?php foreach ($cityServices as $ss => $sn): ?>
            <li><a href="/<?= e($ss) ?>-<?= e($page['city_slug']) ?>" class="text-accent-dark hover:underline"><?= e($sn) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <?php if ($siblingServices): ?>
      <div class="bg-mist rounded-2xl p-6">
        <h2 class="font-bold text-lg">More Shopify services in <?= e($page['city']) ?></h2>
        <ul class="mt-4 space-y-1.5 text-sm">
          <?php foreach ($siblingServices as $ss => $sn): ?>
            <li><a href="/<?= e($ss) ?>-<?= e($page['city_slug']) ?>" class="text-accent-dark hover:underline"><?= e($sn) ?> in <?= e($page['city']) ?></a></li>
          <?php endforeach; ?>
        </ul>
        <a href="/shopify-services" class="inline-block mt-4 text-sm font-semibold text-ink hover:text-accent-dark">All Shopify services &rarr;</a>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</section>

<?php if ($cityGuides): ?>
<!-- Guides & resources for this city -->
<section class="bg-mist py-14">
  <div class="max-w-6xl mx-auto px-4">
    <p class="section-kicker">Free Resources</p>
    <h2 class="section-title mt-2 !text-2xl md:!text-3xl">Guides for <?= e($page['service_name']) ?> in <?= e($page['city']) ?></h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-8">
      <?php foreach ($cityGuides as $cg): $cgTitle = str_replace(['{city}', '{country}'], [$page['city'], $page['country']], $cg['title']); ?>
      <a href="/<?= e($cg['slug']) ?>-<?= e($page['city_slug']) ?>" class="group bg-white rounded-2xl border border-line/70 p-6 shadow-sm hover:shadow-xl hover:shadow-accent/10 hover:-translate-y-1 hover:border-accent/40 transition-all duration-300">
        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-accent-soft text-accent-dark">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5c-1.5-1.3-3.5-2-5.5-2H4v13h2.5c2 0 4 .7 5.5 2 1.5-1.3 3.5-2 5.5-2H20v-13h-2.5c-2 0-4 .7-5.5 2z"/><path stroke-linecap="round" d="M12 6.5v13"/></svg>
        </span>
        <h3 class="mt-4 font-bold leading-snug group-hover:text-accent-dark transition-colors"><?= e($cgTitle) ?></h3>
        <span class="inline-block mt-3 text-xs font-bold uppercase tracking-widest text-accent-dark">Read guide &rarr;</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center relative">
    <h2 class="text-3xl md:text-4xl font-bold drop-shadow">Grow Your Business in <?= e($page['city']) ?></h2>
    <p class="mt-3 text-white/90">Let's talk about your goals — free consultation, no obligation.</p>
    <a href="/contact-us" class="btn-light mt-8">Contact Us</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
