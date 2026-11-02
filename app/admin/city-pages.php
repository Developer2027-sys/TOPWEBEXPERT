<?php
$adminTitle = 'City Pages';
$activeNav = 'city-pages';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/../includes/services-config.php';

$services = services_list();

// Bulk generate
if (($_POST['action'] ?? '') === 'generate') {
    $svcSlugs = $_POST['services'] ?? [];
    $locIds = $_POST['locations'] ?? [];
    if ($svcSlugs === ['__all__']) $svcSlugs = array_keys($services);
    if ($locIds === ['__all__']) {
        $locIds = array_column($conn->query("SELECT id FROM locations WHERE is_active = 1")->fetch_all(MYSQLI_ASSOC), 'id');
    }
    $made = 0; $skipped = 0;
    $stmt = $conn->prepare("INSERT IGNORE INTO city_pages (service_slug, service_name, location_id, title, content, image, meta_title, meta_description, meta_keywords) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($locIds as $locId) {
        $locId = (int)$locId;
        $loc = $conn->query("SELECT * FROM locations WHERE id = $locId")->fetch_assoc();
        if (!$loc) continue;
        foreach ($svcSlugs as $slug) {
            if (!isset($services[$slug])) continue;
            [$svcName, $img] = $services[$slug];
            $title = "$svcName in " . location_label($loc['city'], $loc['country']);
            $content = generate_city_page_content($svcName, $loc['city'], $loc['country'], $slug);
            [$metaTitle, $metaDesc, $metaKeys] = generate_city_page_meta($slug, $svcName, $loc['city'], $loc['country']);
            $stmt->bind_param('ssissssss', $slug, $svcName, $locId, $title, $content, $img, $metaTitle, $metaDesc, $metaKeys);
            $stmt->execute();
            $stmt->affected_rows ? $made++ : $skipped++;
        }
    }
    flash("Generated $made pages" . ($skipped ? " ($skipped already existed, skipped)" : '') . '.');
    header('Location: /admin/city-pages');
    exit;
}

// Shopify keyword expansion (locations + guides + city pages), 25 seconds per click, resumable
if (($_POST['action'] ?? '') === 'shopify-expansion') {
    require_once __DIR__ . '/../includes/keyword-migration.php';
    set_time_limit(60);
    $r = keyword_migration_run($conn, 25);
    $msg = "Shopify keyword expansion: +{$r['locations_added']} locations, +{$r['guides_added']} guides, +{$r['pages_added']} city pages this run"
        . ($r['pages_existing'] ? " ({$r['pages_existing']} already existed)" : '') . '. ';
    $msg .= $r['complete'] ? 'Complete.' : 'Not finished yet — continuing automatically (leave this tab open).';
    if ($r['errors']) $msg .= ' Errors: ' . implode('; ', array_slice($r['errors'], 0, 3));
    flash($msg);
    // While incomplete (and error-free) the page re-submits itself so the whole expansion
    // runs unattended in 25-second batches; any error stops the loop for a human to look.
    header('Location: /admin/city-pages?action=generate' . ((!$r['complete'] && !$r['errors']) ? '&expansion=running' : ''));
    exit;
}

