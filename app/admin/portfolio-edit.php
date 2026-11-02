<?php
$adminTitle = 'Edit Portfolio Item';
$activeNav = 'portfolio';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/includes/upload.php';

$id = (int)($_GET['id'] ?? 0);
$item = ['title' => '', 'image' => ''];
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM portfolio_items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc() ?: $item;
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $uploadError = null;
    $newImage = handle_image_upload('image', WEB_ROOT . '/assets/img/portfolio', $uploadError);
    $image = $newImage ?: ($item['image'] ?? '');

    if ($uploadError) {
        $error = $uploadError;
    } elseif ($title === '') {
        $error = 'Title is required.';
    } elseif ($image === '') {
        $error = 'Please upload a logo/image.';
    } else {
        if ($id) {
            $stmt = $conn->prepare("UPDATE portfolio_items SET title=?, image=? WHERE id=?");
            $stmt->bind_param('ssi', $title, $image, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO portfolio_items (title, image) VALUES (?,?)");
            $stmt->bind_param('ss', $title, $image);
        }
        $stmt->execute();
        flash($id ? 'Item updated.' : 'Item added.');
        header('Location: /admin/portfolio');
        exit;
    }
    $item = compact('title', 'image');
}
?>

<?php if ($error): ?><div class="mb-6 p-4 bg-white border-l-4 border-red-500 text-sm font-semibold"><?= e($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="max-w-lg bg-white border border-neutral-200 p-6 space-y-5">
  <?= csrf_field() ?>
  <div>
    <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Client / Company Name</label>
    <input type="text" name="title" required value="<?= e($item['title']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
  </div>
  <div>
    <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Logo / Image</label>
    <?php if (!empty($item['image'])): ?>
      <img src="/assets/img/portfolio/<?= e($item['image']) ?>" alt="" class="h-16 object-contain mb-3">
    <?php endif; ?>
    <input type="file" name="image" accept="image/*" class="w-full text-sm">
  </div>
  <div class="flex gap-3">
    <button type="submit" class="flex-1 py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors"><?= $id ? 'Update' : 'Add Item' ?></button>
    <a href="/admin/portfolio" class="flex-1 text-center py-3 border border-neutral-300 text-xs font-bold uppercase tracking-widest hover:border-accent">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
