<?php
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function excerpt($text, $len = 140) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $len) return $text;
    return mb_substr($text, 0, $len) . '…';
}

// Web-root filesystem path (assets/libs live at the web root, not under /app)
function web_root_path() {
    return defined('WEB_ROOT') ? WEB_ROOT : dirname(dirname(__DIR__));
}

// Blog thumbnail path: returns the small .thumb.webp if it exists, else the original
function blog_thumb($image) {
    $thumb = preg_replace('/\.(png|jpe?g)$/i', '', $image) . '.thumb.webp';
    if (is_file(web_root_path() . '/assets/img/blog/' . $thumb)) {
        return '/assets/img/blog/' . $thumb;
    }
    return '/assets/img/blog/' . $image;
}

// Intrinsic width/height attributes for an image under the web root.
// Emitting these lets the browser reserve the right box before the file arrives,
// which is what keeps Cumulative Layout Shift at zero. Results are memoised so a
// page with many images still only stats each file once.
function img_dimensions($webPath) {
    static $cache = [];
    if (!array_key_exists($webPath, $cache)) {
        $file = web_root_path() . parse_url($webPath, PHP_URL_PATH);
        $size = is_file($file) ? @getimagesize($file) : false;
        $cache[$webPath] = $size ? [$size[0], $size[1]] : null;
    }
    return $cache[$webPath];
}

// Ready-to-print ` width="..." height="..."` (empty string when the file is unreadable)
function img_size_attrs($webPath) {
    $d = img_dimensions($webPath);
    return $d ? ' width="' . $d[0] . '" height="' . $d[1] . '"' : '';
}

// Versioned asset URL: cache-busts on file change while keeping long-lived caching
function asset($path) {
    $file = web_root_path() . $path;
    $v = is_file($file) ? filemtime($file) : 1;
    return $path . '?v=' . $v;
}

// Highlight SEO keywords in already-escaped text: wraps the first occurrence
// of each term with <strong class="kw"> for on-page keyword emphasis.
function highlight_keywords($escapedText, $terms) {
    foreach ($terms as $term) {
        if ($term === '') continue;
        $quoted = preg_quote(e($term), '/');
        $escapedText = preg_replace(
            '/(?<![\w>])(' . $quoted . ')(?![\w<])/iu',
            '<strong class="kw">$1</strong>',
            $escapedText,
            1
        );
    }
    return $escapedText;
}

// Human-readable place label: "City, Country" — or just the name for country-level
// location rows (added September 2026: "Australia", "UK", "USA", "UAE" ...).
function location_label($city, $country) {
    if ($city === $country || in_array($city, ['UK', 'USA', 'UAE'], true)) return $city;
    return "$city, $country";
}
