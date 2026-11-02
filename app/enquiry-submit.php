<?php
// AJAX endpoint: capture enquiry lead into DB and email it (lead is saved even if mail fails)
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/captcha.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!captcha_verify($conn)) {
    echo json_encode(['success' => false, 'message' => 'Captcha verification failed. Please complete the captcha and try again.']);
    exit;
}

// Spam/flood protection: max 3 submissions per IP per minute.
// Flooders also get an IP block (repeat offenders escalate to a 7-day block).
if (!sec_form_throttle($conn, 3, 60)) {
    sec_block_ip($conn, sec_client_ip(), 'enquiry-flood');
    sec_track_blocked($conn);
    echo json_encode(['success' => false, 'message' => 'Too many submissions. Please wait a minute and try again.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$pageUrl = trim($_POST['page'] ?? '');

if ($name === '' || $email === '' || $phone === '' || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Capture lead
$fullMessage = $message . ($pageUrl ? "\n\n[Enquiry from page: $pageUrl]" : '');
$clientIp = sec_client_ip();
$stmt = $conn->prepare("INSERT INTO inquiries (name, email, phone, message, ip) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('sssss', $name, $email, $phone, $fullMessage, $clientIp);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Could not save your enquiry. Please try again.']);
    exit;
}
sec_track_inquiry($conn, $clientIp);

// Send email notification (best-effort; lead already saved)
$mailSent = false;
try {
    require_once WEB_ROOT . '/libs/phpmailer/src/Exception.php';
    require_once WEB_ROOT . '/libs/phpmailer/src/PHPMailer.php';
    require_once WEB_ROOT . '/libs/phpmailer/src/SMTP.php';

    $cfg = app_config();
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $cfg['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $cfg['smtp_user'];
    $mail->Password = $cfg['smtp_pass'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$cfg['smtp_port'];
    $mail->Timeout = 10;

    $mail->setFrom($cfg['smtp_from'], 'Website Enquiry');
    $mail->addAddress($cfg['smtp_to'], 'TopWebExpert Leads');
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'New Enquiry from Website — ' . $name;
    $safe = fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    $mail->Body = '<h2>New Enquiry (Enquire Now popup)</h2>'
        . '<p><strong>Name:</strong> ' . $safe($name) . '</p>'
        . '<p><strong>Email:</strong> ' . $safe($email) . '</p>'
        . '<p><strong>Phone:</strong> ' . $safe($phone) . '</p>'
        . '<p><strong>Message:</strong> ' . nl2br($safe($message)) . '</p>'
        . ($pageUrl ? '<p><strong>Page:</strong> ' . $safe($pageUrl) . '</p>' : '');
    $mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\nMessage: $message\nPage: $pageUrl";
    $mailSent = $mail->send();
} catch (Throwable $e) {
    $mailSent = false; // lead is already in the database
}

echo json_encode(['success' => true, 'mailed' => $mailSent]);
