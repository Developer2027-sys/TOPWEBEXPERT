<?php
$adminTitle = 'Edit Blog';
$activeNav = 'blogs';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/includes/upload.php';

$id = (int)($_GET['id'] ?? 0);
$post = ['title' => '', 'slug' => '', 'description' => '', 'content' => '', 'date' => date('Y-m-d'), 'image' => ''];
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc() ?: $post;
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = slugify($_POST['slug'] ?: $title);
    $description = trim($_POST['description'] ?? '');
    $content = $_POST['content'] ?? '';
    $date = $_POST['date'] ?: date('Y-m-d');

    $uploadError = null;
    $newImage = handle_image_upload('image', WEB_ROOT . '/assets/img/blog', $uploadError);
    $image = $newImage ?: ($post['image'] ?? '');

    if ($uploadError) {
        $error = $uploadError;
    } elseif ($title === '' || $description === '' || $content === '') {
        $error = 'Title, description and content are required.';
    } elseif (!$id && $image === '') {
        $error = 'Please upload a featured image.';
    } else {
        if ($id) {
            $stmt = $conn->prepare("UPDATE posts SET title=?, slug=?, description=?, content=?, date=?, image=? WHERE id=?");
            $stmt->bind_param('ssssssi', $title, $slug, $description, $content, $date, $image, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO posts (title, slug, description, content, date, image) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('ssssss', $title, $slug, $description, $content, $date, $image);
        }
        if ($stmt->execute()) {
            flash($id ? 'Post updated.' : 'Post created.');
            header('Location: /admin/blogs');
            exit;
        }
        $error = 'Save failed (duplicate slug?).';
    }
    $post = compact('title', 'slug', 'description', 'content', 'date', 'image');
}
?>

<?php if ($error): ?><div class="mb-6 p-4 bg-white border-l-4 border-red-500 text-sm font-semibold"><?= e($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
  <?= csrf_field() ?>
  <div class="lg:col-span-2 bg-white border border-neutral-200 p-6 space-y-5">
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Title</label>
      <input type="text" name="title" required value="<?= e($post['title']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
    </div>
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Slug (URL)</label>
      <input type="text" name="slug" value="<?= e($post['slug']) ?>" placeholder="auto-generated from title" class="w-full border border-neutral-300 px-4 py-3 font-mono text-sm focus:outline-none focus:border-accent">
    </div>
    <div>
      <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Short Description (meta description)</label>
      <textarea name="description" rows="3" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent"><?= e($post['description']) ?></textarea>
    </div>
    <div>
      <div class="flex justify-between items-center mb-2">
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500">Content</label>
        <button type="button" id="toggle-html" class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 border border-neutral-300 hover:border-accent hover:text-accent-dark">HTML view</button>
      </div>
      <link rel="stylesheet" href="/libs/quill.snow.css">
      <div id="quill-editor" class="bg-white" style="min-height:22rem"></div>
      <textarea name="content" id="content-field" rows="16" class="hidden w-full border border-neutral-300 px-4 py-3 font-mono text-sm focus:outline-none focus:border-accent"><?= e($post['content']) ?></textarea>
    </div>
  </div>
  <div class="space-y-6">
    <div class="bg-white border border-neutral-200 p-6 space-y-5">
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Publish Date</label>
        <input type="date" name="date" value="<?= e($post['date']) ?>" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Featured Image</label>
        <?php if (!empty($post['image'])): ?>
          <img src="/assets/img/blog/<?= e($post['image']) ?>" alt="" class="w-full h-36 object-cover border border-neutral-200 mb-3">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="w-full text-sm">
      </div>
      <button type="submit" class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors"><?= $id ? 'Update Post' : 'Publish Post' ?></button>
      <a href="/admin/blogs" class="block text-center py-3 border border-neutral-300 text-xs font-bold uppercase tracking-widest hover:border-accent">Cancel</a>
    </div>
  </div>
</form>

<script src="/libs/quill.js"></script>
<script>
  var contentField = document.getElementById('content-field');
  var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }],
        ['link', 'image', 'blockquote', 'code-block'],
        ['clean']
      ]
    }
  });
  quill.clipboard.dangerouslyPasteHTML(contentField.value);

  var form = contentField.closest('form');
  form.addEventListener('submit', function () {
    if (contentField.classList.contains('hidden')) {
      contentField.value = quill.root.innerHTML;
    }
  });

  // Toggle between rich editor and raw HTML view
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
