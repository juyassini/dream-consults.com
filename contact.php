<?php
/**
 * contact.php - Handle contact form submissions
 * Processes POST requests and stores in SQLite database
 */

header('Content-Type: application/json');

// Enable CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get JSON payload
$input = json_decode(file_get_contents('php://input'), true);

// Fallback to POST form data
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$service = trim($input['service'] ?? '');
$message = trim($input['message'] ?? '');
$phone = trim($input['phone'] ?? '');

if (!$name || !$email || !$service || !$message) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

try {
    // Initialize database
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table if it doesn't exist
    $db->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            service TEXT NOT NULL,
            message TEXT NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Insert submission
    $stmt = $db->prepare("
        INSERT INTO contacts (name, email, phone, service, message)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $phone, $service, $message]);

    // Send email if configured
    if (SEND_EMAIL) {
        $emailSent = sendEmail($name, $email, $service, $message, $phone);
        if ($emailSent) {
            error_log("Email sent successfully to " . CONTACT_RECIPIENT);
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'Submission received and stored']);

} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}

/**
 * Send email via mail() function
 */
function sendEmail($name, $email, $service, $message, $phone = '') {
    $to = CONTACT_RECIPIENT;
    $subject = "Website Contact: {$service} - {$name}";
    $body = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\nService: {$service}\n\nMessage:\n{$message}";
    $headers = "From: " . SENDER_EMAIL . "\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8";

    try {
        // Use PHP mail() function
        $mailSent = mail($to, $subject, $body, $headers);
        
        if ($mailSent) {
            error_log("Email sent successfully to: {$to}");
            return true;
        } else {
            error_log("Mail function failed for recipient: {$to}");
            // Log submission even if email fails
            return false;
        }
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}
?>
