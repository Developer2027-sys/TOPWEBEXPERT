<?php
$adminTitle = 'Locations';
$activeNav = 'locations';
require __DIR__ . '/includes/admin-header.php';
require_once dirname(__DIR__) . '/includes/city-coords.php';

if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Location deleted (its city pages were removed too).');
    header('Location: /admin/locations');
    exit;
}
if (($_POST['action'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $conn->query("UPDATE locations SET is_active = 1 - is_active WHERE id = " . $id);
    header('Location: /admin/locations' . (isset($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
    exit;
}
if (($_POST['action'] ?? '') === 'coords') {
    $id = (int)($_POST['id'] ?? 0);
    $lat = trim($_POST['lat'] ?? '');
    $lng = trim($_POST['lng'] ?? '');
    $latV = is_numeric($lat) ? (float)$lat : null;
    $lngV = is_numeric($lng) ? (float)$lng : null;
    $stmt = $conn->prepare("UPDATE locations SET lat = ?, lng = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('ddi', $latV, $lngV, $id);
        $stmt->execute();
        flash('Coordinates updated — nearby city lists will use them.');
    } else {
        flash('Coordinates columns missing — run scripts/migrate-nearby.php first.');
    }
    header('Location: /admin/locations' . ($_GET ? '?' . http_build_query($_GET) : ''));
    exit;
}
if (($_POST['action'] ?? '') === 'add') {
    $country = trim($_POST['country'] ?? '');
    $city = trim($_POST['city'] ?? '');
    if ($country && $city) {
        $slug = slugify($city);
        $lat = trim($_POST['lat'] ?? '');
        $lng = trim($_POST['lng'] ?? '');
        // Auto-fill coordinates for well-known cities so nearby lists work without manual entry
        if (($lat === '' || $lng === '') && ($known = city_known_coords($slug))) [$lat, $lng] = $known;
        $latV = is_numeric($lat) ? (float)$lat : null;
        $lngV = is_numeric($lng) ? (float)$lng : null;
        $stmt = $conn->prepare("INSERT IGNORE INTO locations (country, city, slug, lat, lng) VALUES (?,?,?,?,?)");
        if ($stmt) {
            $stmt->bind_param('sssdd', $country, $city, $slug, $latV, $lngV);
        } else { // lat/lng columns not migrated yet
            $stmt = $conn->prepare("INSERT IGNORE INTO locations (country, city, slug) VALUES (?,?,?)");
            $stmt->bind_param('sss', $country, $city, $slug);
        }
        $stmt->execute();
        flash($stmt->affected_rows ? 'Location added.' : 'That city already exists.');
    }
    header('Location: /admin/locations');
    exit;
}

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM locations WHERE city LIKE ? OR country LIKE ?");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt = $conn->prepare("SELECT l.*, (SELECT COUNT(*) FROM city_pages cp WHERE cp.location_id = l.id) pages FROM locations l WHERE l.city LIKE ? OR l.country LIKE ? ORDER BY l.country, l.city LIMIT ? OFFSET ?");
    $stmt->bind_param('ssii', $like, $like, $limit, $offset);
} else {
    $total = (int)$conn->query("SELECT COUNT(*) c FROM locations")->fetch_assoc()['c'];
    $stmt = $conn->prepare("SELECT l.*, (SELECT COUNT(*) FROM city_pages cp WHERE cp.location_id = l.id) pages FROM locations l ORDER BY l.country, l.city LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$totalPages = max(1, (int)ceil($total / $limit));
?>

<div class="grid lg:grid-cols-4 gap-6">
  <div class="bg-white border border-neutral-200 p-6 self-start">
    <h2 class="font-bold uppercase tracking-wide text-sm mb-4">Add City</h2>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Country</label>
        <input type="text" name="country" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">City</label>
        <input type="text" name="city" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Latitude</label>
          <input type="text" name="lat" inputmode="decimal" placeholder="auto" class="w-full border border-neutral-300 px-3 py-3 focus:outline-none focus:border-accent">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Longitude</label>
          <input type="text" name="lng" inputmode="decimal" placeholder="auto" class="w-full border border-neutral-300 px-3 py-3 focus:outline-none focus:border-accent">
        </div>
      </div>
      <p class="text-xs text-neutral-400">Optional — known cities are auto-filled. Coordinates power the "nearby cities" list on service pages.</p>
      <button class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Add Location</button>
    </form>
    <form method="get" class="mt-6">
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Search</label>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="city or country..." class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
    </form>
  </div>

  <div class="lg:col-span-3 bg-white border border-neutral-200 overflow-x-auto self-start">
    <div class="px-6 py-4 border-b border-neutral-200 flex flex-wrap justify-between items-center gap-2">
      <h2 class="font-bold uppercase tracking-wide text-sm"><?= $total ?> Locations &middot; page <?= $page ?>/<?= $totalPages ?></h2>
      <div class="flex items-center gap-4">
        <?php if ($page > 1): ?><a href="/admin/locations?<?= e(http_build_query(['q' => $q, 'page' => $page - 1])) ?>" class="text-xs uppercase tracking-widest font-semibold hover:text-accent-dark">&larr; Prev</a><?php endif; ?>
        <?php if ($page < $totalPages): ?><a href="/admin/locations?<?= e(http_build_query(['q' => $q, 'page' => $page + 1])) ?>" class="text-xs uppercase tracking-widest font-semibold hover:text-accent-dark">Next &rarr;</a><?php endif; ?>
        <a href="/admin/city-pages?action=generate" class="text-xs uppercase tracking-widest text-accent-dark font-semibold hover:underline">Generate pages &rarr;</a>
      </div>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-neutral-100 text-left text-xs uppercase tracking-widest text-neutral-500">
        <tr><th class="px-6 py-3">Country</th><th class="px-6 py-3">City</th><th class="px-6 py-3">Slug</th><th class="px-6 py-3">Coordinates (lat, lng)</th><th class="px-6 py-3">Pages</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-right">Actions</th></tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <?php foreach ($rows as $l): ?>
        <tr class="hover:bg-neutral-50">
          <td class="px-6 py-2.5"><?= e($l['country']) ?></td>
          <td class="px-6 py-2.5 font-semibold"><?= e($l['city']) ?></td>
          <td class="px-6 py-2.5 font-mono text-xs text-neutral-500"><?= e($l['slug']) ?></td>
          <td class="px-6 py-2.5">
            <form method="post" class="flex items-center gap-1.5">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="coords"><input type="hidden" name="id" value="<?= $l['id'] ?>">
              <input type="text" name="lat" value="<?= e($l['lat'] ?? '') ?>" placeholder="lat" inputmode="decimal" class="w-20 border border-neutral-300 px-2 py-1 text-xs font-mono focus:outline-none focus:border-accent">
              <input type="text" name="lng" value="<?= e($l['lng'] ?? '') ?>" placeholder="lng" inputmode="decimal" class="w-20 border border-neutral-300 px-2 py-1 text-xs font-mono focus:outline-none focus:border-accent">
              <button class="px-2 py-1 border border-neutral-300 text-[10px] font-bold uppercase hover:border-accent" title="Save coordinates">Save</button>
            </form>
          </td>
          <td class="px-6 py-2.5"><?= $l['pages'] ?></td>
          <td class="px-6 py-2.5">
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $l['id'] ?>">
              <button class="px-2 py-1 text-[10px] font-bold uppercase tracking-widest <?= $l['is_active'] ? 'bg-accent text-neutral-950' : 'bg-neutral-200 text-neutral-500' ?>"><?= $l['is_active'] ? 'Active' : 'Off' ?></button>
            </form>
          </td>
          <td class="px-6 py-2.5 text-right">
            <form method="post" class="inline" onsubmit="return confirm('Delete <?= e($l['city']) ?> and all its pages?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $l['id'] ?>">
              <button class="px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-red-500 hover:text-red-600">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
