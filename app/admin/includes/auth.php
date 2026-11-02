<?php
// Admin auth core: secure sessions, CSRF, rate limiting, login guards
// Output buffering lets pages redirect (header Location) even after HTML output has started
ob_start();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/totp.php';

// Hardened session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_name('TWEADMIN');
session_start();

// Idle timeout: 30 minutes
if (isset($_SESSION['admin_last_seen']) && time() - $_SESSION['admin_last_seen'] > 1800) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['admin_last_seen'] = time();

// CSRF
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            http_response_code(403);
            die('Invalid CSRF token. Go back and retry.');
        }
    }
}

// Rate limiting: max 5 failed attempts per username/IP in 15 minutes
function login_blocked($conn, $username, $ip) {
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM login_attempts WHERE (username = ? OR ip = ?) AND attempted_at > (NOW() - INTERVAL 15 MINUTE)");
    $stmt->bind_param('ss', $username, $ip);
    $stmt->execute();
    return ((int)$stmt->get_result()->fetch_assoc()['c']) >= 5;
}
function record_failed_login($conn, $username, $ip) {
    $stmt = $conn->prepare("INSERT INTO login_attempts (username, ip) VALUES (?, ?)");
    $stmt->bind_param('ss', $username, $ip);
    $stmt->execute();
}
function clear_login_attempts($conn, $username) {
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
}

// Guards
function is_logged_in() {
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_2fa_ok']);
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: /admin/login');
        exit;
    }
}
function require_password_stage() {
    // For the 2FA step: password verified but 2FA pending
    if (empty($_SESSION['admin_pending_id'])) {
        header('Location: /admin/login');
        exit;
    }
}
function admin_row($conn) {
    $id = $_SESSION['admin_id'] ?? $_SESSION['admin_pending_id'] ?? 0;
    $stmt = $conn->prepare("SELECT id, username, email, totp_secret, totp_enabled FROM admins WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Flash messages
function flash($msg = null) {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}
