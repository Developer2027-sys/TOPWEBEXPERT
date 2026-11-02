<?php
// Shopify services hub: /shopify-services
// The one page that links to every Shopify service (35) and, for each, its 12
// country-level pages. Without it the 50,000 Shopify city pages were reachable only
// through the sitemap — no crawl path from the homepage, no internal PageRank.
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/services-config.php';

$pageTitle = 'Shopify Development Agency & Shopify Developers Worldwide | TopWebExpert';
$metaDescription = 'Hire Shopify developers and a full-service Shopify agency: store design, theme and app development, Shopify Plus, migrations from WooCommerce, Magento, WordPress and BigCommerce, SEO, speed and CRO. Serving the USA, UK, Canada, Australia, UAE, India and 1,400+ cities.';
$metaKeywords = 'shopify agency, shopify developer, shopify development company, hire shopify developer, shopify expert, shopify plus agency, shopify migration, shopify web design';

$groups = [
    'Hire Shopify experts' => ['shopify-developer', 'shopify-agency', 'shopify-development-company', 'shopify-expert', 'dedicated-shopify-developer', 'outsource-shopify-development', 'white-label-shopify-development', 'shopify-liquid-developer'],
    'Build & design' => ['shopify-development', 'shopify-store-setup', 'shopify-web-design', 'shopify-redesign', 'shopify-theme-development', 'shopify-one-product-store-design', 'shopify-dropshipping-store-design', 'shopify-fashion-store-design'],
    'Apps, integrations & enterprise' => ['shopify-app-development', 'shopify-api-integration', 'shopify-erp-integration', 'shopify-checkout-customization', 'shopify-plus-agency', 'shopify-b2b-development', 'shopify-headless-development', 'shopify-multi-store-setup'],
    'Migrate to Shopify' => ['shopify-migration', 'woocommerce-to-shopify-migration', 'magento-to-shopify-migration', 'wordpress-to-shopify-migration', 'bigcommerce-to-shopify-migration', 'shopify-to-shopify-plus-migration'],
    'Grow & maintain' => ['shopify-seo-services', 'shopify-conversion-rate-optimization', 'shopify-speed-optimization', 'shopify-maintenance-services', 'shopify-store-audit'],
];
$services = services_list();
$templates = service_templates();
$countries = ['india' => 'India', 'usa' => 'USA', 'uk' => 'UK', 'canada' => 'Canada', 'australia' => 'Australia', 'uae' => 'UAE',
    'germany' => 'Germany', 'netherlands' => 'Netherlands', 'ireland' => 'Ireland', 'new-zealand' => 'New Zealand', 'south-africa' => 'South Africa', 'saudi-arabia' => 'Saudi Arabia'];
$topCities = ['noida' => 'Noida', 'delhi' => 'Delhi', 'mumbai' => 'Mumbai', 'bangalore' => 'Bangalore', 'london' => 'London', 'new-york' => 'New York',
    'dubai' => 'Dubai', 'sydney' => 'Sydney', 'toronto' => 'Toronto', 'singapore' => 'Singapore', 'los-angeles' => 'Los Angeles', 'manchester' => 'Manchester'];
