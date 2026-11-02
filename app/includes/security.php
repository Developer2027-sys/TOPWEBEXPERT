<?php
// Security layer, applied to page routes (not static assets).
// - Security headers on every response
// - Enforces IP blocks set MANUALLY in Admin > Traffic & IPs (the only source of blocks)
// - Refuses the single request for scraper / AI-training user agents and for
//   hack-probe paths (wp-login, .env, .git ...) — without blocking the IP
// - Per-IP / per-minute visit tracking for the admin traffic dashboard
//
// There is deliberately NO automatic IP blocking and NO rate limiter any more.
// The old version blocked an IP for 30 minutes (7 days after 3 strikes) when it
// exceeded 90 requests a minute or requested any old WordPress path; that also hit
// search engine crawlers re-checking URLs from the previous site, so it was removed.
// Search engine crawlers (Googlebot, Bingbot, ...) are never refused: an old
// WordPress path just gets a 410 so the URL drops out of their index.

const IP_STATS_RETENTION_DAYS = 90; // per-IP rows older than this are pruned

function sec_client_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function sec_is_localhost($ip) {
    return in_array($ip, ['127.0.0.1', '::1'], true) && empty($_SERVER['HTTP_X_SEC_TEST']);
}

// Search engine bots we WANT (SEO) — never refused by UA rules
function sec_is_search_bot($ua) {
    return (bool)preg_match('/googlebot|google-inspectiontool|adsbot-google|bingbot|slurp|duckduckbot|baiduspider|yandex(bot)?|applebot/i', $ua);
}

// Scrapers, HTTP libraries and AI-training crawlers — request refused (403)
function sec_is_bad_agent($ua) {
    if ($ua === '') return true; // empty UA on a page request = script
    return (bool)preg_match(
        '/python-requests|python-urllib|scrapy|aiohttp|httpx|go-http-client|okhttp|'
        . 'java\/|libwww|winhttp|curl\/|wget\/|phantomjs|headlesschrome|puppeteer|playwright|selenium|'
        . 'httrack|webcopier|webzip|sitesnagger|nutch|dataminr|'
        . 'gptbot|ccbot|bytespider|petalbot|claudebot|anthropic-ai|google-extended|omgili|diffbot|'
        . 'amazonbot|semrushbot|ahrefsbot|mj12bot|dotbot|blexbot|serpstatbot/i',
        $ua
    );
}

// Attack-probe paths -> request refused (403). Old WordPress *content* paths
// (wp-content uploads, tag/feed pages) are not in this list: the router answers
// them with 410 Gone so search engines and social previews clean up their index.
function sec_is_probe_path($path) {
    return (bool)preg_match(
        '#wp-login|wp-admin|wp-includes|wp-content/.*\.php|xmlrpc\.php|\.env|\.git|\.htaccess|\.htpasswd|'
        . 'phpmyadmin|pma/|adminer|\.sql$|\.bak$|/etc/passwd|eval-stdin|composer\.(json|lock)|'
        . 'config\.php\.|\.DS_Store|web\.config$#i',
        $path
    );
}

function sec_headers() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

function sec_denied_response($conn = null) {
    if ($conn) sec_track_blocked($conn);
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Access denied.";
    exit;
}

function sec_forbidden($conn = null) {
    if ($conn) sec_track_blocked($conn);
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden.";
    exit;
}

// Plain 410 for a search engine re-checking a hack-probe path from the old site's index.
function sec_gone() {
    http_response_code(410);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    echo "Gone.";
    exit;
}

