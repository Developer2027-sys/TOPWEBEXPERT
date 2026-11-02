<?php
require __DIR__ . '/includes/auth.php';
csrf_check();
require_password_stage();

$admin = admin_row($conn);
if ($admin['totp_enabled']) { header('Location: /admin/verify-2fa'); exit; }

// Generate a secret once per setup session
if (empty($_SESSION['setup_totp_secret'])) {
    $_SESSION['setup_totp_secret'] = totp_generate_secret();
}
$secret = $_SESSION['setup_totp_secret'];
$uri = totp_uri($secret, $admin['username']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (totp_verify($secret, $_POST['code'] ?? '')) {
        $stmt = $conn->prepare("UPDATE admins SET totp_secret = ?, totp_enabled = 1 WHERE id = ?");
        $stmt->bind_param('si', $secret, $admin['id']);
        $stmt->execute();
        unset($_SESSION['setup_totp_secret']);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_2fa_ok'] = true;
        unset($_SESSION['admin_pending_id']);
        flash('Google Authenticator enabled successfully. Welcome!');
        header('Location: /admin');
        exit;
    }
    $error = 'Code did not match. Scan the QR again and enter the current code.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set Up Two-Factor | TopWebExpert Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="min-h-screen bg-neutral-950 flex items-center justify-center p-4">
  <div class="w-full max-w-lg">
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-white tracking-wide uppercase">Set Up Google Authenticator</h1>
      <p class="mt-2 text-neutral-500 text-sm">One-time security setup for your admin account</p>
      <div class="w-16 h-1 bg-accent mx-auto mt-4"></div>
    </div>
    <div class="bg-neutral-900 border border-neutral-800 p-8">
      <ol class="text-neutral-400 text-sm space-y-2 list-decimal pl-5">
        <li>Install <span class="text-white font-semibold">Google Authenticator</span> on your phone</li>
        <li>Scan this QR code (or enter the key manually)</li>
        <li>Enter the 6-digit code below to activate</li>
      </ol>
      <div class="mt-6 flex justify-center">
        <div id="qrcode" class="bg-white p-4"></div>
      </div>
      <p class="mt-5 text-center text-xs text-neutral-500 uppercase tracking-widest">Manual key</p>
      <p class="mt-1 text-center font-mono text-accent text-sm break-all select-all"><?= e(trim(chunk_split($secret, 4, ' '))) ?></p>

      <?php if ($error): ?>
        <p class="mt-6 p-3 bg-neutral-800 border-l-4 border-accent text-sm text-white"><?= e($error) ?></p>
      <?php endif; ?>
      <form method="post" action="/admin/setup-2fa" class="mt-6 space-y-4" autocomplete="off">
        <?= csrf_field() ?>
        <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required
               placeholder="000000"
               class="w-full bg-neutral-950 border border-neutral-700 text-white text-center text-3xl tracking-[0.5em] px-4 py-4 focus:outline-none focus:border-accent">
        <button type="submit" class="w-full bg-accent text-neutral-950 font-bold uppercase tracking-widest text-sm py-3 hover:bg-white transition-colors">Activate 2FA &rarr;</button>
      </form>
      <a href="/admin/logout" class="block mt-5 text-center text-neutral-500 text-xs uppercase tracking-widest hover:text-accent">Cancel</a>
    </div>
  </div>
  <script src="/libs/qrcode.js"></script>
  <script>
    var qr = qrcode(0, 'M');
    qr.addData(<?= json_encode($uri) ?>);
    qr.make();
    document.getElementById('qrcode').innerHTML = qr.createSvgTag({ cellSize: 5, margin: 0 });
  </script>
</body>
</html>
