<?php
require_once __DIR__ . '/includes/functions.php';
$data = json_decode(file_get_contents(__DIR__ . '/content/about-us.json'), true);
$pageTitle = $data['pageTitle'];
$metaDescription = $data['metaDescription'];
$hero = $data['sections'][0];
$stats = $data['sections'][1];
require __DIR__ . '/includes/header.php';
?>

<section class="hero-grad text-white py-20 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center">
    <p class="font-semibold uppercase tracking-widest text-sm text-white/80">TopWebExpert</p>
    <h1 class="text-4xl md:text-5xl font-bold mt-3"><?= e($hero['heading']) ?></h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
    <p class="mt-8 text-white/90 leading-relaxed"><?= e($hero['paragraphs'][0]) ?></p>
    <a href="/contact-us" class="btn-light mt-8">Contact Us</a>
  </div>
</section>

<section class="bg-mist py-16">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <h2 class="section-title"><?= e($stats['heading']) ?></h2>
    <p class="mt-3 text-ink-soft"><?= e($stats['subheading']) ?></p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-10">
      <?php foreach ($stats['items'] as $stat): ?>
      <div class="hero-grad p-10 rounded-3xl shadow-xl shadow-accent/25 hover:-translate-y-1.5 transition-transform duration-300">
        <p class="text-4xl md:text-5xl font-bold text-white drop-shadow"><?= e($stat['title']) ?></p>
        <p class="mt-2 text-white/90 text-sm uppercase tracking-wide"><?= e($stat['description']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
