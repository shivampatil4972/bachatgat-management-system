<?php
/**
 * Login Process Handler
 * Bachat Gat Smart Management System
 * 
 * Handles login form submission and returns JSON response
 */

// Load configuration first (this will start session properly)
define('BASE_PATH', dirname(__DIR__));
require_once '../config/config.php';
require_once '../config/constants.php';

// Set JSON header
header('Content-Type: application/json');

// Check if this is a POST request
if (!isPost()) {
    jsonResponse(false, 'Invalid request method.');
}

// Check CSRF token (optional for now, implement properly in production)
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     jsonResponse(false, 'Invalid security token. Please refresh and try again.');
// }

// If already logged in, destroy the old session so a new login can proceed
if (isLoggedIn()) {
    destroyUserSession();
    session_start();
}

// Get and normalize form data
$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';

// Strict server-side validation
if ($email === '' || $password === '') {
    jsonResponse(false, 'Username/Email and password are required.');
}

if (strlen($email) > 100) {
    jsonResponse(false, 'Username/Email must be at most 100 characters.');
}

if (!validateEmail($email)) {
    jsonResponse(false, 'Please enter a valid email address.');
}

if (strlen($password) < 6) {
    jsonResponse(false, 'Password must be at least 6 characters.');
}

if (strlen($password) > 128) {
    jsonResponse(false, 'Password is too long.');
}

// Create auth controller instance
$authController = new AuthController();

// Attempt login
$result = $authController->login($email, $password, $rememberMe);

// Return JSON response
jsonResponse(
    $result['success'],
    $result['message'],
    [
        'redirect' => $result['redirect'] ?? null,
        'remaining_attempts' => $result['remaining_attempts'] ?? null
    ]
);

?>
