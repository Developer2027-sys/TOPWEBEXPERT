<?php
// Traffic & IP monitor: live request graph, per-IP stats, manual + bulk IP blocking.
require_once __DIR__ . '/includes/auth.php';
require_login();

// Admin-only page: surface PHP errors instead of a white screen (helps diagnose hosting issues)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ---- One-time auto-migration (tables + blocked_ips upgrade) ----
function traffic_migrate($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS ip_stats (
      ip VARCHAR(45) PRIMARY KEY,
      first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      requests INT NOT NULL DEFAULT 0,
      inquiries INT NOT NULL DEFAULT 0,
      last_path VARCHAR(255) NULL,
      last_ua VARCHAR(255) NULL,
      INDEX idx_last_seen (last_seen),
      INDEX idx_requests (requests)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    $conn->query("CREATE TABLE IF NOT EXISTS traffic_minutes (
      minute DATETIME PRIMARY KEY,
      hits INT NOT NULL DEFAULT 0,
      blocked INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    // Daily rollups keep long-range history (weekly/monthly/yearly charts)
    // after the per-minute rows are pruned at 7 days
    $conn->query("CREATE TABLE IF NOT EXISTS traffic_days (
      day DATE PRIMARY KEY,
      hits INT NOT NULL DEFAULT 0,
      blocked INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    // Tables created at different times can get different utf8mb4 collations
    // (newer MariaDB defaults to uca1400_ai_ci) — that breaks the
    // ip_stats <-> blocked_ips join with "Illegal mix of collations".
    // Normalise every traffic table to utf8mb4_general_ci once.
    foreach (['ip_stats', 'blocked_ips', 'rate_limits', 'traffic_minutes', 'traffic_days'] as $t) {
        $res = $conn->query("SELECT table_collation FROM information_schema.tables
                             WHERE table_schema = DATABASE() AND table_name = '$t'");
        if ($res && ($row = $res->fetch_row()) && $row[0] !== null && strcasecmp($row[0], 'utf8mb4_general_ci') !== 0) {
            $conn->query("ALTER TABLE $t CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        }
    }
    foreach (['strikes' => "INT NOT NULL DEFAULT 1", 'is_manual' => "TINYINT(1) NOT NULL DEFAULT 0"] as $col => $def) {
        $res = $conn->query("SHOW COLUMNS FROM blocked_ips LIKE '$col'");
        if ($res && !$res->num_rows) $conn->query("ALTER TABLE blocked_ips ADD COLUMN $col $def");
    }
    // TIMESTAMP tops out at year 2038 — permanent manual blocks need DATETIME
    $res = $conn->query("SHOW COLUMNS FROM blocked_ips LIKE 'until_at'");
    if ($res && ($c = $res->fetch_assoc()) && stripos($c['Type'], 'datetime') === false) {
        $conn->query("ALTER TABLE blocked_ips MODIFY until_at DATETIME NOT NULL");
    }
    // Geolocation cache columns (looked up lazily when the admin views this page)
    $res = $conn->query("SHOW COLUMNS FROM ip_stats LIKE 'geo_done'");
    if ($res && !$res->num_rows) {
        $conn->query("ALTER TABLE ip_stats
                      ADD COLUMN geo_country VARCHAR(80) NULL,
                      ADD COLUMN geo_city VARCHAR(80) NULL,
                      ADD COLUMN geo_isp VARCHAR(120) NULL,
                      ADD COLUMN geo_done TINYINT(1) NOT NULL DEFAULT 0");
    }
    // Housekeeping: roll full days older than 7 days into traffic_days (overwrite
    // semantics = idempotent), then prune those minute rows; stale rate windows;
    // expired temp blocks
    $conn->query("INSERT INTO traffic_days (day, hits, blocked)
                  SELECT DATE(minute), SUM(hits), SUM(blocked) FROM traffic_minutes
                  WHERE minute < (CURDATE() - INTERVAL 7 DAY) GROUP BY DATE(minute)
                  ON DUPLICATE KEY UPDATE hits = VALUES(hits), blocked = VALUES(blocked)");
    $conn->query("DELETE FROM traffic_minutes WHERE minute < (CURDATE() - INTERVAL 7 DAY)");
    $conn->query("DELETE FROM rate_limits WHERE window_start < (NOW() - INTERVAL 1 HOUR)");
    $conn->query("DELETE FROM blocked_ips WHERE is_manual = 0 AND until_at < NOW()");

    // Verify the tables actually exist (a hosting DB user without CREATE rights would fail silently)
    return $conn->query("SELECT 1 FROM ip_stats LIMIT 0") && $conn->query("SELECT 1 FROM traffic_minutes LIMIT 0")
        && $conn->query("SELECT 1 FROM traffic_days LIMIT 0");
}
$trafficReady = traffic_migrate($conn);
$dbError = $trafficReady ? '' : $conn->error;

// Safe one-value query helper — never fatals if a table/query is unavailable
function tq($conn, $sql, $default = 0) {
    $res = $conn->query($sql);
    if (!$res) return $default;
    $row = $res->fetch_row();
    return $row !== null ? $row[0] : $default;
}

// Lazily look up country/city/ISP for IPs shown on this page (one batch call to
// ip-api.com, results cached in ip_stats so each IP is looked up only once).
function traffic_geo_fill($conn, &$rows) {
    $pending = [];
    foreach ($rows as $i => $r) {
        if (!empty($r['geo_done'])) continue;
        if (filter_var($r['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            // localhost / LAN ranges — nothing to geolocate
            $conn->query("UPDATE ip_stats SET geo_country = 'Local network', geo_done = 1 WHERE ip = '" . $conn->real_escape_string($r['ip']) . "'");
            $rows[$i]['geo_country'] = 'Local network';
            $rows[$i]['geo_done'] = 1;
            continue;
        }
        $pending[$r['ip']] = $i;
        if (count($pending) >= 90) break; // ip-api batch limit is 100
    }
    if (!$pending || !function_exists('curl_init')) return;

    $payload = json_encode(array_map(fn($ip) => ['query' => $ip, 'fields' => 'status,query,country,city,isp'], array_keys($pending)));
    $ch = curl_init('http://ip-api.com/batch');
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6, CURLOPT_CONNECTTIMEOUT => 4,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = $resp ? json_decode($resp, true) : null;
    if (!is_array($data)) return; // lookup service unreachable — retried on next page view

    foreach ($data as $g) {
        $ip = $g['query'] ?? '';
        if (!isset($pending[$ip])) continue;
        $country = ($g['status'] ?? '') === 'success' ? substr($g['country'] ?? '', 0, 80) : 'Unknown';
        $city = substr($g['city'] ?? '', 0, 80);
        $isp = substr($g['isp'] ?? '', 0, 120);
        $conn->query("UPDATE ip_stats SET geo_country = '" . $conn->real_escape_string($country) . "',
                      geo_city = '" . $conn->real_escape_string($city) . "',
                      geo_isp = '" . $conn->real_escape_string($isp) . "',
                      geo_done = 1 WHERE ip = '" . $conn->real_escape_string($ip) . "'");
        $i = $pending[$ip];
        $rows[$i]['geo_country'] = $country;
        $rows[$i]['geo_city'] = $city;
        $rows[$i]['geo_isp'] = $isp;
        $rows[$i]['geo_done'] = 1;
    }
}

// ---- Live JSON feed for the charts/tiles (GET, session-authenticated) ----
if (isset($_GET['json'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    // Anchor the time grid to MySQL's clock — PHP and MySQL timezones can differ
    $dbNow = strtotime($conn->query("SELECT NOW() n")->fetch_assoc()['n']);
    $range = $_GET['range'] ?? 'minutes';

    // Zero-filled per-day series; merges live minute rows with traffic_days rollups
    $dailySeries = function ($fromDay, $toDay, $labelFmt) use ($conn) {
        $grid = [];
        for ($t = strtotime($fromDay . ' 12:00'); $t <= strtotime($toDay . ' 12:00'); $t += 86400) $grid[date('Y-m-d', $t)] = ['h' => 0, 'b' => 0];
        $f = $conn->real_escape_string($fromDay);
        $to = $conn->real_escape_string($toDay);
        $res = $conn->query("SELECT d, SUM(h) h, SUM(b) b FROM (
                               SELECT DATE(minute) d, hits h, blocked b FROM traffic_minutes WHERE minute >= '$f 00:00:00' AND minute <= '$to 23:59:59'
                               UNION ALL
                               SELECT day d, hits, blocked FROM traffic_days WHERE day BETWEEN '$f' AND '$to'
                             ) t GROUP BY d");
        if ($res) while ($r = $res->fetch_assoc()) if (isset($grid[$r['d']])) $grid[$r['d']] = ['h' => (int)$r['h'], 'b' => (int)$r['b']];
        $out = [];
        foreach ($grid as $d => $v) $out[] = [date($labelFmt, strtotime($d . ' 12:00')), $v['h'], $v['b']];
        return $out;
    };
    // Zero-filled per-month series between two YYYY-MM months (inclusive)
    $monthlySeries = function ($fromMonth, $toMonth) use ($conn) {
        $grid = [];
        for ($t = strtotime($fromMonth . '-01'); $t <= strtotime($toMonth . '-01'); $t = strtotime(date('Y-m-01', $t) . ' +1 month')) $grid[date('Y-m', $t)] = ['h' => 0, 'b' => 0];
        $f = $conn->real_escape_string($fromMonth);
        $to = $conn->real_escape_string($toMonth);
        $res = $conn->query("SELECT ym, SUM(h) h, SUM(b) b FROM (
                               SELECT DATE_FORMAT(minute, '%Y-%m') ym, hits h, blocked b FROM traffic_minutes WHERE DATE_FORMAT(minute, '%Y-%m') BETWEEN '$f' AND '$to'
                               UNION ALL
                               SELECT DATE_FORMAT(day, '%Y-%m') ym, hits, blocked FROM traffic_days WHERE DATE_FORMAT(day, '%Y-%m') BETWEEN '$f' AND '$to'
                             ) t GROUP BY ym");
        if ($res) while ($r = $res->fetch_assoc()) if (isset($grid[$r['ym']])) $grid[$r['ym']] = ['h' => (int)$r['h'], 'b' => (int)$r['b']];
        $out = [];
        foreach ($grid as $m => $v) $out[] = [date('M Y', strtotime($m . '-01')), $v['h'], $v['b']];
        return $out;
    };

    if ($range === 'hours') {
        $hours = [];
        for ($i = 23; $i >= 0; $i--) $hours[date('Y-m-d H:00:00', $dbNow - $i * 3600)] = ['h' => 0, 'b' => 0];
        $res = $conn->query("SELECT DATE_FORMAT(minute, '%Y-%m-%d %H:00:00') hr, SUM(hits) h, SUM(blocked) b
                             FROM traffic_minutes WHERE minute > (NOW() - INTERVAL 25 HOUR) GROUP BY hr");
        if ($res) while ($r = $res->fetch_assoc()) if (isset($hours[$r['hr']])) $hours[$r['hr']] = ['h' => (int)$r['h'], 'b' => (int)$r['b']];
        $series = [];
        foreach ($hours as $k => $v) $series[] = [substr($k, 11, 5), $v['h'], $v['b']];
    } elseif ($range === 'week') {
        $series = $dailySeries(date('Y-m-d', $dbNow - 6 * 86400), date('Y-m-d', $dbNow), 'D d M');
    } elseif ($range === 'month') {
        $series = $dailySeries(date('Y-m-d', $dbNow - 29 * 86400), date('Y-m-d', $dbNow), 'd M');
    } elseif ($range === 'year') {
        $series = $monthlySeries(date('Y-m', strtotime(date('Y-m-01', $dbNow) . ' -11 months')), date('Y-m', $dbNow));
    } elseif ($range === 'custom') {
        $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-d', $dbNow - 29 * 86400);
        $to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : date('Y-m-d', $dbNow);
        if (strtotime($from) > strtotime($to)) [$from, $to] = [$to, $from];
        $span = (strtotime($to) - strtotime($from)) / 86400;
        if ($span > 1830) $from = date('Y-m-d', strtotime($to) - 1830 * 86400); // cap at ~5 years
        if ($span > 92) {
            $series = $monthlySeries(date('Y-m', strtotime($from)), date('Y-m', strtotime($to)));
        } else {
            $series = $dailySeries($from, $to, 'd M');
        }
    } else { // minutes (default)
        $range = 'minutes';
        $minutes = [];
        for ($i = 59; $i >= 0; $i--) $minutes[date('Y-m-d H:i:00', $dbNow - $i * 60)] = ['h' => 0, 'b' => 0];
        $res = $conn->query("SELECT minute, hits, blocked FROM traffic_minutes WHERE minute > (NOW() - INTERVAL 61 MINUTE)");
        if ($res) while ($r = $res->fetch_assoc()) if (isset($minutes[$r['minute']])) $minutes[$r['minute']] = ['h' => (int)$r['hits'], 'b' => (int)$r['blocked']];
        $series = [];
        foreach ($minutes as $k => $v) $series[] = [substr($k, 11, 5), $v['h'], $v['b']];
    }

    $totalH = 0;
    $totalB = 0;
    $peak = 0;
    foreach ($series as $p) {
        $totalH += $p[1];
        $totalB += $p[2];
        $peak = max($peak, $p[1] + $p[2]);
    }
    $summary = [
        'total' => $totalH,
        'blocked' => $totalB,
        'peak' => $peak,
        // Requests landing in the current minute — the "live" counter
        'live' => (int)tq($conn, "SELECT hits FROM traffic_minutes WHERE minute = DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:00')"),
    ];

    $tiles = [
        'today' => (int)tq($conn, "SELECT COALESCE(SUM(hits),0) FROM traffic_minutes WHERE minute >= CURDATE()"),
        'blocked_today' => (int)tq($conn, "SELECT COALESCE(SUM(blocked),0) FROM traffic_minutes WHERE minute >= CURDATE()"),
        'visitors' => (int)tq($conn, "SELECT COUNT(*) FROM ip_stats WHERE last_seen >= CURDATE()"),
        'inquiries' => (int)tq($conn, "SELECT COUNT(*) FROM inquiries WHERE created_at >= CURDATE()"),
        'blocked_now' => (int)tq($conn, "SELECT COUNT(*) FROM blocked_ips WHERE until_at > NOW()"),
    ];
    echo json_encode(['series' => $series, 'range' => $range, 'summary' => $summary, 'tiles' => $tiles]);
    exit;
}

$adminTitle = 'Traffic & IPs';
$activeNav = 'traffic';
require __DIR__ . '/includes/admin-header.php';

$backQuery = $_GET ? '?' . http_build_query($_GET) : '';
$FOREVER = '2099-01-01 00:00:00';

// ---- Manual block (by IP typed in, or from a row) ----
if (($_POST['action'] ?? '') === 'block') {
    $ip = trim($_POST['ip'] ?? '');
    $reason = trim($_POST['reason'] ?? '') ?: 'manual block';
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        $r = $conn->real_escape_string('manual: ' . substr($reason, 0, 100));
        $ipe = $conn->real_escape_string($ip);
        $ok = $conn->query("INSERT INTO blocked_ips (ip, reason, until_at, strikes, is_manual) VALUES ('$ipe', '$r', '$FOREVER', 1, 1)
                            ON DUPLICATE KEY UPDATE reason = VALUES(reason), until_at = VALUES(until_at), is_manual = 1");
        // Pre-migration schema on the server (no strikes/is_manual columns yet)
        if (!$ok) $ok = $conn->query("INSERT INTO blocked_ips (ip, reason, until_at) VALUES ('$ipe', '$r', '$FOREVER')
                                      ON DUPLICATE KEY UPDATE reason = VALUES(reason), until_at = VALUES(until_at)");
        flash($ok ? "IP $ip blocked permanently (unblock anytime below)." : "Could not block $ip — database said: " . $conn->error);
    } else {
        flash('Enter a valid IP address.');
    }
    header('Location: /admin/traffic' . $backQuery);
    exit;
}

// ---- Unblock ----
if (($_POST['action'] ?? '') === 'unblock') {
    $ip = trim($_POST['ip'] ?? '');
    $conn->query("DELETE FROM blocked_ips WHERE ip = '" . $conn->real_escape_string($ip) . "'");
    flash("IP $ip unblocked.");
    header('Location: /admin/traffic' . $backQuery);
    exit;
}

// ---- Bulk actions on selected IPs ----
if (($_POST['action'] ?? '') === 'bulk') {
    $ips = array_values(array_filter((array)($_POST['ips'] ?? []), fn($v) => filter_var($v, FILTER_VALIDATE_IP)));
    $do = $_POST['bulk_do'] ?? '';
    $inList = "'" . implode("','", array_map(fn($ip) => $conn->real_escape_string($ip), $ips)) . "'";
    if (!$ips) {
        flash('No IPs selected.');
    } elseif ($do === 'block') {
        $done = 0;
        foreach ($ips as $ip) {
            $ipe = $conn->real_escape_string($ip);
            $ok = $conn->query("INSERT INTO blocked_ips (ip, reason, until_at, strikes, is_manual) VALUES ('$ipe', 'manual: bulk block', '$FOREVER', 1, 1)
                                ON DUPLICATE KEY UPDATE reason = VALUES(reason), until_at = VALUES(until_at), is_manual = 1");
            if (!$ok) $ok = $conn->query("INSERT INTO blocked_ips (ip, reason, until_at) VALUES ('$ipe', 'manual: bulk block', '$FOREVER')
                                          ON DUPLICATE KEY UPDATE reason = VALUES(reason), until_at = VALUES(until_at)");
            if ($ok) $done++;
        }
        flash($done === count($ips) ? "$done IPs blocked." : "$done of " . count($ips) . ' IPs blocked — database said: ' . $conn->error);
    } elseif ($do === 'unblock') {
        $conn->query("DELETE FROM blocked_ips WHERE ip IN ($inList)");
        flash($conn->affected_rows . ' IPs unblocked.');
    } elseif ($do === 'clear') {
        $conn->query("DELETE FROM ip_stats WHERE ip IN ($inList)");
        flash($conn->affected_rows . ' IP records cleared.');
    } else {
        flash('Choose a bulk action first.');
    }
    header('Location: /admin/traffic' . $backQuery);
    exit;
}

// ---- IP table: view filter + search + sort + pagination ----
// Plain escaped queries (same mechanism as the tiles/chips/graph) so the table
// can never silently disagree with the counts around it; any SQL error is
// captured and shown in the table area instead of a false "no traffic yet".
$view = in_array($_GET['view'] ?? '', ['blocked', 'top'], true) ? $_GET['view'] : 'all';
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// The server schema can drift (failed ALTERs on shared hosting) — adapt to what exists
$ipCols = $bCols = [];
if ($res = $conn->query("SHOW COLUMNS FROM ip_stats")) while ($c = $res->fetch_assoc()) $ipCols[$c['Field']] = true;
if ($res = $conn->query("SHOW COLUMNS FROM blocked_ips")) while ($c = $res->fetch_assoc()) $bCols[$c['Field']] = true;
$hasGeo = isset($ipCols['geo_country']);

$where = [];
if ($q !== '') {
    $like = "'%" . $conn->real_escape_string($q) . "%'";
    $geoSql = $hasGeo ? " OR s.geo_country LIKE $like OR s.geo_city LIKE $like OR s.geo_isp LIKE $like" : '';
    $where[] = "(s.ip LIKE $like OR s.last_path LIKE $like OR s.last_ua LIKE $like$geoSql)";
}
if ($view === 'blocked') $where[] = "b.ip IS NOT NULL";
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$orderSql = $view === 'top' ? ' ORDER BY s.requests DESC' : ' ORDER BY s.last_seen DESC';
$blockCols = isset($bCols['is_manual']) ? 'b.is_manual, b.strikes' : '1 AS is_manual, 1 AS strikes';

// If the collation normalisation in traffic_migrate() could not run (e.g. the
// hosting DB user lacks ALTER), compare the ip columns under one explicit
// collation. utf8mb4_bin = exact byte match, correct for IP strings.
$joinCollate = '';
$res = $conn->query("SELECT COUNT(DISTINCT collation_name) FROM information_schema.columns
                     WHERE table_schema = DATABASE() AND table_name IN ('ip_stats', 'blocked_ips') AND column_name = 'ip'");
if ($res && ($row = $res->fetch_row()) && (int)$row[0] > 1) $joinCollate = ' COLLATE utf8mb4_bin';

$base = "FROM ip_stats s LEFT JOIN blocked_ips b ON b.ip$joinCollate = s.ip$joinCollate AND b.until_at > NOW()";
$sqlError = '';
$total = 0;
$rows = [];
$res = $conn->query("SELECT COUNT(*) c $base $whereSql");
if ($res) {
    $total = (int)$res->fetch_assoc()['c'];
} else {
    $sqlError = $conn->error;
}
$totalPages = max(1, (int)ceil($total / $limit));

$res = $conn->query("SELECT s.*, b.reason AS block_reason, b.until_at, $blockCols $base $whereSql $orderSql LIMIT $limit OFFSET $offset");
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
} elseif ($sqlError === '') {
    $sqlError = $conn->error;
}
if ($rows && $hasGeo) traffic_geo_fill($conn, $rows);

// Blocked IPs that never reached tracking (probe/agent blocks) — show above table on Blocked view
$orphanBlocks = [];
if ($view === 'blocked') {
    $res = $conn->query("SELECT b.* FROM blocked_ips b LEFT JOIN ip_stats s ON s.ip$joinCollate = b.ip$joinCollate
                         WHERE b.until_at > NOW() AND s.ip IS NULL ORDER BY b.created_at DESC LIMIT 50");
    if ($res) $orphanBlocks = $res->fetch_all(MYSQLI_ASSOC);
}
$viewCounts = [
    'all' => (int)tq($conn, "SELECT COUNT(*) FROM ip_stats"),
    'blocked' => (int)tq($conn, "SELECT COUNT(*) FROM blocked_ips WHERE until_at > NOW()"),
];
?>

<?php if (!$trafficReady): ?>
<div class="a-card p-5 mb-6 !border-red-300 bg-red-50">
  <p class="font-bold text-red-700 text-sm">Traffic tables could not be created.</p>
  <p class="text-sm text-red-600 mt-1">Database error: <?= e($dbError ?: 'unknown') ?>. The hosting database user needs CREATE and ALTER privileges. Everything else on the site keeps working; tracking starts once this is resolved.</p>
</div>
<?php endif; ?>

<!-- Live stat tiles -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4" id="tiles">
  <?php
  $tileDefs = [
      ['today', 'Requests Today', 'click'],
      ['visitors', 'Unique Visitors Today', 'users'],
      ['inquiries', 'Inquiries Today', 'mail'],
      ['blocked_today', 'Blocked Hits Today', 'wrench'],
      ['blocked_now', 'IPs Blocked Now', 'pin'],
  ];
  foreach ($tileDefs as [$key, $label, $icon]): ?>
  <div class="a-tile">
    <span class="a-tile-icon"><?= svg_icon($icon, 'w-5 h-5') ?></span>
    <p class="text-3xl font-bold tracking-tight" data-tile="<?= e($key) ?>">—</p>
    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-neutral-400"><?= e($label) ?></p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Live chart -->
<div class="a-card mt-6 p-4 sm:p-6">
  <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
    <div>
      <h2 class="font-bold uppercase tracking-wide text-sm">Traffic</h2>
      <p class="text-xs text-neutral-400 mt-0.5">Auto-refreshes every 8 seconds &middot; <span class="inline-block w-2.5 h-2.5 rounded-sm bg-accent align-middle"></span> requests <span class="inline-block w-2.5 h-2.5 rounded-sm bg-neutral-900 align-middle ml-2"></span> blocked</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button type="button" data-range="minutes" class="chart-range a-chip a-chip-on">60 min</button>
      <button type="button" data-range="hours" class="chart-range a-chip">24 hrs</button>
      <button type="button" data-range="week" class="chart-range a-chip">7 days</button>
      <button type="button" data-range="month" class="chart-range a-chip">30 days</button>
      <button type="button" data-range="year" class="chart-range a-chip">12 months</button>
      <button type="button" data-range="custom" class="chart-range a-chip">Custom</button>
    </div>
  </div>

  <!-- Custom date range (shown when Custom is selected) -->
  <div id="custom-range" class="hidden flex-wrap items-end gap-3 mb-4">
    <div>
      <label class="a-label">From</label>
      <input type="date" id="custom-from" class="a-input !w-auto">
    </div>
    <div>
      <label class="a-label">To</label>
      <input type="date" id="custom-to" class="a-input !w-auto">
    </div>
    <button type="button" id="custom-apply" class="a-btn-dark">Apply</button>
  </div>

  <!-- Range summary + live counter -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 mb-4">
    <div class="rounded-xl bg-neutral-50 px-3.5 py-2.5">
      <p class="text-lg font-bold tracking-tight" id="sum-total">—</p>
      <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-neutral-400">Requests <span id="sum-range-label">in range</span></p>
    </div>
    <div class="rounded-xl bg-neutral-50 px-3.5 py-2.5">
      <p class="text-lg font-bold tracking-tight" id="sum-blocked">—</p>
      <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-neutral-400">Blocked in range</p>
    </div>
    <div class="rounded-xl bg-neutral-50 px-3.5 py-2.5">
      <p class="text-lg font-bold tracking-tight" id="sum-peak">—</p>
      <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-neutral-400">Peak <span id="sum-peak-label">bar</span></p>
    </div>
    <div class="rounded-xl bg-accent-soft px-3.5 py-2.5">
      <p class="text-lg font-bold tracking-tight text-accent-deep flex items-center gap-2">
        <span class="relative flex h-2 w-2 shrink-0"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-60"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span></span>
        <span id="sum-live">—</span>
      </p>
      <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-accent-deep/60">Live &middot; requests this minute</p>
    </div>
  </div>

  <div class="relative">
    <svg id="traffic-chart" class="block w-full h-40 sm:h-56"></svg>
    <div id="chart-tip" class="hidden absolute z-10 pointer-events-none bg-neutral-950 text-white text-xs rounded-lg px-3 py-2 shadow-xl whitespace-nowrap"></div>
  </div>
  <div id="chart-xlabels" class="flex justify-between text-[10px] text-neutral-400 uppercase tracking-wide mt-2 pl-9"></div>
</div>

<!-- Block an IP + bulk bar -->
<div class="mt-6 grid lg:grid-cols-3 gap-4">
  <form method="post" class="a-card p-5 flex flex-wrap items-end gap-3 lg:col-span-2">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="block">
    <div class="flex-1 min-w-40">
      <label class="a-label">Block an IP address</label>
      <input type="text" name="ip" required placeholder="e.g. 203.0.113.45" class="a-input font-mono">
    </div>
    <div class="flex-1 min-w-40">
      <label class="a-label">Reason (optional)</label>
      <input type="text" name="reason" placeholder="spam / abuse..." class="a-input">
    </div>
    <button class="a-btn-dark">Block IP</button>
  </form>
  <form id="bulk-form" method="post" onsubmit="return bulkConfirm()" class="a-card p-5 flex flex-wrap items-end gap-3">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="bulk">
    <div class="flex-1 min-w-40">
      <label class="a-label">Bulk &middot; <span id="bulk-count" class="text-neutral-950">0</span> selected</label>
      <select name="bulk_do" id="bulk-do" class="a-input">
        <option value="">Choose action...</option>
        <option value="block">Block selected</option>
        <option value="unblock">Unblock selected</option>
        <option value="clear">Delete stats of selected</option>
      </select>
    </div>
    <button class="a-btn-dark">Apply</button>
  </form>
</div>

<!-- Filter tabs + search -->
<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
  <div class="flex flex-wrap gap-2">
    <?php foreach (['all' => 'All Visitors (' . $viewCounts['all'] . ')', 'top' => 'Most Requests', 'blocked' => 'Blocked (' . $viewCounts['blocked'] . ')'] as $key => $label): ?>
      <a href="/admin/traffic?<?= e(http_build_query(array_filter(['view' => $key, 'q' => $q]))) ?>"
         class="a-chip <?= $view === $key ? 'a-chip-on' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <form method="get" class="flex gap-2 w-full sm:w-auto">
    <input type="hidden" name="view" value="<?= e($view) ?>">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search IP, page, user agent..." class="a-input min-w-0 flex-1 sm:flex-none sm:!w-64">
    <button class="a-btn-dark shrink-0">Go</button>
  </form>
</div>

<?php if ($orphanBlocks): ?>
<div class="a-card mt-4 p-5">
  <h3 class="a-label !mb-3">Blocked before tracking (bots stopped at the gate)</h3>
  <div class="flex flex-wrap gap-2">
    <?php foreach ($orphanBlocks as $b): ?>
    <form method="post" class="inline-flex items-center gap-2 pl-3 pr-1.5 py-1.5 rounded-xl bg-neutral-100 text-xs font-mono">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="unblock"><input type="hidden" name="ip" value="<?= e($b['ip']) ?>">
      <span class="font-bold"><?= e($b['ip']) ?></span>
      <span class="text-neutral-400 font-sans"><?= e(excerpt($b['reason'] ?? '', 34)) ?></span>
      <button class="px-2 py-1 rounded-lg bg-white border border-neutral-200 font-sans font-bold uppercase text-[10px] hover:border-accent" title="Unblock">Unblock</button>
    </form>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<p class="text-sm text-neutral-400 mt-4 mb-3"><?= $total ?> IPs<?= $q !== '' ? ' matching "' . e($q) . '"' : '' ?> &middot; page <?= $page ?>/<?= $totalPages ?></p>

<?php
$emptyMsg = $sqlError !== ''
    ? 'Could not load the IP list — database said: ' . $sqlError
    : ($q !== '' || $view !== 'all'
        ? 'No IPs match this view or search.'
        : 'No traffic recorded yet — data appears as soon as visitors browse the site');
?>

<div class="a-card overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="text-left text-[10px] uppercase tracking-[0.14em] text-neutral-400">
      <tr>
        <th class="px-4 py-3.5"><input type="checkbox" id="check-all" title="Select all on this page"></th>
        <th class="px-4 py-3.5">IP Address</th><th class="px-4 py-3.5">Location / ISP</th><th class="px-4 py-3.5">Requests</th><th class="px-4 py-3.5">Inquiries</th>
        <th class="px-4 py-3.5">First Seen</th><th class="px-4 py-3.5">Last Seen</th><th class="px-4 py-3.5">Last Page</th>
        <th class="px-4 py-3.5">Device / Agent</th><th class="px-4 py-3.5">Status</th><th class="px-4 py-3.5 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-neutral-100">
      <?php foreach ($rows as $r): $blocked = $r['until_at'] !== null; ?>
      <tr class="hover:bg-neutral-50/70 align-top">
        <td class="px-4 py-3"><input type="checkbox" class="row-check" name="ips[]" value="<?= e($r['ip']) ?>" form="bulk-form"></td>
        <td class="px-4 py-3 font-mono font-bold whitespace-nowrap"><?= e($r['ip']) ?></td>
        <td class="px-4 py-3 whitespace-nowrap">
          <?php if (!empty($r['geo_country'])): ?>
            <span class="font-semibold"><?= e(trim(($r['geo_city'] ?? '') !== '' ? $r['geo_city'] . ', ' . $r['geo_country'] : $r['geo_country'])) ?></span>
            <?php if (!empty($r['geo_isp'])): ?><br><span class="text-neutral-400 text-xs" title="<?= e($r['geo_isp']) ?>"><?= e(excerpt($r['geo_isp'], 26)) ?></span><?php endif; ?>
          <?php elseif ($hasGeo): ?>
            <span class="text-neutral-300">looking up&hellip;</span>
          <?php else: ?>
            <span class="text-neutral-300">&mdash;</span>
          <?php endif; ?>
        </td>
        <td class="px-4 py-3 font-semibold"><?= number_format($r['requests']) ?></td>
        <td class="px-4 py-3"><?= $r['inquiries'] ? '<span class="font-bold text-accent-deep">' . number_format($r['inquiries']) . '</span>' : '<span class="text-neutral-300">0</span>' ?></td>
        <td class="px-4 py-3 text-neutral-400 whitespace-nowrap"><?= e(date('d M H:i', strtotime($r['first_seen']))) ?></td>
        <td class="px-4 py-3 text-neutral-500 whitespace-nowrap"><?= e(date('d M H:i', strtotime($r['last_seen']))) ?></td>
        <td class="px-4 py-3 text-neutral-500 max-w-40 truncate font-mono text-xs" title="<?= e($r['last_path']) ?>"><?= e($r['last_path']) ?></td>
        <td class="px-4 py-3 text-neutral-400 max-w-44 truncate text-xs" title="<?= e($r['last_ua']) ?>"><?= e($r['last_ua']) ?></td>
        <td class="px-4 py-3 whitespace-nowrap">
          <?php if ($blocked): ?>
            <span class="a-badge bg-neutral-950 text-white" title="<?= e($r['block_reason'] . ' — until ' . $r['until_at']) ?>"><?= $r['is_manual'] ? 'Blocked' : 'Auto-blocked' ?></span>
          <?php elseif ($r['requests'] >= 500): ?>
            <span class="a-badge bg-neutral-200 text-neutral-600">Heavy</span>
          <?php else: ?>
            <span class="a-badge bg-accent-soft text-accent-deep">Active</span>
          <?php endif; ?>
        </td>
        <td class="px-4 py-3 text-right">
          <form method="post" class="inline" <?= $blocked ? '' : 'onsubmit="return confirm(\'Block ' . e($r['ip']) . ' permanently?\')"' ?>>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $blocked ? 'unblock' : 'block' ?>"><input type="hidden" name="ip" value="<?= e($r['ip']) ?>">
            <?php if (!$blocked): ?><input type="hidden" name="reason" value="blocked from IP table"><?php endif; ?>
            <button class="a-btn-ghost !px-3 !py-1.5 <?= $blocked ? '' : 'hover:!border-red-500 hover:!text-red-600' ?>"><?= $blocked ? 'Unblock' : 'Block' ?></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="11" class="px-6 py-12 text-center <?= $sqlError !== '' ? 'text-red-600' : 'text-neutral-400' ?>"><?= e($emptyMsg) ?></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="mt-4 flex items-center gap-4">
  <?php if ($page > 1): ?><a href="/admin/traffic?<?= e(http_build_query(array_filter(['view' => $view, 'q' => $q, 'page' => $page - 1]))) ?>" class="text-xs uppercase tracking-widest font-semibold hover:text-accent-dark">&larr; Prev</a><?php endif; ?>
  <span class="text-xs text-neutral-400">page <?= $page ?> / <?= $totalPages ?></span>
  <?php if ($page < $totalPages): ?><a href="/admin/traffic?<?= e(http_build_query(array_filter(['view' => $view, 'q' => $q, 'page' => $page + 1]))) ?>" class="text-xs uppercase tracking-widest font-semibold hover:text-accent-dark">Next &rarr;</a><?php endif; ?>
</div>
<?php endif; ?>

<!-- Floating multi-select action bar (appears when IPs are selected) -->
<div id="bulk-bar" class="hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-40 items-center gap-1.5 sm:gap-2 bg-neutral-950 text-white rounded-2xl shadow-2xl pl-4 pr-2 py-2 max-w-[calc(100vw-1.5rem)] overflow-x-auto">
  <span class="text-[11px] font-bold uppercase tracking-widest whitespace-nowrap"><span id="bulk-bar-count">0</span> selected</span>
  <button type="button" onclick="bulkDo('block')" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-red-500 text-[11px] font-bold uppercase tracking-widest transition-colors whitespace-nowrap">Block</button>
  <button type="button" onclick="bulkDo('unblock')" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-accent hover:text-neutral-950 text-[11px] font-bold uppercase tracking-widest transition-colors whitespace-nowrap">Unblock</button>
  <button type="button" onclick="bulkDo('clear')" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-red-500 text-[11px] font-bold uppercase tracking-widest transition-colors whitespace-nowrap">Delete</button>
  <button type="button" onclick="bulkClearSel()" class="px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-neutral-400 hover:text-white transition-colors" title="Clear selection">&#10005;</button>
</div>

<script>
(function () {
  // ---- Traffic chart: multi-range, live-updating, hover tooltip ----
  var data = null, range = 'minutes', customFrom = '', customTo = '';
  var svg = document.getElementById('traffic-chart');
  var tip = document.getElementById('chart-tip');
  var geom = null; // bar x-ranges from the last draw, for tooltip hit-testing

  var RANGE_LABEL = {minutes: 'last 60 min', hours: 'last 24 hrs', week: 'last 7 days', month: 'last 30 days', year: 'last 12 months', custom: 'custom range'};
  var PEAK_LABEL = {minutes: 'minute', hours: 'hour', week: 'day', month: 'day', year: 'month', custom: 'bar'};

  function fmtNum(v) {
    if (v >= 1000000) return (v / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (v >= 1000) return (v / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    return String(Math.round(v));
  }
  // Round up to 1/2/5 x 10^k so the y-axis gets clean numbers
  function niceCeil(v) {
    var p = Math.pow(10, Math.floor(Math.log(v) / Math.LN10));
    var m = v / p;
    return (m <= 1 ? 1 : m <= 2 ? 2 : m <= 5 ? 5 : 10) * p;
  }

  function jsonUrl() {
    var u = '/admin/traffic?json=1&range=' + range;
    if (range === 'custom' && customFrom && customTo) u += '&from=' + customFrom + '&to=' + customTo;
    return u;
  }

  function draw() {
    if (!data) return;
    var series = data.series;
    var W = svg.clientWidth || 600, H = svg.clientHeight || 200;
    svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
    var padL = 36, padR = 4, padT = 8, padB = 6;
    var plotW = W - padL - padR, plotH = H - padT - padB;
    var n = series.length, bw = plotW / n;
    var max = 1, i;
    for (i = 0; i < n; i++) max = Math.max(max, series[i][1] + series[i][2]);
    var nice = niceCeil(max);
    var out = '';
    // gridlines + y-axis value labels
    for (var g = 0; g <= 4; g++) {
      var val = nice * g / 4;
      var gy = padT + plotH - (val / nice) * plotH;
      out += '<line x1="' + padL + '" y1="' + gy + '" x2="' + W + '" y2="' + gy + '" stroke="' + (g === 0 ? '#e5e5e5' : '#f1f1f1') + '" stroke-width="1"/>';
      out += '<text x="' + (padL - 7) + '" y="' + (gy + 3) + '" text-anchor="end" font-size="9" fill="#a3a3a3">' + fmtNum(val) + '</text>';
    }
    geom = [];
    for (i = 0; i < n; i++) {
      var d = series[i];
      var hHits = d[1] / nice * plotH;
      var hBlk = d[2] / nice * plotH;
      var x = padL + i * bw + Math.min(1.5, bw * 0.12);
      var w = Math.max(1, bw - Math.min(3, bw * 0.24));
      var yTop = padT + plotH - hHits;
      if (d[1] > 0) out += '<rect x="' + x + '" y="' + yTop + '" width="' + w + '" height="' + Math.max(2, hHits) + '" rx="1.5" fill="#00c1ae"/>';
      if (d[2] > 0) out += '<rect x="' + x + '" y="' + (yTop - Math.max(2, hBlk)) + '" width="' + w + '" height="' + Math.max(2, hBlk) + '" rx="1.5" fill="#171717"/>';
      if (d[1] === 0 && d[2] === 0) out += '<rect x="' + x + '" y="' + (padT + plotH - 2) + '" width="' + w + '" height="2" rx="1" fill="#ececec"/>';
      geom.push({x0: padL + i * bw, x1: padL + (i + 1) * bw});
    }
    svg.innerHTML = out;

    // x-axis labels: up to 6 evenly picked across the range
    var xl = document.getElementById('chart-xlabels');
    var count = Math.min(n, 6), picks = [];
    for (var j = 0; j < count; j++) picks.push(series[Math.round(j * (n - 1) / Math.max(1, count - 1))][0]);
    xl.innerHTML = picks.map(function (t) { return '<span>' + t + '</span>'; }).join('');

    // summary numbers
    var s = data.summary || {};
    document.getElementById('sum-total').textContent = Number(s.total || 0).toLocaleString();
    document.getElementById('sum-blocked').textContent = Number(s.blocked || 0).toLocaleString();
    document.getElementById('sum-peak').textContent = Number(s.peak || 0).toLocaleString();
    document.getElementById('sum-live').textContent = Number(s.live || 0).toLocaleString();
    document.getElementById('sum-range-label').textContent = RANGE_LABEL[data.range] || 'in range';
    document.getElementById('sum-peak-label').textContent = 'per ' + (PEAK_LABEL[data.range] || 'bar');
  }

  // Tooltip: hit-test the bar under the cursor
  svg.addEventListener('mousemove', function (ev) {
    if (!data || !geom) return;
    var rect = svg.getBoundingClientRect();
    var mx = ev.clientX - rect.left;
    var hit = -1;
    for (var k = 0; k < geom.length; k++) if (mx >= geom[k].x0 && mx < geom[k].x1) { hit = k; break; }
    if (hit < 0) { tip.classList.add('hidden'); return; }
    var d = data.series[hit];
    tip.innerHTML = '<span class="font-bold">' + d[0] + '</span><br>' + Number(d[1]).toLocaleString() + ' requests' + (d[2] ? '<br>' + Number(d[2]).toLocaleString() + ' blocked' : '');
    tip.classList.remove('hidden');
    tip.style.left = Math.min(Math.max(mx - tip.offsetWidth / 2, 0), rect.width - tip.offsetWidth) + 'px';
    tip.style.top = Math.max(0, (ev.clientY - rect.top) - tip.offsetHeight - 12) + 'px';
  });
  svg.addEventListener('mouseleave', function () { tip.classList.add('hidden'); });

  function refresh() {
    fetch(jsonUrl(), {credentials: 'same-origin'}).then(function (r) { return r.json(); }).then(function (j) {
      data = j;
      Object.keys(j.tiles).forEach(function (k) {
        var el = document.querySelector('[data-tile="' + k + '"]');
        if (el) el.textContent = Number(j.tiles[k]).toLocaleString();
      });
      draw();
    }).catch(function () {});
  }

  var customBox = document.getElementById('custom-range');
  document.querySelectorAll('.chart-range').forEach(function (b) {
    b.addEventListener('click', function () {
      range = b.dataset.range;
      document.querySelectorAll('.chart-range').forEach(function (x) { x.classList.toggle('a-chip-on', x === b); });
      if (range === 'custom') {
        customBox.classList.remove('hidden');
        customBox.classList.add('flex');
        if (!customFrom) { // default: last 30 days
          customTo = new Date().toISOString().slice(0, 10);
          customFrom = new Date(Date.now() - 29 * 86400000).toISOString().slice(0, 10);
          document.getElementById('custom-from').value = customFrom;
          document.getElementById('custom-to').value = customTo;
        }
      } else {
        customBox.classList.add('hidden');
        customBox.classList.remove('flex');
      }
      refresh();
    });
  });
  document.getElementById('custom-apply').addEventListener('click', function () {
    var f = document.getElementById('custom-from').value, t = document.getElementById('custom-to').value;
    if (!f || !t) { alert('Pick both From and To dates.'); return; }
    customFrom = f;
    customTo = t;
    refresh();
  });

  var resizeTimer = null;
  window.addEventListener('resize', function () { clearTimeout(resizeTimer); resizeTimer = setTimeout(draw, 150); });

  refresh();
  setInterval(refresh, 8000);

  // ---- Bulk selection + floating action bar ----
  var all = document.getElementById('check-all');
  var boxes = Array.prototype.slice.call(document.querySelectorAll('.row-check'));
  var count = document.getElementById('bulk-count');
  var bar = document.getElementById('bulk-bar');
  var barCount = document.getElementById('bulk-bar-count');
  function selCount() { return boxes.filter(function (b) { return b.checked; }).length; }
  function recount() {
    var n = selCount();
    count.textContent = n;
    barCount.textContent = n;
    bar.classList.toggle('hidden', n === 0);
    bar.classList.toggle('flex', n > 0);
    if (all) all.checked = n > 0 && n === boxes.length;
  }
  if (all) all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); recount(); });
  boxes.forEach(function (b) { b.addEventListener('change', recount); });

  // Floating-bar actions reuse the bulk form (programmatic submit skips onsubmit)
  window.bulkDo = function (act) {
    var n = selCount();
    if (!n) { alert('Select at least one IP first.'); return; }
    var msgs = {
      block: 'Block ' + n + ' selected IPs permanently?',
      unblock: 'Unblock ' + n + ' selected IPs?',
      clear: 'Delete tracked stats of ' + n + ' selected IPs? They reappear if they visit again.'
    };
    if (!confirm(msgs[act])) return;
    document.getElementById('bulk-do').value = act;
    document.getElementById('bulk-form').submit();
  };
  window.bulkClearSel = function () { boxes.forEach(function (b) { b.checked = false; }); recount(); };
  window.bulkConfirm = function () {
    var n = selCount();
    var act = document.getElementById('bulk-do').value;
    if (!n) { alert('Select at least one IP first.'); return false; }
    if (!act) { alert('Choose a bulk action first.'); return false; }
    if (act === 'block') return confirm('Block ' + n + ' selected IPs permanently?');
    if (act === 'clear') return confirm('Delete tracked stats of ' + n + ' selected IPs?');
    return true;
  };
})();
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
