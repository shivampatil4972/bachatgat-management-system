<?php
/**
 * Forgot Password Process Handler
 * Handles password reset token generation and email notification
 */

// Buffer ALL output so PHP warnings/notices don't corrupt the JSON response
ob_start();

// Load configuration (handles session_start + BASE_PATH internally)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once '../config/config.php';
require_once '../config/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get and validate email
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        throw new Exception('Email address is required');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address format');
    }
    
    // Get database instance
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if user exists with this email
    $user = $db->selectOne(
        "SELECT user_id, full_name, email, role, status FROM users WHERE email = ?",
        [$email]
    );
    
    // Security: Always show success message even if email doesn't exist
    // This prevents email enumeration attacks
    if (!$user) {
        // Simulate processing time to prevent timing attacks
        usleep(500000); // 0.5 seconds
        
        $response['success'] = true;
        $response['message'] = 'If this email is registered, you will receive a password reset link shortly.';
        ob_clean();
        echo json_encode($response);
        exit;
    }
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        throw new Exception('Your account is not active. Please contact administrator.');
    }
    
    // Generate secure random token
    $token = bin2hex(random_bytes(32)); // 64 character token
    
    // Set token expiration (1 hour from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Delete any existing password reset tokens for this user
    $db->delete(
        "DELETE FROM user_tokens WHERE user_id = ? AND type = 'password_reset'",
        [$user['user_id']]
    );
    
    // Insert new password reset token
    $tokenId = $db->insert(
        "INSERT INTO user_tokens (user_id, token, type, expires_at, created_at) 
         VALUES (?, ?, 'password_reset', ?, NOW())",
        [$user['user_id'], $token, $expiresAt]
    );
    
    if (!$tokenId) {
        throw new Exception('Failed to generate reset token. Please try again.');
    }
    
    // Create reset link
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $resetLink = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/reset-password.php?token=' . $token;
    
    // Prepare email content
    $subject = 'Password Reset Request - Bachat Gat';
    $userName = htmlspecialchars($user['full_name']);
    $userRole = ucfirst($user['role']);
    
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔑 Password Reset Request</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>{$userName}</strong>,</p>
                
                <p>We received a request to reset the password for your <strong>{$userRole}</strong> account.</p>
                
                <p>Click the button below to reset your password:</p>
                
                <p style='text-align: center;'>
                    <a href='{$resetLink}' class='button'>Reset Password</a>
                </p>
                
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>{$resetLink}</p>
                
                <div class='warning'>
                    <strong>⚠️ Important:</strong>
                    <ul>
                        <li>This link will expire in <strong>1 hour</strong></li>
                        <li>If you didn't request this reset, please ignore this email</li>
                        <li>For security, this link can only be used once</li>
                    </ul>
                </div>
                
                <p>If you have any questions, please contact your administrator.</p>
                
                <p>Best regards,<br><strong>Bachat Gat Management Team</strong></p>
            </div>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; " . date('Y') . " Bachat Gat Smart Management System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Bachat Gat <noreply@bachatgat.com>',
        'Reply-To: support@bachatgat.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Send email
    $emailSent = mail($email, $subject, $emailBody, implode("\r\n", $headers));
    
    // NOTE: In development, email may not be sent if mail server is not configured
    // For production, use PHPMailer or similar library with SMTP
    
    if (!$emailSent) {
        // Log the error but don't expose it to the user
        error_log("Failed to send password reset email to: {$email}");
        
        // In development, we can show the reset link for testing
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $response['success'] = true;
            $response['message'] = 'Password reset link generated (email not configured).';
            $response['data'] = [
                'reset_link' => $resetLink,
                'note' => 'Email server not configured. Use the link above to reset password.'
            ];
        } else {
            throw new Exception('Failed to send reset email. Please try again or contact support.');
        }
    } else {
        $response['success'] = true;
        $response['message'] = 'If this email is registered, you will receive a password reset link shortly. Please check your inbox.';
    }
    
    // Log activity
    if (function_exists('logActivity')) {
        logActivity($user['user_id'], 'Password Reset Requested', 'Password reset token generated for ' . $email);
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log error
    error_log("Forgot Password Error: " . $e->getMessage());
}

// Discard any stray PHP output (warnings/notices) and return clean JSON
ob_clean();
echo json_encode($response);
exit;
