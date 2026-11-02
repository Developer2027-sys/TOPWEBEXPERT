<?php
$adminTitle = 'Inquiries';
$activeNav = 'inquiries';
require __DIR__ . '/includes/admin-header.php';

// One-time auto-migration: status column for the inquiry workflow (new -> contacted -> closed).
// Runs on the admin page only, so production upgrades itself on first visit — no manual SQL needed.
$hasStatus = false;
$res = $conn->query("SHOW COLUMNS FROM inquiries LIKE 'status'");
if ($res && $res->num_rows) $hasStatus = true;
if (!$hasStatus) {
    $hasStatus = (bool)$conn->query("ALTER TABLE inquiries ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'new'");
}

$STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'];
$statusOk = fn($s) => isset($STATUSES[$s]);

$backQuery = $_GET ? '?' . http_build_query($_GET) : '';

// ---- Create ----
if (($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $status = $statusOk($_POST['status'] ?? '') ? $_POST['status'] : 'new';
    if ($name && $phone) {
        $stmt = $conn->prepare("INSERT INTO inquiries (name, email, phone, message, ip, status) VALUES (?, ?, ?, ?, 'admin', ?)");
        $stmt->bind_param('sssss', $name, $email, $phone, $message, $status);
        $stmt->execute();
        flash('Inquiry added.');
    } else {
        flash('Name and phone are required.');
    }
    header('Location: /admin/inquiries' . $backQuery);
    exit;
}

// ---- Update ----
if (($_POST['action'] ?? '') === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $status = $statusOk($_POST['status'] ?? '') ? $_POST['status'] : 'new';
    if ($id && $name && $phone) {
        $stmt = $conn->prepare("UPDATE inquiries SET name = ?, email = ?, phone = ?, message = ?, status = ? WHERE id = ?");
        $stmt->bind_param('sssssi', $name, $email, $phone, $message, $status, $id);
        $stmt->execute();
        flash('Inquiry #' . $id . ' updated.');
    } else {
        flash('Name and phone are required.');
    }
    header('Location: /admin/inquiries' . $backQuery);
    exit;
}

// ---- Single status change (quick dropdown) ----
if (($_POST['action'] ?? '') === 'status') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $statusOk($_POST['status'] ?? '')) {
        $stmt = $conn->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $_POST['status'], $id);
        $stmt->execute();
        flash('Inquiry #' . $id . ' marked ' . $STATUSES[$_POST['status']] . '.');
    }
    header('Location: /admin/inquiries' . $backQuery);
    exit;
}

// ---- Delete (single) ----
if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Inquiry deleted.');
    header('Location: /admin/inquiries' . $backQuery);
    exit;
}

// ---- Bulk actions (delete / set status) ----
if (($_POST['action'] ?? '') === 'bulk') {
    $ids = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? []))));
    $do = $_POST['bulk_do'] ?? '';
    if (!$ids) {
        flash('No inquiries selected.');
    } else {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        if ($do === 'delete') {
            $stmt = $conn->prepare("DELETE FROM inquiries WHERE id IN ($ph)");
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            flash($stmt->affected_rows . ' inquiries deleted.');
        } elseif (strpos($do, 'status:') === 0 && $statusOk(substr($do, 7))) {
            $status = substr($do, 7);
            $stmt = $conn->prepare("UPDATE inquiries SET status = ? WHERE id IN ($ph)");
            $stmt->bind_param('s' . $types, $status, ...$ids);
            $stmt->execute();
            flash($stmt->affected_rows . ' inquiries marked ' . $STATUSES[$status] . '.');
        } else {
            flash('Choose a bulk action first.');
        }
    }
    header('Location: /admin/inquiries' . $backQuery);
    exit;
}

