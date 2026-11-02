<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$home = json_decode(file_get_contents(__DIR__ . '/content/home.json'), true);
$pageTitle = $home['pageTitle'];
$metaDescription = $home['metaDescription'];

// DB: testimonials + client logos
$testimonials = [];
$res = $conn->query("SELECT name, designation, message FROM testimonials");
while ($row = $res->fetch_assoc()) $testimonials[] = $row;

$logos = [];
$res = $conn->query("SELECT image FROM portfolio_items");
while ($row = $res->fetch_assoc()) $logos[] = $row['image'];

$sections = [];
foreach ($home['sections'] as $s) $sections[$s['type']][] = $s;
$hero = $sections['hero'][0];
$intro = $sections['content'][0];
$leading = $sections['content'][1];
$about = $sections['content+stats'][0];
$love = $sections['cards'][0];
$faq = $sections['faq'][0];

// FAQPage structured data for rich results
$faqSchema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faq['items'] as $qa) {
    $faqSchema['mainEntity'][] = [
        '@type' => 'Question',
        'name' => $qa['title'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa['description']],
    ];
}
$jsonLd = [$faqSchema];

// The hero banner is this page's LCP element. It is served as a real <img> with a
// WebP srcset (built by app/scripts/optimize-images.php) and preloaded in <head>, so
// the browser can start fetching it before the stylesheet has even been parsed.
$heroSrcset = implode(', ', array_map(
    fn($w) => asset("/assets/img/hero/banner-{$w}.webp") . " {$w}w",
    [480, 768, 1024, 1326]
));
$heroSizes = '100vw';
$needsSwiper = true;
$lcpPreload = '<link rel="preload" as="image" type="image/webp" fetchpriority="high"'
    . ' imagesrcset="' . e($heroSrcset) . '" imagesizes="' . e($heroSizes) . '">' . "\n";

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative min-h-[80vh] flex items-center">
  <picture class="contents">
    <source type="image/webp" srcset="<?= e($heroSrcset) ?>" sizes="<?= e($heroSizes) ?>">
    <img src="/assets/img/banner.png" alt="Digital marketing and website development team at TopWebExpert, Gurugram"
         width="1326" height="610" fetchpriority="high" decoding="async"
         class="absolute inset-0 w-full h-full object-cover">
  </picture>
  <div class="absolute inset-0 bg-gradient-to-r from-ink/90 via-ink/65 to-ink/20"></div>
  <div class="blob w-72 h-72 bg-accent/20 -top-20 right-10 blur-2xl"></div>
  <div class="blob blob-slow w-48 h-48 bg-accent/25 bottom-10 right-1/3 blur-xl"></div>
  <div class="relative max-w-7xl mx-auto px-4 py-24 w-full">
    <span class="inline-block rounded-full bg-accent/20 border border-accent/50 text-accent px-4 py-1 text-sm font-semibold backdrop-blur-sm">Best Digital Marketing Agency in Gurugram</span>
    <h1 class="mt-6 text-5xl md:text-7xl font-bold text-white leading-tight">
      <span class="text-accent">Digital</span><br>Marketing
    </h1>
    <p class="mt-6 max-w-xl text-gray-200 text-lg leading-relaxed"><?= e($hero['paragraphs'][0]) ?></p>
    <div class="mt-8 flex flex-wrap gap-4">
      <a href="/contact-us" class="btn-primary">Contact Us</a>
      <a href="/our-services" class="btn-light">Our Services</a>
    </div>
  </div>
</section>

