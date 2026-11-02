<?php
// RFC 6238 TOTP implementation (SHA1, 6 digits, 30s period) — Google Authenticator compatible

function base32_encode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($data) as $c) {
        $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 5) as $chunk) {
        $chunk = str_pad($chunk, 5, '0');
        $out .= $alphabet[bindec($chunk)];
    }
    return $out;
}

function base32_decode_str($b32) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $b32));
    $bits = '';
    for ($i = 0; $i < strlen($b32); $i++) {
        $pos = strpos($alphabet, $b32[$i]);
        if ($pos === false) continue;
        $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 8) as $byte) {
        if (strlen($byte) === 8) $out .= chr(bindec($byte));
    }
    return $out;
}

function totp_generate_secret($length = 20) {
    return base32_encode(random_bytes($length));
}

function totp_code($secretB32, $timestamp = null, $period = 30, $digits = 6) {
    $key = base32_decode_str($secretB32);
    $counter = (int) floor(($timestamp ?? time()) / $period);
    $binCounter = pack('N*', 0) . pack('N*', $counter);
    $hash = hash_hmac('sha1', $binCounter, $key, true);
    $offset = ord(substr($hash, -1)) & 0x0F;
    $value = (
        ((ord($hash[$offset]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF)
    );
    return str_pad((string)($value % (10 ** $digits)), $digits, '0', STR_PAD_LEFT);
}

// Verify with +/- 1 window drift tolerance
function totp_verify($secretB32, $code, $window = 1) {
    $code = preg_replace('/\s+/', '', (string)$code);
    if (!preg_match('/^\d{6}$/', $code)) return false;
    $now = time();
    for ($i = -$window; $i <= $window; $i++) {
        if (hash_equals(totp_code($secretB32, $now + $i * 30), $code)) return true;
    }
    return false;
}

function totp_uri($secretB32, $account, $issuer = 'TopWebExpert Admin') {
    return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
        . '?secret=' . $secretB32
        . '&issuer=' . rawurlencode($issuer)
        . '&algorithm=SHA1&digits=6&period=30';
}
