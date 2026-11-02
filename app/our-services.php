<?php
require_once __DIR__ . '/includes/functions.php';
$data = json_decode(file_get_contents(__DIR__ . '/content/our-services.json'), true);
$pageTitle = $data['pageTitle'];
$metaDescription = $data['metaDescription'];
require __DIR__ . '/includes/header.php';
?>

<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center">
    <p class="font-semibold uppercase tracking-widest text-sm text-white/80">TopWebExpert</p>
    <h1 class="text-4xl md:text-5xl font-bold mt-3">Our Services</h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
  </div>
</section>

<?php require __DIR__ . '/includes/services-grids.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
