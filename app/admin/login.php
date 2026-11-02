<?php
require __DIR__ . '/includes/auth.php';
csrf_check();

if (is_logged_in()) { header('Location: /admin'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (login_blocked($conn, $username, $ip)) {
        $error = 'Too many failed attempts. Try again after 15 minutes.';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            clear_login_attempts($conn, $username);
            session_regenerate_id(true);
            $_SESSION['admin_pending_id'] = (int)$admin['id'];
            unset($_SESSION['admin_id'], $_SESSION['admin_2fa_ok']);
            header('Location: /admin/verify-2fa');
            exit;
        }
        record_failed_login($conn, $username, $ip);
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | TopWebExpert</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="min-h-screen bg-neutral-950 flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <img src="/assets/logo.png" alt="TopWebExpert" class="w-40 mx-auto bg-white rounded-xl p-3">
      <h1 class="mt-6 text-2xl font-bold text-white tracking-wide uppercase">Admin Panel</h1>
      <div class="w-16 h-1 bg-accent mx-auto mt-3"></div>
    </div>
    <div class="bg-neutral-900 border border-neutral-800 p-8">
      <?php if ($error): ?>
        <p class="mb-5 p-3 bg-neutral-800 border-l-4 border-accent text-sm text-white"><?= e($error) ?></p>
      <?php endif; ?>
      <form method="post" action="/admin/login" class="space-y-5" autocomplete="off">
        <?= csrf_field() ?>
        <div>
          <label for="username" class="block text-xs font-semibold uppercase tracking-widest text-neutral-400 mb-2">Username</label>
          <input type="text" id="username" name="username" required autofocus
                 class="w-full bg-neutral-950 border border-neutral-700 text-white px-4 py-3 focus:outline-none focus:border-accent">
        </div>
        <div>
          <label for="password" class="block text-xs font-semibold uppercase tracking-widest text-neutral-400 mb-2">Password</label>
          <input type="password" id="password" name="password" required
                 class="w-full bg-neutral-950 border border-neutral-700 text-white px-4 py-3 focus:outline-none focus:border-accent">
        </div>
        <button type="submit" class="w-full bg-accent text-neutral-950 font-bold uppercase tracking-widest text-sm py-3 hover:bg-white transition-colors">Sign In &rarr;</button>
      </form>
    </div>
    <p class="mt-6 text-center text-neutral-600 text-xs uppercase tracking-widest">Protected by two-factor authentication</p>
  </div>
</body>
</html>
