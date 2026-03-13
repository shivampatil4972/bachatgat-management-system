<?php
/**
 * Registration Process Handler
 * Bachat Gat Smart Management System
 * 
 * Handles registration form submission and returns JSON response
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

// Redirect if already logged in
if (isLoggedIn()) {
    jsonResponse(false, 'You are already logged in.', ['redirect' => getLoginRedirectUrl()]);
}

// Get form data
$data = [
    'full_name' => trim($_POST['full_name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'password' => $_POST['password'] ?? '',
    'confirm_password' => $_POST['confirm_password'] ?? '',
    'address' => trim($_POST['address'] ?? ''),
    'city' => trim($_POST['city'] ?? ''),
    'state' => trim($_POST['state'] ?? ''),
    'pincode' => trim($_POST['pincode'] ?? '')
];

// Create auth controller instance
$authController = new AuthController();

// Attempt registration
$result = $authController->register($data);

// Return JSON response
jsonResponse(
    $result['success'],
    $result['message'],
    [
        'member_code' => $result['member_code'] ?? null
    ]
);

?>