// ---- Read: search + status filter + pagination ----
$q = trim($_GET['q'] ?? '');
$fStatus = $statusOk($_GET['status'] ?? '') ? $_GET['status'] : '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = '';
if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)";
    array_push($params, $like, $like, $like, $like);
    $types .= 'ssss';
}
if ($fStatus !== '') {
    $where[] = "status = ?";
    $params[] = $fStatus;
    $types .= 's';
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$stmt = $conn->prepare("SELECT COUNT(*) c FROM inquiries" . $whereSql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['c'];
$totalPages = max(1, (int)ceil($total / $limit));

$stmt = $conn->prepare("SELECT * FROM inquiries" . $whereSql . " ORDER BY id DESC LIMIT ? OFFSET ?");
$types2 = $types . 'ii';
$params2 = array_merge($params, [$limit, $offset]);
$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Status counts for the filter tabs (always across the full table, with search applied)
$counts = ['' => 0, 'new' => 0, 'contacted' => 0, 'closed' => 0];
$res = $conn->query("SELECT status, COUNT(*) c FROM inquiries GROUP BY status");
if ($res) while ($r = $res->fetch_assoc()) { $counts[$r['status']] = (int)$r['c']; $counts[''] += (int)$r['c']; }

$badge = [
    'new' => 'bg-accent text-neutral-950',
    'contacted' => 'bg-blue-100 text-blue-700',
    'closed' => 'bg-neutral-200 text-neutral-500',
];
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <!-- Status filter tabs -->
  <div class="flex flex-wrap gap-2">
    <?php foreach (['' => 'All'] + $STATUSES as $key => $label): ?>
      <a href="/admin/inquiries?<?= e(http_build_query(array_filter(['q' => $q, 'status' => $key]))) ?>"
         class="px-3 py-1.5 text-xs font-bold uppercase tracking-widest border <?= $fStatus === (string)$key ? 'bg-neutral-950 text-white border-neutral-950' : 'bg-white border-neutral-300 text-neutral-500 hover:border-neutral-950 hover:text-neutral-950' ?>">
        <?= e($label) ?> (<?= $counts[$key] ?? 0 ?>)
      </a>
    <?php endforeach; ?>
  </div>
  <div class="flex items-center gap-2">
    <form method="get" class="flex">
      <?php if ($fStatus): ?><input type="hidden" name="status" value="<?= e($fStatus) ?>"><?php endif; ?>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, email, phone..." class="border border-neutral-300 px-3 py-2 text-sm w-56 focus:outline-none focus:border-accent">
      <button class="px-4 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Go</button>
    </form>
    <button type="button" onclick="document.getElementById('inq-add').classList.toggle('hidden')"
            class="px-4 py-2 bg-accent text-neutral-950 text-xs font-bold uppercase tracking-widest hover:bg-neutral-950 hover:text-white transition-colors">+ Add Inquiry</button>
  </div>
</div>

<!-- Add form (hidden until toggled) -->
<div id="inq-add" class="hidden mb-6 bg-white border border-neutral-200 p-6">
  <h2 class="font-bold uppercase tracking-wide text-sm mb-4">Add Inquiry (manual entry — phone call / walk-in)</h2>
  <form method="post" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Name *</label>
      <input type="text" name="name" required class="w-full border border-neutral-300 px-3 py-2.5 text-sm focus:outline-none focus:border-accent">
    </div>
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Email</label>
      <input type="email" name="email" class="w-full border border-neutral-300 px-3 py-2.5 text-sm focus:outline-none focus:border-accent">
    </div>
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Phone *</label>
      <input type="text" name="phone" required class="w-full border border-neutral-300 px-3 py-2.5 text-sm focus:outline-none focus:border-accent">
    </div>
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Status</label>
      <select name="status" class="w-full border border-neutral-300 px-3 py-2.5 text-sm focus:outline-none focus:border-accent">
        <?php foreach ($STATUSES as $k => $v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="sm:col-span-2 lg:col-span-3">
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Message</label>
      <textarea name="message" rows="2" class="w-full border border-neutral-300 px-3 py-2.5 text-sm focus:outline-none focus:border-accent"></textarea>
    </div>
    <div class="flex items-end">
      <button class="w-full py-2.5 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Save Inquiry</button>
    </div>
  </form>
</div>

<!-- Bulk action bar (checkboxes reference this form via the form="" attribute) -->
<form id="bulk-form" method="post" onsubmit="return bulkConfirm()">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="bulk">
  <div class="mb-4 bg-white border border-neutral-200 px-4 py-3 flex flex-wrap items-center gap-3">
    <span class="text-xs font-bold uppercase tracking-widest text-neutral-500">Bulk: <span id="bulk-count" class="text-neutral-950">0</span> selected</span>
    <select name="bulk_do" id="bulk-do" class="border border-neutral-300 px-3 py-1.5 text-sm focus:outline-none focus:border-accent">
      <option value="">Choose action...</option>
      <?php foreach ($STATUSES as $k => $v): ?><option value="status:<?= e($k) ?>">Mark <?= e($v) ?></option><?php endforeach; ?>
      <option value="delete">Delete selected</option>
    </select>
    <button class="px-4 py-1.5 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Apply</button>
  </div>
</form>

<p class="text-sm text-neutral-500 mb-4"><?= $total ?> inquiries<?= $q !== '' ? ' matching "' . e($q) . '"' : '' ?> &middot; page <?= $page ?>/<?= $totalPages ?></p>

<div class="bg-white border border-neutral-200 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-neutral-100 text-left text-xs uppercase tracking-widest text-neutral-500">
      <tr>
        <th class="px-4 py-3"><input type="checkbox" id="check-all" title="Select all on this page"></th>
        <th class="px-4 py-3">#</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Phone</th>
        <th class="px-4 py-3">Message</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Received</th><th class="px-4 py-3 text-right">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-neutral-100">
      <?php foreach ($rows as $r): ?>
      <tr class="hover:bg-neutral-50 align-top">
        <td class="px-4 py-3"><input type="checkbox" class="row-check" name="ids[]" value="<?= $r['id'] ?>" form="bulk-form"></td>
        <td class="px-4 py-3 text-neutral-400"><?= $r['id'] ?></td>
        <td class="px-4 py-3 font-semibold whitespace-nowrap"><?= e($r['name']) ?></td>
        <td class="px-4 py-3"><?php if ($r['email']): ?><a href="mailto:<?= e($r['email']) ?>" class="text-accent-dark hover:underline"><?= e($r['email']) ?></a><?php endif; ?></td>
        <td class="px-4 py-3 whitespace-nowrap"><a href="tel:<?= e($r['phone']) ?>" class="hover:underline"><?= e($r['phone']) ?></a></td>
        <td class="px-4 py-3 text-neutral-600 max-w-md"><?= e($r['message']) ?></td>
        <td class="px-4 py-3">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <select name="status" onchange="this.form.submit()"
                    class="px-2 py-1 text-[11px] font-bold uppercase tracking-widest border-0 cursor-pointer <?= $badge[$r['status'] ?? 'new'] ?? $badge['new'] ?>">
              <?php foreach ($STATUSES as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= ($r['status'] ?? 'new') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td class="px-4 py-3 text-neutral-500 whitespace-nowrap">
          <?= e(date('d M Y H:i', strtotime($r['created_at']))) ?>
          <?php if (!empty($r['ip']) && $r['ip'] !== 'admin'): ?>
            <br><a href="/admin/traffic?q=<?= e(urlencode($r['ip'])) ?>" class="font-mono text-[11px] text-neutral-400 hover:text-accent-dark hover:underline" title="View this IP in Traffic & IPs"><?= e($r['ip']) ?></a>
          <?php endif; ?>
        </td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
          <button type="button" onclick="document.getElementById('edit-<?= $r['id'] ?>').classList.toggle('hidden')"
                  class="px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-accent">Edit</button>
          <form method="post" class="inline" onsubmit="return confirm('Delete inquiry #<?= $r['id'] ?>?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-red-500 hover:text-red-600">Delete</button>
          </form>
        </td>
      </tr>
      <!-- Inline edit row -->
      <tr id="edit-<?= $r['id'] ?>" class="hidden bg-neutral-50">
        <td colspan="9" class="px-4 py-4">
          <form method="post" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <input type="text" name="name" value="<?= e($r['name']) ?>" required placeholder="Name" class="border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent">
            <input type="email" name="email" value="<?= e($r['email']) ?>" placeholder="Email" class="border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent">
            <input type="text" name="phone" value="<?= e($r['phone']) ?>" required placeholder="Phone" class="border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent">
            <select name="status" class="border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent">
              <?php foreach ($STATUSES as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= ($r['status'] ?? 'new') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="flex gap-2">
              <button class="flex-1 py-2 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Save</button>
              <button type="button" onclick="document.getElementById('edit-<?= $r['id'] ?>').classList.add('hidden')" class="px-3 py-2 border border-neutral-300 text-xs font-bold uppercase">Cancel</button>
            </div>
            <textarea name="message" rows="2" placeholder="Message" class="sm:col-span-2 lg:col-span-5 border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:border-accent"><?= e($r['message']) ?></textarea>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="9" class="px-6 py-10 text-center text-neutral-400">No inquiries<?= $q !== '' || $fStatus !== '' ? ' match this filter' : ' yet' ?></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="mt-4 flex items-center gap-4">
  <?php if ($page > 1): ?><a href="/admin/inquiries?<?= e(http_build_query(array_filter(['q' => $q, 'status' => $fStatus, 'page' => $page - 1]))) ?>" class="text-xs uppercase tracking-widest font-semibold hover:text-accent-dark">&larr; Prev</a><?php endif; ?>
  <span class="text-xs text-neutral-500">page <?= $page ?> / <?= $totalPages ?></span>
  <?php if ($page < $totalPages): ?><a href="/admin/inquiries?<?= e(http_build_query(array_filter(['q' => $q, 'status' => $fStatus, 'page' => $page + 1]))) ?>" class="text-xs uppercase tracking-widest font-semibold hover:text-accent-dark">Next &rarr;</a><?php endif; ?>
</div>
<?php endif; ?>

<script>
(function () {
  var all = document.getElementById('check-all');
  var boxes = Array.prototype.slice.call(document.querySelectorAll('.row-check'));
  var count = document.getElementById('bulk-count');
  function refresh() {
    var n = boxes.filter(function (b) { return b.checked; }).length;
    count.textContent = n;
    if (all) { all.checked = n > 0 && n === boxes.length; }
  }
  if (all) all.addEventListener('change', function () {
    boxes.forEach(function (b) { b.checked = all.checked; });
    refresh();
  });
  boxes.forEach(function (b) { b.addEventListener('change', refresh); });
  window.bulkConfirm = function () {
    var n = boxes.filter(function (b) { return b.checked; }).length;
    var act = document.getElementById('bulk-do').value;
    if (!n) { alert('Select at least one inquiry first.'); return false; }
    if (!act) { alert('Choose a bulk action first.'); return false; }
    if (act === 'delete') return confirm('Delete ' + n + ' selected inquiries? This cannot be undone.');
    return true;
  };
})();
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
