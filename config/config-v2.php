<?php
/**
 * Improved Configuration File (v2)
 * Uses environment variables for security
 * 
 * Include this file instead of the old config.php
 * Usage: require_once 'config/config-v2.php';
 */

// Load environment variables
require_once dirname(__DIR__) . '/src/Env.php';
require_once dirname(__DIR__) . '/src/Response.php';

// ========================================
// ENVIRONMENT CONFIGURATION
// ========================================

define('ENVIRONMENT', Env::get('APP_ENV', 'development'));
define('APP_DEBUG', Env::get('APP_DEBUG', 'false') === 'true');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', APP_DEBUG ? 1 : 0);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ========================================
// SESSION CONFIGURATION
// ========================================

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');

session_name(Env::get('SESSION_NAME', 'bachat_gat'));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// TIMEZONE
// ========================================

date_default_timezone_set('Asia/Kolkata');

// ========================================
// DATABASE CONFIGURATION
// ========================================

define('DB_HOST', Env::get('DB_HOST', 'localhost'));
define('DB_PORT', Env::get('DB_PORT', 3306));
define('DB_NAME', Env::get('DB_NAME', 'bachat_gat_db'));
define('DB_USER', Env::get('DB_USER', 'root'));
define('DB_PASS', Env::get('DB_PASSWORD', ''));
define('DB_CHARSET', 'utf8mb4');

// ========================================
// APPLICATION SETTINGS
// ========================================

define('APP_NAME', Env::get('APP_NAME', 'Bachat Gat'));
define('APP_URL', Env::get('APP_URL', 'http://localhost/bachat_gat'));
define('APP_VERSION', '2.0.0');

// ========================================
// PATHS
// ========================================

define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');
define('CONFIG_PATH', BASE_PATH . '/config');
define('UPLOADS_PATH', BASE_PATH . '/assets/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

// Create required directories
foreach ([UPLOADS_PATH . '/profiles', UPLOADS_PATH . '/documents', LOGS_PATH] as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ========================================
// SECURITY
// ========================================

define('BCRYPT_COST', (int)Env::get('BCRYPT_COST', 12));
define('SESSION_TIMEOUT', (int)Env::get('SESSION_TIMEOUT', 1800));
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_DURATION', 900);
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// ========================================
// AUTOLOADER
// ========================================

spl_autoload_register(function ($class) {
    $paths = [
        SRC_PATH . '/Services/',
        SRC_PATH . '/Middleware/',
        SRC_PATH . '/Validators/',
        BASE_PATH . '/classes/',
        BASE_PATH . '/helpers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ========================================
// GLOBAL HELPERS
// ========================================

/**
 * Get configuration value
 */
function config($key, $default = null) {
    return Env::get($key, $default);
}

/**
 * Log error
 */
function logError($message, $context = []) {
    $logFile = LOGS_PATH . '/error.log';
    $entry = date('Y-m-d H:i:s') . ' | ' . $message . ' | ' . json_encode($context) . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
}

/**
 * Send JSON response
 */
function sendJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

?>
