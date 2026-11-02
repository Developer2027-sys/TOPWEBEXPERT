<?php
// Generic service page renderer. Expects $contentFile (e.g. 'seo.json') set by the page stub.
$data = json_decode(file_get_contents(__DIR__ . '/../content/' . $contentFile), true);
$pageTitle = $data['pageTitle'] ?? 'TopWebExpert';
$metaDescription = $data['metaDescription'] ?? '';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/seo.php';
$svcRoute = $data['route'] ?? '/';
$svcHeading = $data['sections'][0]['heading'] ?? $pageTitle;
$jsonLd = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $svcHeading,
        'provider' => ['@type' => 'Organization', 'name' => 'TopWebExpert'],
        'description' => $metaDescription,
    ],
    breadcrumb_schema($conn, ['Home' => '/', 'Our Services' => '/our-services', $svcHeading => $svcRoute]),
];
require __DIR__ . '/header.php';

// Flatten sections into: hero, rows (image+text), cta
$hero = null; $rows = []; $cta = null;
foreach ($data['sections'] as $s) {
    $type = $s['type'] ?? 'content';
    if ($type === 'hero' && !$hero) { $hero = $s; continue; }
    if ($type === 'cta') { $cta = $s; continue; }
    if (!empty($s['items'])) {
        foreach ($s['items'] as $it) {
            $rows[] = [
                'title' => $it['title'] ?? ($s['heading'] ?? ''),
                'text' => $it['description'] ?? '',
                'image' => $it['image'] ?? ($it['img'] ?? null),
                'button' => $it['button'] ?? null,
            ];
        }
    } elseif (!empty($s['heading']) || !empty($s['paragraphs'])) {
        $rows[] = [
            'title' => $s['heading'] ?? '',
            'text' => implode("\n\n", $s['paragraphs'] ?? []),
            'image' => $s['image'] ?? null,
            'button' => null,
        ];
    }
}
?>

<!-- Hero -->
<section class="hero-grad text-white py-20 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-white/10"></div>
  <div class="absolute -top-20 -left-20 w-64 h-64 rounded-full bg-white/10"></div>
  <div class="max-w-5xl mx-auto px-4 text-center relative">
    <p class="font-semibold uppercase tracking-widest text-sm text-white/80">TopWebExpert</p>
    <h1 class="text-4xl md:text-5xl font-bold mt-3 drop-shadow"><?= e($hero['heading'] ?? $data['pageTitle']) ?></h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
    <?php foreach (($hero['paragraphs'] ?? []) as $p): ?>
      <p class="mt-6 text-white/90 leading-relaxed max-w-3xl mx-auto"><?= e($p) ?></p>
    <?php endforeach; ?>
  </div>
</section>

<!-- Alternating rows -->
<?php foreach ($rows as $i => $row): $reverse = $i % 2 === 1; ?>
<section class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-accent-soft' ?> py-16 relative overflow-hidden">
  <?php if ($i % 2 === 1): ?><div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-accent/10"></div><?php endif; ?>
  <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center relative">
    <?php if (!empty($row['image'])): ?>
    <div class="<?= $reverse ? 'md:order-2' : '' ?> relative">
      <div class="absolute -inset-3 rounded-3xl bg-accent/15 <?= $reverse ? 'rotate-2' : '-rotate-2' ?>"></div>
      <?php $rowImg = '/assets/img/' . $row['image']; ?>
      <img src="<?= e($rowImg) ?>" alt="<?= e($row['title']) ?>" class="relative w-full h-auto img-card"<?= img_size_attrs($rowImg) ?> decoding="async" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
    </div>
    <?php endif; ?>
    <div class="<?= $reverse ? 'md:order-1' : '' ?> <?= empty($row['image']) ? 'md:col-span-2 max-w-3xl mx-auto text-center' : '' ?>">
      <?php if ($row['title']): ?>
        <h2 class="text-2xl md:text-3xl font-bold text-ink"><?= e($row['title']) ?></h2>
        <div class="w-16 h-1 bg-accent rounded-full mt-3 mb-5 <?= empty($row['image']) ? 'mx-auto' : '' ?>"></div>
      <?php endif; ?>
      <?php $kwTerms = [$row['title'], $svcHeading, 'TopWebExpert', 'digital marketing', 'Gurugram', 'SEO']; ?>
      <?php foreach (array_filter(preg_split('/\n+/', $row['text'])) as $p): ?>
        <p class="text-ink-soft leading-relaxed mb-4"><?= highlight_keywords(e($p), $kwTerms) ?></p>
      <?php endforeach; ?>
      <a href="/contact-us" class="btn-outline mt-2"><?= e($row['button'] ?? 'Contact Us') ?></a>
    </div>
  </div>
</section>
<?php endforeach; ?>

<!-- CTA banner -->
<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center relative">
    <h2 class="text-3xl md:text-4xl font-bold drop-shadow"><?= e($cta['heading'] ?? "Let's Talk About Your Goals") ?></h2>
    <?php if (!empty($cta['subheading'])): ?><p class="mt-3 text-white/90"><?= e($cta['subheading']) ?></p><?php endif; ?>
    <a href="/contact-us" class="btn-light mt-8">Contact Us</a>
  </div>
</section>

<?php require __DIR__ . '/footer.php'; ?>
