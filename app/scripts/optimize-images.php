<?php
/**
 * Image optimiser — run from the CLI:
 *     php app/scripts/optimize-images.php
 *
 * Two jobs:
 *   1. WebP twins.   For every PNG/JPEG under /assets, write "<file>.webp" next to it.
 *                    The router (dev) and .htaccess (production) serve the twin
 *                    automatically when the browser sends "Accept: image/webp",
 *                    so the HTML keeps its original .png/.jpg URLs and nothing
 *                    about SEO or existing links changes.
 *   2. Hero variants. The homepage banner is the LCP element, so it also gets
 *                    downscaled WebP copies in /assets/img/hero for use in srcset.
 *
 * Safe to re-run: a twin is only rebuilt when the source is newer than it.
 * Requires the GD extension with WebP support (XAMPP PHP ships with it).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}
if (!function_exists('imagewebp')) {
    exit("GD is missing WebP support — cannot continue.\n");
}

const WEBP_QUALITY   = 82;   // visually lossless for photos at typical display sizes
const ASSETS_DIR     = __DIR__ . '/../../assets';
const HERO_SOURCE    = ASSETS_DIR . '/img/banner.png';
const HERO_OUT_DIR   = ASSETS_DIR . '/img/hero';
const HERO_WIDTHS    = [480, 768, 1024, 1326];
const LOGO_SOURCE    = ASSETS_DIR . '/logo.png';
const LOGO_WHITE     = ASSETS_DIR . '/logo-white.png';

$force = in_array('--force', $argv, true);

/**
 * Real image format from the file signature, not the extension — a handful of
 * blog uploads are WebP files that were saved with a .jpg name.
 */
function real_format(string $file): string
{
    $head = @file_get_contents($file, false, null, 0, 12);
    if ($head === false || strlen($head) < 12) return 'unknown';
    if (str_starts_with($head, "\x89PNG\r\n\x1a\n"))                     return 'png';
    if (str_starts_with($head, "\xFF\xD8\xFF"))                          return 'jpeg';
    if (str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP') return 'webp';
    return 'unknown';
}

/** Load a PNG/JPEG into a GD image, or null when unreadable. */
function load_image(string $file): ?GdImage
{
    $img = match (real_format($file)) {
        'png'  => @imagecreatefrompng($file),
        'jpeg' => @imagecreatefromjpeg($file),
        'webp' => @imagecreatefromwebp($file),
        default => false,
    };
    return $img ?: null;
}

/** Write $img as WebP, keeping any alpha channel intact. */
function save_webp(GdImage $img, string $dest): bool
{
    imagepalettetotruecolor($img);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    return imagewebp($img, $dest, WEBP_QUALITY);
}

/** Proportionally resize, preserving transparency. */
function resize(GdImage $src, int $width): GdImage
{
    $w = imagesx($src);
    $h = imagesy($src);
    $height = max(1, (int) round($h * ($width / $w)));

    $dst = imagecreatetruecolor($width, $height);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $w, $h);

    return $dst;
}

function kb(int $bytes): string
{
    return number_format($bytes / 1024, 1) . 'KB';
}

/**
 * Reversed-out copy of the logo for the dark footer.
 *
 * The master logo is artwork on an opaque white background, so two things happen:
 *   1. the white background is lifted into real transparency — for a shape drawn on
 *      white, coverage is (255 - min(r,g,b)), and dividing the colour back out of
 *      that coverage recovers the original ink;
 *   2. the recovered ink is switched to white wherever it is neutral (the wordmark),
 *      while anything saturated (the teal mark) keeps its colour.
 *
 * Anti-aliased edges fall out of the maths as partially transparent pixels, so the
 * result stays smooth on any background rather than showing a white halo.
 */
