<?php
// Legal page renderer (terms/privacy). Expects $contentFile set by page stub.
require_once __DIR__ . '/functions.php';
$data = json_decode(file_get_contents(__DIR__ . '/../content/' . $contentFile), true);
$pageTitle = $data['pageTitle'] ?? 'TopWebExpert';
$metaDescription = $data['metaDescription'] ?? '';
$heading = '';
$intro = [];
$sections = [];
foreach ($data['sections'] as $s) {
    if (($s['type'] ?? '') === 'heading') {
        $heading = $s['heading'];
        $intro = $s['paragraphs'] ?? [];
        continue;
    }
    $sections[] = $s;
}
require __DIR__ . '/header.php';
?>

<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold"><?= e($heading) ?></h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
  </div>
</section>

<section class="bg-mist py-16">
  <div class="max-w-4xl mx-auto px-4 bg-white rounded-3xl shadow-lg border border-line/60 p-8 md:p-12">
    <?php foreach ($intro as $p): ?>
      <p class="text-ink-soft leading-relaxed mb-4"><?= e($p) ?></p>
    <?php endforeach; ?>
    <?php foreach ($sections as $s): ?>
      <?php if (!empty($s['heading'])): ?>
        <h2 class="text-2xl font-bold mt-8 first:mt-0">
          <span class="border-b-2 border-accent pb-1"><?= e($s['heading']) ?></span>
        </h2>
      <?php endif; ?>
      <?php foreach (($s['paragraphs'] ?? []) as $p): ?>
        <p class="mt-4 text-ink-soft leading-relaxed"><?= e($p) ?></p>
      <?php endforeach; ?>
      <?php if (!empty($s['list']) || !empty($s['items'])): ?>
        <ul class="mt-4 list-disc pl-6 text-ink-soft space-y-2">
          <?php foreach (($s['list'] ?? $s['items']) as $li): ?>
            <li><?= e(is_array($li) ? ($li['text'] ?? implode(' ', $li)) : $li) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/footer.php'; ?>
