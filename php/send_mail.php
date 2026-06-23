<?php
/**
 * YashRaj Systems & Services - Contact Form Mail Handler
 * Securely processes enquiries and forwards them via Gmail SMTP using PHPMailer.
 */

// Force UTF-8 JSON response
header('Content-Type: application/json; charset=utf-8');

// Strict CORS / Origin Protection
$allowed_origins = [
    'https://vikashsingh9999.github.io',
    'http://localhost',
    'http://127.0.0.1'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (!empty($origin)) {
    $normalized_origin = rtrim($origin, '/');
    $origin_host = parse_url($origin, PHP_URL_HOST);
    $server_host = $_SERVER['HTTP_HOST'] ?? '';
    
    // Remove port numbers from host comparison if present
    if (strpos($server_host, ':') !== false) {
        $server_host = explode(':', $server_host)[0];
    }
    
    $is_allowed = false;
    
    // Allow if request is from the same domain
    if ($origin_host && $server_host && (strcasecmp($origin_host, $server_host) === 0 || strcasecmp(str_replace('www.', '', $origin_host), str_replace('www.', '', $server_host)) === 0)) {
        $is_allowed = true;
    }
    // Check direct matching allowed origins
    elseif (in_array($normalized_origin, $allowed_origins)) {
        $is_allowed = true;
    }
    // Allow local development on localhost or 127.0.0.1 with any port
    elseif (preg_match('/^http:\/\/localhost(:\d+)?$/', $normalized_origin) || 
            preg_match('/^http:\/\/127\.0\.0\.1(:\d+)?$/', $normalized_origin)) {
        $is_allowed = true;
    }

    if ($is_allowed) {
        header("Access-Control-Allow-Origin: $normalized_origin");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
    } else {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized cross-origin request block.']);
        exit;
    }
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['status' => 'error', 'message' => 'Request method not supported. Only POST requests allowed.']);
    exit;
}

// Enable sessions safely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'use_strict_mode' => true,
    ]);
}

// Rate Limiting: 1 submission per 45 seconds per session
$rate_limit_seconds = 45;
if (isset($_SESSION['last_submit_time'])) {
    $elapsed = time() - $_SESSION['last_submit_time'];
    if ($elapsed < $rate_limit_seconds) {
        header('HTTP/1.1 429 Too Many Requests');
        echo json_encode([
            'status' => 'error',
            'message' => 'Spam protection active. Please wait ' . ($rate_limit_seconds - $elapsed) . ' seconds before submitting again.'
        ]);
        exit;
    }
}

// Include PHPMailer Classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Parse POST/JSON Request
$input = [];
$content_type = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if (strpos($content_type, 'application/json') !== false) {
    $raw_json = file_get_contents('php://input');
    $input = json_decode($raw_json, true) ?? [];
} else {
    $input = $_POST;
}

// Sanitize Inputs
$name = trim($input['name'] ?? '');
$company = trim($input['company'] ?? '');
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$service = trim($input['service'] ?? '');
$message = trim($input['message'] ?? '');

$name = htmlspecialchars(strip_tags($name), ENT_QUOTES, 'UTF-8');
$company = htmlspecialchars(strip_tags($company), ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(strip_tags($phone), ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$service = htmlspecialchars(strip_tags($service), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(strip_tags($message), ENT_QUOTES, 'UTF-8');

// Input Validation Checks
if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid name (2-100 characters).']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit;
}

// Phone: support formats like +91 1234567890 or 0987654321
if (empty($phone) || strlen($phone) < 8 || strlen($phone) > 22 || 
    (!preg_match('/^[+][0-9\s\-()]+$/', $phone) && !preg_match('/^[0-9\s\-()]+$/', $phone))) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid phone number (digits and spaces/+ only).']);
    exit;
}

// Service Validation Against whitelist
$allowed_services = [
    'Industrial Automation Solutions',
    'PLC, SCADA & DCS Systems',
    'Instrumentation Engineering',
    'Industrial Control Panels',
    'Sensors & Field Instruments Supply',
    'Calibration & Maintenance Services',
    'Electrical & Control Integration',
    'Training Programs'
];
if (!empty($service) && !in_array($service, $allowed_services)) {
    $service = 'Custom / Other Service Interest';
}

if (empty($service)) {
    $service = 'General Enquiry';
}

if (empty($message) || strlen($message) < 5 || strlen($message) > 5000) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a message details (min 5 characters).']);
    exit;
}

// SMTP configuration parameters
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_SECURE', PHPMailer::ENCRYPTION_SMTPS);
define('SMTP_USER', 'yashrajsystem07@gmail.com');
define('SMTP_PASS', 'xhvd pzhl sdeg epnl'); // App Password

