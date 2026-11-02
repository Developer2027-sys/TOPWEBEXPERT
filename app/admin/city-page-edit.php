<?php
$adminTitle = 'Edit City Page';
$activeNav = 'city-pages';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/includes/upload.php';
require __DIR__ . '/../includes/services-config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT cp.*, l.city, l.country, l.slug AS city_slug FROM city_pages cp JOIN locations l ON l.id = cp.location_id WHERE cp.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { flash('Page not found.'); header('Location: /admin/city-pages'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $metaKeys = trim($_POST['meta_keywords'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $tier = min(3, max(1, (int)($_POST['tier'] ?? ($row['tier'] ?? 3))));

    $uploadError = null;
    $newImage = handle_image_upload('image', WEB_ROOT . '/assets/img', $uploadError);
    $image = $newImage ?: $row['image'];

    if ($uploadError) {
        $error = $uploadError;
    } elseif ($title === '' || $content === '') {
        $error = 'Title and content are required.';
    } else {
        $stmt = $conn->prepare("UPDATE city_pages SET title=?, content=?, image=?, meta_title=?, meta_description=?, meta_keywords=?, is_active=? WHERE id=?");
        $stmt->bind_param('ssssssii', $title, $content, $image, $metaTitle, $metaDesc, $metaKeys, $isActive, $id);
        $stmt->execute();
        if (array_key_exists('tier', $row)) {
            $stmt = $conn->prepare("UPDATE city_pages SET tier=? WHERE id=?");
            $stmt->bind_param('ii', $tier, $id);
            $stmt->execute();
        }
        flash('City page updated.');
        header('Location: /admin/city-pages');
        exit;
    }
    $row = array_merge($row, compact('title', 'content', 'image') + ['meta_title' => $metaTitle, 'meta_description' => $metaDesc, 'meta_keywords' => $metaKeys, 'is_active' => $isActive]
        + (array_key_exists('tier', $row) ? ['tier' => $tier] : []));
}
$url = '/' . $row['service_slug'] . '-' . $row['city_slug'];
?>

<?php if ($error): ?><div class="mb-6 p-4 bg-white border-l-4 border-red-500 text-sm font-semibold"><?= e($error) ?></div><?php endif; ?>

<div class="mb-6 flex items-center gap-3 text-sm">
  <span class="px-3 py-1.5 bg-neutral-950 text-accent text-xs font-bold uppercase"><?= e($row['service_name']) ?></span>
  <span class="px-3 py-1.5 bg-neutral-200 text-xs font-bold uppercase"><?= e($row['city'] . ', ' . $row['country']) ?></span>
  <a href="<?= e($url) ?>" target="_blank" class="text-accent-dark font-mono text-xs hover:underline"><?= e($url) ?> &nearr;</a>
</div>

<form method="post" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
  <?= csrf_field() ?>
  <div class="lg:col-span-2 bg-white border border-neutral-200 p-6 space-y-5">
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Page Heading (H1)</label>
      <input type="text" name="title" required value="<?= e($row['title']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
    </div>
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Content (HTML allowed — use h2/p)</label>
      <textarea name="content" rows="18" required class="w-full border border-neutral-300 px-4 py-3 font-mono text-sm focus:outline-none focus:border-accent"><?= e($row['content']) ?></textarea>
    </div>
  </div>
  <div class="space-y-6">
    <div class="bg-white border border-neutral-200 p-6 space-y-5">
      <label class="flex items-center gap-3 text-sm font-semibold">
        <input type="checkbox" name="is_active" <?= $row['is_active'] ? 'checked' : '' ?> class="w-4 h-4 accent-[#00c1ae]"> Page live (uncheck for draft)
      </label>
      <?php if (array_key_exists('tier', $row)): ?>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Google visibility (tier)</label>
        <select name="tier" class="w-full border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:border-accent bg-white">
          <?php foreach ([1 => 'Tier 1 - core market (indexed)', 2 => 'Tier 2 - proven page (indexed)', 3 => 'Tier 3 - hidden from Google (noindex)'] as $tv => $tl): ?>
          <option value="<?= $tv ?>" <?= (int)$row['tier'] === $tv ? 'selected' : '' ?>><?= e($tl) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="mt-2 text-xs text-neutral-500">Move a page to Tier 1/2 only once it has real local content (clients, prices, results in this city).</p>
      </div>
      <?php endif; ?>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Image</label>
        <?php if (!empty($row['image'])): ?>
          <img src="/assets/img/<?= e($row['image']) ?>" alt="" class="w-full h-32 object-cover border border-neutral-200 mb-3">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="w-full text-sm">
      </div>
    </div>
    <div class="bg-white border border-neutral-200 p-6 space-y-5">
      <h2 class="font-bold uppercase tracking-wide text-sm border-b border-neutral-200 pb-3">SEO</h2>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Meta Title <span class="text-neutral-400 normal-case">(<?= mb_strlen($row['meta_title'] ?? '') ?> chars)</span></label>
        <input type="text" name="meta_title" value="<?= e($row['meta_title']) ?>" class="w-full border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Meta Description</label>
        <textarea name="meta_description" rows="3" class="w-full border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:border-accent"><?= e($row['meta_description']) ?></textarea>
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Meta Keywords</label>
        <textarea name="meta_keywords" rows="2" class="w-full border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:border-accent"><?= e($row['meta_keywords']) ?></textarea>
      </div>
      <button type="submit" class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Save Page</button>
      <a href="/admin/city-pages" class="block text-center py-3 border border-neutral-300 text-xs font-bold uppercase tracking-widest hover:border-accent">Back</a>
    </div>
  </div>
</form>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
