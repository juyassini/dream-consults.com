<?php
/**
 * config.php - Configuration for PHP backend
 */

// Database settings
define('DB_PATH', __DIR__ . '/submissions.db');

// Email settings
define('SEND_EMAIL', true); // Enable email sending
define('CONTACT_RECIPIENT', 'juyassini@gmail.com');
define('SENDER_EMAIL', 'noreply@dreamconsults.com'); // Fallback sender
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'localhost');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('USE_MAIL_FUNCTION', true); // Use PHP mail() as fallback

// Admin authentication - Use hashed password for security
// Generate hash: php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
// Then set in .env: ADMIN_PASSWORD_HASH=<hash>
// Fallback: Plain password (NOT RECOMMENDED for production)
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASSWORD_HASH') ?: '');
define('ADMIN_PASSWORD_PLAIN', 'dream2026'); // DEPRECATED: Use password hash instead

// CORS settings
define('ALLOWED_ORIGINS', ['http://localhost:8000', 'http://127.0.0.1:8000', 'http://localhost:5000', 'http://127.0.0.1:5000']);

// Error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
?>
