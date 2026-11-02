<?php
// Generated-file cache — sitemaps only.
//
// Pages are NEVER written to disk. Every page (home, service pages, blog posts,
// city pages, city guides) is rendered live from the database on each request.
//
// The previous version cached every URL (and every query-string variant of it) as
// its own HTML file under app/cache/pages. With 250,000+ database pages that grew
// to 220,000 files / 9 GB on the hosting account, which counts against the inode
// and disk quota and slows every file operation. Pages cost a few indexed queries
// each, so serving them live is cheaper than that cache ever was.
//
// The only files kept here are the sitemap parts (app/cache/sitemaps, about 30
// small XML files, regenerated every SITEMAP_CACHE_TTL seconds or on any admin save).

const SITEMAP_CACHE_TTL = 21600;  // 6 hours
const CACHE_CLEAR_BUDGET = 20;    // seconds one cache_clear_status() call may spend deleting

function cache_root() {
    return dirname(__DIR__) . '/cache';
}

// Delete generated files: cached sitemaps, plus any page files left behind by the
// old caching scheme. Deletes for at most $budgetSeconds so a folder still holding
// the old 200k-file cache cannot time out an admin request; call again to continue.
// Returns ['deleted' => n, 'complete' => bool].
function cache_clear_status($budgetSeconds = CACHE_CLEAR_BUDGET) {
    $deleted = 0;
    $complete = true;
    $deadline = microtime(true) + $budgetSeconds;
    foreach (['/sitemaps' => 'xml', '/pages' => 'html'] as $sub => $ext) {
        $dir = cache_root() . $sub;
        if (!is_dir($dir)) continue;
        foreach (new DirectoryIterator($dir) as $f) {
            if (microtime(true) > $deadline) { $complete = false; break 2; }
            if ($f->isFile() && $f->getExtension() === $ext && @unlink($f->getPathname())) $deleted++;
        }
        // Old page-cache folder: remove it once empty so nothing can be written there again
        if ($sub === '/pages') @rmdir($dir);
    }
    return ['deleted' => $deleted, 'complete' => $complete];
}

// Backwards-compatible names used by the admin panel
function page_cache_clear_status($budgetSeconds = CACHE_CLEAR_BUDGET) { return cache_clear_status($budgetSeconds); }
function page_cache_clear() { return cache_clear_status()['deleted']; }
