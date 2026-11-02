<?php
// Form CAPTCHA layer, configurable from Admin > Site Settings.
// Providers: off | recaptcha-v2 (checkbox) | recaptcha-v3 (invisible) | turnstile (Cloudflare).
// Keys live in the settings table so the admin can switch provider or rotate keys anytime.

function captcha_provider($conn = null) {
    $S = site_settings($conn);
    $p = $S['captcha_provider'] ?? 'off';
    if (!in_array($p, ['recaptcha-v2', 'recaptcha-v3', 'turnstile'], true)) return 'off';
    // Provider without keys behaves as off (never lock visitors out by misconfiguration)
    if (strpos($p, 'recaptcha') === 0 && (($S['recaptcha_site_key'] ?? '') === '' || ($S['recaptcha_secret'] ?? '') === '')) return 'off';
    if ($p === 'turnstile' && (($S['turnstile_site_key'] ?? '') === '' || ($S['turnstile_secret'] ?? '') === '')) return 'off';
    return $p;
}

// Widget markup for inside a <form>. For v3 the token is fetched on submit.
//
// The provider's script (reCAPTCHA is ~500KB of JavaScript across its frames) is NOT
// requested up front. Every page on this site carries the "Enquire Now" pop-up, so
// loading it eagerly meant every visitor paid for a form most of them never open.
// captcha_loader() below fetches it the moment the visitor actually approaches a form.
function captcha_field($conn = null) {
    $S = site_settings($conn);
    $provider = captcha_provider($conn);
    $key = '';
    $src = '';

    switch ($provider) {
        case 'recaptcha-v2':
            $key = $S['recaptcha_site_key'];
            $src = 'https://www.google.com/recaptcha/api.js';
            $html = '<div class="g-recaptcha captcha-widget" data-sitekey="' . htmlspecialchars($key, ENT_QUOTES) . '"></div>';
            break;
        case 'recaptcha-v3':
            $key = $S['recaptcha_site_key'];
            $src = 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode($key);
            $html = '<input type="hidden" name="g-recaptcha-response" class="captcha-v3-token">'
                  . '<span class="captcha-widget" hidden></span>';
            break;
        case 'turnstile':
            $key = $S['turnstile_site_key'];
            $src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
            $html = '<div class="cf-turnstile captcha-widget" data-sitekey="' . htmlspecialchars($key, ENT_QUOTES) . '"></div>';
            break;
        default:
            return '';
    }

    return $html . captcha_loader($provider, $key, $src);
}

// One-time inline loader. Emitted with the first widget on the page; later widgets
// reuse it (a page can hold both the contact form and the enquiry pop-up).
function captcha_loader($provider, $key, $src) {
    static $emitted = false;
    if ($emitted) return '';
    $emitted = true;

    // JSON_HEX_TAG keeps a stray "</script>" in a settings value from breaking out.
    $cfg = json_encode(
        ['src' => $src, 'provider' => $provider, 'key' => $key],
        JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );

    return <<<HTML
<script>
(function (w, d) {
  var cfg = {$cfg};
  var pending = null;

  // Fetch the provider script once; resolves as soon as it has run.
  w.loadCaptcha = function () {
    if (!pending) {
      pending = new Promise(function (resolve) {
        var s = d.createElement('script');
        s.src = cfg.src;
        s.async = true;
        s.onload = resolve;
        s.onerror = resolve;   // never trap the visitor behind a failed CDN
        d.head.appendChild(s);
      });
    }
    return pending;
  };

  function arm() {
    var widgets = d.querySelectorAll('.captcha-widget');
    if (!widgets.length) return;

    // Visible widgets (the contact page) load as they approach the viewport.
    if ('IntersectionObserver' in w) {
      var io = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
          if (entries[i].isIntersecting) { io.disconnect(); w.loadCaptcha(); return; }
        }
      }, { rootMargin: '300px 0px' });
      for (var i = 0; i < widgets.length; i++) io.observe(widgets[i]);
    } else {
      w.loadCaptcha();
    }

    // Hidden widgets (the pop-up) load when the pop-up is opened, and any form
    // the visitor starts filling in loads its captcha immediately.
    var openers = d.querySelectorAll('[data-enquire-open]');
    for (var j = 0; j < openers.length; j++) {
      openers[j].addEventListener('click', function () { w.loadCaptcha(); }, { once: true });
    }
    for (var k = 0; k < widgets.length; k++) {
      var form = widgets[k].closest('form');
      if (form) form.addEventListener('focusin', function () { w.loadCaptcha(); }, { once: true });
    }
  }

  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', arm);
  else arm();

  // reCAPTCHA v3 mints its token at submit time, so make sure the script is in
  // place before letting the submission through.
  if (cfg.provider === 'recaptcha-v3') {
    d.addEventListener('submit', function (ev) {
      var f = ev.target;
      var t = f.querySelector('.captcha-v3-token');
      if (!t || t.value) return;
      ev.preventDefault();
      ev.stopPropagation();
      w.loadCaptcha().then(function () {
        if (typeof grecaptcha === 'undefined') {
          if (f.requestSubmit) f.requestSubmit(); else f.submit();
          return;
        }
        grecaptcha.ready(function () {
          grecaptcha.execute(cfg.key, { action: 'enquiry' }).then(function (tok) {
            t.value = tok;
            if (f.requestSubmit) f.requestSubmit(); else f.submit();
          });
        });
      });
    }, true);
  }
})(window, document);
</script>
HTML;
}

// Server-side verification. Fails OPEN on network errors so leads are never lost
// because Google/Cloudflare was briefly unreachable from the hosting server.
function captcha_verify($conn = null) {
    $S = site_settings($conn);
    $provider = captcha_provider($conn);
    if ($provider === 'off') return true;

    if ($provider === 'turnstile') {
        $token = $_POST['cf-turnstile-response'] ?? '';
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $secret = $S['turnstile_secret'];
    } else {
        $token = $_POST['g-recaptcha-response'] ?? '';
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $secret = $S['recaptcha_secret'];
    }
    if ($token === '') return false; // widget not completed

    $body = http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => sec_client_ip()]);
    $resp = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST', 'timeout' => 8,
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $body,
        ]]);
        $resp = @file_get_contents($url, false, $ctx);
    }
    if ($resp === false || $resp === '') return true; // network failure -> fail open

    $data = json_decode($resp, true);
    if (!is_array($data)) return true;
    if (empty($data['success'])) return false;
    // v3 returns a score (0 = bot, 1 = human); require a modest minimum
    if ($provider === 'recaptcha-v3' && isset($data['score']) && (float)$data['score'] < 0.3) return false;
    return true;
}
