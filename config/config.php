<?php
/**
 * Global Configuration File
 * Bachat Gat Smart Management System
 * 
 * This file contains application-wide settings and configuration
 * Load this file at the beginning of every page
 * 
 * @author Your Name
 * @version 1.0
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// ========================================
// ENVIRONMENT CONFIGURATION
// ========================================

// Set environment: 'development' or 'production'
define('ENVIRONMENT', 'development');

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// ========================================
// SESSION CONFIGURATION
// ========================================

// Session settings
ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1); // Only use cookies for session
ini_set('session.cookie_secure', 0);    // Set to 1 if using HTTPS
ini_set('session.cookie_lifetime', 0);  // Session expires when browser closes

// Session name
session_name('BACHAT_GAT_SESSION');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// TIMEZONE CONFIGURATION
// ========================================

// Set default timezone (India Standard Time)
date_default_timezone_set('Asia/Kolkata');

// ========================================
// DATABASE CONFIGURATION
// ========================================

// Database credentials (can be moved to .env in production)
define('DB_HOST', 'localhost');
define('DB_NAME', 'bachat_gat_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ========================================
// APPLICATION INFORMATION
// ========================================

define('APP_NAME', 'Bachat Gat Smart Management');
define('APP_SHORT_NAME', 'Bachat Gat');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Your Name');
define('APP_DESCRIPTION', 'Self-Help Group Financial Management System');

// ========================================
// URL CONFIGURATION
// ========================================

// Get protocol (HTTP or HTTPS)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// Get host
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Base URL (adjust this according to your local setup)
define('BASE_URL', $protocol . '://' . $host . '/bachat_gat/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'assets/uploads/');

// ========================================
// DIRECTORY PATHS
// ========================================

define('ROOT_PATH', dirname(__DIR__) . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('CLASSES_PATH', ROOT_PATH . 'classes/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('HELPERS_PATH', ROOT_PATH . 'helpers/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ASSETS_PATH . 'uploads/');
define('LOGS_PATH', ROOT_PATH . 'logs/');

// Create required directories if they don't exist
$required_dirs = [
    UPLOADS_PATH . 'profiles',
    UPLOADS_PATH . 'documents',
    LOGS_PATH
];

foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ========================================
// SECURITY CONFIGURATION
// ========================================

// Password hashing algorithm
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);

// Password minimum length
define('PASSWORD_MIN_LENGTH', 6);

// Session timeout (in seconds) - 30 minutes
define('SESSION_TIMEOUT', 1800);

// Maximum login attempts
define('MAX_LOGIN_ATTEMPTS', 5);

// Login lockout duration (in seconds) - 15 minutes
define('LOGIN_LOCKOUT_DURATION', 900);

// CSRF token name
define('CSRF_TOKEN_NAME', 'csrf_token');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// ========================================
// BUSINESS CONFIGURATION
// ========================================

// Currency settings
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// Date format
define('DATE_FORMAT', 'd-m-Y');
define('DATE_FORMAT_SQL', 'Y-m-d');
define('DATETIME_FORMAT', 'd-m-Y h:i A');

// Pagination
define('RECORDS_PER_PAGE', 10);
define('PAGINATION_LINKS', 5);

// Default loan settings (can be overridden from database)
define('DEFAULT_INTEREST_RATE', 12.00);
define('MIN_LOAN_AMOUNT', 5000);
define('MAX_LOAN_AMOUNT', 100000);
define('MIN_INSTALLMENT_MONTHS', 3);
define('MAX_INSTALLMENT_MONTHS', 24);

// Member code prefix
define('MEMBER_CODE_PREFIX', 'MEM');
define('LOAN_NUMBER_PREFIX', 'LOAN');

// Late fee configuration
define('LATE_FEE_ENABLED', true);
define('LATE_FEE_PERCENTAGE', 2); // 2% of installment amount
define('LATE_FEE_GRACE_DAYS', 3); // 3 days grace period

// ========================================
// EMAIL CONFIGURATION
// ========================================

define('MAIL_ENABLED', false); // Set to true to enable email
define('MAIL_FROM_EMAIL', 'noreply@bachatgat.com');
define('MAIL_FROM_NAME', APP_NAME);

// SMTP settings (if using SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// ========================================
// SMS CONFIGURATION
// ========================================

define('SMS_ENABLED', false); // Set to true to enable SMS
define('SMS_API_KEY', '');
define('SMS_SENDER_ID', 'BACHAT');

// ========================================
// NOTIFICATION SETTINGS
// ========================================

// Email notifications
define('NOTIFY_LOAN_APPROVAL', true);
define('NOTIFY_LOAN_REJECTION', true);
define('NOTIFY_LOAN_DISBURSEMENT', true);
define('NOTIFY_INSTALLMENT_DUE', true);
define('NOTIFY_INSTALLMENT_OVERDUE', true);

// Days before installment due date to send reminder
define('INSTALLMENT_REMINDER_DAYS', 3);

// ========================================
// DASHBOARD SETTINGS
// ========================================

// Dashboard refresh interval (in seconds)
define('DASHBOARD_REFRESH_INTERVAL', 300); // 5 minutes

// Chart colors
define('CHART_COLORS', [
    'primary' => '#6366f1',
    'secondary' => '#8b5cf6',
    'success' => '#10b981',
    'danger' => '#ef4444',
    'warning' => '#f59e0b',
    'info' => '#3b82f6',
]);

// ========================================
// ROLE DEFINITIONS
// ========================================

define('ROLE_ADMIN', 'admin');
define('ROLE_MEMBER', 'member');

// ========================================
// STATUS DEFINITIONS
// ========================================

// User status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');

// Loan status
define('LOAN_STATUS_PENDING', 'pending');
define('LOAN_STATUS_APPROVED', 'approved');
define('LOAN_STATUS_REJECTED', 'rejected');
define('LOAN_STATUS_DISBURSED', 'disbursed');
define('LOAN_STATUS_COMPLETED', 'completed');
define('LOAN_STATUS_DEFAULTED', 'defaulted');

// Installment status
define('INSTALLMENT_STATUS_PENDING', 'pending');
define('INSTALLMENT_STATUS_PAID', 'paid');
define('INSTALLMENT_STATUS_PARTIAL', 'partial');
define('INSTALLMENT_STATUS_OVERDUE', 'overdue');

// ========================================
// HELPER FUNCTIONS
// ========================================

/**
 * Autoload classes
 */
