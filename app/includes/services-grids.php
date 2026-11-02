<?php
// Shared "Our Services" grids (used on Home and Our Services pages)
require_once __DIR__ . '/icons.php';
$servicesData = json_decode(file_get_contents(__DIR__ . '/../content/our-services.json'), true);
?>
<?php foreach ($servicesData['sections'] as $gi => $grid): ?>
<section class="<?= $gi % 2 === 0 ? 'bg-mist' : 'bg-accent-soft' ?> py-16 relative overflow-hidden">
  <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-accent/10"></div>
  <div class="absolute -bottom-20 -left-20 w-72 h-72 rounded-full bg-accent/10"></div>
  <div class="max-w-7xl mx-auto px-4 relative">
    <p class="section-kicker text-center">What we do</p>
    <h2 class="section-title text-center mt-2 mb-12"><?= e($grid['heading']) ?></h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
      <?php foreach ($grid['items'] as $sci => $svc): ?>
      <a href="<?= e($svc['link']) ?>" class="card-mono group flex gap-6 items-start reveal reveal-delay-<?= $sci % 2 ?>">
        <span class="icon-badge group-hover:scale-110 transition-transform duration-300">
          <?= svg_icon(icon_for($svc['title'])) ?>
        </span>
        <span>
          <span class="block text-lg font-bold text-ink group-hover:text-accent-dark transition-colors"><?= e($svc['title']) ?></span>
          <span class="block w-10 h-0.5 bg-accent mt-2 group-hover:w-16 transition-all duration-300"></span>
          <span class="block mt-3 text-sm text-ink-soft leading-relaxed"><?= e($svc['description']) ?></span>
          <span class="inline-flex items-center gap-1 mt-3 text-accent-dark font-semibold text-sm">Learn more <span class="group-hover:translate-x-1 transition-transform">&rarr;</span></span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endforeach; ?>