// Main gate. Call with a live mysqli connection and the request path.
function security_gate($conn, $path) {
    sec_headers();

    $ip = sec_client_ip();
    if (sec_is_localhost($ip)) return; // local dev/testing

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isSearchBot = sec_is_search_bot($ua);

    // 1) Manually blocked IP (Admin > Traffic & IPs)? Rows left over from the old
    //    automatic blocking expire on their own (until_at) or can be removed in the admin.
    $stmt = @$conn->prepare("SELECT 1 FROM blocked_ips WHERE ip = ? AND until_at > NOW() LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $ip);
        $stmt->execute();
        if ($stmt->get_result()->fetch_row()) sec_denied_response($conn);
    } elseif ((int)$conn->errno !== 1146) { // 1146 = table not created yet: nothing to check
        db_unavailable();
    }

    // 2) Hack-probe paths: refuse this request only (search engines get 410)
    if (sec_is_probe_path($path)) {
        if ($isSearchBot) sec_gone();
        sec_forbidden($conn);
    }

    // 3) Scraper / AI-training user agents: refuse this request only (search bots exempt)
    if (!$isSearchBot && sec_is_bad_agent($ua)) {
        sec_forbidden($conn);
    }

    // 4) Track the visit (per-IP stats + per-minute counters for the admin traffic dashboard).
    // Admin pages are not tracked; missing tables (before the traffic migration) fail silently.
    if (strpos($path, '/admin') !== 0) {
        sec_track($conn, $ip, $path, $ua);
    }

    // 5) Housekeeping on ~1 in 500 requests so the tracking tables cannot grow without
    // bound (a scraper swarm once left 240,000 single-hit rows in ip_stats).
    if (mt_rand(1, 500) === 1) sec_housekeeping($conn);
}

function sec_housekeeping($conn) {
    @$conn->query("DELETE FROM rate_limits WHERE window_start < (NOW() - INTERVAL 1 HOUR)");
    @$conn->query("DELETE FROM blocked_ips WHERE is_manual = 0 AND until_at < NOW()");
    @$conn->query("DELETE FROM ip_stats WHERE last_seen < (NOW() - INTERVAL " . IP_STATS_RETENTION_DAYS . " DAY) LIMIT 5000");
}

// Per-IP stats + per-minute traffic counters. @-suppressed so the site keeps working
// even if the tables have not been created yet (Admin > Traffic creates them).
function sec_track($conn, $ip, $path, $ua) {
    $stmt = @$conn->prepare("INSERT INTO ip_stats (ip, requests, last_path, last_ua) VALUES (?, 1, ?, ?)
                             ON DUPLICATE KEY UPDATE requests = requests + 1, last_seen = NOW(),
                               last_path = VALUES(last_path), last_ua = VALUES(last_ua)");
    if ($stmt) {
        $p = substr($path, 0, 255);
        $u = substr($ua, 0, 255);
        $stmt->bind_param('sss', $ip, $p, $u);
        @$stmt->execute();
    }
    @$conn->query("INSERT INTO traffic_minutes (minute, hits) VALUES (DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00'), 1)
                   ON DUPLICATE KEY UPDATE hits = hits + 1");
}

// Count denied hits too, so the traffic chart shows blocked/refused volume separately
function sec_track_blocked($conn) {
    @$conn->query("INSERT INTO traffic_minutes (minute, hits, blocked) VALUES (DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00'), 0, 1)
                   ON DUPLICATE KEY UPDATE blocked = blocked + 1");
}

// Record an inquiry against the IP's stats (called from the enquiry endpoints)
function sec_track_inquiry($conn, $ip) {
    $stmt = @$conn->prepare("INSERT INTO ip_stats (ip, requests, inquiries) VALUES (?, 0, 1)
                             ON DUPLICATE KEY UPDATE inquiries = inquiries + 1, last_seen = NOW()");
    if ($stmt) { $stmt->bind_param('s', $ip); @$stmt->execute(); }
}

// Throttle for form endpoints: max $max submissions per IP per $seconds
function sec_form_throttle($conn, $max = 3, $seconds = 60) {
    $ip = sec_client_ip();
    if (sec_is_localhost($ip)) return true;
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM inquiries WHERE ip = ? AND created_at > (NOW() - INTERVAL ? SECOND)");
    if (!$stmt) return true;
    $stmt->bind_param('si', $ip, $seconds);
    $stmt->execute();
    return ((int)$stmt->get_result()->fetch_assoc()['c']) < $max;
}