function make_white_logo(string $src, string $dest): bool
{
    $img = load_image($src);
    if (!$img) return false;

    imagepalettetotruecolor($img);
    $w = imagesx($img);
    $h = imagesy($img);

    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $blank = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefill($out, 0, 0, $blank);

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $c = imagecolorat($img, $x, $y);
            $srcAlpha = ($c >> 24) & 0x7F;          // 0 = opaque, 127 = clear
            $r = ($c >> 16) & 0xFF;
            $g = ($c >> 8) & 0xFF;
            $b = $c & 0xFF;

            // Already transparent in the source: nothing to recover.
            if ($srcAlpha === 127) {
                imagesetpixel($out, $x, $y, $blank);
                continue;
            }

            $coverage = 255 - min($r, $g, $b);      // 0 on pure white, 255 on solid ink
            if ($coverage === 0) {
                imagesetpixel($out, $x, $y, $blank);
                continue;
            }

            // Undo the blend with the white backdrop.
            $unblend = function (int $v) use ($coverage): int {
                return (int) max(0, min(255, round(($v - (255 - $coverage)) * 255 / $coverage)));
            };
            $r = $unblend($r);
            $g = $unblend($g);
            $b = $unblend($b);

            // Neutral ink is the wordmark — reverse it out to white.
            if (max($r, $g, $b) - min($r, $g, $b) < 40) {
                $r = $g = $b = 255;
            }

            // Combine recovered coverage with any alpha the source already had.
            $alpha255 = (int) round($coverage * (127 - $srcAlpha) / 127);
            $gdAlpha  = 127 - (int) round($alpha255 * 127 / 255);
            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $gdAlpha));
        }
    }

    $ok = imagepng($out, $dest, 9);
    imagedestroy($img);
    imagedestroy($out);
    return $ok;
}

// --------------------------------------------------------- Derived brand assets
// Built before the WebP pass so they pick up a twin in the same run.
if (is_file(LOGO_SOURCE)) {
    if ($force || !is_file(LOGO_WHITE) || filemtime(LOGO_WHITE) < filemtime(LOGO_SOURCE)) {
        if (make_white_logo(LOGO_SOURCE, LOGO_WHITE)) {
            echo 'Footer logo: ' . basename(LOGO_WHITE) . ' rebuilt (' . kb(filesize(LOGO_WHITE)) . ")\n\n";
        } else {
            echo "Footer logo: could not rebuild " . basename(LOGO_WHITE) . "\n\n";
        }
    }
}

// ---------------------------------------------------------------- WebP twins
$made = $skipped = 0;
$sourceBytes = $webpBytes = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(ASSETS_DIR, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) continue;
    // Skip the downscaled hero copies — they are already WebP.
    if (str_starts_with($file->getPath(), HERO_OUT_DIR)) continue;

    $src  = $file->getPathname();
    $dest = $src . '.webp';

    // Already a WebP file wearing a .jpg/.png name — nothing to gain from a twin.
    if (real_format($src) === 'webp') {
        $skipped++;
        continue;
    }

    if (!$force && is_file($dest) && filemtime($dest) >= filemtime($src)) {
        $skipped++;
        $sourceBytes += $file->getSize();
        $webpBytes   += filesize($dest);
        continue;
    }

    $img = load_image($src);
    if (!$img) {
        echo "  ! unreadable: {$src}\n";
        continue;
    }
    if (!save_webp($img, $dest)) {
        echo "  ! failed: {$src}\n";
        imagedestroy($img);
        continue;
    }
    imagedestroy($img);

    $made++;
    $sourceBytes += $file->getSize();
    $webpBytes   += filesize($dest);
    printf("  + %-58s %9s -> %9s\n", basename($src), kb($file->getSize()), kb(filesize($dest)));
}

echo "\nWebP twins: {$made} written, {$skipped} already current.\n";
if ($sourceBytes) {
    printf(
        "Raster payload: %s -> %s (%.0f%% smaller when WebP is served)\n",
        kb($sourceBytes),
        kb($webpBytes),
        (1 - $webpBytes / $sourceBytes) * 100
    );
}

// ------------------------------------------------------------- Hero variants
if (!is_file(HERO_SOURCE)) {
    echo "\nHero source missing (" . HERO_SOURCE . ") — skipping responsive variants.\n";
    exit(0);
}

if (!is_dir(HERO_OUT_DIR) && !mkdir(HERO_OUT_DIR, 0775, true) && !is_dir(HERO_OUT_DIR)) {
    exit("\nCould not create " . HERO_OUT_DIR . "\n");
}

$hero = load_image(HERO_SOURCE);
if (!$hero) exit("\nCould not read the hero source image.\n");

echo "\nHero variants (source " . imagesx($hero) . 'x' . imagesy($hero) . "):\n";
foreach (HERO_WIDTHS as $width) {
    if ($width > imagesx($hero)) continue;      // never upscale
    $dest = HERO_OUT_DIR . "/banner-{$width}.webp";
    $variant = resize($hero, $width);
    if (save_webp($variant, $dest)) {
        printf("  + %-20s %9s\n", basename($dest), kb(filesize($dest)));
    } else {
        echo "  ! failed: {$dest}\n";
    }
    imagedestroy($variant);
}
imagedestroy($hero);

echo "\nDone.\n";
