<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Our Portfolio - TopWebExpert';
$metaDescription = 'Explore the portfolio of TopWebExpert — companies we have created results for.';

$items = [];
$res = $conn->query("SELECT id, title, image FROM portfolio_items");
while ($row = $res->fetch_assoc()) $items[] = $row;

require __DIR__ . '/includes/header.php';
?>

<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center">
    <p class="font-semibold uppercase tracking-widest text-sm text-white/80">Our Work</p>
    <h1 class="text-4xl md:text-5xl font-bold mt-3">Our Portfolio</h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
  </div>
</section>

<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php foreach ($items as $i => $item): $logoSrc = '/assets/img/portfolio/' . $item['image']; ?>
    <div class="card-mono flex items-center justify-center p-8">
      <img src="<?= e($logoSrc) ?>" alt="<?= e($item['title']) ?>" class="max-h-24 w-auto object-contain img-mono"<?= img_size_attrs($logoSrc) ?> decoding="async" loading="<?= $i < 4 ? 'eager' : 'lazy' ?>">
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
