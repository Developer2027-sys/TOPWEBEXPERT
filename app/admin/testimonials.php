<?php
$adminTitle = 'Testimonials';
$activeNav = 'testimonials';
require __DIR__ . '/includes/admin-header.php';

if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Testimonial deleted.');
    header('Location: /admin/testimonials');
    exit;
}

// Inline create/update
if (($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $designation && $message) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE testimonials SET name=?, designation=?, message=? WHERE id=?");
            $stmt->bind_param('sssi', $name, $designation, $message, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO testimonials (name, designation, message) VALUES (?,?,?)");
            $stmt->bind_param('sss', $name, $designation, $message);
        }
        $stmt->execute();
        flash($id ? 'Testimonial updated.' : 'Testimonial added.');
        header('Location: /admin/testimonials');
        exit;
    }
    flash('All fields are required.');
    header('Location: /admin/testimonials');
    exit;
}

$editId = (int)($_GET['edit'] ?? 0);
$editRow = ['id' => 0, 'name' => '', 'designation' => '', 'message' => ''];
if ($editId) {
    $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc() ?: $editRow;
}
$rows = $conn->query("SELECT * FROM testimonials ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="bg-white border border-neutral-200 p-6 self-start">
    <h2 class="font-bold uppercase tracking-wide text-sm mb-4"><?= $editRow['id'] ? 'Edit Testimonial #' . $editRow['id'] : 'Add Testimonial' ?></h2>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Client Name</label>
        <input type="text" name="name" required value="<?= e($editRow['name']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Designation</label>
        <input type="text" name="designation" required value="<?= e($editRow['designation']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Message</label>
        <textarea name="message" rows="5" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent"><?= e($editRow['message']) ?></textarea>
      </div>
      <button type="submit" class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors"><?= $editRow['id'] ? 'Update' : 'Add' ?></button>
      <?php if ($editRow['id']): ?><a href="/admin/testimonials" class="block text-center py-3 border border-neutral-300 text-xs font-bold uppercase tracking-widest hover:border-accent">Cancel Edit</a><?php endif; ?>
    </form>
  </div>

  <div class="lg:col-span-2 space-y-4">
    <?php foreach ($rows as $t): ?>
    <div class="bg-white border border-neutral-200 p-5 flex justify-between gap-4">
      <div>
        <p class="text-sm text-neutral-600 italic">&ldquo;<?= e($t['message']) ?>&rdquo;</p>
        <p class="mt-2 font-bold text-sm"><?= e($t['name']) ?> <span class="text-neutral-400 font-normal">&mdash; <?= e($t['designation']) ?></span></p>
      </div>
      <div class="flex flex-col gap-2 shrink-0">
        <a href="/admin/testimonials?edit=<?= $t['id'] ?>" class="px-3 py-1.5 bg-neutral-950 text-white text-xs font-bold uppercase text-center hover:bg-accent hover:text-neutral-950">Edit</a>
        <form method="post" onsubmit="return confirm('Delete this testimonial?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
          <button class="w-full px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-red-500 hover:text-red-600">Delete</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><p class="text-center text-neutral-400 py-10">No testimonials yet</p><?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
