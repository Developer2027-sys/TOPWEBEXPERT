<?php
// One-time migration: admin security, settings, SEO, locations, city pages
require dirname(__DIR__) . '/includes/db.php';

$conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS totp_secret VARCHAR(64) NULL");
$conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS totp_enabled TINYINT(1) NOT NULL DEFAULT 0");

$conn->query("CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_time (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS settings (
  skey VARCHAR(100) PRIMARY KEY,
  svalue TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS page_seo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  route VARCHAR(255) NOT NULL UNIQUE,
  meta_title VARCHAR(255) NULL,
  meta_description TEXT NULL,
  meta_keywords TEXT NULL,
  og_image VARCHAR(255) NULL,
  noindex TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  country VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS city_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_slug VARCHAR(150) NOT NULL,
  service_name VARCHAR(150) NOT NULL,
  location_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  image VARCHAR(255) NULL,
  meta_title VARCHAR(255) NULL,
  meta_description TEXT NULL,
  meta_keywords TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_service_loc (service_slug, location_id),
  FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Secure the admin account: bcrypt password (change after first login)
$newPass = password_hash('TopWeb@Admin2026', PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->bind_param('s', $newPass);
$stmt->execute();
echo "admin password reset: " . $stmt->affected_rows . "\n";

// Default settings
$defaults = [
  'site_name' => 'TopWebExpert',
  'address' => 'Chandhat, Palwal Kailash Nagar HSIIDC Dudhola Industrial Area Haryana',
  'phone' => '+91 9050126213',
  'email' => 'info@topwebexpert.com',
  'whatsapp' => '9050126213',
  'facebook' => 'https://www.facebook.com/profile.php?id=100087658086893',
  'instagram' => 'https://www.instagram.com/topwebexpert_/',
  'linkedin' => 'https://www.linkedin.com/company/topwebexpert/',
  'footer_about' => "TopWebExpert is the best digital marketing agency in Gurugram, we're committed to helping you build your business online. Whether you're just starting out or looking to take your digital presence to the next level, we have the expertise and experience to help you succeed.",
  'site_url' => 'http://localhost:8090',
];
$stmt = $conn->prepare("INSERT IGNORE INTO settings (skey, svalue) VALUES (?, ?)");
foreach ($defaults as $k => $v) { $stmt->bind_param('ss', $k, $v); $stmt->execute(); }
echo "settings seeded\n";

// Seed popular cities worldwide
$cities = [
  'India' => ['Noida','Delhi','Mumbai','Bangalore','Hyderabad','Chennai','Kolkata','Pune','Gurgaon','Jaipur','Ahmedabad','Lucknow','Chandigarh','Indore'],
  'United States' => ['New York','Los Angeles','Chicago','Houston','San Francisco','Miami','Seattle','Boston','Austin','Dallas'],
  'United Kingdom' => ['London','Manchester','Birmingham','Leeds','Glasgow'],
  'United Arab Emirates' => ['Dubai','Abu Dhabi','Sharjah'],
  'Canada' => ['Toronto','Vancouver','Montreal','Calgary'],
  'Australia' => ['Sydney','Melbourne','Brisbane','Perth'],
  'Germany' => ['Berlin','Munich','Frankfurt','Hamburg'],
  'France' => ['Paris','Lyon','Marseille'],
  'Singapore' => ['Singapore'],
  'Japan' => ['Tokyo','Osaka'],
  'Netherlands' => ['Amsterdam','Rotterdam'],
  'Spain' => ['Madrid','Barcelona'],
  'Italy' => ['Rome','Milan'],
  'Turkey' => ['Istanbul','Ankara'],
  'Indonesia' => ['Jakarta','Bali'],
  'Malaysia' => ['Kuala Lumpur'],
  'Thailand' => ['Bangkok'],
  'Philippines' => ['Manila'],
  'Vietnam' => ['Ho Chi Minh City','Hanoi'],
  'South Korea' => ['Seoul'],
  'Pakistan' => ['Karachi','Lahore','Islamabad'],
  'Bangladesh' => ['Dhaka'],
  'Sri Lanka' => ['Colombo'],
  'Nepal' => ['Kathmandu'],
  'New Zealand' => ['Auckland'],
  'Ireland' => ['Dublin'],
  'Sweden' => ['Stockholm'],
  'Norway' => ['Oslo'],
  'Denmark' => ['Copenhagen'],
  'Finland' => ['Helsinki'],
  'Switzerland' => ['Zurich','Geneva'],
  'Austria' => ['Vienna'],
  'Belgium' => ['Brussels'],
  'Poland' => ['Warsaw'],
  'Portugal' => ['Lisbon'],
  'Greece' => ['Athens'],
  'Israel' => ['Tel Aviv'],
  'Saudi Arabia' => ['Riyadh','Jeddah'],
  'Qatar' => ['Doha'],
  'Kuwait' => ['Kuwait City'],
  'Oman' => ['Muscat'],
  'Bahrain' => ['Manama'],
  'Egypt' => ['Cairo'],
  'South Africa' => ['Johannesburg','Cape Town'],
  'Nigeria' => ['Lagos','Abuja'],
  'Kenya' => ['Nairobi'],
  'Brazil' => ['Sao Paulo','Rio de Janeiro'],
  'Mexico' => ['Mexico City'],
  'Argentina' => ['Buenos Aires'],
  'Chile' => ['Santiago'],
  'Colombia' => ['Bogota'],
  'Peru' => ['Lima'],
  'Russia' => ['Moscow'],
  'China' => ['Shanghai','Beijing','Shenzhen'],
];
require_once dirname(__DIR__) . '/includes/functions.php';
$stmt = $conn->prepare("INSERT IGNORE INTO locations (country, city, slug) VALUES (?, ?, ?)");
$count = 0;
foreach ($cities as $country => $list) {
    foreach ($list as $city) {
        $slug = slugify($city);
        $stmt->bind_param('sss', $country, $city, $slug);
        $stmt->execute();
        $count += $stmt->affected_rows;
    }
}
echo "locations seeded: $count\n";
echo "migration done\n";
