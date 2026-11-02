<?php
$adminTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/admin-header.php';

if (($_POST['action'] ?? '') === 'clear-cache') {
    $r = page_cache_clear_status();
    flash($r['complete']
        ? "Cache cleared ({$r['deleted']} files). Fresh pages will be generated on next visit."
        : "Deleted {$r['deleted']} cached files in this run — more remain. Click \"Clear Page Cache\" again to continue (or run php app/scripts/purge-cache.php on the server).");
    header('Location: /admin');
    exit;
}

$counts = [];
foreach (['posts', 'portfolio_items', 'testimonials', 'inquiries', 'locations', 'city_pages'] as $t) {
    $counts[$t] = (int)$conn->query("SELECT COUNT(*) c FROM $t")->fetch_assoc()['c'];
}
$recent = $conn->query("SELECT name, email, phone, message, created_at FROM inquiries ORDER BY id DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

// Inquiries per day, last 14 days (for the trend chart)
$trend = array_fill(0, 14, 0);
$res = $conn->query("SELECT DATE(created_at) d, COUNT(*) c FROM inquiries WHERE created_at > (NOW() - INTERVAL 14 DAY) GROUP BY DATE(created_at)");
while ($r = $res->fetch_assoc()) {
    $idx = 13 - (int)floor((time() - strtotime($r['d'])) / 86400);
    if ($idx >= 0 && $idx < 14) $trend[$idx] = (int)$r['c'];
}
$maxTrend = max(1, max($trend));

$cards = [
    ['Blogs', $counts['posts'], '/admin/blogs', 'pen'],
    ['Portfolio Items', $counts['portfolio_items'], '/admin/portfolio', 'image'],
    ['Testimonials', $counts['testimonials'], '/admin/testimonials', 'quote'],
    ['Inquiries', $counts['inquiries'], '/admin/inquiries', 'mail'],
    ['Locations', $counts['locations'], '/admin/locations', 'pin'],
    ['City Pages', $counts['city_pages'], '/admin/city-pages', 'globe'],
];
// Traffic tables may not exist until Admin > Traffic is opened once
$hasTraffic = (bool)@$conn->query("SELECT 1 FROM traffic_minutes LIMIT 1");
?>

<!-- Content counts -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
  <?php foreach ($cards as [$label, $n, $href, $icon]): ?>
  <a href="<?= e($href) ?>" class="a-tile group">
    <span class="absolute -right-4 -bottom-4 text-neutral-100 group-hover:text-accent-soft transition-colors"><?= svg_icon($icon, 'w-20 h-20') ?></span>
    <span class="relative block">
      <span class="a-tile-icon"><?= svg_icon($icon, 'w-5 h-5') ?></span>
      <p class="text-3xl font-bold tracking-tight"><?= number_format($n) ?></p>
      <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-neutral-400"><?= e($label) ?></p>
    </span>
  </a>
  <?php endforeach; ?>
</div>

<!-- Live traffic -->
<div class="a-card mt-6 p-6">
  <div class="flex flex-wrap justify-between items-center gap-3 mb-5">
    <div>
      <h2 class="font-bold uppercase tracking-wide text-sm">Website Traffic &mdash; Live</h2>
      <p class="text-xs text-neutral-400 mt-0.5">Requests per minute, last hour &middot; auto-refreshes every 8 seconds</p>
    </div>
    <div class="flex items-center gap-3">
      <?php if ($hasTraffic): ?>
      <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-60"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-accent"></span></span>
      <span class="text-[10px] font-bold uppercase tracking-widest text-neutral-400">Live</span>
      <?php endif; ?>
      <a href="/admin/traffic" class="a-btn-ghost">Traffic &amp; IPs &rarr;</a>
    </div>
  </div>
  <?php if ($hasTraffic): ?>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
    <?php foreach ([['today', 'Requests Today'], ['visitors', 'Unique Visitors'], ['inquiries', 'Inquiries Today'], ['blocked_now', 'IPs Blocked']] as [$key, $label]): ?>
    <div class="rounded-xl bg-mist px-4 py-3">
      <p class="text-2xl font-bold tracking-tight" data-tile="<?= e($key) ?>">—</p>
      <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-neutral-400 mt-0.5"><?= e($label) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  <svg id="traffic-chart" viewBox="0 0 960 140" class="w-full h-32" preserveAspectRatio="none"></svg>
  <?php else: ?>
  <p class="text-sm text-neutral-400 py-6 text-center">Open <a href="/admin/traffic" class="font-semibold text-accent-deep hover:underline">Traffic &amp; IPs</a> once to activate visitor tracking — live data will appear here.</p>
  <?php endif; ?>
</div>

<!-- Inquiries trend (last 14 days) -->
<div class="a-card mt-6 p-6">
  <div class="flex justify-between items-center mb-4">
    <h2 class="font-bold uppercase tracking-wide text-sm">Inquiries &mdash; Last 14 Days</h2>
    <span class="text-xs text-neutral-400 uppercase tracking-widest"><?= array_sum($trend) ?> total</span>
  </div>
  <svg viewBox="0 0 560 120" class="w-full h-28" preserveAspectRatio="none" aria-label="Inquiries per day, last 14 days">
    <?php foreach ($trend as $i => $v):
        $barH = $v > 0 ? max(6, ($v / $maxTrend) * 100) : 3;
        $x = $i * 40 + 6;
        $y = 110 - $barH;
    ?>
      <rect x="<?= $x ?>" y="<?= $y ?>" width="28" height="<?= $barH ?>" rx="4"
            fill="<?= $v > 0 ? '#00c1ae' : '#ececec' ?>">
        <title><?= date('d M', strtotime('-' . (13 - $i) . ' days')) ?>: <?= $v ?> inquiries</title>
      </rect>
    <?php endforeach; ?>
  </svg>
  <div class="flex justify-between text-[10px] text-neutral-400 uppercase tracking-wide mt-1">
    <span><?= date('d M', strtotime('-13 days')) ?></span>
    <span>Today</span>
  </div>
</div>

<div class="mt-6 grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 a-card overflow-hidden">
    <div class="px-6 py-4 border-b border-neutral-100 flex justify-between items-center">
      <h2 class="font-bold uppercase tracking-wide text-sm">Recent Inquiries</h2>
      <a href="/admin/inquiries" class="text-xs uppercase tracking-widest text-accent-deep font-semibold hover:underline">View all &rarr;</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-[10px] uppercase tracking-[0.14em] text-neutral-400">
          <tr><th class="px-6 py-3">Name</th><th class="px-6 py-3">Contact</th><th class="px-6 py-3">Message</th><th class="px-6 py-3">Date</th></tr>
        </thead>
        <tbody class="divide-y divide-neutral-100">
          <?php foreach ($recent as $r): ?>
          <tr class="hover:bg-neutral-50/70">
            <td class="px-6 py-3 font-semibold"><?= e($r['name']) ?></td>
            <td class="px-6 py-3"><?= e($r['email']) ?><br><span class="text-neutral-400"><?= e($r['phone']) ?></span></td>
            <td class="px-6 py-3 text-neutral-500"><?= e(excerpt($r['message'], 60)) ?></td>
            <td class="px-6 py-3 text-neutral-400 whitespace-nowrap"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$recent): ?><tr><td colspan="4" class="px-6 py-8 text-center text-neutral-400">No inquiries yet</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="a-card overflow-hidden self-start">
    <div class="px-6 py-4 border-b border-neutral-100"><h2 class="font-bold uppercase tracking-wide text-sm">Quick Actions</h2></div>
    <div class="p-6 space-y-3">
      <a href="/admin/blog-edit" class="a-btn-dark w-full">+ New Blog Post</a>
      <a href="/admin/city-pages?action=generate" class="a-btn-dark w-full">+ Generate City Pages</a>
      <a href="/admin/portfolio-edit" class="a-btn-dark w-full">+ Add Portfolio Item</a>
      <a href="/admin/seo" class="a-btn-ghost w-full">Manage SEO</a>
      <a href="/sitemap.xml" target="_blank" class="a-btn-ghost w-full">View Sitemap &nearr;</a>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="clear-cache">
        <button class="a-btn-ghost w-full">Clear Page Cache</button>
      </form>
    </div>
  </div>
