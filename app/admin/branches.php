<?php
$adminTitle = 'Branches';
$activeNav = 'branches';
require __DIR__ . '/includes/admin-header.php';

if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $total = (int)$conn->query("SELECT COUNT(*) c FROM branches")->fetch_assoc()['c'];
    if ($total <= 1) {
        flash('At least one branch is required — add another before deleting this one.');
    } else {
        $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        // Ensure a primary branch always exists
        $hasPrimary = (int)$conn->query("SELECT COUNT(*) c FROM branches WHERE is_primary = 1")->fetch_assoc()['c'];
        if (!$hasPrimary) $conn->query("UPDATE branches SET is_primary = 1 ORDER BY sort_order, id LIMIT 1");
        flash('Branch deleted.');
    }
    header('Location: /admin/branches');
    exit;
}

if (($_POST['action'] ?? '') === 'primary') {
    $id = (int)($_POST['id'] ?? 0);
    $conn->query("UPDATE branches SET is_primary = 0");
    $conn->query("UPDATE branches SET is_primary = 1, is_active = 1 WHERE id = $id");
    flash('Primary branch updated — top bar and schema now use this branch.');
    header('Location: /admin/branches');
    exit;
}

if (($_POST['action'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $conn->query("UPDATE branches SET is_active = 1 - is_active WHERE id = $id AND is_primary = 0");
    header('Location: /admin/branches');
    exit;
}

if (($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    if ($name && $address && $phone && $email) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE branches SET name=?, address=?, phone=?, email=?, sort_order=? WHERE id=?");
            $stmt->bind_param('ssssii', $name, $address, $phone, $email, $sort, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO branches (name, address, phone, email, sort_order) VALUES (?,?,?,?,?)");
            $stmt->bind_param('ssssi', $name, $address, $phone, $email, $sort);
        }
        $stmt->execute();
        flash($id ? 'Branch updated.' : 'Branch added — it now shows on the contact page and footer.');
    } else {
        flash('All fields are required.');
    }
    header('Location: /admin/branches');
    exit;
}

$editId = (int)($_GET['edit'] ?? 0);
$editRow = ['id' => 0, 'name' => '', 'address' => '', 'phone' => '', 'email' => '', 'sort_order' => 0];
if ($editId) {
    $stmt = $conn->prepare("SELECT * FROM branches WHERE id = ?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc() ?: $editRow;
}
$rows = $conn->query("SELECT * FROM branches ORDER BY is_primary DESC, sort_order, id")->fetch_all(MYSQLI_ASSOC);
?>

<p class="text-sm text-neutral-500 mb-6">Branches appear on the Contact page, footer and top bar. The <span class="font-bold">Primary</span> branch is used in the header top bar and search-engine schema.</p>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="bg-white border border-neutral-200 p-6 self-start">
    <h2 class="font-bold uppercase tracking-wide text-sm mb-4"><?= $editRow['id'] ? 'Edit Branch' : 'Add Branch' ?></h2>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Branch Name</label>
        <input type="text" name="name" required placeholder="e.g. Delhi Office" value="<?= e($editRow['name']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Full Address</label>
        <textarea name="address" rows="3" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent"><?= e($editRow['address']) ?></textarea>
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Phone</label>
        <input type="text" name="phone" required placeholder="+91 ..." value="<?= e($editRow['phone']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Email</label>
        <input type="email" name="email" required value="<?= e($editRow['email']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int)$editRow['sort_order'] ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <button class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors"><?= $editRow['id'] ? 'Update Branch' : 'Add Branch' ?></button>
      <?php if ($editRow['id']): ?><a href="/admin/branches" class="block text-center py-3 border border-neutral-300 text-xs font-bold uppercase tracking-widest hover:border-accent">Cancel Edit</a><?php endif; ?>
    </form>
  </div>

  <div class="lg:col-span-2 space-y-4">
    <?php foreach ($rows as $b): ?>
    <div class="bg-white border <?= $b['is_primary'] ? 'border-accent' : 'border-neutral-200' ?> p-5 flex flex-wrap justify-between gap-4">
      <div class="min-w-0">
        <p class="font-bold flex items-center gap-2 flex-wrap">
          <?= e($b['name']) ?>
          <?php if ($b['is_primary']): ?><span class="px-2 py-0.5 bg-accent text-neutral-950 text-[10px] font-bold uppercase tracking-widest">Primary</span><?php endif; ?>
          <?php if (!$b['is_active']): ?><span class="px-2 py-0.5 bg-neutral-200 text-neutral-500 text-[10px] font-bold uppercase tracking-widest">Hidden</span><?php endif; ?>
        </p>
        <p class="mt-2 text-sm text-neutral-600"><?= e($b['address']) ?></p>
        <p class="mt-1 text-sm text-neutral-500"><?= e($b['phone']) ?> &middot; <?= e($b['email']) ?></p>
      </div>
      <div class="flex flex-col gap-2 shrink-0">
        <a href="/admin/branches?edit=<?= $b['id'] ?>" class="px-3 py-1.5 bg-neutral-950 text-white text-xs font-bold uppercase text-center hover:bg-accent hover:text-neutral-950">Edit</a>
        <?php if (!$b['is_primary']): ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="primary"><input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button class="w-full px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-accent hover:text-accent-dark">Make Primary</button>
          </form>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button class="w-full px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-accent"><?= $b['is_active'] ? 'Hide' : 'Show' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Delete this branch?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button class="w-full px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-red-500 hover:text-red-600">Delete</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
