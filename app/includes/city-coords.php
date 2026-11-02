<?php
// Known coordinates (lat, lng) for cities, keyed by location slug.
// Used to seed locations.lat/lng and to auto-fill coordinates when the admin
// adds a known city without typing them. Unknown cities can get coordinates
// manually via Admin > Locations.
function city_known_coords($slug = null) {
    static $map = null;
    if ($map === null) {
        $map = city_coords_base();
        // Bulk-generated coordinates for the full locations table (see scripts/migrate-nearby.php)
        $dataFile = __DIR__ . '/city-coords-data.php';
        if (is_file($dataFile)) $map += require $dataFile;
    }
    if ($slug === null) return $map;
    return $map[$slug] ?? null;
}

function city_coords_base() {
    return [
        // India
        'noida' => [28.5355, 77.3910], 'delhi' => [28.6139, 77.2090], 'mumbai' => [19.0760, 72.8777],
        'bangalore' => [12.9716, 77.5946], 'hyderabad' => [17.3850, 78.4867], 'chennai' => [13.0827, 80.2707],
        'kolkata' => [22.5726, 88.3639], 'pune' => [18.5204, 73.8567], 'gurgaon' => [28.4595, 77.0266],
        'jaipur' => [26.9124, 75.7873], 'ahmedabad' => [23.0225, 72.5714], 'lucknow' => [26.8467, 80.9462],
        'chandigarh' => [30.7333, 76.7794], 'indore' => [22.7196, 75.8577],
        // United States
        'new-york' => [40.7128, -74.0060], 'los-angeles' => [34.0522, -118.2437], 'chicago' => [41.8781, -87.6298],
        'houston' => [29.7604, -95.3698], 'san-francisco' => [37.7749, -122.4194], 'miami' => [25.7617, -80.1918],
        'seattle' => [47.6062, -122.3321], 'boston' => [42.3601, -71.0589], 'austin' => [30.2672, -97.7431],
        'dallas' => [32.7767, -96.7970],
        // United Kingdom
        'london' => [51.5074, -0.1278], 'manchester' => [53.4808, -2.2426], 'birmingham' => [52.4862, -1.8904],
        'leeds' => [53.8008, -1.5491], 'glasgow' => [55.8642, -4.2518],
        // United Arab Emirates
        'dubai' => [25.2048, 55.2708], 'abu-dhabi' => [24.4539, 54.3773], 'sharjah' => [25.3463, 55.4209],
        // Canada
        'toronto' => [43.6532, -79.3832], 'vancouver' => [49.2827, -123.1207], 'montreal' => [45.5017, -73.5673],
        'calgary' => [51.0447, -114.0719],
        // Australia
        'sydney' => [-33.8688, 151.2093], 'melbourne' => [-37.8136, 144.9631], 'brisbane' => [-27.4698, 153.0251],
        'perth' => [-31.9505, 115.8605],
        // Germany
        'berlin' => [52.5200, 13.4050], 'munich' => [48.1351, 11.5820], 'frankfurt' => [50.1109, 8.6821],
        'hamburg' => [53.5511, 9.9937],
        // France
        'paris' => [48.8566, 2.3522], 'lyon' => [45.7640, 4.8357], 'marseille' => [43.2965, 5.3698],
        // Asia
        'singapore' => [1.3521, 103.8198], 'tokyo' => [35.6762, 139.6503], 'osaka' => [34.6937, 135.5023],
        'jakarta' => [-6.2088, 106.8456], 'bali' => [-8.4095, 115.1889], 'kuala-lumpur' => [3.1390, 101.6869],
        'bangkok' => [13.7563, 100.5018], 'manila' => [14.5995, 120.9842], 'ho-chi-minh-city' => [10.8231, 106.6297],
        'hanoi' => [21.0278, 105.8342], 'seoul' => [37.5665, 126.9780], 'shanghai' => [31.2304, 121.4737],
        'beijing' => [39.9042, 116.4074], 'shenzhen' => [22.5431, 114.0579],
        // South Asia
        'karachi' => [24.8607, 67.0011], 'lahore' => [31.5204, 74.3587], 'islamabad' => [33.6844, 73.0479],
        'dhaka' => [23.8103, 90.4125], 'colombo' => [6.9271, 79.8612], 'kathmandu' => [27.7172, 85.3240],
        // Europe
        'amsterdam' => [52.3676, 4.9041], 'rotterdam' => [51.9244, 4.4777], 'madrid' => [40.4168, -3.7038],
        'barcelona' => [41.3874, 2.1686], 'rome' => [41.9028, 12.4964], 'milan' => [45.4642, 9.1900],
        'istanbul' => [41.0082, 28.9784], 'ankara' => [39.9334, 32.8597], 'dublin' => [53.3498, -6.2603],
        'stockholm' => [59.3293, 18.0686], 'oslo' => [59.9139, 10.7522], 'copenhagen' => [55.6761, 12.5683],
        'helsinki' => [60.1699, 24.9384], 'zurich' => [47.3769, 8.5417], 'geneva' => [46.2044, 6.1432],
        'vienna' => [48.2082, 16.3738], 'brussels' => [50.8503, 4.3517], 'warsaw' => [52.2297, 21.0122],
        'lisbon' => [38.7223, -9.1393], 'athens' => [37.9838, 23.7275], 'moscow' => [55.7558, 37.6173],
        // Middle East & Africa
        'tel-aviv' => [32.0853, 34.7818], 'riyadh' => [24.7136, 46.6753], 'jeddah' => [21.4858, 39.1925],
        'doha' => [25.2854, 51.5310], 'kuwait-city' => [29.3759, 47.9774], 'muscat' => [23.5880, 58.3829],
        'manama' => [26.2285, 50.5860], 'cairo' => [30.0444, 31.2357], 'johannesburg' => [-26.2041, 28.0473],
        'cape-town' => [-33.9249, 18.4241], 'lagos' => [6.5244, 3.3792], 'abuja' => [9.0765, 7.3986],
        'nairobi' => [-1.2921, 36.8219],
        // Americas & Oceania
        'sao-paulo' => [-23.5505, -46.6333], 'rio-de-janeiro' => [-22.9068, -43.1729], 'mexico-city' => [19.4326, -99.1332],
        'buenos-aires' => [-34.6037, -58.3816], 'santiago' => [-33.4489, -70.6693], 'bogota' => [4.7110, -74.0721],
        'lima' => [-12.0464, -77.0428], 'auckland' => [-36.8485, 174.7633],
    ];
}
