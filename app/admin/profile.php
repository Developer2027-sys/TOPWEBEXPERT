<?php
$adminTitle = 'My Profile';
$activeNav = 'profile';
require __DIR__ . '/includes/admin-header.php';

$error = '';
if (($_POST['action'] ?? '') === 'password') {
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->bind_param('i', $me['id']);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['password'];

    if (!password_verify($current, $hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 10) {
        $error = 'New password must be at least 10 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $newHash, $me['id']);
        $stmt->execute();
        flash('Password changed successfully.');
        header('Location: /admin/profile');
        exit;
    }
}

if (($_POST['action'] ?? '') === 'reset2fa') {
    $current = $_POST['current2'] ?? '';
    $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->bind_param('i', $me['id']);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['password'];
    if (!password_verify($current, $hash)) {
        $error = 'Password is incorrect — 2FA was not reset.';
    } else {
        $stmt = $conn->prepare("UPDATE admins SET totp_secret = NULL, totp_enabled = 0 WHERE id = ?");
        $stmt->bind_param('i', $me['id']);
        $stmt->execute();
        session_unset();
        session_destroy();
        header('Location: /admin/login');
        exit;
    }
}
?>

<?php if ($error): ?><div class="mb-6 p-4 bg-white border-l-4 border-red-500 text-sm font-semibold"><?= e($error) ?></div><?php endif; ?>

<div class="grid lg:grid-cols-2 gap-6 max-w-4xl">
  <div class="bg-white border border-neutral-200 p-6">
    <h2 class="font-bold uppercase tracking-wide text-sm mb-5 border-b border-neutral-200 pb-3">Change Password</h2>
    <form method="post" class="space-y-4" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Current Password</label>
        <input type="password" name="current" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">New Password (min 10 chars)</label>
        <input type="password" name="new" required minlength="10" class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Confirm New Password</label>
        <input type="password" name="confirm" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <button class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Update Password</button>
    </form>
  </div>

  <div class="bg-white border border-neutral-200 p-6 self-start">
    <h2 class="font-bold uppercase tracking-wide text-sm mb-5 border-b border-neutral-200 pb-3">Two-Factor Authentication</h2>
    <p class="text-sm text-neutral-600">Status:
      <?php if ($me['totp_enabled']): ?>
        <span class="px-2 py-1 bg-accent text-neutral-950 text-xs font-bold uppercase tracking-widest">Enabled</span>
      <?php else: ?>
        <span class="px-2 py-1 bg-neutral-200 text-neutral-600 text-xs font-bold uppercase tracking-widest">Not set up</span>
      <?php endif; ?>
    </p>
    <p class="mt-4 text-sm text-neutral-500">Resetting 2FA will log you out. On next login you will scan a new QR code with Google Authenticator (use this if you changed phones).</p>
    <form method="post" class="mt-5 space-y-4" onsubmit="return confirm('Reset 2FA and log out now?')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="reset2fa">
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2">Confirm with Password</label>
        <input type="password" name="current2" required class="w-full border border-neutral-300 px-4 py-3 focus:outline-none focus:border-accent">
      </div>
      <button class="w-full py-3 border-2 border-neutral-950 text-xs font-bold uppercase tracking-widest hover:bg-neutral-950 hover:text-accent transition-colors">Reset 2FA</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
