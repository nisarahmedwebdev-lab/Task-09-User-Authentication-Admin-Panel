<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'user_auth_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration - UPDATE THIS PATH TO YOUR ACTUAL PROJECT PATH
define('SITE_URL', 'http://localhost/user-auth-system/');
define('SITE_PATH', __DIR__ . '/');

// Upload directory
define('UPLOAD_DIR', SITE_PATH . 'uploads/');

// File upload settings
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>