<!-- Intro -->
<section class="bg-white py-16">
  <div class="max-w-4xl mx-auto px-4 text-center">
    <p class="section-kicker"><?= e($intro['subheading']) ?></p>
    <h2 class="section-title mt-2"><?= e($intro['heading']) ?></h2>
    <p class="mt-6 text-ink-soft leading-relaxed"><?= highlight_keywords(e($intro['paragraphs'][0]), ['Digital marketing agency in Gurugram', 'SEO services', 'PPC services', 'web marketing', 'Internet Marketing']) ?></p>
    <div class="mt-10 hero-grad text-white p-8 md:p-10 text-left rounded-3xl shadow-2xl shadow-accent/30 relative overflow-hidden">
      <div class="absolute inset-0 hero-dots opacity-40"></div>
      <div class="relative">
        <h3 class="text-2xl font-bold"><?= e($intro['items'][0]['title']) ?></h3>
        <div class="w-16 h-1 bg-white/70 rounded-full mt-3"></div>
        <p class="mt-4 text-white/90 leading-relaxed"><?= e($intro['items'][0]['description']) ?></p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/services-grids.php'; ?>

<!-- India's Leading -->
<section class="bg-white py-16">
  <div class="max-w-4xl mx-auto px-4 text-center">
    <h2 class="section-title"><?= e($leading['heading']) ?></h2>
    <p class="section-kicker mt-2"><?= e($leading['subheading']) ?></p>
    <?php foreach ($leading['paragraphs'] as $p): ?>
      <p class="mt-5 text-ink-soft leading-relaxed"><?= e($p) ?></p>
    <?php endforeach; ?>
    <a href="/contact-us" class="btn-primary mt-8">Contact Us</a>
  </div>
</section>

