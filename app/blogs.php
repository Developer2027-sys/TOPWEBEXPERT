<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Blog Posts - TopWebExpert';
$metaDescription = 'Latest articles on web development and digital marketing from TopWebExpert.';

$limit = 9;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$total = (int)$conn->query("SELECT COUNT(*) c FROM posts WHERE date <= CURDATE()")->fetch_assoc()['c'];
$totalPages = max(1, (int)ceil($total / $limit));

$posts = [];
$stmt = $conn->prepare("SELECT title, description, date, image, slug FROM posts WHERE date <= CURDATE() ORDER BY date DESC, id DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $posts[] = $row;

require __DIR__ . '/includes/header.php';
?>

<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center">
    <p class="font-semibold uppercase tracking-widest text-sm text-white/80">Insights</p>
    <h1 class="text-4xl md:text-5xl font-bold mt-3">Blog Posts</h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
  </div>
</section>

<section class="bg-mist py-16">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($posts as $bi => $post): ?>
      <a href="/blog/<?= e($post['slug']) ?>" class="card-mono p-0 overflow-hidden group flex flex-col">
        <div class="overflow-hidden">
          <img src="<?= e(blog_thumb($post['image'])) ?>" alt="<?= e($post['title']) ?>" class="w-full h-48 object-cover img-mono group-hover:scale-105 transition-transform duration-300" loading="<?= $bi < 3 ? 'eager' : 'lazy' ?>" decoding="async" width="480" height="192">
        </div>
        <div class="p-6 flex flex-col grow">
          <p class="text-xs text-ink-soft uppercase tracking-wide"><?= e(date('d M Y', strtotime($post['date']))) ?></p>
          <h2 class="mt-2 text-lg font-bold group-hover:text-accent transition-colors"><?= e($post['title']) ?></h2>
          <p class="mt-3 text-sm text-ink-soft leading-relaxed grow"><?= e(excerpt($post['description'], 120)) ?></p>
          <span class="mt-4 text-accent font-semibold text-sm uppercase tracking-wide">Read More &rarr;</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <?php
      // Windowed pagination: 1 ... (page-2 .. page+2) ... last
      $window = [];
      for ($i = 1; $i <= $totalPages; $i++) {
          if ($i === 1 || $i === $totalPages || abs($i - $page) <= 2) $window[] = $i;
      }
    ?>
    <div class="flex flex-wrap justify-center items-center gap-2 mt-12">
      <?php if ($page > 1): ?>
        <a href="/blogs?page=<?= $page - 1 ?>" class="px-4 h-10 rounded-full flex items-center border border-line font-semibold hover:border-accent hover:text-accent-dark transition-colors">&larr; Prev</a>
      <?php endif; ?>
      <?php $prev = 0; foreach ($window as $i): ?>
        <?php if ($i - $prev > 1): ?><span class="text-ink-soft px-1">&hellip;</span><?php endif; $prev = $i; ?>
        <a href="/blogs?page=<?= $i ?>" class="w-10 h-10 rounded-full flex items-center justify-center border font-semibold transition-colors <?= $i === $page ? 'hero-grad text-white border-transparent shadow-lg shadow-accent/30' : 'border-line hover:border-accent hover:text-accent-dark' ?>"><?= $i ?></a>
      <?php endforeach; ?>
      <?php if ($page < $totalPages): ?>
        <a href="/blogs?page=<?= $page + 1 ?>" class="px-4 h-10 rounded-full flex items-center border border-line font-semibold hover:border-accent hover:text-accent-dark transition-colors">Next &rarr;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
