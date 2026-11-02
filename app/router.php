<?php
// Front controller / router. Included by the web-root index.php (WEB_ROOT defined there;
// fall back for safety). App code lives here in /app; assets/libs are at the web root.
if (!defined('WEB_ROOT')) define('WEB_ROOT', dirname(__DIR__));

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($rawPath) || $rawPath === '') $rawPath = '/';
$path = rtrim($rawPath, '/');
if ($path === '') $path = '/';

// ---- Static files: long cache + optional WebP upgrade (served from web root) ----
$file = WEB_ROOT . $path;
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$staticTypes = [
    'css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'gif' => 'image/gif',
    'svg' => 'image/svg+xml', 'ico' => 'image/x-icon', 'woff' => 'font/woff', 'woff2' => 'font/woff2',
    'txt' => 'text/plain', 'xml' => 'application/xml',
];
if ($path !== '/' && isset($staticTypes[$ext]) && file_exists($file) && !is_dir($file)) {
    // Serve pre-generated .webp instead of png/jpg when the browser supports it
    if (in_array($ext, ['png', 'jpg', 'jpeg']) && strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'image/webp') !== false && file_exists($file . '.webp')) {
        $file .= '.webp';
        $ext = 'webp';
    }
    header('Content-Type: ' . $staticTypes[$ext]);
    header('Cache-Control: public, max-age=31536000, immutable');
    header('Vary: Accept');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    return true;
}

// ---- Security gate: manual IP blocks, scraper/probe refusal, security headers ----
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';
security_gate($conn, $path);

// ---- Admin panel ----
if ($path === '/admin' || strpos($path, '/admin/') === 0) {
    $adminRoutes = [
        '/admin' => 'index.php',
        '/admin/login' => 'login.php',
        '/admin/verify-2fa' => 'verify-2fa.php',
        '/admin/setup-2fa' => 'setup-2fa.php',
        '/admin/logout' => 'logout.php',
        '/admin/blogs' => 'blogs.php',
        '/admin/blog-edit' => 'blog-edit.php',
        '/admin/portfolio' => 'portfolio.php',
        '/admin/portfolio-edit' => 'portfolio-edit.php',
        '/admin/testimonials' => 'testimonials.php',
        '/admin/inquiries' => 'inquiries.php',
        '/admin/traffic' => 'traffic.php',
        '/admin/service-guides' => 'service-guides.php',
        '/admin/service-guide-edit' => 'service-guide-edit.php',
        '/admin/branches' => 'branches.php',
        '/admin/locations' => 'locations.php',
        '/admin/city-pages' => 'city-pages.php',
        '/admin/city-page-edit' => 'city-page-edit.php',
        '/admin/seo' => 'seo.php',
        '/admin/settings' => 'settings.php',
        '/admin/profile' => 'profile.php',
    ];
    if (isset($adminRoutes[$path])) {
        require __DIR__ . '/admin/' . $adminRoutes[$path];
        return true;
    }
    header('Location: /admin');
    return true;
}

// ---- Sitemap addresses from the old WordPress site (Yoast / core) → the real one.
//      Search Console still had them registered and reported every one as broken. ----
if (preg_match('#^/(sitemap_index\.xml|wp-sitemap(-[a-z0-9-]+)?\.xml|[a-z0-9-]+-sitemap\d*\.xml|sitemap\.xml\.gz|sitemap\.txt)$#', $path)) {
    header('Location: /sitemap.xml', true, 301);
    return true;
}

// ---- Sitemaps: index + parts (file-cached) ----
if (preg_match('#^/sitemap(?:-([a-z]+)(?:-(\d+))?)?\.xml$#', $path, $m)) {
    $_GET['part'] = $m[1] ?? 'index';
    $_GET['chunk'] = (int)($m[2] ?? 1);
    require WEB_ROOT . '/sitemap.php';
    return true;
}

// ---- URL hygiene: one canonical spelling per page ----
// Search engines had indexed the same page under several spellings (trailing slash,
// capital letters); each duplicate dilutes ranking and shows up as a separate URL in
// Search Console. Redirect them permanently to the single canonical form.
if (in_array($method, ['GET', 'HEAD'], true)) {
    $canon = strtolower($path);
    if ($canon !== $rawPath) {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . $canon . ($qs !== '' ? '?' . $qs : ''), true, 301);
        return true;
    }
}

// ---- Blog detail: /blog/{slug} — rendered live from the database ----
if (preg_match('#^/blog/([^/]+)$#', $path, $m)) {
    $_GET['slug'] = urldecode($m[1]);
    require __DIR__ . '/blog.php';
    return true;
}

// ---- Static page routes (rendered live, like every other page) ----
$routes = [
    '/' => 'home.php',
    '/enquiry-submit' => 'enquiry-submit.php',
    '/about-us' => 'about-us.php',
    '/our-services' => 'our-services.php',
    '/shopify-services' => 'shopify-services.php',
    '/our-portfolio' => 'our-portfolio.php',
    '/blogs' => 'blogs.php',
    '/contact-us' => 'contact-us.php',
    '/thank-you' => 'thank-you.php',
    '/terms-and-conditions' => 'terms-and-conditions.php',
    '/privacy-policy' => 'privacy-policy.php',
    '/web-development-company' => 'web-development-company.php',
    '/website-maintenance-services' => 'website-maintenance-services.php',
    '/ecommerce-website-development-company' => 'ecommerce-website-development-company.php',
    '/search-engine-optimization' => 'search-engine-optimization.php',
    '/social-media-optimization' => 'social-media-optimization.php',
    '/google-marketing' => 'google-marketing.php',
    '/facebook-marketing' => 'facebook-marketing.php',
    '/content-marketing' => 'content-marketing.php',
    '/pay-per-click' => 'pay-per-click.php',
    '/sms-marketing' => 'sms-marketing.php',
    '/whatsapp-marketing' => 'whatsapp-marketing.php',
    '/graphic-designing' => 'graphic-designing.php',
];
if (isset($routes[$path])) {
    require __DIR__ . '/' . $routes[$path];
    return true;
}

