<?php
// Safe image upload helper. Returns stored filename or null; sets $uploadError on failure.
function handle_image_upload($field, $destDir, &$uploadError = null) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $uploadError = 'Upload failed (error ' . $f['error'] . ').'; return null; }
    if ($f['size'] > 5 * 1024 * 1024) { $uploadError = 'Image too large (max 5 MB).'; return null; }

    $info = @getimagesize($f['tmp_name']);
    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($allowed[$info[2]])) { $uploadError = 'Only JPG, PNG, WEBP or GIF images allowed.'; return null; }

    $ext = $allowed[$info[2]];
    $base = pathinfo($f['name'], PATHINFO_FILENAME);
    $base = preg_replace('/[^a-zA-Z0-9-_]+/', '-', $base);
    $name = time() . '_' . substr($base, 0, 60) . '.' . $ext;

    if (!is_dir($destDir)) mkdir($destDir, 0775, true);
    if (!move_uploaded_file($f['tmp_name'], rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $name)) {
        $uploadError = 'Could not save uploaded file.';
        return null;
    }
    return $name;
}
