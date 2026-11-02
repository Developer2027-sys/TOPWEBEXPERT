<?php
$adminTitle = 'Portfolio';
$activeNav = 'portfolio';
require __DIR__ . '/includes/admin-header.php';

if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM portfolio_items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Portfolio item deleted.');
    header('Location: /admin/portfolio');
    exit;
}

$items = $conn->query("SELECT id, title, image FROM portfolio_items ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
  <p class="text-sm text-neutral-500"><?= count($items) ?> items (shown in the home logo carousel and portfolio page)</p>
  <a href="/admin/portfolio-edit" class="px-5 py-2.5 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">+ Add Item</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
  <?php foreach ($items as $it): ?>
  <div class="bg-white border border-neutral-200 p-4 flex flex-col items-center gap-3">
    <img src="/assets/img/portfolio/<?= e($it['image']) ?>" alt="<?= e($it['title']) ?>" class="h-16 object-contain">
    <p class="text-sm font-semibold text-center"><?= e($it['title']) ?></p>
    <div class="flex gap-2">
      <a href="/admin/portfolio-edit?id=<?= $it['id'] ?>" class="px-3 py-1.5 bg-neutral-950 text-white text-xs font-bold uppercase hover:bg-accent hover:text-neutral-950">Edit</a>
      <form method="post" onsubmit="return confirm('Delete this item?')">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $it['id'] ?>">
        <button class="px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-red-500 hover:text-red-600">Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (!$items): ?><p class="col-span-full text-center text-neutral-400 py-10">No portfolio items yet</p><?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
