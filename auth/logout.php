<?php
/**
 * Logout Handler
 * Bachat Gat Smart Management System
 * 
 * Handles user logout and session cleanup
 */

// Start session and load configuration
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . 'auth/login.php');
}

// Create auth controller instance
$authController = new AuthController();

// Logout user
$result = $authController->logout();

// Set flash message
setFlashMessage($result['message'], 'success');

// Redirect to login page
redirect(BASE_URL . 'auth/login.php');

?>
