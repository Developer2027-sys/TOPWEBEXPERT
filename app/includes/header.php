<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/seo.php';
if (!isset($conn)) require_once __DIR__ . '/db.php';

// Note: HTTP compression is handled by the web server (Apache mod_deflate — see .htaccess).
// PHP-level zlib.output_compression is intentionally NOT used here because it conflicts
// with the full-page cache and the server's own gzip, producing truncated responses.

$S = site_settings($conn);
$pageTitle = $pageTitle ?? ($S['site_name'] . ' - Best Website Development & Digital Marketing Agency in Gurugram');
$metaDescription = $metaDescription ?? 'TopWebExpert offers the best website development and digital marketing services in Gurugram. Enhance your online presence with our expert solutions.';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Tracking IDs from Admin > Site Settings (sanitized — only ID characters allowed)
$gtmId = preg_replace('/[^A-Za-z0-9_-]/', '', $S['gtm_id'] ?? '');
$gaId = preg_replace('/[^A-Za-z0-9_-]/', '', $S['ga_id'] ?? '');
$clarityId = preg_replace('/[^A-Za-z0-9]/', '', $S['clarity_id'] ?? '');
$menu = [
    ['name' => 'Home', 'path' => '/'],
    ['name' => 'About Us', 'path' => '/about-us'],
    ['name' => 'Our Services', 'path' => '/our-services'],
    ['name' => 'Portfolio', 'path' => '/our-portfolio'],
    ['name' => 'Blogs', 'path' => '/blogs'],
    ['name' => 'Contact Us', 'path' => '/contact-us'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php /* LCP hint: pages set $lcpPreload (see home.php) so the browser starts the
           hero image before it has parsed the CSS that would otherwise reveal it. */ ?>
  <?= $lcpPreload ?? '' ?>
  <link rel="stylesheet" href="<?= e(asset('/assets/css/style.css')) ?>">
  <?php render_seo($conn, [
      'title' => $pageTitle,
      'description' => $metaDescription,
      'keywords' => $metaKeywords ?? '',
      'og_image' => $ogImage ?? null,
      'og_type' => $ogType ?? 'website',
      'noindex' => $noindex ?? false,
      'json_ld' => $jsonLd ?? [],
  ]) ?>
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  <?php if (!empty($needsSwiper)): $swiperCss = e(asset('/libs/swiper-bundle.min.css')); ?>
  <?php /* Every slider on the site sits below the fold, so the Swiper stylesheet is
           loaded without blocking the first paint. */ ?>
  <link rel="stylesheet" href="<?= $swiperCss ?>" media="print" onload="this.media='all';this.onload=null">
  <noscript><link rel="stylesheet" href="<?= $swiperCss ?>"></noscript>
  <?php endif; ?>
  <?php if ($gtmId || $gaId || $clarityId): ?>
  <?php /* Analytics (GTM / GA4 / Clarity) are loaded on the first user interaction, or
           shortly after load for visitors who never interact. Same tags, same IDs — they
           just no longer compete with the page for main-thread time and bandwidth. */ ?>
  <script>
  (function (w, d) {
    var gtm = <?= json_encode($gtmId) ?>, ga = <?= json_encode($gaId) ?>, clarity = <?= json_encode($clarityId) ?>;
    var started = false;
    var events = ['pointerdown', 'touchstart', 'keydown', 'wheel', 'scroll'];

    function inject(src) {
      var s = d.createElement('script');
      s.async = true;
      s.src = src;
      d.head.appendChild(s);
    }

    function start() {
      if (started) return;
      started = true;
      events.forEach(function (ev) { w.removeEventListener(ev, start); });

      if (gtm) {
        w.dataLayer = w.dataLayer || [];
        w.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
        inject('https://www.googletagmanager.com/gtm.js?id=' + gtm);
      }
      if (ga) {
        w.dataLayer = w.dataLayer || [];
        w.gtag = function () { w.dataLayer.push(arguments); };
        w.gtag('js', new Date());
        w.gtag('config', ga);
        inject('https://www.googletagmanager.com/gtag/js?id=' + ga);
      }
      if (clarity) {
        w.clarity = w.clarity || function () { (w.clarity.q = w.clarity.q || []).push(arguments); };
        inject('https://www.clarity.ms/tag/' + clarity);
      }
    }

    events.forEach(function (ev) { w.addEventListener(ev, start, { passive: true, once: true }); });
    // Fallback so sessions without any interaction are still recorded.
    var idle = function () { setTimeout(start, 2500); };
    if (d.readyState === 'complete') idle(); else w.addEventListener('load', idle, { once: true });
  })(window, document);
  </script>
  <?php endif; ?>
</head>
<body class="bg-white text-ink"<?= !empty($needsSwiper) ? ' data-swiper-js="' . e(asset('/libs/swiper-bundle.min.js')) . '"' : '' ?>>
<?php if ($gtmId): ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= $gtmId ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php endif; ?>
<div id="scroll-progress"></div>

<!-- Top bar -->
<?php $PB = primary_branch($conn); ?>
<div class="hidden sm:block hero-grad text-white text-sm">
  <div class="max-w-7xl mx-auto px-4 py-2 flex justify-end gap-8">
    <span class="inline-flex items-center gap-2"><?= svg_icon('pin', 'w-4 h-4') ?><span class="font-semibold">Address:</span> <?= e($PB['address']) ?></span>
    <a href="tel:<?= e(preg_replace('/\D+/', '', $PB['phone'])) ?>" class="inline-flex items-center gap-2 hover:opacity-80 transition-opacity"><?= svg_icon('phone', 'w-4 h-4') ?><span class="font-semibold">Contact No:</span> <?= e($PB['phone']) ?></a>
  </div>
</div>

<!-- Navbar -->
<header class="bg-white border-b border-line sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <?php /* Intrinsic dimensions (696x220) rather than hand-written ones — the old
             144x43 pair did not match the file's aspect ratio, so the reserved box was
             the wrong shape and the header nudged on load. */ ?>
    <a href="/" class="shrink-0" aria-label="<?= e($S['site_name']) ?> home"><img src="/assets/logo.png" alt="<?= e($S['site_name']) ?> logo" class="w-36 h-auto"<?= img_size_attrs('/assets/logo.png') ?> fetchpriority="high" decoding="async"></a>

    <nav class="hidden lg:flex items-center gap-8" aria-label="Main navigation">
      <?php foreach ($menu as $item): $active = $currentPath === $item['path']; ?>
        <a href="<?= e($item['path']) ?>"
           class="font-semibold uppercase text-sm tracking-wide pb-1 border-b-2 transition-colors <?= $active ? 'border-accent text-ink' : 'border-transparent text-ink-soft hover:text-ink hover:border-accent' ?>">
          <?= e($item['name']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <a href="/contact-us" class="hidden lg:block btn-primary">Get in Touch</a>

    <!-- Mobile hamburger -->
    <button id="nav-toggle" class="lg:hidden p-2" aria-label="Open menu">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
  </div>

  <!-- Mobile slide-in menu -->
  <div id="mobile-menu" class="fixed inset-y-0 left-0 w-72 bg-white shadow-2xl z-50 -translate-x-full transition-transform duration-300 lg:hidden">
    <div class="p-4 flex justify-between items-center border-b border-line">
      <img src="/assets/logo.png" alt="<?= e($S['site_name']) ?> logo" class="w-28 h-auto"<?= img_size_attrs('/assets/logo.png') ?> decoding="async">
      <button id="nav-close" class="p-2" aria-label="Close menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <nav class="flex flex-col p-4 gap-4" aria-label="Mobile navigation">
      <?php foreach ($menu as $item): ?>
        <a href="<?= e($item['path']) ?>" class="font-semibold uppercase text-sm tracking-wide text-ink-soft hover:text-accent"><?= e($item['name']) ?></a>
      <?php endforeach; ?>
      <a href="/contact-us" class="btn-primary text-center mt-4">Get in Touch</a>
    </nav>
  </div>
</header>

<main>
