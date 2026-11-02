<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/captcha.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!captcha_verify($conn)) {
        $error = 'Captcha verification failed. Please tick the captcha box and try again.';
    } elseif (!sec_form_throttle($conn, 3, 60)) {
        sec_block_ip($conn, sec_client_ip(), 'enquiry-flood');
        $error = 'Too many submissions. Please wait a minute and try again.';
    } elseif ($name === '' || $email === '' || $phone === '' || $message === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $clientIp = sec_client_ip();
        $stmt = $conn->prepare("INSERT INTO inquiries (name, email, phone, message, ip) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $email, $phone, $message, $clientIp);
        if ($stmt->execute()) {
            sec_track_inquiry($conn, $clientIp);
            header('Location: /thank-you');
            exit;
        }
        $error = 'Something went wrong. Please try again.';
    }
}

$pageTitle = 'Contact Us - TopWebExpert | Get In Touch For Digital Solutions';
$metaDescription = "Contact TopWebExpert for professional website development and digital marketing services. We're here to help you grow your business online.";
require __DIR__ . '/includes/header.php';
?>

<section class="hero-grad text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 hero-dots opacity-30"></div>
  <div class="max-w-4xl mx-auto px-4 text-center">
    <p class="font-semibold uppercase tracking-widest text-sm text-white/80">Get in touch</p>
    <h1 class="text-4xl md:text-5xl font-bold mt-3">Contact Us</h1>
    <div class="w-24 h-1.5 bg-white/80 rounded-full mx-auto mt-6"></div>
  </div>
</section>

<section class="bg-mist py-16">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-10">
    <div class="bg-white rounded-3xl shadow-lg border border-line/60 p-8">
      <h2 class="text-2xl font-bold">Our Offices</h2>
      <div class="w-16 h-1 bg-accent rounded-full mt-3 mb-6"></div>
      <div class="space-y-5">
        <?php foreach (site_branches($conn) as $br): ?>
        <div class="rounded-2xl border <?= $br['is_primary'] ? 'border-accent/50 bg-accent-soft' : 'border-line/70 bg-mist' ?> p-5">
          <p class="font-bold flex items-center gap-2">
            <span class="icon-badge w-9 h-9 rounded-full shrink-0"><?= svg_icon('pin', 'w-4 h-4') ?></span>
            <?= e($br['name']) ?>
            <?php if ($br['is_primary']): ?><span class="text-[10px] font-bold uppercase tracking-widest text-accent-deep bg-white rounded-full px-2 py-0.5">Head Office</span><?php endif; ?>
          </p>
          <p class="mt-3 text-sm text-ink-soft leading-relaxed"><?= e($br['address']) ?></p>
          <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm">
            <a href="tel:<?= e(preg_replace('/\D+/', '', $br['phone'])) ?>" class="inline-flex items-center gap-1.5 text-accent-dark font-semibold hover:underline"><?= svg_icon('phone', 'w-4 h-4') ?> <?= e($br['phone']) ?></a>
            <a href="mailto:<?= e($br['email']) ?>" class="inline-flex items-center gap-1.5 text-accent-dark font-semibold hover:underline"><?= svg_icon('mail', 'w-4 h-4') ?> <?= e($br['email']) ?></a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php /* Google Map always follows the PRIMARY branch (set in Admin > Branches) */ ?>
      <iframe class="w-full h-64 mt-8 rounded-2xl shadow-md border border-line/60"
        src="https://www.google.com/maps?q=<?= e(urlencode(primary_branch($conn)['address'])) ?>&output=embed"
        title="Map: <?= e(primary_branch($conn)['name'] ?? 'Our office') ?>"
        loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>

    <div class="bg-white rounded-3xl shadow-lg border border-line/60 p-8">
      <h2 class="text-2xl font-bold">Send a Message</h2>
      <div class="w-16 h-1 bg-accent rounded-full mt-3 mb-6"></div>
      <?php if ($error): ?>
        <p class="mb-4 p-3 rounded-xl bg-accent-soft border-l-4 border-accent text-sm font-semibold"><?= e($error) ?></p>
      <?php endif; ?>
      <form method="post" action="/contact-us" class="space-y-5">
        <div>
          <label for="name" class="block text-sm font-semibold uppercase tracking-wide mb-1">Name</label>
          <input type="text" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>"
                 class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30 transition-shadow">
        </div>
        <div>
          <label for="email" class="block text-sm font-semibold uppercase tracking-wide mb-1">Email</label>
          <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"
                 class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30 transition-shadow">
        </div>
        <div>
          <label for="phone" class="block text-sm font-semibold uppercase tracking-wide mb-1">Phone</label>
          <input type="tel" id="phone" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>"
                 class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30 transition-shadow">
        </div>
        <div>
          <label for="message" class="block text-sm font-semibold uppercase tracking-wide mb-1">Message</label>
          <textarea id="message" name="message" rows="5" required
                    class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30 transition-shadow"><?= e($_POST['message'] ?? '') ?></textarea>
        </div>
        <?= captcha_field($conn) ?>
        <button type="submit" class="btn-primary w-full">Send Message</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
