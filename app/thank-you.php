<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Thank You - TopWebExpert';
require __DIR__ . '/includes/header.php';
?>

<section class="bg-white py-32">
  <div class="max-w-2xl mx-auto px-4 text-center">
    <div class="icon-badge w-24 h-24 rounded-full mx-auto animate-bounce"><?= svg_icon('check', 'w-12 h-12') ?></div>
    <h1 class="text-4xl md:text-5xl font-bold mt-8">Thank <span class="text-accent">You!</span></h1>
    <p class="mt-6 text-ink-soft leading-relaxed">Your message has been sent successfully. We will get back to you as soon as possible!</p>
    <a href="/" class="btn-primary mt-10">Back to Home</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
