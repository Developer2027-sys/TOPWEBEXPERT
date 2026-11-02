<?php
// Inline SVG icon set (stroke-based, inherits currentColor)
function svg_icon($name, $class = 'w-7 h-7') {
    $body = [
        'search'   => '<circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/>',
        'share'    => '<circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><line x1="8.2" y1="10.8" x2="15.8" y2="7.2"/><line x1="8.2" y1="13.2" x2="15.8" y2="16.8"/>',
        'click'    => '<path d="M4 4l16 7-6.5 2.5L11 20z"/>',
        'whatsapp' => '<path d="M12 3a9 9 0 00-7.8 13.5L3 21l4.7-1.2A9 9 0 1012 3z"/><path d="M9 8.5c0 4 2.5 6.5 6.5 6.5l1-2-2.5-1-1 1c-1.5-.5-2-1-2.5-2.5l1-1-1-2.5z"/>',
        'sms'      => '<path d="M4 5h16v11H9l-5 4z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="12" x2="13" y2="12"/>',
        'pen'      => '<path d="M4 20l4-1L20 7l-3-3L5 16l-1 4z"/><line x1="14" y1="6" x2="18" y2="10"/>',
        'laptop'   => '<rect x="4" y="5" width="16" height="10" rx="1"/><line x1="2" y1="18" x2="22" y2="18"/>',
        'palette'  => '<path d="M12 3a9 9 0 100 18c1.5 0 2-1 1.5-2s0-2 1.5-2h2a4 4 0 004-4c0-5-4-10-9-10z"/><circle cx="8" cy="10" r="1.2"/><circle cx="12" cy="7.5" r="1.2"/><circle cx="16" cy="10" r="1.2"/>',
        'google'   => '<circle cx="12" cy="12" r="9"/><path d="M16 12h-4"/><path d="M16 12a4 4 0 11-1.2-2.8"/>',
        'facebook' => '<circle cx="12" cy="12" r="9"/><path d="M13.5 21v-7h2.5l.5-3h-3V9.5c0-1 .5-1.5 1.5-1.5h1.5V5.2C15.8 5.1 15 5 14.2 5 12 5 10.5 6.3 10.5 8.7V11H8v3h2.5v7"/>',
        'cart'     => '<path d="M3 5h2l2.5 10h10L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="16" cy="19" r="1.6"/>',
        'wrench'   => '<path d="M14.5 6.5a4 4 0 015.5 5L13 18.5a2.1 2.1 0 01-3-3L17 8.5a4 4 0 00-5.5-5l-1 1L14 8"/>',
        'users'    => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19a5.5 5.5 0 0111 0"/><circle cx="16.5" cy="9.5" r="2.3"/><path d="M15.5 14.5a4.8 4.8 0 015 4.5"/>',
        'smile'    => '<circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="0.8" fill="currentColor"/><circle cx="15" cy="10" r="0.8" fill="currentColor"/><path d="M8.5 14.5a4.5 4.5 0 007 0"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'pin'      => '<path d="M12 21s-7-5.6-7-11a7 7 0 0114 0c0 5.4-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7.5l9 6 9-6"/>',
        'phone'    => '<path d="M5 4h4l1.5 4.5L8 10a13 13 0 006 6l1.5-2.5L20 15v4a2 2 0 01-2 2A17 17 0 013 6a2 2 0 012-2z"/>',
        'check'    => '<path d="M5 13l4 4L19 7"/>',
        'quote'    => '<path d="M7 7h4v4H8a3 3 0 003 3v2a5 5 0 01-5-5V7z"/><path d="M14 7h4v4h-3a3 3 0 003 3v2a5 5 0 01-5-5V7z"/>',
        'grid'     => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
        'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 010 18M12 3a14 14 0 000 18"/>',
        'image'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 15l-5-5-9 9"/>',
    ];
    $svg = $body[$name] ?? $body['check'];
    return '<svg class="' . htmlspecialchars($class, ENT_QUOTES) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $svg . '</svg>';
}

// Map card titles to icons
function icon_for($title) {
    $map = [
        'Search Engine Optimization' => 'search',
        'Social Media Optimization' => 'share',
        'Pay-Per-Click (PPC)' => 'click',
        'WhatsApp Marketing' => 'whatsapp',
        'SMS Marketing' => 'sms',
        'Content Marketing' => 'pen',
        'Website Designing' => 'laptop',
        'Graphic Designing' => 'palette',
        'Google Ads' => 'google',
        'Facebook Ads' => 'facebook',
        'E-Commerce Solution' => 'cart',
        'Shopify Development & Agency Services' => 'cart',
        'Website Maintenance' => 'wrench',
        'Experienced Team' => 'users',
        'We are Friendly' => 'smile',
        'We are On Time' => 'clock',
    ];
    return $map[$title] ?? 'check';
}
