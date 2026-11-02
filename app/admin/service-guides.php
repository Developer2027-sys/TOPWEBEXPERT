<?php
$adminTitle = 'City Guides';
$activeNav = 'service-guides';
require __DIR__ . '/includes/admin-header.php';

// One-time auto-migration: guide templates rendered per city (like city_pages)
$conn->query("CREATE TABLE IF NOT EXISTS service_blogs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_slug VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  meta_title VARCHAR(255) NULL,
  meta_description TEXT NULL,
  meta_keywords TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_service (service_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if (($_POST['action'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $conn->query("UPDATE service_blogs SET is_active = 1 - is_active WHERE id = " . $id);
    header('Location: /admin/service-guides' . ($_GET ? '?' . http_build_query($_GET) : ''));
    exit;
}
if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM service_blogs WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Guide deleted.');
    header('Location: /admin/service-guides');
    exit;
}

$services = [];
$res = $conn->query("SELECT DISTINCT service_slug, service_name FROM city_pages ORDER BY service_name");
if ($res) while ($r = $res->fetch_assoc()) $services[$r['service_slug']] = $r['service_name'];

$fService = $_GET['service'] ?? '';
if ($fService !== '' && !isset($services[$fService])) $fService = '';

if ($fService !== '') {
    $stmt = $conn->prepare("SELECT * FROM service_blogs WHERE service_slug = ? ORDER BY service_slug, sort_order, id");
    $stmt->bind_param('s', $fService);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $rows = $conn->query("SELECT * FROM service_blogs ORDER BY service_slug, sort_order, id")->fetch_all(MYSQLI_ASSOC);
}
$total = count($rows);
$activeCities = (int)$conn->query("SELECT COUNT(DISTINCT cp.location_id) FROM city_pages cp JOIN locations l ON l.id = cp.location_id WHERE cp.is_active = 1 AND l.is_active = 1")->fetch_row()[0];
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <div>
    <p class="text-sm text-neutral-500"><?= $total ?> guide templates &middot; each renders for every active city of its service (URL: <span class="font-mono text-xs">/guide-slug-city</span>) &middot; auto-included in the sitemap</p>
    <p class="text-xs text-neutral-400 mt-1">Placeholders <span class="font-mono">{city}</span>, <span class="font-mono">{country}</span>, <span class="font-mono">{city_slug}</span> are replaced per city. These guides do not appear in the Blogs section.</p>
  </div>
  <div class="flex items-center gap-2">
    <form method="get">
      <select name="service" onchange="this.form.submit()" class="a-input !w-64">
        <option value="">All services</option>
        <?php foreach ($services as $slug => $name): ?>
          <option value="<?= e($slug) ?>" <?= $fService === $slug ? 'selected' : '' ?>><?= e($name) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <a href="/admin/service-guide-edit" class="a-btn-dark">+ New Guide</a>
  </div>
</div>

<div class="a-card overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="text-left text-[10px] uppercase tracking-[0.14em] text-neutral-400">
      <tr><th class="px-5 py-3.5">Service</th><th class="px-5 py-3.5">Title</th><th class="px-5 py-3.5">Slug</th><th class="px-5 py-3.5">Updated</th><th class="px-5 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-neutral-100">
      <?php $prevService = null; foreach ($rows as $g): ?>
      <tr class="hover:bg-neutral-50/70 <?= $prevService !== null && $prevService !== $g['service_slug'] ? 'border-t-4 !border-t-neutral-100' : '' ?>">
        <td class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-neutral-500 whitespace-nowrap"><?= e($services[$g['service_slug']] ?? $g['service_slug']) ?></td>
        <td class="px-5 py-3 font-semibold"><?= e($g['title']) ?></td>
        <td class="px-5 py-3 font-mono text-xs text-neutral-400"><?= e($g['slug']) ?>-<span class="text-accent-dark">city</span></td>
        <td class="px-5 py-3 text-neutral-400 whitespace-nowrap"><?= e(date('d M Y', strtotime($g['updated_at']))) ?></td>
        <td class="px-5 py-3">
          <form method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $g['id'] ?>">
            <button class="a-badge <?= $g['is_active'] ? 'bg-accent text-neutral-950' : 'bg-neutral-200 text-neutral-500' ?>"><?= $g['is_active'] ? 'Live' : 'Off' ?></button>
          </form>
        </td>
        <td class="px-5 py-3 text-right whitespace-nowrap">
          <a href="/admin/service-guide-edit?id=<?= $g['id'] ?>" class="a-btn-ghost !px-3 !py-1.5">Edit</a>
          <form method="post" class="inline" onsubmit="return confirm('Delete this guide for all cities?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $g['id'] ?>">
            <button class="a-btn-ghost !px-3 !py-1.5 hover:!border-red-500 hover:!text-red-600">Delete</button>
          </form>
        </td>
      </tr>
      <?php $prevService = $g['service_slug']; endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="px-6 py-12 text-center text-neutral-400">No guides yet — click "+ New Guide" to create one</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<p class="text-xs text-neutral-400 mt-4">Reach: one live guide = ~<?= number_format($activeCities) ?> city URLs in the sitemap (per its service's active cities).</p>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