// Helper: send notification email
function sendNotificationMail($name, $company, $phone, $email, $service, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;

        // No verbose debug in production (prevent credential leaks)
        $mail->SMTPDebug  = SMTP::DEBUG_OFF; 

        // Recipients
        $mail->setFrom(SMTP_USER, 'YashRaj Systems WebPortal');
        $mail->addAddress('sales.yashrajsystems@gmail.com');
        $mail->addAddress('sales@yashrajsystems.com');
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Web Enquiry: ' . $name;
        
        $ip_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1a1a1a; background-color: #f7f7f7; padding: 20px; }
                .card { background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; }
                .hdr { background-color: #8C1007; color: #ffffff; padding: 20px; font-size: 20px; font-weight: bold; text-align: center; }
                .body { padding: 30px; }
                .item { margin-bottom: 18px; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; }
                .lbl { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #8C1007; letter-spacing: 0.5px; }
                .val { font-size: 15px; margin-top: 5px; color: #2b2b2b; white-space: pre-wrap; }
                .ftr { background-color: #fafafa; padding: 15px; text-align: center; font-size: 11px; color: #888888; border-top: 1px solid #eeeeee; }
            </style>
        </head>
        <body>
            <div class='card'>
                <div class='hdr'>New Enquiry Received</div>
                <div class='body'>
                    <div class='item'><div class='lbl'>Full Name</div><div class='val'>{$name}</div></div>
                    <div class='item'><div class='lbl'>Company</div><div class='val'>".(!empty($company) ? $company : 'N/A')."</div></div>
                    <div class='item'><div class='lbl'>Phone</div><div class='val'>{$phone}</div></div>
                    <div class='item'><div class='lbl'>Email</div><div class='val'>{$email}</div></div>
                    <div class='item'><div class='lbl'>Service Interest</div><div class='val'>{$service}</div></div>
                    <div class='item'><div class='lbl'>Message</div><div class='val'>{$message}</div></div>
                </div>
                <div class='ftr'>
                    IP Address: {$ip_addr} | Time: ".date('Y-m-d H:i:s')."
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "YashRaj Systems & Services - New Web Enquiry\n\n" .
                         "Full Name: {$name}\n" .
                         "Company: ".(!empty($company) ? $company : 'N/A')."\n" .
                         "Phone: {$phone}\n" .
                         "Email: {$email}\n" .
                         "Service Interest: {$service}\n\n" .
                         "Message:\n{$message}\n";

        return $mail->send();
    } catch (Exception $e) {
        error_log("PHPMailer Send Error (Notification): " . $e->getMessage());
        return false;
    }
}

// Helper: send client confirmation email
function sendConfirmationMail($name, $email, $service) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;

        // Recipients
        $mail->setFrom(SMTP_USER, 'YashRaj Systems & Services');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'We Received Your Enquiry - YashRaj Systems';
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1a1a1a; background-color: #f7f7f7; padding: 20px; }
                .card { background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; }
                .hdr { background-color: #8C1007; color: #ffffff; padding: 20px; font-size: 20px; font-weight: bold; text-align: center; }
                .body { padding: 30px; font-size: 15px; color: #333333; }
                .accent-box { border-left: 4px solid #8C1007; background-color: #fcf8f8; padding: 15px; margin: 20px 0; border-radius: 0 4px 4px 0; }
                .ftr { background-color: #fafafa; padding: 20px; text-align: center; font-size: 11px; color: #888888; border-top: 1px solid #eeeeee; }
            </style>
        </head>
        <body>
            <div class='card'>
                <div class='hdr'>YashRaj Systems & Services</div>
                <div class='body'>
                    <p>Dear <strong>{$name}</strong>,</p>
                    <p>Thank you for reaching out to us. We have received your enquiry regarding:</p>
                    <div class='accent-box'>
                        <strong>Service Interest:</strong> {$service}
                    </div>
                    <p>Our automation engineering and support team is currently reviewing your details and will get back to you with a comprehensive response or quotation within 24 business hours.</p>
                    <p>If your requirement is urgent, please do not hesitate to contact us directly by calling <strong>+91 9422 323 128</strong> or messaging us on WhatsApp.</p>
                    <br>
                    <p>Best Regards,</p>
                    <p><strong>Customer Engagement Team</strong><br>YashRaj Systems & Services</p>
                </div>
                <div class='ftr'>
                    Aston Plaza, Ambegaon Bk, Pune – 411 046, Maharashtra, India<br>
                    &copy; 2026 YashRaj Systems & Services. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Dear {$name},\n\n" .
                         "Thank you for reaching out to YashRaj Systems & Services. We have received your enquiry regarding {$service}.\n\n" .
                         "Our engineering and support team is currently reviewing your request and will get back to you within 24 business hours.\n\n" .
                         "For urgent requirements, please contact us directly at +91 9422 323 128.\n\n" .
                         "Best Regards,\n" .
                         "Customer Engagement Team\n" .
                         "YashRaj Systems & Services";

        return $mail->send();
    } catch (Exception $e) {
        error_log("PHPMailer Send Error (Confirmation): " . $e->getMessage());
        return false;
    }
}

// Execute Sending
$notification_success = sendNotificationMail($name, $company, $phone, $email, $service, $message);

if ($notification_success) {
    // Attempt to send confirmation to client (non-blocking if it fails)
    sendConfirmationMail($name, $email, $service);

    // Save timestamp for rate limit check
    $_SESSION['last_submit_time'] = time();

    echo json_encode([
        'status' => 'success',
        'message' => 'Enquiry submitted successfully. Confirmation email sent.'
    ]);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while sending the email. Please try again later.'
    ]);
}
