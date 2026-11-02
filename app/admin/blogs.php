<?php
$adminTitle = 'Blogs';
$activeNav = 'blogs';
require __DIR__ . '/includes/admin-header.php';

if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Blog post deleted.');
    header('Location: /admin/blogs');
    exit;
}

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

if ($q !== '') {
    $like = "%$q%";
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM posts WHERE title LIKE ? OR slug LIKE ?");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt = $conn->prepare("SELECT id, title, slug, date, image FROM posts WHERE title LIKE ? OR slug LIKE ? ORDER BY date DESC, id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ssii', $like, $like, $limit, $offset);
} else {
    $total = (int)$conn->query("SELECT COUNT(*) c FROM posts")->fetch_assoc()['c'];
    $stmt = $conn->prepare("SELECT id, title, slug, date, image FROM posts ORDER BY date DESC, id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$totalPages = max(1, (int)ceil($total / $limit));
?>

<div class="flex flex-wrap justify-between items-center gap-3 mb-6">
  <form method="get" class="flex gap-2 w-full sm:w-auto">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title / slug..." class="flex-1 sm:flex-none border border-neutral-300 px-4 py-2 text-sm focus:outline-none focus:border-accent">
    <button class="px-4 py-2 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 shrink-0">Search</button>
  </form>
  <div class="flex items-center gap-3">
    <p class="text-sm text-neutral-500"><?= $total ?> posts &middot; page <?= $page ?>/<?= $totalPages ?></p>
    <a href="/admin/blog-edit" class="px-5 py-2.5 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">+ New Post</a>
  </div>
</div>

<div class="bg-white border border-neutral-200 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-neutral-100 text-left text-xs uppercase tracking-widest text-neutral-500">
      <tr><th class="px-6 py-3">Image</th><th class="px-6 py-3">Title</th><th class="px-6 py-3">Slug</th><th class="px-6 py-3">Date</th><th class="px-6 py-3 text-right">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-neutral-100">
      <?php foreach ($posts as $p): ?>
      <tr class="hover:bg-neutral-50">
        <td class="px-6 py-3"><img src="/assets/img/blog/<?= e($p['image']) ?>" alt="" class="w-16 h-12 object-cover border border-neutral-200"></td>
        <td class="px-6 py-3 font-semibold"><?= e($p['title']) ?></td>
        <td class="px-6 py-3 text-neutral-500 font-mono text-xs"><?= e($p['slug']) ?></td>
        <td class="px-6 py-3 text-neutral-500 whitespace-nowrap">
          <?= e($p['date']) ?>
          <?php if ($p['date'] > date('Y-m-d')): ?>
            <span class="block mt-1 px-2 py-0.5 bg-neutral-950 text-accent text-[10px] font-bold uppercase tracking-widest w-fit">Scheduled</span>
          <?php else: ?>
            <span class="block mt-1 px-2 py-0.5 bg-accent text-neutral-950 text-[10px] font-bold uppercase tracking-widest w-fit">Live</span>
          <?php endif; ?>
        </td>
        <td class="px-6 py-3 text-right whitespace-nowrap">
          <a href="/blog/<?= e($p['slug']) ?>" target="_blank" class="inline-block px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-accent hover:text-accent-dark">View</a>
          <a href="/admin/blog-edit?id=<?= $p['id'] ?>" class="inline-block px-3 py-1.5 bg-neutral-950 text-white text-xs font-bold uppercase hover:bg-accent hover:text-neutral-950">Edit</a>
          <form method="post" class="inline" onsubmit="return confirm('Delete this post?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="px-3 py-1.5 border border-neutral-300 text-xs font-bold uppercase hover:border-red-500 hover:text-red-600">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$posts): ?><tr><td colspan="5" class="px-6 py-10 text-center text-neutral-400">No posts found</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="flex flex-wrap items-center gap-2 mt-6">
  <?php if ($page > 1): ?>
    <a href="/admin/blogs?<?= e(http_build_query(['q' => $q, 'page' => $page - 1])) ?>" class="px-4 py-2 border border-neutral-300 text-xs font-bold uppercase hover:border-accent">&larr; Prev</a>
  <?php endif; ?>
  <span class="text-sm text-neutral-500 px-2">Page <?= $page ?> of <?= $totalPages ?></span>
  <?php if ($page < $totalPages): ?>
    <a href="/admin/blogs?<?= e(http_build_query(['q' => $q, 'page' => $page + 1])) ?>" class="px-4 py-2 border border-neutral-300 text-xs font-bold uppercase hover:border-accent">Next &rarr;</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
