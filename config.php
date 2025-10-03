<?php
// config.php - Petty Cash System
// Database configuration and application settings

define('DB_HOST', 'localhost');
define('DB_NAME', 'pettycashsystem');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/pettycashsystem');

// Application settings
define('APP_NAME', 'Petty Cash System');
define('APP_VERSION', '1.0');
define('COMPANY_NAME', 'Your Company Name');

// Petty Cash Settings
define('MAX_PETTY_CASH_AMOUNT', 10000.00);
define('MAX_SINGLE_EXPENSE', 500.00);
define('CASH_CUSTODIAN', 'Finance Department');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Nairobi, Kenya timezone
date_default_timezone_set('Africa/Nairobi');

// Auto-login for testing (remove in production)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Admin User';
}
?>