$describe = function ($slug) use ($templates) {
    if (isset($templates[$slug])) return str_replace(['{place}', '{city}', '{country}'], ['your market', 'your city', 'your country'], $templates[$slug]['meta']);
    return 'Complete Shopify store development: strategy, design, theme, apps, payments and launch for growing brands.';
};
$hubItems = [];
foreach ($groups as $g => $slugs) foreach ($slugs as $s) $hubItems[] = ['@type' => 'ListItem', 'position' => count($hubItems) + 1, 'name' => $services[$s][0], 'url' => rtrim(site_settings($conn)['site_url'], '/') . "/$s-india"];
$jsonLd = [
    ['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'Shopify services by TopWebExpert', 'itemListElement' => $hubItems],
    breadcrumb_schema($conn, ['Home' => '/', 'Our Services' => '/our-services', 'Shopify Services' => '/shopify-services']),
];
require __DIR__ . '/includes/header.php';
?>

<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-white/10"></div>
  <div class="max-w-5xl mx-auto px-4 text-center relative">
    <nav class="text-white/70 text-xs uppercase tracking-widest" aria-label="Breadcrumb">
      <a href="/" class="hover:text-white">Home</a> / <a href="/our-services" class="hover:text-white">Our Services</a> / <span class="text-white">Shopify Services</span>
    </nav>
    <h1 class="text-3xl md:text-5xl font-bold mt-5 drop-shadow">Shopify Development Agency &amp; Shopify Developers</h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
    <p class="mt-5 text-white/90 max-w-3xl mx-auto">Design, development, apps, migrations and growth for Shopify and Shopify Plus stores. An India-based team working in your time zone, serving merchants in the USA, UK, Canada, Australia, the Gulf, Europe and 1,400+ cities.</p>
    <a href="/contact-us" class="btn-light mt-8">Get a Free Shopify Consultation</a>
  </div>
</section>

<section class="bg-white border-b border-line">
  <div class="max-w-6xl mx-auto px-4 py-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
    <?php foreach ([['35', 'Shopify Services'], ['12', 'Countries Served'], ['150+', 'Happy Clients'], ['500+', 'Projects Delivered']] as [$n, $l]): ?>
    <div class="flex items-center justify-center gap-3">
      <span class="text-3xl font-bold text-accent-dark"><?= e($n) ?></span>
      <span class="text-xs uppercase tracking-wide text-ink-soft text-left leading-tight"><?= e($l) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-white py-14">
  <div class="max-w-6xl mx-auto px-4 prose-mono">
    <h2>Why brands choose TopWebExpert for Shopify</h2>
    <p>Most Shopify projects fail on the basics: a theme that is slow, apps that fight each other, a migration that loses rankings, or a developer who disappears. TopWebExpert runs Shopify work the way an agency should - written scope, fixed quotes, staging themes, code review and a named project lead - at rates an offshore team can offer. Every service below is delivered by developers who work on Shopify every day, and each one is available in every city and country we serve.</p>
  </div>
</section>

<?php $gi = 0; foreach ($groups as $heading => $slugs): ?>
<section class="<?= $gi++ % 2 === 0 ? 'bg-mist' : 'bg-accent-soft' ?> py-14">
  <div class="max-w-7xl mx-auto px-4">
    <p class="section-kicker">Shopify services</p>
    <h2 class="section-title mt-2 mb-10 !text-2xl md:!text-3xl"><?= e($heading) ?></h2>
    <div class="grid md:grid-cols-2 gap-6">
      <?php foreach ($slugs as $slug): if (!isset($services[$slug])) continue; [$name, $img] = $services[$slug]; ?>
      <div class="bg-white rounded-2xl border border-line/70 p-6 shadow-sm">
        <h3 class="text-lg font-bold"><a href="/<?= e($slug) ?>-india" class="hover:text-accent-dark"><?= e($name) ?></a></h3>
        <p class="mt-2 text-sm text-ink-soft leading-relaxed"><?= e($describe($slug)) ?></p>
        <p class="mt-4 text-xs font-bold uppercase tracking-widest text-ink-soft">Available in</p>
        <p class="mt-1 text-sm leading-relaxed">
          <?php $i = 0; foreach ($countries as $cs => $cn): ?><?= $i++ ? ' &middot; ' : '' ?><a href="/<?= e($slug) ?>-<?= e($cs) ?>" class="text-accent-dark hover:underline"><?= e($cn) ?></a><?php endforeach; ?>
        </p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endforeach; ?>

<section class="bg-white py-14">
  <div class="max-w-6xl mx-auto px-4">
    <h2 class="section-title !text-2xl md:!text-3xl">Shopify developers in major cities</h2>
    <p class="mt-3 text-ink-soft">Local pages for the cities that send us the most Shopify work. Every service above is available in each of them and in 1,400 more.</p>
    <ul class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 text-sm">
      <?php foreach ($topCities as $cs => $cn): ?>
        <li><a href="/shopify-developer-<?= e($cs) ?>" class="text-accent-dark hover:underline">Shopify Developer in <?= e($cn) ?></a></li>
        <li><a href="/shopify-agency-<?= e($cs) ?>" class="text-accent-dark hover:underline">Shopify Agency in <?= e($cn) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center relative">
    <h2 class="text-3xl md:text-4xl font-bold drop-shadow">Ready to build or fix your Shopify store?</h2>
    <p class="mt-3 text-white/90">Free consultation, fixed quotes, no obligation.</p>
    <a href="/contact-us" class="btn-light mt-8">Contact Us</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
