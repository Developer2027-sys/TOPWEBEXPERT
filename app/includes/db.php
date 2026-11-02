<?php
// Web root path (works whether invoked via front controller or a CLI script).
// app/includes/db.php -> dirname twice -> web root (php-site/)
if (!defined('WEB_ROOT')) define('WEB_ROOT', dirname(dirname(__DIR__)));

// Load credentials from config.php (gitignored). Falls back to sample for a clear error.
$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    die('Configuration missing: copy app/includes/config.sample.php to config.php and set your credentials.');
}
$config = require $configFile;
function app_config($key = null) {
    static $c = null;
    if ($c === null) $c = require __DIR__ . '/config.php';
    return $key === null ? $c : ($c[$key] ?? null);
}

// "Try again later" response for a database outage (connection refused, too many
// connections, server gone away). Sent as 503 + Retry-After: search engines treat
// that as temporary and keep the page indexed. The old behaviour was a 200 page
// reading "Database connection failed", which Google records as a soft 404 and
// removes from the index.
function db_unavailable($detail = '') {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Database unavailable: $detail\n");
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: 300');
        header('Cache-Control: no-store');
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Temporarily unavailable</title>'
        . '<meta name="robots" content="noindex"></head><body style="font-family:sans-serif;text-align:center;padding:4rem">'
        . '<h1>We&rsquo;ll be right back</h1><p>The site is briefly unavailable. Please try again in a few minutes.</p></body></html>';
    exit;
}

// PHP 8.1+ throws mysqli exceptions by default; this codebase uses falsy-return
// checks (@$conn->query / if(!$stmt)) for schema fallbacks, so use legacy mode.
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    error_log('DB connect failed: ' . $conn->connect_error);
    db_unavailable($conn->connect_error);
}
$conn->set_charset('utf8mb4');
