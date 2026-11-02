<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = $conn->prepare("SELECT title, description, date, image, content, slug FROM posts WHERE slug = ? AND date <= CURDATE() LIMIT 1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Post Not Found - TopWebExpert';
    require __DIR__ . '/includes/header.php';
    echo '<section class="py-24 text-center"><h1 class="section-title">Post not found</h1><a href="/blogs" class="btn-primary mt-8">Back to Blogs</a></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' - TopWebExpert';
$metaDescription = excerpt($post['description'], 160);
$ogType = 'article';
$ogImage = '/assets/img/blog/' . $post['image'];
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/seo.php';
$jsonLd = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post['title'],
        'description' => $metaDescription,
        'image' => rtrim(site_settings($conn)['site_url'], '/') . '/assets/img/blog/' . $post['image'],
        'datePublished' => $post['date'],
        'author' => ['@type' => 'Organization', 'name' => 'TopWebExpert'],
        'publisher' => ['@type' => 'Organization', 'name' => 'TopWebExpert'],
        'mainEntityOfPage' => rtrim(site_settings($conn)['site_url'], '/') . '/blog/' . $post['slug'],
    ],
    breadcrumb_schema($conn, ['Home' => '/', 'Blogs' => '/blogs', $post['title'] => '/blog/' . $post['slug']]),
];

$related = [];
$stmt = $conn->prepare("SELECT title, description, image, slug FROM posts WHERE slug != ? AND date <= CURDATE() ORDER BY date DESC, id DESC LIMIT 5");
$stmt->bind_param('s', $slug);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $related[] = $row;

require __DIR__ . '/includes/header.php';
?>

<section class="bg-mist py-12">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-3 gap-10">
    <article class="lg:col-span-2 bg-white rounded-3xl shadow-lg border border-line/60 p-6 md:p-10">
      <p class="text-xs text-ink-soft uppercase tracking-wide"><?= e(date('d M Y', strtotime($post['date']))) ?></p>
      <h1 class="mt-2 text-3xl md:text-4xl font-bold"><?= e($post['title']) ?></h1>
      <div class="w-20 h-1 bg-accent mt-4"></div>
      <?php /* Featured image is this page's LCP element — eager, high priority, sized. */ ?>
      <?php $postImg = '/assets/img/blog/' . $post['image']; ?>
      <img src="<?= e($postImg) ?>" alt="<?= e($post['title']) ?>" class="w-full h-auto mt-8 rounded-2xl shadow-md"<?= img_size_attrs($postImg) ?> fetchpriority="high" decoding="async">
      <div class="prose-mono mt-8"><?= $post['content'] /* trusted HTML from own database */ ?></div>
    </article>

    <aside>
      <h2 class="text-xl font-bold border-b-2 border-accent pb-3">Related Posts</h2>
      <div class="mt-6 space-y-5">
        <?php foreach ($related as $r): ?>
        <a href="/blog/<?= e($r['slug']) ?>" class="flex gap-4 group">
          <img src="<?= e(blog_thumb($r['image'])) ?>" alt="<?= e($r['title']) ?>" class="w-20 h-20 object-cover img-mono border border-line shrink-0" loading="lazy" width="80" height="80">
          <span>
            <span class="block font-semibold text-sm leading-snug group-hover:text-accent transition-colors"><?= e($r['title']) ?></span>
            <span class="block mt-1 text-xs text-ink-soft"><?= e(excerpt($r['description'], 80)) ?></span>
          </span>
        </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
