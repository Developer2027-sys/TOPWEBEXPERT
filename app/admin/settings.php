<?php
$adminTitle = 'Site Settings';
$activeNav = 'settings';
require __DIR__ . '/includes/admin-header.php';

$fields = [
    'site_name' => ['Site Name', 'text'],
    'site_url' => ['Site URL (for canonical/sitemap)', 'text'],
    'address' => ['Address', 'textarea'],
    'address_locality' => ['Head Office City (for Google schema, e.g. Gurugram)', 'text'],
    'address_region' => ['Head Office State (e.g. Haryana)', 'text'],
    'postal_code' => ['Head Office PIN Code (e.g. 122018)', 'text'],
    'google_business_url' => ['Google Business Profile / Maps URL', 'text'],
    'phone' => ['Phone', 'text'],
    'email' => ['Email', 'text'],
    'whatsapp' => ['WhatsApp Number', 'text'],
    'facebook' => ['Facebook URL', 'text'],
    'instagram' => ['Instagram URL', 'text'],
    'linkedin' => ['LinkedIn URL', 'text'],
    'twitter' => ['X (Twitter) URL', 'text'],
    'youtube' => ['YouTube URL', 'text'],
    'medium' => ['Medium URL', 'text'],
    'footer_about' => ['Footer About Text', 'textarea'],
    'footer_copyright' => ['Footer Copyright Line (e.g. ©2026 copyright by Topwebexpert. All rights reserved.)', 'text'],
    'gtm_id' => ['Google Tag Manager Container ID (e.g. GTM-XXXXXXX)', 'text'],
    'ga_id' => ['Google Analytics 4 Measurement ID (e.g. G-XXXXXXXXXX)', 'text'],
    'clarity_id' => ['Microsoft Clarity Project ID (e.g. abcd1234ef)', 'text'],
    'captcha_provider' => ['Form CAPTCHA Protection', 'select', [
        'off' => 'Off (rate limiting only)',
        'recaptcha-v2' => 'Google reCAPTCHA v2 (checkbox)',
        'recaptcha-v3' => 'Google reCAPTCHA v3 (invisible)',
        'turnstile' => 'Cloudflare Turnstile',
    ]],
    'recaptcha_site_key' => ['reCAPTCHA Site Key', 'text'],
    'recaptcha_secret' => ['reCAPTCHA Secret Key', 'text'],
    'turnstile_site_key' => ['Cloudflare Turnstile Site Key', 'text'],
    'turnstile_secret' => ['Cloudflare Turnstile Secret Key', 'text'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO settings (skey, svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)");
    foreach ($fields as $key => $meta) {
        $val = trim($_POST[$key] ?? '');
        $stmt->bind_param('ss', $key, $val);
        $stmt->execute();
    }
    flash('Settings saved — the frontend header, footer and contact info are updated.');
    header('Location: /admin/settings');
    exit;
}

$settings = [];
$res = $conn->query("SELECT * FROM settings");
while ($r = $res->fetch_assoc()) $settings[$r['skey']] = $r['svalue'];
?>

<form method="post" class="max-w-3xl bg-white border border-neutral-200 p-6 space-y-5">
  <?= csrf_field() ?>
  <p class="text-sm text-neutral-500 border-b border-neutral-200 pb-4">These values control the frontend website: top bar, footer, contact page, WhatsApp button and tracking codes (Google Tag Manager / Analytics are added to every page automatically when their IDs are set below).</p>
  <?php foreach ($fields as $key => $meta): [$label, $type] = $meta; $val = $settings[$key] ?? ''; ?>
  <div>
    <label class="block text-xs font-bold uppercase tracking-widest text-neutral-500 mb-2"><?= e($label) ?></label>
    <?php if ($type === 'textarea'): ?>
      <textarea name="<?= e($key) ?>" rows="3" class="w-full border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:border-accent"><?= e($val) ?></textarea>
    <?php elseif ($type === 'select'): ?>
      <select name="<?= e($key) ?>" class="w-full border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:border-accent bg-white">
        <?php foreach ($meta[2] as $ov => $ol): ?>
          <option value="<?= e($ov) ?>" <?= $val === $ov ? 'selected' : '' ?>><?= e($ol) ?></option>
        <?php endforeach; ?>
      </select>
    <?php else: ?>
      <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>" class="w-full border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:border-accent">
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <button class="w-full py-3 bg-neutral-950 text-white text-xs font-bold uppercase tracking-widest hover:bg-accent hover:text-neutral-950 transition-colors">Save Settings</button>
</form>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
