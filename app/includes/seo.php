<?php
// Renders full SEO head block: canonical, meta, Open Graph, Twitter, JSON-LD.
// Uses: $pageTitle, $metaDescription, $metaKeywords, $ogImage, $noindex, $jsonLd (array of extra schema blocks)
// Per-route overrides come from the page_seo table (managed in Admin > SEO Manager).

function seo_route_overrides($conn, $route) {
    $stmt = $conn->prepare("SELECT * FROM page_seo WHERE route = ? LIMIT 1");
    $stmt->bind_param('s', $route);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function render_seo($conn, $opts = []) {
    $s = site_settings($conn);
    $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $ovr = seo_route_overrides($conn, $route) ?: [];

    $title = ($ovr['meta_title'] ?? '') ?: ($opts['title'] ?? $s['site_name']);
    $desc = ($ovr['meta_description'] ?? '') ?: ($opts['description'] ?? '');
    $keys = ($ovr['meta_keywords'] ?? '') ?: ($opts['keywords'] ?? '');
    $noindex = !empty($ovr['noindex']) || !empty($opts['noindex']);
    // noindex => 'follow' hides the page but lets link value pass through (Tier 3 city / guide pages)
    $robotsFollow = empty($ovr['noindex']) && ($opts['noindex'] ?? null) === 'follow';
    $canonical = rtrim($s['site_url'], '/') . $route;
    $ogImage = $opts['og_image'] ?? ($ovr['og_image'] ?? '/assets/logo.png');
    if ($ogImage && $ogImage[0] === '/') $ogImage = rtrim($s['site_url'], '/') . $ogImage;

    echo '<title>' . e($title) . "</title>\n";
    if ($desc) echo '  <meta name="description" content="' . e($desc) . "\">\n";
    if ($keys) echo '  <meta name="keywords" content="' . e($keys) . "\">\n";
    echo '  <meta name="robots" content="' . ($noindex ? ($robotsFollow ? 'noindex, follow' : 'noindex, nofollow') : 'index, follow') . "\">\n";
    echo '  <link rel="canonical" href="' . e($canonical) . "\">\n";
    echo '  <meta name="author" content="' . e($s['site_name']) . "\">\n";

    // Open Graph
    echo '  <meta property="og:type" content="' . e($opts['og_type'] ?? 'website') . "\">\n";
    echo '  <meta property="og:site_name" content="' . e($s['site_name']) . "\">\n";
    echo '  <meta property="og:title" content="' . e($title) . "\">\n";
    if ($desc) echo '  <meta property="og:description" content="' . e($desc) . "\">\n";
    echo '  <meta property="og:url" content="' . e($canonical) . "\">\n";
    echo '  <meta property="og:image" content="' . e($ogImage) . "\">\n";

    // Twitter
    echo '  <meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '  <meta name="twitter:title" content="' . e($title) . "\">\n";
    if ($desc) echo '  <meta name="twitter:description" content="' . e($desc) . "\">\n";
    echo '  <meta name="twitter:image" content="' . e($ogImage) . "\">\n";

    // JSON-LD: Organization + WebSite on every page
    $base = rtrim($s['site_url'], '/');
    $branches = function_exists('site_branches') ? site_branches($conn) : [];
    $addresses = [];
    foreach ($branches as $br) {
        $addresses[] = ['@type' => 'PostalAddress', 'name' => $br['name'], 'streetAddress' => $br['address'], 'addressCountry' => 'IN'];
    }
    if (!$addresses) $addresses[] = ['@type' => 'PostalAddress', 'streetAddress' => $s['address'], 'addressCountry' => 'IN'];
    // The head office (primary branch) carries the structured city / state / PIN from
    // Admin > Settings, so it matches the Google Business Profile exactly.
    $addresses[0] += array_filter([
        'addressLocality' => $s['address_locality'] ?? '',
        'addressRegion' => $s['address_region'] ?? '',
        'postalCode' => $s['postal_code'] ?? '',
    ]);
    $pb = $branches[0] ?? ['phone' => $s['phone'], 'email' => $s['email']];
    $sameAs = array_values(array_filter([
        $s['facebook'], $s['instagram'], $s['linkedin'],
        $s['twitter'] ?? '', $s['youtube'] ?? '', $s['medium'] ?? '', $s['google_business_url'] ?? '',
    ]));
    $schemas = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $s['site_name'],
            'url' => $base,
            'logo' => $base . '/assets/logo.png',
            'email' => $pb['email'],
            'telephone' => $pb['phone'],
            'address' => count($addresses) === 1 ? $addresses[0] : $addresses,
            'sameAs' => $sameAs,
        ],
        // Head office as a local business: same name, address and phone as the Google Business Profile
        array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => $s['site_name'],
            'url' => $base,
            'image' => $base . '/assets/logo.png',
            'telephone' => $pb['phone'],
            'email' => $pb['email'],
            'address' => $addresses[0],
            'hasMap' => $s['google_business_url'] ?? '',
            'sameAs' => $sameAs,
        ]),
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $s['site_name'],
            'url' => $base,
        ],
    ];
    foreach (($opts['json_ld'] ?? []) as $extra) $schemas[] = $extra;
    foreach ($schemas as $schema) {
        echo '  <script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }
}

// Helper: BreadcrumbList schema
function breadcrumb_schema($conn, $items) {
    $s = site_settings($conn);
    $base = rtrim($s['site_url'], '/');
    $list = [];
    $pos = 1;
    foreach ($items as $name => $url) {
        $list[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $name, 'item' => $base . $url];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list];
}