</div>

<?php if ($hasTraffic): ?>
<script>
(function () {
  var svg = document.getElementById('traffic-chart');
  function refresh() {
    fetch('/admin/traffic?json=1', {credentials: 'same-origin'}).then(function (r) { return r.json(); }).then(function (j) {
      Object.keys(j.tiles).forEach(function (k) {
        var el = document.querySelector('[data-tile="' + k + '"]');
        if (el) el.textContent = Number(j.tiles[k]).toLocaleString();
      });
      var series = j.minutes, W = 960, H = 140, n = series.length, bw = W / n, max = 1;
      series.forEach(function (d) { max = Math.max(max, d[1] + d[2]); });
      var out = '';
      series.forEach(function (d, i) {
        var hHits = d[1] / max * (H - 20);
        var hBlk = d[2] / max * (H - 20);
        var x = i * bw + 1.5, w = Math.max(1.5, bw - 3), y = H - 10 - hHits;
        if (d[1] > 0) out += '<rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + Math.max(2, hHits) + '" rx="1.5" fill="#00c1ae"><title>' + d[0] + ' — ' + d[1] + ' requests</title></rect>';
        if (d[2] > 0) out += '<rect x="' + x + '" y="' + (y - Math.max(2, hBlk)) + '" width="' + w + '" height="' + Math.max(2, hBlk) + '" rx="1.5" fill="#171717"><title>' + d[0] + ' — ' + d[2] + ' blocked</title></rect>';
        if (d[1] === 0 && d[2] === 0) out += '<rect x="' + x + '" y="' + (H - 12) + '" width="' + w + '" height="2" rx="1" fill="#ececec"/>';
      });
      svg.innerHTML = out;
    }).catch(function () {});
  }
  refresh();
  setInterval(refresh, 8000);
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