if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM city_pages WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('City page deleted.');
    header('Location: /admin/city-pages');
    exit;
}
if (($_POST['action'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $conn->query("UPDATE city_pages SET is_active = 1 - is_active WHERE id = $id");
    header('Location: /admin/city-pages?' . http_build_query(array_intersect_key($_GET, ['q' => 1, 'svc' => 1, 'page' => 1])));
    exit;
}

$showGenerate = ($_GET['action'] ?? '') === 'generate';
// Only load the (large) locations list when the generate form is actually shown
$locations = $showGenerate
    ? $conn->query("SELECT id, city, country FROM locations WHERE is_active = 1 ORDER BY country, city")->fetch_all(MYSQLI_ASSOC)
    : [];

// Filters + pagination for the list
$q = trim($_GET['q'] ?? '');
$svc = trim($_GET['svc'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;

// A JOIN to locations is only needed when searching by city/country
$needJoin = ($q !== '');
$join = $needJoin ? 'JOIN locations l ON l.id = cp.location_id' : '';
$where = '1=1'; $params = []; $types = '';
if ($q !== '') { $where .= ' AND (l.city LIKE ? OR l.country LIKE ? OR cp.title LIKE ?)'; $like = "%$q%"; $params = [$like, $like, $like]; $types = 'sss'; }
if ($svc !== '') { $where .= ' AND cp.service_slug = ?'; $params[] = $svc; $types .= 's'; }

// Count: no join and no WHERE at all in the common (unfiltered) case = uses table stats, instant
if ($q === '' && $svc === '') {
    $total = (int)$conn->query("SELECT COUNT(*) c FROM city_pages")->fetch_assoc()['c'];
} else {
    $countSql = "SELECT COUNT(*) c FROM city_pages cp $join WHERE $where";
    $stmt = $conn->prepare($countSql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['c'];
}
$totalPages = max(1, (int)ceil($total / $limit));

// List: select ONLY the columns the table shows — never the heavy `content`/meta fields.
// For the unfiltered listing, STRAIGHT_JOIN forces MySQL to read city_pages first in
// primary-key order (LIMIT 30, no filesort) instead of scanning all 1460 locations and
// sorting 40k rows in a temp table — turns a ~3s query into a few milliseconds.
$straight = $needJoin ? 'JOIN' : 'STRAIGHT_JOIN';
$listSql = "SELECT cp.id, cp.service_slug, cp.service_name, cp.title, cp.is_active,
                   l.city, l.country, l.slug AS city_slug
            FROM city_pages cp
            $straight locations l ON l.id = cp.location_id
            WHERE $where ORDER BY cp.id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($listSql);
$types2 = $types . 'ii'; $params2 = array_merge($params, [$limit, $offset]);
$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($showGenerate): ?>
<div class="bg-white border border-neutral-200 p-6 mb-8">
  <h2 class="font-bold uppercase tracking-wide text-sm mb-2">Shopify Keyword Expansion</h2>
  <p class="text-sm text-neutral-500 mb-4">Adds the 12 country-level locations, 9 Shopify guide articles and the city pages for the 34 Shopify keyword services. Runs for 25 seconds per click and resumes where it stopped — keep clicking until it reports "Complete". (Over SSH: <code>php app/scripts/migrate-shopify-keywords.php</code>)</p>
  <form method="post" id="shopify-expansion-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="shopify-expansion">
    <button type="submit" class="btn-primary">Run Shopify keyword expansion</button>
    <?php if (($_GET['expansion'] ?? '') === 'running'): ?>
      <span class="ml-3 text-sm text-neutral-500">Running… next batch starts in a moment. Close this tab to stop.</span>
      <script>setTimeout(function () { document.getElementById('shopify-expansion-form').submit(); }, 1500);</script>
    <?php endif; ?>
  </form>
</div>
<div class="bg-white border border-neutral-200 p-6 mb-8">
  <h2 class="font-bold uppercase tracking-wide text-sm mb-2">Bulk Generate City Pages</h2>
  <p class="text-sm text-neutral-500 mb-5">Select services and cities — a fully SEO-ready page (title, content, meta tags, image) is created for every combination. Existing pages are never overwritten.</p>
  <form method="post" class="grid md:grid-cols-2 gap-6">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="generate">
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Services</label>
      <select name="services[]" multiple size="13" class="w-full border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent">
        <option value="__all__" selected>— ALL SERVICES —</option>
        <?php foreach ($services as $slug => [$name, $img]): ?>
          <option value="<?= e($slug) ?>"><?= e($name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Cities (<?= count($locations) ?> active)</label>
      <select name="locations[]" multiple size="13" class="w-full border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent">
        <option value="__all__" selected>— ALL ACTIVE CITIES —</option>
        <?php foreach ($locations as $l): ?>
          <option value="<?= $l['id'] ?>"><?= e($l['city'] . ' (' . $l['country'] . ')') ?></option>
        <?php endforeach; ?>
      </select>
      <p class="mt-2 text-xs text-neutral-400">Hold Ctrl to select specific items. Leave "ALL" selected for everything.</p>
    </div>
    <button class="md:col-span-2 py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Generate Pages Now</button>
  </form>
</div>
<?php endif; ?>

<div class="flex flex-wrap justify-between items-center gap-3 mb-6">
  <form method="get" class="flex flex-wrap gap-2 w-full lg:w-auto">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search city / title..." class="flex-1 min-w-[8rem] border border-neutral-300 px-4 py-2 text-sm focus:outline-none focus:border-accent">
    <select name="svc" class="border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent">
      <option value="">All services</option>
      <?php foreach ($services as $slug => [$name, $img]): ?>
        <option value="<?= e($slug) ?>" <?= $svc === $slug ? 'selected' : '' ?>><?= e($name) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="px-4 py-2 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 shrink-0">Filter</button>
  </form>
  <div class="flex gap-2">
    <span class="text-sm text-neutral-500 self-center"><?= $total ?> pages</span>
    <a href="/admin/city-pages?action=generate" class="px-5 py-2.5 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">+ Bulk Generate</a>
  </div>
</div>

<div class="bg-white border border-neutral-200 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-neutral-100 text-left text-xs uppercase tracking-widest text-neutral-500">
      <tr><th class="px-6 py-3">Page</th><th class="px-6 py-3">Service</th><th class="px-6 py-3">City</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-right">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-neutral-100">
      <?php foreach ($rows as $r): $url = '/' . $r['service_slug'] . '-' . $r['city_slug']; ?>
      <tr class="hover:bg-neutral-50">
        <td class="px-6 py-2.5 font-semibold max-w-sm"><?= e($r['title']) ?><br><span class="font-mono text-[11px] text-neutral-400 font-normal"><?= e($url) ?></span></td>
        <td class="px-6 py-2.5 text-neutral-600"><?= e($r['service_name']) ?></td>
        <td class="px-6 py-2.5 whitespace-nowrap"><?= e($r['city']) ?>, <span class="text-neutral-400"><?= e($r['country']) ?></span></td>
        <td class="px-6 py-2.5">
          <form method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="px-2 py-1 text-[10px] font-bold uppercase tracking-widest <?= $r['is_active'] ? 'bg-accent text-neutral-950' : 'bg-neutral-200 text-neutral-500' ?>"><?= $r['is_active'] ? 'Live' : 'Draft' ?></button>
          </form>
        </td>
        <td class="px-6 py-2.5 text-right whitespace-nowrap">
          <a href="<?= e($url) ?>" target="_blank" class="inline-block px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-accent hover:text-accent-dark">View</a>
          <a href="/admin/city-page-edit?id=<?= $r['id'] ?>" class="inline-block px-3 py-1.5 bg-neutral-950 text-white text-xs font-bold uppercase hover:bg-accent hover:text-neutral-950">Edit</a>
          <form method="post" class="inline" onsubmit="return confirm('Delete this page?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-red-500 hover:text-red-600">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="px-6 py-10 text-center text-neutral-400">No city pages yet — use Bulk Generate to create them.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<?php
  // Windowed pagination: 1 … (page-2 .. page+2) … last  + Prev/Next + jump-to-page
  $window = [];
  for ($i = 1; $i <= $totalPages; $i++) {
      if ($i === 1 || $i === $totalPages || abs($i - $page) <= 2) $window[] = $i;
  }
  $pageUrl = fn($p) => '/admin/city-pages?' . e(http_build_query(['q' => $q, 'svc' => $svc, 'page' => $p]));
?>
<div class="flex flex-wrap items-center gap-2 mt-6">
  <?php if ($page > 1): ?>
    <a href="<?= $pageUrl($page - 1) ?>" class="px-3 h-9 flex items-center text-xs font-bold border border-neutral-300 hover:border-accent">&larr; Prev</a>
  <?php endif; ?>
  <?php $prev = 0; foreach ($window as $i): ?>
    <?php if ($i - $prev > 1): ?><span class="px-1 text-neutral-400">&hellip;</span><?php endif; $prev = $i; ?>
    <a href="<?= $pageUrl($i) ?>" class="w-9 h-9 flex items-center justify-center text-xs font-bold border <?= $i === $page ? 'bg-neutral-950 text-accent border-neutral-950' : 'border-neutral-300 hover:border-accent' ?>"><?= $i ?></a>
  <?php endforeach; ?>
  <?php if ($page < $totalPages): ?>
    <a href="<?= $pageUrl($page + 1) ?>" class="px-3 h-9 flex items-center text-xs font-bold border border-neutral-300 hover:border-accent">Next &rarr;</a>
  <?php endif; ?>

  <form method="get" class="flex items-center gap-1 ml-2">
    <input type="hidden" name="q" value="<?= e($q) ?>"><input type="hidden" name="svc" value="<?= e($svc) ?>">
    <span class="text-xs text-neutral-500">Go to</span>
    <input type="number" name="page" min="1" max="<?= $totalPages ?>" value="<?= $page ?>" class="w-16 h-9 border border-neutral-300 px-2 text-xs text-center focus:outline-none focus:border-accent">
    <span class="text-xs text-neutral-500">/ <?= $totalPages ?></span>
    <button class="px-3 h-9 bg-neutral-950 text-white text-xs font-bold uppercase hover:bg-accent hover:text-neutral-950">Go</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
