// Scroll-reveal animations
if ('IntersectionObserver' in window) {
  var revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (en.isIntersecting) {
        en.target.classList.add('in-view');
        revealObserver.unobserve(en.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(function (el) { revealObserver.observe(el); });

  // Stats count-up (elements with data-count, text like "150+" / "75K")
  var countObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (!en.isIntersecting) return;
      countObserver.unobserve(en.target);
      var el = en.target;
      var raw = el.getAttribute('data-count') || el.textContent;
      var m = raw.match(/^([\d.]+)(.*)$/);
      if (!m) return;
      var target = parseFloat(m[1]);
      var suffix = m[2] || '';
      var start = null;
      var step = function (ts) {
        if (!start) start = ts;
        var p = Math.min((ts - start) / 1200, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased) + suffix;
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('[data-count]').forEach(function (el) { countObserver.observe(el); });
}

// Scroll progress bar + back-to-top.
// Coalesced into one rAF per frame — reading scrollHeight on every scroll event
// forces a synchronous layout and shows up as jank on low-end phones.
var progress = document.getElementById('scroll-progress');
var backTop = document.getElementById('back-to-top');
if (progress || backTop) {
  var ticking = false;
  window.addEventListener('scroll', function () {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () {
      ticking = false;
      var h = document.documentElement;
      if (progress) {
        var scrollable = h.scrollHeight - h.clientHeight;
        progress.style.width = (scrollable > 0 ? (h.scrollTop / scrollable) * 100 : 0) + '%';
      }
      if (backTop) backTop.classList.toggle('show', h.scrollTop > 500);
    });
  }, { passive: true });
}
if (backTop) backTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

// Mobile menu
const toggle = document.getElementById('nav-toggle');
const closeBtn = document.getElementById('nav-close');
const menu = document.getElementById('mobile-menu');
if (toggle && menu) {
  toggle.addEventListener('click', () => menu.classList.remove('-translate-x-full'));
  closeBtn.addEventListener('click', () => menu.classList.add('-translate-x-full'));
  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && !toggle.contains(e.target)) menu.classList.add('-translate-x-full');
  });
}

// FAQ accordion (one open at a time)
document.querySelectorAll('[data-faq]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const body = btn.nextElementSibling;
    const isOpen = !body.classList.contains('hidden');
    document.querySelectorAll('[data-faq]').forEach((b) => {
      b.nextElementSibling.classList.add('hidden');
      b.querySelector('.faq-icon').textContent = '+';
    });
    if (!isOpen) {
      body.classList.remove('hidden');
      btn.querySelector('.faq-icon').textContent = '−';
    }
  });
});

// Enquire Now popup
var enquireModal = document.getElementById('enquire-modal');
if (enquireModal) {
  var enquireForm = document.getElementById('enquire-form');
  var enquireError = document.getElementById('enquire-error');
  var enquireSuccess = document.getElementById('enquire-success');
  var enquireBtn = document.getElementById('enquire-submit-btn');

  document.querySelectorAll('[data-enquire-open]').forEach(function (b) {
    b.addEventListener('click', function () {
      enquireModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    });
  });
  var closeEnquire = function () {
    enquireModal.classList.add('hidden');
    document.body.style.overflow = '';
    enquireForm.classList.remove('hidden');
    enquireSuccess.classList.add('hidden');
    enquireError.classList.add('hidden');
  };
  document.querySelectorAll('[data-enquire-close]').forEach(function (b) {
    b.addEventListener('click', closeEnquire);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !enquireModal.classList.contains('hidden')) closeEnquire();
  });

  enquireForm.addEventListener('submit', function (e) {
    e.preventDefault();
    enquireError.classList.add('hidden');
    enquireBtn.disabled = true;
    enquireBtn.textContent = 'Sending...';
    var data = new FormData(enquireForm);
    data.append('page', window.location.href);
    fetch('/enquiry-submit', { method: 'POST', body: data })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) {
          enquireForm.reset();
          enquireForm.classList.add('hidden');
          enquireSuccess.classList.remove('hidden');
        } else {
          enquireError.textContent = res.message || 'Something went wrong. Please try again.';
          enquireError.classList.remove('hidden');
        }
      })
      .catch(function () {
        enquireError.textContent = 'Network error. Please try again.';
        enquireError.classList.remove('hidden');
      })
      .finally(function () {
        enquireBtn.disabled = false;
        enquireBtn.textContent = 'Submit Enquiry';
        // Captcha tokens are single-use — reset the widget so a retry works
        try {
          if (window.grecaptcha && enquireForm.querySelector('.g-recaptcha')) grecaptcha.reset();
          if (window.turnstile && enquireForm.querySelector('.cf-turnstile')) turnstile.reset();
          var t = enquireForm.querySelector('.captcha-v3-token');
          if (t) t.value = '';
        } catch (err) {}
      });
  });
}

// ---------------------------------------------------------------------------
// Sliders
//
// Swiper is ~150KB of JavaScript and every slider on this site sits well below
// the fold, so the library is fetched only after the page has finished loading
// (or sooner, if the visitor scrolls towards a slider first). Nothing about the
// sliders themselves changes — they just stop delaying the first paint.
// ---------------------------------------------------------------------------
(function () {
  var sliders = document.querySelectorAll('.swiper');
  var src = document.body.getAttribute('data-swiper-js');
  if (!sliders.length || !src) return;

  var requested = false;

  function initSliders() {
    if (!window.Swiper) return;

    if (document.querySelector('.testimonials-swiper')) {
      new Swiper('.testimonials-swiper', {
        slidesPerView: 1,
        spaceBetween: 24,
        loop: true,
        navigation: { nextEl: '.testi-next', prevEl: '.testi-prev' },
        breakpoints: { 640: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } },
      });
    }

    if (document.querySelector('.logos-swiper')) {
      new Swiper('.logos-swiper', {
        slidesPerView: 2,
        spaceBetween: 32,
        loop: true,
        autoplay: { delay: 1800, disableOnInteraction: false },
        breakpoints: { 640: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 1024: { slidesPerView: 6 } },
      });
    }
  }

  function loadSwiper() {
    if (requested) return;
    requested = true;
    var s = document.createElement('script');
    s.src = src;
    s.onload = initSliders;
    document.head.appendChild(s);
  }

  // Pull it forward if a slider is about to scroll into view.
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      for (var i = 0; i < entries.length; i++) {
        if (entries[i].isIntersecting) { io.disconnect(); loadSwiper(); return; }
      }
    }, { rootMargin: '600px 0px' });
    for (var i = 0; i < sliders.length; i++) io.observe(sliders[i]);
  }

  function onIdle() {
    if (window.requestIdleCallback) requestIdleCallback(loadSwiper, { timeout: 2000 });
    else setTimeout(loadSwiper, 200);
  }
  if (document.readyState === 'complete') onIdle();
  else window.addEventListener('load', onIdle, { once: true });
})();