// ---- City guide articles: /{guide-slug}-{city-slug} (checked before service pages
//      so a guide slug can never be swallowed by a shorter service prefix) ----
if (preg_match('#^/([a-z0-9-]+)$#', $path, $gm)) {
    $gres = $conn->query("SELECT slug FROM service_blogs WHERE is_active = 1");
    if ($gres === false) {
        // 1146 = table missing (guides feature not migrated yet): simply no guides.
        // Anything else is a database failure: answer 503 so search engines retry
        // later. Falling through used to turn every guide URL into a hard 404 the
        // moment the database hiccupped, and Google then dropped those pages.
        if ((int)$conn->errno !== 1146) db_unavailable();
    } else {
        $guideSlugs = array_column($gres->fetch_all(MYSQLI_ASSOC), 'slug');
        usort($guideSlugs, fn($a, $b) => strlen($b) - strlen($a));
        foreach ($guideSlugs as $gs) {
            if (strpos($gm[1], $gs . '-') === 0) {
                $gCity = substr($gm[1], strlen($gs) + 1);
                $gstmt = $conn->prepare("SELECT 1 FROM locations WHERE slug = ? AND is_active = 1 LIMIT 1");
                if (!$gstmt) db_unavailable();
                $gstmt->bind_param('s', $gCity);
                $gstmt->execute();
                if ($gstmt->get_result()->fetch_row()) {
                    $_GET['guide_slug'] = $gs;
                    $_GET['city_slug'] = $gCity;
                    require __DIR__ . '/guide-page.php';
                    return true;
                }
            }
        }
    }
}

// ---- Service + city pages: /{service-slug}-{city-slug} (single hyphenated slug) ----
require_once __DIR__ . '/includes/services-config.php';
$serviceSlugs = array_keys(services_list());
// Longest slugs first so e.g. "search-engine-optimization-..." wins over shorter prefixes
usort($serviceSlugs, fn($a, $b) => strlen($b) - strlen($a));

if (preg_match('#^/([a-z0-9-]+)$#', $path, $m)) {
    foreach ($serviceSlugs as $svc) {
        if (strpos($m[1], $svc . '-') === 0) {
            $citySlug = substr($m[1], strlen($svc) + 1);
            // Old WordPress-era form /{service}-in-{city}: permanent redirect when that city exists
            if (strpos($citySlug, 'in-') === 0 && in_array($method, ['GET', 'HEAD'], true)) {
                $oldCity = substr($citySlug, 3);
                $stmt = $conn->prepare("SELECT 1 FROM locations WHERE slug = ? AND is_active = 1 LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('s', $oldCity);
                    $stmt->execute();
                    if ($stmt->get_result()->fetch_row()) {
                        header('Location: /' . $svc . '-' . $oldCity, true, 301);
                        return true;
                    }
                }
            }
            $_GET['service_slug'] = $svc;
            $_GET['city_slug'] = $citySlug;
            require __DIR__ . '/city-page.php';
            return true;
        }
    }
}

// ---- Old two-segment URLs: 301 redirect to the new hyphenated format ----
if (preg_match('#^/([a-z0-9-]+)/([a-z0-9-]+)$#', $path, $m) && in_array($m[1], $serviceSlugs, true)) {
    header('Location: /' . $m[1] . '-' . $m[2], true, 301);
    return true;
}

// ---- URLs left over from the previous WordPress site ----
// Google still holds these in its index and keeps re-requesting them; every one of
// them was a 404 in Search Console. Redirect the ones that have a new home and answer
// 410 Gone for the ones that do not, so the old URLs drop out of the index cleanly.
// (/{service}-in-{city} is redirected inside the service-page branch above.)
if (in_array($method, ['GET', 'HEAD'], true)) {
    // /{post-slug}  ->  /blog/{post-slug}   (WordPress served posts at the root)
    if (preg_match('#^/([a-z0-9-]+)$#', $path, $m)) {
        $stmt = $conn->prepare("SELECT slug FROM posts WHERE slug = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $m[1]);
            $stmt->execute();
            if ($stmt->get_result()->fetch_row()) {
                header('Location: /blog/' . $m[1], true, 301);
                return true;
            }
        }
    }
    // WordPress-only structures that have no equivalent here
    if (preg_match('#^/(tag|category|author|page|comments|feed|wp-content|wp-includes|wp-json)(/|$)|/feed$|\.html$#', $path)
        || (($_SERVER['QUERY_STRING'] ?? '') !== '' && preg_match('/(^|&)(p|page_id|attachment_id|cat|tag|s)=/', $_SERVER['QUERY_STRING']))) {
        http_response_code(410);
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo "Gone. This address belonged to the previous version of the website and no longer exists.\n";
        return true;
    }
}

// ---- 404 ----
http_response_code(404);
$pageTitle = 'Page Not Found - TopWebExpert';
$noindex = true;
require __DIR__ . '/includes/header.php';
echo '<section class="py-24 text-center"><h1 class="section-title">404 — Page not found</h1><p class="mt-4 text-ink-soft">The page you are looking for does not exist.</p><a href="/" class="btn-primary mt-8">Back to Home</a></section>';
require __DIR__ . '/includes/footer.php';
return true;