spl_autoload_register(function ($class) {
    $file = CLASSES_PATH . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Include helper files
 */
if (file_exists(HELPERS_PATH . 'functions.php')) {
    require_once HELPERS_PATH . 'functions.php';
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

/**
 * Check if user is member
 */
function isMember() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === ROLE_MEMBER;
}

/**
 * Redirect to URL
 */
function redirect($url, $permanent = false) {
    if ($permanent) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header('Location: ' . $url);
    exit();
}

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Get time ago
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    if ($diff < 31536000) return floor($diff / 2592000) . ' months ago';
    return floor($diff / 31536000) . ' years ago';
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Debug helper (only in development)
 */
function dd($data) {
    if (ENVIRONMENT === 'development') {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}

// ========================================
// SESSION ACTIVITY CHECK
// ========================================

// Check session timeout
if (isLoggedIn()) {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        // Session expired
        session_unset();
        session_destroy();
        redirect(BASE_URL . 'auth/login.php?timeout=1');
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// ========================================
// AUTOLOAD CLASSES
// ========================================

spl_autoload_register(function ($className) {
    $classPath = ROOT_PATH . 'classes/' . $className . '.php';
    if (file_exists($classPath)) {
        require_once $classPath;
    }
});

// ========================================
// LOAD CONSTANTS
// ========================================

require_once CONFIG_PATH . 'constants.php';

// ========================================
// LOAD DATABASE CLASS
// ========================================

require_once CONFIG_PATH . 'db.php';

// ========================================
// LOAD HELPER FILES
// ========================================

require_once ROOT_PATH . 'helpers/functions.php';
require_once ROOT_PATH . 'helpers/session.php';

?>
