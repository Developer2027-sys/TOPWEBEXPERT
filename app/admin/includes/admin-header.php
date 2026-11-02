<?php
// Admin layout header. Expects: $adminTitle (page title), $activeNav (nav key)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../includes/cache.php';
require_login();
csrf_check();
// Any admin change invalidates the frontend page cache so edits go live instantly
// (Short time budget: a leftover multi-thousand-file cache from the old caching
// scheme must not stall every admin save — Dashboard > Clear Page Cache finishes it.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') page_cache_clear_status(3);
$me = admin_row($conn);
$adminTitle = $adminTitle ?? 'Dashboard';
$activeNav = $activeNav ?? '';

require_once __DIR__ . '/../../includes/icons.php';
$navItems = [
    'dashboard'    => ['Dashboard', '/admin', 'grid'],
    'blogs'        => ['Blogs', '/admin/blogs', 'pen'],
    'portfolio'    => ['Portfolio', '/admin/portfolio', 'image'],
    'testimonials' => ['Testimonials', '/admin/testimonials', 'quote'],
    'inquiries'    => ['Inquiries', '/admin/inquiries', 'mail'],
    'traffic'      => ['Traffic & IPs', '/admin/traffic', 'click'],
    'branches'     => ['Branches', '/admin/branches', 'pin'],
    'locations'    => ['Locations', '/admin/locations', 'globe'],
    'city-pages'   => ['City Pages', '/admin/city-pages', 'share'],
    'service-guides' => ['City Guides', '/admin/service-guides', 'laptop'],
    'seo'          => ['SEO Manager', '/admin/seo', 'search'],
    'settings'     => ['Site Settings', '/admin/settings', 'wrench'],
    'profile'      => ['My Profile', '/admin/profile', 'users'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle) ?> | TopWebExpert Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body text-neutral-900 min-h-screen">
<div class="flex min-h-screen">

  <!-- Mobile sidebar backdrop -->
  <div id="sidebar-backdrop" class="fixed inset-0 bg-neutral-950/60 backdrop-blur-sm z-40 hidden lg:hidden"></div>

  <!-- Sidebar -->
  <aside class="w-64 shrink-0 bg-neutral-950 text-neutral-400 flex flex-col fixed inset-y-0 left-0 z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300" id="admin-sidebar">
    <div class="p-5">
      <a href="/admin" class="block bg-white p-2.5 rounded-2xl"><img src="/assets/logo.png" alt="TopWebExpert" class="w-32 mx-auto"></a>
      <p class="mt-3 text-center text-[10px] uppercase tracking-[0.35em] text-neutral-600">Admin Panel</p>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-1">
      <?php foreach ($navItems as $key => [$label, $href, $icon]): $on = $key === $activeNav; ?>
        <a href="<?= e($href) ?>"
           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold uppercase tracking-wide transition-all duration-200 <?= $on ? 'bg-neutral-900 text-white shadow-[inset_0_0_0_1px_rgba(255,255,255,0.06)]' : 'hover:bg-neutral-900/60 hover:text-white' ?>">
          <?php if ($on): ?><span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 rounded-r-full bg-accent"></span><?php endif; ?>
          <span class="<?= $on ? 'text-accent' : 'text-neutral-600 group-hover:text-accent' ?> transition-colors"><?= svg_icon($icon, 'w-5 h-5') ?></span><?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-white/5 space-y-2">
      <a href="/" target="_blank" class="flex items-center justify-center gap-2 text-[11px] font-bold uppercase tracking-widest py-2.5 rounded-xl border border-neutral-800 hover:border-accent hover:text-accent transition-colors">View Website &nearr;</a>
      <a href="/admin/logout" class="flex items-center justify-center gap-2 text-[11px] font-bold uppercase tracking-widest py-2.5 rounded-xl bg-accent text-neutral-950 hover:bg-white transition-colors">Logout</a>
    </div>
  </aside>

  <!-- Main column -->
  <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">

    <!-- Top header -->
    <header class="bg-white/85 backdrop-blur-md border-b border-neutral-200/70 sticky top-0 z-30">
      <div class="px-4 sm:px-6 py-3 sm:py-3.5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <button id="sidebar-toggle" class="lg:hidden p-2 rounded-xl border border-neutral-200 bg-white shrink-0" aria-label="Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <div class="min-w-0">
            <p class="hidden sm:block text-[10px] font-bold uppercase tracking-[0.2em] text-neutral-400">TopWebExpert / Admin</p>
            <h1 class="text-base sm:text-lg font-bold tracking-tight truncate"><?= e($adminTitle) ?></h1>
          </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
          <span class="hidden md:block text-[11px] text-neutral-400 font-semibold uppercase tracking-widest"><?= e(date('D, d M Y')) ?></span>
          <span class="hidden md:block w-px h-6 bg-neutral-200"></span>
          <span class="w-9 h-9 rounded-full bg-neutral-950 text-accent flex items-center justify-center font-bold shrink-0 ring-2 ring-accent/30 ring-offset-2"><?= e(strtoupper(substr($me['username'], 0, 1))) ?></span>
          <span class="hidden sm:block font-semibold text-sm"><?= e($me['username']) ?></span>
        </div>
      </div>
    </header>

    <!-- Body -->
    <main class="flex-1 p-4 sm:p-6">
      <?php if ($m = flash()): ?>
        <div class="mb-6 px-5 py-4 bg-white rounded-2xl border border-neutral-200/70 border-l-4 !border-l-accent shadow-[0_1px_3px_rgba(17,24,39,0.05)] text-sm font-semibold"><?= e($m) ?></div>
      <?php endif; ?>