<!-- Industries we serve -->
<?php
$industries = [
    ['School & Institutions', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5l9.5 4.5L12 12.5 2.5 8 12 3.5z"/><path stroke-linecap="round" d="M6 10v5.5c0 1.4 2.7 2.8 6 2.8s6-1.4 6-2.8V10"/><path stroke-linecap="round" d="M21.5 8v5"/>'],
    ['Food & Beverage', '<path stroke-linecap="round" d="M3.5 17.5h17"/><path stroke-linecap="round" d="M5 17.5a7 7 0 0114 0"/><path stroke-linecap="round" d="M12 10.5V9"/><circle cx="12" cy="8" r="1"/><path stroke-linecap="round" d="M6.5 20.5h11"/>'],
    ['Government', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8.5 5.5h-17L12 3z"/><path stroke-linecap="round" d="M4.5 8.5V18M9.5 8.5V18M14.5 8.5V18M19.5 8.5V18M3 18h18M2.5 21h19"/>'],
    ['Automotive', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 16l1.8-5.5A1.5 1.5 0 017.2 9.5h9.6a1.5 1.5 0 011.4 1L20 16"/><rect x="3" y="15.5" width="18" height="4" rx="1.2"/><circle cx="7.5" cy="17.5" r="0.6" fill="currentColor"/><circle cx="16.5" cy="17.5" r="0.6" fill="currentColor"/>'],
    ['Real Estate', '<rect x="5.5" y="4" width="9" height="16.5" rx="0.8"/><path stroke-linecap="round" d="M14.5 9.5h4v11M3.5 20.5h17"/><path stroke-linecap="round" d="M8.5 8h1.2M11 8h1.2M8.5 11.5h1.2M11 11.5h1.2M8.5 15h1.2M11 15h1.2"/>'],
    ['Healthcare', '<circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M12 8.2v7.6M8.2 12h7.6"/>'],
    ['E-Commerce & Retail', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h2l2.2 10.5a1.4 1.4 0 001.4 1.1h7.5a1.4 1.4 0 001.4-1l1.6-6.6H7"/><circle cx="10.2" cy="20" r="1.3"/><circle cx="16.6" cy="20" r="1.3"/>'],
    ['B2B Businesses', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12l4.5-4.5 4 2.5L15.5 6l2 1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 15.5l2.5 2 2.5-2 2.5 2 3-2.5"/><path stroke-linecap="round" d="M2.5 12l5.5 6M21.5 12l-5-5.5"/>'],
];
?>
<section class="bg-mist py-16">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <p class="section-kicker">Industries</p>
    <h2 class="section-title mt-2">Industries We Serve With <span class="text-accent-dark">Competency</span></h2>
    <p class="mt-5 max-w-3xl mx-auto text-ink-soft leading-relaxed">Deep research of each industry and specialised digital marketing knowledge help us boost your business with ease and build a strong lead over the competition in your market.</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 mt-10">
      <?php foreach ($industries as $ii => [$iname, $ipath]): ?>
      <div class="group bg-white rounded-2xl border border-line/70 p-6 sm:p-8 shadow-sm hover:shadow-2xl hover:shadow-accent/15 hover:-translate-y-1.5 hover:border-accent/40 transition-all duration-300 reveal reveal-delay-<?= $ii % 4 ?>">
        <span class="inline-flex w-16 h-16 items-center justify-center rounded-2xl bg-accent-soft text-accent-dark group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
          <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><?= $ipath ?></svg>
        </span>
        <h3 class="mt-4 font-bold text-sm sm:text-base group-hover:text-accent-dark transition-colors"><?= e($iname) ?></h3>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- About + stats -->
<section class="bg-mist py-16">
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
    <div>
      <p class="section-kicker">Who we are</p>
      <h2 class="section-title mt-2"><?= e($about['heading']) ?></h2>
      <?php foreach ($about['paragraphs'] as $p): ?>
        <p class="mt-5 text-ink-soft leading-relaxed"><?= e($p) ?></p>
      <?php endforeach; ?>
    </div>
    <div class="grid grid-cols-2 gap-5">
      <?php foreach ($about['items'] as $si => $stat): ?>
      <div class="hero-grad text-center p-8 rounded-3xl shadow-xl shadow-accent/25 hover:-translate-y-1.5 transition-transform duration-300 reveal reveal-delay-<?= $si % 4 ?> <?= $si % 2 === 1 ? 'mt-6' : '' ?>">
        <p class="text-4xl md:text-5xl font-bold text-white drop-shadow" data-count="<?= e($stat['title']) ?>"><?= e($stat['title']) ?></p>
        <p class="mt-2 text-white/90 text-sm uppercase tracking-wide"><?= e($stat['description']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Why People Love Us -->
<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4">
    <h2 class="section-title text-center"><?= e($love['heading']) ?></h2>
    <div class="grid md:grid-cols-3 gap-6 mt-10">
      <?php foreach ($love['items'] as $li => $card): ?>
      <div class="card-mono text-center group reveal reveal-delay-<?= $li % 3 ?>">
        <div class="icon-badge mx-auto w-16 h-16 rounded-full group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
          <?= svg_icon(icon_for($card['title']), 'w-8 h-8') ?>
        </div>
        <h3 class="mt-5 text-lg font-bold group-hover:text-accent-dark transition-colors"><?= e($card['title']) ?></h3>
        <div class="w-10 h-0.5 bg-accent mx-auto mt-2 group-hover:w-16 transition-all duration-300"></div>
        <p class="mt-4 text-sm text-ink-soft leading-relaxed"><?= e($card['description']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="bg-mist py-16">
  <div class="max-w-7xl mx-auto px-4">
    <h2 class="section-title text-center"><?= e($faq['heading']) ?></h2>
    <div class="grid md:grid-cols-2 gap-4 mt-10">
      <?php foreach ($faq['items'] as $qa): ?>
      <div class="bg-white rounded-2xl shadow-sm border border-line/60 self-start overflow-hidden hover:shadow-lg hover:border-accent/40 transition-all duration-300">
        <button data-faq class="w-full flex justify-between items-center gap-4 p-5 text-left font-semibold hover:text-accent-dark transition-colors">
          <span><?= e($qa['title']) ?></span>
          <span class="faq-icon shrink-0 w-8 h-8 rounded-full bg-accent-soft text-accent-dark flex items-center justify-center text-xl leading-none font-bold">+</span>
        </button>
        <div class="hidden px-5 pb-5 text-sm text-ink-soft leading-relaxed border-t border-line/60 pt-4"><?= e($qa['description']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="bg-white py-16 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-center justify-between">
      <h2 class="section-title">What Our Clients Say</h2>
      <div class="flex gap-3">
        <button class="testi-prev w-11 h-11 rounded-full border-2 border-accent text-accent-dark hover:bg-accent hover:text-white transition-colors shadow-sm" aria-label="Previous">&larr;</button>
        <button class="testi-next w-11 h-11 rounded-full border-2 border-accent text-accent-dark hover:bg-accent hover:text-white transition-colors shadow-sm" aria-label="Next">&rarr;</button>
      </div>
    </div>
    <div class="swiper testimonials-swiper mt-10 !pt-8">
      <div class="swiper-wrapper">
        <?php foreach ($testimonials as $t): ?>
        <div class="swiper-slide h-auto">
          <div class="card-mono h-full flex flex-col relative pt-10">
            <span class="absolute -top-0 left-6 -translate-y-1/2 icon-badge w-12 h-12 rounded-full"><?= svg_icon('quote', 'w-6 h-6') ?></span>
            <span class="stars" aria-label="5 star rating">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            <p class="mt-2 text-sm text-ink-soft leading-relaxed grow italic"><?= e($t['message']) ?></p>
            <div class="mt-5 pt-4 border-t border-line/70 flex items-center gap-3">
              <span class="w-11 h-11 rounded-full bg-accent-soft text-accent-dark flex items-center justify-center font-bold text-lg"><?= e(mb_strtoupper(mb_substr($t['name'], 0, 1))) ?></span>
              <span>
                <span class="block font-bold text-ink"><?= e($t['name']) ?></span>
                <span class="block text-xs text-accent-dark font-semibold uppercase tracking-wide"><?= e($t['designation']) ?></span>
              </span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Brands we work with -->
<section class="bg-mist py-16 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4">
    <p class="section-kicker text-center">Our Clients</p>
    <h2 class="section-title mt-2 text-center">Brands We <span class="text-accent-dark">Work With</span></h2>
    <p class="mt-4 max-w-3xl mx-auto text-center text-ink-soft leading-relaxed">We help each brand and industry build and raise their brand image, credibility and authenticity from scratch.</p>
    <div class="swiper logos-swiper mt-10">
      <div class="swiper-wrapper items-center">
        <?php foreach ($logos as $i => $img): $logoSrc = '/assets/img/portfolio/' . $img; ?>
        <div class="swiper-slide flex items-center justify-center">
          <img src="<?= e($logoSrc) ?>" alt="Portfolio Item <?= $i + 1 ?>" class="max-h-20 w-auto object-contain img-mono"<?= img_size_attrs($logoSrc) ?> loading="lazy" decoding="async">
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Technologies we work with -->
<?php
$techLogos = [
    ['css', 'CSS3'], ['javascript', 'JavaScript'], ['php', 'PHP'], ['python', 'Python'],
    ['csharp', '.NET C#'], ['react', 'React'], ['angular', 'Angular'], ['vuejs', 'Vue.js'],
    ['go', 'Go'], ['nodejs', 'Node.js'], ['wordpress', 'WordPress'], ['flutter', 'Flutter'],
];
?>
<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <p class="section-kicker">Our Stack</p>
    <h2 class="section-title mt-2">Technologies We <span class="text-accent-dark">Work With</span></h2>
    <p class="mt-4 max-w-3xl mx-auto text-ink-soft leading-relaxed">Modern, battle-tested technologies power every website, app and campaign we deliver.</p>
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4 sm:gap-5 mt-10">
      <?php foreach ($techLogos as $ti => [$tfile, $tname]): ?>
      <div class="group flex flex-col items-center gap-3 rounded-2xl border border-line/60 bg-white p-5 sm:p-6 shadow-sm hover:shadow-xl hover:shadow-accent/10 hover:-translate-y-1 hover:border-accent/40 transition-all duration-300 reveal reveal-delay-<?= $ti % 4 ?>">
        <img src="<?= e(asset('/assets/img/tech/' . $tfile . '.svg')) ?>" alt="<?= e($tname) ?> logo" width="48" height="48"
             class="w-10 h-10 sm:w-12 sm:h-12 object-contain grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-300" loading="lazy">
        <span class="text-xs sm:text-sm font-semibold text-ink-soft group-hover:text-ink transition-colors"><?= e($tname) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
