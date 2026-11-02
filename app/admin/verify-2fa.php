<?php
require __DIR__ . '/includes/auth.php';
csrf_check();
require_password_stage();

$admin = admin_row($conn);

// First login: 2FA not set up yet -> setup flow
if (!$admin['totp_enabled']) {
    header('Location: /admin/setup-2fa');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (login_blocked($conn, $admin['username'], $ip)) {
        $error = 'Too many failed attempts. Try again after 15 minutes.';
    } elseif (totp_verify($admin['totp_secret'], $code)) {
        clear_login_attempts($conn, $admin['username']);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_2fa_ok'] = true;
        unset($_SESSION['admin_pending_id']);
        header('Location: /admin');
        exit;
    } else {
        record_failed_login($conn, $admin['username'], $ip);
        $error = 'Invalid authenticator code. Try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Two-Factor Verification | TopWebExpert Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="min-h-screen bg-neutral-950 flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto bg-neutral-900 border border-accent flex items-center justify-center">
        <svg class="w-8 h-8 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="1"/><path d="M8 10V7a4 4 0 018 0v3"/><circle cx="12" cy="15" r="1.5" fill="currentColor"/></svg>
      </div>
      <h1 class="mt-6 text-2xl font-bold text-white tracking-wide uppercase">Two-Factor Verification</h1>
      <p class="mt-2 text-neutral-500 text-sm">Enter the 6-digit code from your Google Authenticator app</p>
    </div>
    <div class="bg-neutral-900 border border-neutral-800 p-8">
      <?php if ($error): ?>
        <p class="mb-5 p-3 bg-neutral-800 border-l-4 border-accent text-sm text-white"><?= e($error) ?></p>
      <?php endif; ?>
      <form method="post" action="/admin/verify-2fa" class="space-y-5" autocomplete="off">
        <?= csrf_field() ?>
        <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus
               placeholder="000000"
               class="w-full bg-neutral-950 border border-neutral-700 text-white text-center text-3xl tracking-[0.5em] px-4 py-4 focus:outline-none focus:border-accent">
        <button type="submit" class="w-full bg-accent text-neutral-950 font-bold uppercase tracking-widest text-sm py-3 hover:bg-white transition-colors">Verify &rarr;</button>
      </form>
      <a href="/admin/logout" class="block mt-5 text-center text-neutral-500 text-xs uppercase tracking-widest hover:text-accent">&larr; Back to login</a>
    </div>
  </div>
</body>
</html>
