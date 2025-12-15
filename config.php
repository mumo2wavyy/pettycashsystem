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

// Petty Cash Settings
define('MAX_PETTY_CASH_AMOUNT', 10000.00);
define('MAX_SINGLE_EXPENSE', 500.00);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Nairobi timezone
date_default_timezone_set('Africa/Nairobi');

// REMOVED AUTO-LOGIN - Users must now login properly
?>
