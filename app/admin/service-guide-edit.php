<?php
$adminTitle = 'Edit City Guide';
$activeNav = 'service-guides';
require __DIR__ . '/includes/admin-header.php';

$services = [];
$res = $conn->query("SELECT DISTINCT service_slug, service_name FROM city_pages ORDER BY service_name");
if ($res) while ($r = $res->fetch_assoc()) $services[$r['service_slug']] = $r['service_name'];

$id = (int)($_GET['id'] ?? 0);
$g = ['service_slug' => '', 'slug' => '', 'title' => '', 'content' => '', 'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'sort_order' => 0, 'is_active' => 1];
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM service_blogs WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $g = $stmt->get_result()->fetch_assoc() ?: $g;
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = $_POST['service_slug'] ?? '';
    $slug = slugify($_POST['slug'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDescription = trim($_POST['meta_description'] ?? '');
    $metaKeywords = trim($_POST['meta_keywords'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // Guide slugs must never look like a service+city URL prefix
    $conflict = '';
    foreach (array_keys($services) as $sslug) {
        if ($slug === $sslug || strpos($slug, $sslug . '-') === 0) { $conflict = $sslug; break; }
    }
    if (!isset($services[$service])) {
        $error = 'Choose a service.';
    } elseif ($slug === '' || $title === '' || $content === '') {
        $error = 'Slug, title and content are required.';
    } elseif ($conflict) {
        $error = "Slug cannot start with a service slug ($conflict) — it would clash with city page URLs.";
    } else {
        if ($id) {
            $stmt = $conn->prepare("UPDATE service_blogs SET service_slug=?, slug=?, title=?, content=?, meta_title=?, meta_description=?, meta_keywords=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->bind_param('sssssssiii', $service, $slug, $title, $content, $metaTitle, $metaDescription, $metaKeywords, $sortOrder, $isActive, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO service_blogs (service_slug, slug, title, content, meta_title, meta_description, meta_keywords, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssssii', $service, $slug, $title, $content, $metaTitle, $metaDescription, $metaKeywords, $sortOrder, $isActive);
        }
        if ($stmt->execute()) {
            flash($id ? 'Guide updated for all its cities.' : 'Guide created.');
            header('Location: /admin/service-guides');
            exit;
        }
        $error = 'Save failed — is the slug already used by another guide?';
    }
    $g = ['service_slug' => $service, 'slug' => $slug, 'title' => $title, 'content' => $content,
          'meta_title' => $metaTitle, 'meta_description' => $metaDescription, 'meta_keywords' => $metaKeywords,
          'sort_order' => $sortOrder, 'is_active' => $isActive];
}
?>

<?php if ($error): ?><div class="a-card p-4 mb-6 !border-red-300 bg-red-50 text-sm font-semibold text-red-700"><?= e($error) ?></div><?php endif; ?>

<div class="a-card p-4 mb-6 text-sm text-neutral-500">
  Placeholders: <span class="font-mono bg-neutral-100 px-1.5 py-0.5 rounded">{city}</span> = city name,
  <span class="font-mono bg-neutral-100 px-1.5 py-0.5 rounded">{country}</span> = country,
  <span class="font-mono bg-neutral-100 px-1.5 py-0.5 rounded">{city_slug}</span> = city URL slug (use inside links, e.g.
  <span class="font-mono text-xs">href="/search-engine-optimization-{city_slug}"</span>). They are replaced automatically on every city's version of this guide.
</div>

<form method="post" class="grid lg:grid-cols-3 gap-6">
  <?= csrf_field() ?>
  <div class="lg:col-span-2 a-card p-6 space-y-5">
    <div>
      <label class="a-label">Title (can contain {city})</label>
      <input type="text" name="title" required value="<?= e($g['title']) ?>" class="a-input">
    </div>
    <div>
      <label class="a-label">Guide Slug (URL becomes /slug-city)</label>
      <input type="text" name="slug" required value="<?= e($g['slug']) ?>" placeholder="e.g. seo-pricing-explained" class="a-input font-mono text-sm">
    </div>
    <div>
      <div class="flex justify-between items-center mb-2">
        <label class="a-label !mb-0">Content (1500–2000 words recommended)</label>
        <button type="button" id="toggle-html" class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 border border-neutral-300 rounded-lg hover:border-accent hover:text-accent-dark">HTML view</button>
      </div>
      <link rel="stylesheet" href="/libs/quill.snow.css">
      <div id="quill-editor" class="bg-white" style="min-height:26rem"></div>
      <textarea name="content" id="content-field" rows="20" class="hidden w-full border border-neutral-300 px-4 py-3 font-mono text-xs focus:outline-none focus:border-accent"><?= e($g['content']) ?></textarea>
    </div>
  </div>
  <div class="space-y-6 self-start">
    <div class="a-card p-6 space-y-5">
      <div>
        <label class="a-label">Service</label>
        <select name="service_slug" required class="a-input">
          <option value="">Choose service...</option>
          <?php foreach ($services as $slug => $name): ?>
            <option value="<?= e($slug) ?>" <?= $g['service_slug'] === $slug ? 'selected' : '' ?>><?= e($name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="a-label">Meta Title (max ~60 chars, can use {city})</label>
        <input type="text" name="meta_title" value="<?= e($g['meta_title']) ?>" class="a-input">
      </div>
      <div>
        <label class="a-label">Meta Description (140–158 chars, can use {city})</label>
        <textarea name="meta_description" rows="3" class="a-input"><?= e($g['meta_description']) ?></textarea>
      </div>
      <div>
        <label class="a-label">Meta Keywords (comma separated, can use {city})</label>
        <textarea name="meta_keywords" rows="3" class="a-input"><?= e($g['meta_keywords']) ?></textarea>
      </div>
      <div class="flex items-center gap-6">
        <div class="flex-1">
          <label class="a-label">Sort Order</label>
          <input type="number" name="sort_order" value="<?= (int)$g['sort_order'] ?>" class="a-input">
        </div>
        <label class="flex items-center gap-2 mt-5 text-sm font-semibold cursor-pointer">
          <input type="checkbox" name="is_active" <?= $g['is_active'] ? 'checked' : '' ?>> Live
        </label>
      </div>
      <button type="submit" class="a-btn-dark w-full"><?= $id ? 'Update Guide' : 'Create Guide' ?></button>
      <a href="/admin/service-guides" class="a-btn-ghost w-full">Cancel</a>
    </div>
  </div>
</form>

<script src="/libs/quill.js"></script>
<script>
  var contentField = document.getElementById('content-field');
  var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: { toolbar: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link', 'blockquote'], ['clean']] }
  });
  quill.clipboard.dangerouslyPasteHTML(contentField.value);
  contentField.closest('form').addEventListener('submit', function () {
    if (contentField.classList.contains('hidden')) contentField.value = quill.root.innerHTML;
  });
  var htmlMode = false;
  document.getElementById('toggle-html').addEventListener('click', function () {
    htmlMode = !htmlMode;
    var editorBox = document.getElementById('quill-editor');
    var toolbar = document.querySelector('.ql-toolbar');
    if (htmlMode) {
      contentField.value = quill.root.innerHTML;
      contentField.classList.remove('hidden');
      editorBox.style.display = 'none';
      if (toolbar) toolbar.style.display = 'none';
      this.textContent = 'Editor view';
    } else {
      quill.clipboard.dangerouslyPasteHTML(contentField.value);
      contentField.classList.add('hidden');
      editorBox.style.display = '';
      if (toolbar) toolbar.style.display = '';
      this.textContent = 'HTML view';
    }
  });
</script>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
