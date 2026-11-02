<?php
$adminTitle = 'SEO Manager';
$activeNav = 'seo';
require __DIR__ . '/includes/admin-header.php';

// All static site routes that can have SEO overrides
$routes = [
    '/', '/about-us', '/our-services', '/our-portfolio', '/blogs', '/contact-us',
    '/terms-and-conditions', '/privacy-policy',
    '/web-development-company', '/website-maintenance-services', '/ecommerce-website-development-company',
    '/search-engine-optimization', '/social-media-optimization', '/google-marketing', '/facebook-marketing',
    '/content-marketing', '/pay-per-click', '/sms-marketing', '/whatsapp-marketing', '/graphic-designing',
];

if (($_POST['action'] ?? '') === 'save') {
    $route = $_POST['route'] ?? '';
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $metaKeys = trim($_POST['meta_keywords'] ?? '');
    $noindex = isset($_POST['noindex']) ? 1 : 0;
    if (in_array($route, $routes, true)) {
        $stmt = $conn->prepare("INSERT INTO page_seo (route, meta_title, meta_description, meta_keywords, noindex) VALUES (?,?,?,?,?)
                                ON DUPLICATE KEY UPDATE meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), meta_keywords=VALUES(meta_keywords), noindex=VALUES(noindex)");
        $stmt->bind_param('ssssi', $route, $metaTitle, $metaDesc, $metaKeys, $noindex);
        $stmt->execute();
        flash('SEO saved for ' . $route);
    }
    header('Location: /admin/seo');
    exit;
}

$overrides = [];
$res = $conn->query("SELECT * FROM page_seo");
while ($r = $res->fetch_assoc()) $overrides[$r['route']] = $r;

$edit = $_GET['route'] ?? '';
$row = $overrides[$edit] ?? ['meta_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'noindex' => 0];
?>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1 bg-white border border-neutral-200 self-start">
    <div class="px-6 py-4 border-b border-neutral-200"><h2 class="font-bold uppercase tracking-wide text-sm">Pages</h2></div>
    <nav class="divide-y divide-neutral-100 max-h-[32rem] overflow-y-auto">
      <?php foreach ($routes as $r): $has = isset($overrides[$r]); ?>
      <a href="/admin/seo?route=<?= urlencode($r) ?>" class="flex justify-between items-center px-6 py-3 text-sm hover:bg-neutral-50 <?= $edit === $r ? 'bg-neutral-100 font-bold' : '' ?>">
        <span class="font-mono text-xs"><?= e($r) ?></span>
        <span class="text-[10px] font-bold uppercase tracking-widest <?= $has ? 'text-accent-dark' : 'text-neutral-300' ?>"><?= $has ? 'Custom' : 'Default' ?></span>
      </a>
      <?php endforeach; ?>
    </nav>
  </div>

  <div class="lg:col-span-2">
    <?php if ($edit): ?>
    <div class="bg-white border border-neutral-200 p-6">
      <h2 class="font-bold uppercase tracking-wide text-sm mb-1">SEO for <span class="font-mono text-accent-dark"><?= e($edit) ?></span></h2>
      <p class="text-xs text-neutral-500 mb-6">Leave a field empty to use the page's built-in default. Blog posts and city pages have their own SEO fields in their editors.</p>
      <form method="post" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="route" value="<?= e($edit) ?>">
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Meta Title <span class="normal-case text-neutral-400">(recommended 50-60 chars)</span></label>
          <input type="text" name="meta_title" value="<?= e($row['meta_title']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Meta Description <span class="normal-case text-neutral-400">(recommended 150-160 chars)</span></label>
          <textarea name="meta_description" rows="3" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent"><?= e($row['meta_description']) ?></textarea>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Meta Keywords</label>
          <textarea name="meta_keywords" rows="2" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent"><?= e($row['meta_keywords']) ?></textarea>
        </div>
        <label class="flex items-center gap-3 text-sm font-semibold">
          <input type="checkbox" name="noindex" <?= !empty($row['noindex']) ? 'checked' : '' ?> class="w-4 h-4 accent-[#00c1ae]"> Noindex (hide this page from Google)
        </label>
        <button class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Save SEO</button>
      </form>
    </div>
    <?php else: ?>
    <div class="bg-white border border-neutral-200 p-10 text-center text-neutral-400">
      <p class="font-bold uppercase tracking-widest text-sm">Select a page on the left to edit its SEO</p>
      <p class="mt-3 text-sm">Every page already ships with defaults: canonical URL, Open Graph, Twitter cards, JSON-LD structured data, sitemap entry.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
