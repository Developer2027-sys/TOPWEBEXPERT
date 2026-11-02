</main>

<?php
require_once __DIR__ . '/captcha.php';
$S = site_settings($conn ?? null); $waNum = preg_replace('/\D+/', '', $S['whatsapp']);
// WhatsApp pre-filled message carries the page name + URL so the team knows which page the visitor came from
$waPage = trim(preg_replace('/\s*[|\-–]\s*Top\s*Web\s*Expert.*$/i', '', $pageTitle ?? ''));
$waPageUrl = rtrim($S['site_url'], '/') . (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$waText = rawurlencode('Hi, Topwebexpert Team! I am on this page: ' . ($waPage !== '' ? '"' . $waPage . '" - ' : '') . $waPageUrl . ' and I would like to know more.');
?>
<!-- Footer -->
<footer class="bg-ink text-gray-300">
  <div class="max-w-7xl mx-auto px-4 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
    <div>
      <?php /* Reversed-out logo (built by app/scripts/optimize-images.php) so the
               wordmark stays legible on the dark footer. */ ?>
      <a href="/" class="inline-block" aria-label="<?= e($S['site_name']) ?> home">
        <img src="/assets/logo-white.png" alt="<?= e($S['site_name']) ?> logo"
             class="w-44 h-auto"<?= img_size_attrs('/assets/logo-white.png') ?> loading="lazy" decoding="async">
      </a>
      <p class="mt-4 text-sm leading-relaxed"><?= e($S['footer_about']) ?></p>
    </div>
    <div>
      <h3 class="text-white font-bold uppercase tracking-wider mb-4">Useful Links</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="/our-services" class="hover:text-accent transition-colors">Our Services</a></li>
        <li><a href="/shopify-services" class="hover:text-accent transition-colors">Shopify Services</a></li>
        <li><a href="/blogs" class="hover:text-accent transition-colors">Blogs</a></li>
        <li><a href="/terms-and-conditions" class="hover:text-accent transition-colors">Terms and Conditions</a></li>
        <li><a href="/privacy-policy" class="hover:text-accent transition-colors">Privacy Policy</a></li>
      </ul>
    </div>
    <div>
      <h3 class="text-white font-bold uppercase tracking-wider mb-4">Get in Touch</h3>
      <?php /* First two active branches (primary first, then admin sort order — managed in Admin > Branches) */ ?>
      <?php foreach (array_slice(site_branches($conn ?? null), 0, 2) as $bi => $br): ?>
      <div class="<?= $bi > 0 ? 'mt-5 pt-4 border-t border-white/10' : '' ?>">
        <p class="text-accent text-xs font-bold uppercase tracking-widest mb-2"><?= e($br['name']) ?></p>
        <ul class="space-y-2 text-sm">
          <li class="flex gap-3"><span class="text-accent shrink-0 mt-0.5"><?= svg_icon('pin', 'w-4 h-4') ?></span> <?= e($br['address']) ?></li>
          <li><a href="mailto:<?= e($br['email']) ?>" class="flex gap-3 items-center hover:text-accent transition-colors"><span class="text-accent"><?= svg_icon('mail', 'w-4 h-4') ?></span> <?= e($br['email']) ?></a></li>
          <li><a href="tel:<?= e(preg_replace('/\D+/', '', $br['phone'])) ?>" class="flex gap-3 items-center hover:text-accent transition-colors"><span class="text-accent"><?= svg_icon('phone', 'w-4 h-4') ?></span> <?= e($br['phone']) ?></a></li>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
    <div>
      <h3 class="text-white font-bold uppercase tracking-wider mb-4">Follow Us</h3>
      <ul class="space-y-2 text-sm">
        <?php if ($S['facebook']): ?><li><a href="<?= e($S['facebook']) ?>" target="_blank" rel="noopener" class="hover:text-accent transition-colors">Facebook</a></li><?php endif; ?>
        <?php if ($S['instagram']): ?><li><a href="<?= e($S['instagram']) ?>" target="_blank" rel="noopener" class="hover:text-accent transition-colors">Instagram</a></li><?php endif; ?>
        <?php if ($S['linkedin']): ?><li><a href="<?= e($S['linkedin']) ?>" target="_blank" rel="noopener" class="hover:text-accent transition-colors">LinkedIn</a></li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10">
    <p class="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-accent capitalize"><?= e($S['footer_copyright'] ?: '©2024-25 copyright by Topwebexpert . all right reserved.') ?></p>
  </div>
</footer>

<!-- Floating WhatsApp button (desktop/tablet) -->
<a href="https://api.whatsapp.com/send/?phone=<?= e($waNum) ?>&text=<?= $waText ?>&type=phone_number&app_absent=0"
   target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
   class="hidden sm:flex fixed bottom-5 left-5 z-50 w-14 h-14 rounded-full bg-[#25D366] items-center justify-center shadow-xl hover:scale-110 transition-transform">
  <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<!-- Back to top -->
<button id="back-to-top" aria-label="Back to top">
  <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
</button>

<!-- Mobile sticky bottom bar: WhatsApp + Enquire Now -->
<div class="sm:hidden fixed bottom-0 inset-x-0 z-50 grid grid-cols-2 shadow-[0_-4px_16px_rgba(0,0,0,0.15)]">
  <a href="https://api.whatsapp.com/send/?phone=<?= e($waNum) ?>&text=<?= $waText ?>&type=phone_number&app_absent=0"
     target="_blank" rel="noopener"
     class="flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold uppercase tracking-wide text-sm py-4">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    WhatsApp
  </a>
  <button type="button" data-enquire-open
     class="flex items-center justify-center gap-2 hero-grad text-white font-bold uppercase tracking-wide text-sm py-4">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7.5l9 6 9-6"/></svg>
    Enquire Now
  </button>
</div>
<div class="sm:hidden h-14"></div><!-- spacer so content is not hidden behind the bar -->

<!-- Enquire Now popup -->
<div id="enquire-modal" class="hidden fixed inset-0 z-[60]">
  <div class="absolute inset-0 bg-ink/70 backdrop-blur-sm" data-enquire-close></div>
  <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 sm:w-[28rem] max-h-[90vh] overflow-y-auto bg-white rounded-3xl shadow-2xl">
    <div class="hero-grad text-white p-6 rounded-t-3xl relative overflow-hidden">
      <div class="absolute inset-0 hero-dots opacity-30"></div>
      <button type="button" data-enquire-close aria-label="Close" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/40 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      <div class="relative">
        <h2 class="text-2xl font-bold">Enquire Now</h2>
        <p class="mt-1 text-white/90 text-sm">Fill the form — our team will contact you within 24 hours.</p>
      </div>
    </div>
    <form id="enquire-form" class="p-6 space-y-4">
      <div id="enquire-error" class="hidden p-3 rounded-xl bg-red-50 border-l-4 border-red-500 text-sm font-semibold"></div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-ink-soft mb-1.5" for="enq-name">Name</label>
        <input id="enq-name" type="text" name="name" required class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-ink-soft mb-1.5" for="enq-email">Email</label>
        <input id="enq-email" type="email" name="email" required class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-ink-soft mb-1.5" for="enq-phone">Phone</label>
        <input id="enq-phone" type="tel" name="phone" required class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30">
      </div>
      <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-ink-soft mb-1.5" for="enq-message">Message</label>
        <textarea id="enq-message" name="message" rows="3" required class="w-full rounded-xl border border-line px-4 py-3 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/30"></textarea>
      </div>
      <?= captcha_field($conn ?? null) ?>
      <button type="submit" id="enquire-submit-btn" class="btn-primary w-full !rounded-xl">Submit Enquiry</button>
    </form>
    <div id="enquire-success" class="hidden p-10 text-center">
      <div class="icon-badge w-20 h-20 rounded-full mx-auto">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      </div>
      <h3 class="mt-6 text-2xl font-bold">Thank You!</h3>
      <p class="mt-2 text-ink-soft text-sm">Your enquiry has been received. We will get back to you shortly.</p>
      <button type="button" data-enquire-close class="btn-outline mt-6 !rounded-xl">Close</button>
    </div>
  </div>
</div>

<?php /* Swiper (~150KB) is not referenced here: main.js fetches it from the
         data-swiper-js attribute on <body> once the page has finished loading,
         so it never competes with the first paint. */ ?>
<script src="<?= e(asset('/assets/js/main.js')) ?>" defer></script>
</body>
</html>
