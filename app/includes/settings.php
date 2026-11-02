<?php
// Site settings loaded from DB (single query per request, cached statically)
function site_settings($conn = null) {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [
        'site_name' => 'TopWebExpert',
        'site_url' => 'http://localhost:8090',
        'address' => 'Chandhat, Palwal Kailash Nagar HSIIDC Dudhola Industrial Area Haryana',
        'phone' => '+91 9050126213',
        'email' => 'info@topwebexpert.com',
        'whatsapp' => '9050126213',
        'address_locality' => '', 'address_region' => '', 'postal_code' => '',
        'google_business_url' => '',
        'facebook' => '', 'instagram' => '', 'linkedin' => '',
        'twitter' => '', 'youtube' => '', 'medium' => '',
        'footer_about' => '',
        'footer_copyright' => '',
        'gtm_id' => '', 'ga_id' => '', 'clarity_id' => '',
        'captcha_provider' => 'off',
        'recaptcha_site_key' => '', 'recaptcha_secret' => '',
        'turnstile_site_key' => '', 'turnstile_secret' => '',
    ];
    if ($conn) {
        $res = @$conn->query("SELECT skey, svalue FROM settings");
        if ($res) while ($r = $res->fetch_assoc()) $cache[$r['skey']] = $r['svalue'];
    }
    return $cache;
}

// All active branches, primary first. Falls back to settings if the table is empty.
function site_branches($conn = null) {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    if ($conn) {
        $res = @$conn->query("SELECT * FROM branches WHERE is_active = 1 ORDER BY is_primary DESC, sort_order, id");
        if ($res) $cache = $res->fetch_all(MYSQLI_ASSOC);
    }
    if (!$cache) {
        $s = site_settings($conn);
        $cache = [[
            'id' => 0, 'name' => 'Head Office', 'address' => $s['address'],
            'phone' => $s['phone'], 'email' => $s['email'], 'is_primary' => 1, 'is_active' => 1,
        ]];
    }
    return $cache;
}

// The primary branch (first in the ordered list)
function primary_branch($conn = null) {
    $b = site_branches($conn);
    return $b[0];
}
