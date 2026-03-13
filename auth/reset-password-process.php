<?php
/**
 * Reset Password Process Handler
 * Handles password update using valid reset token
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
    
    // Get form data
    $token = trim($_POST['token'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($token)) {
        throw new Exception('Invalid reset token');
    }
    
    if (empty($newPassword)) {
        throw new Exception('Password is required');
    }
    
    if (strlen($newPassword) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }
    
    if ($newPassword !== $confirmPassword) {
        throw new Exception('Passwords do not match');
    }
    
    // Get database instance
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Validate token and get user info
    $tokenData = $db->selectOne(
        "SELECT ut.token_id, ut.user_id, ut.expires_at, u.full_name, u.email, u.role, u.status 
         FROM user_tokens ut
         INNER JOIN users u ON ut.user_id = u.user_id
         WHERE ut.token = ? AND ut.type = 'password_reset'",
        [$token]
    );
    
    if (!$tokenData) {
        throw new Exception('Invalid reset token. This link may have already been used.');
    }
    
    // Check if token has expired
    if (strtotime($tokenData['expires_at']) < time()) {
        // Delete expired token
        $db->delete("DELETE FROM user_tokens WHERE token_id = ?", [$tokenData['token_id']]);
        throw new Exception('This reset link has expired. Please request a new password reset.');
    }
    
    // Check if account is active
    if ($tokenData['status'] !== 'active') {
        throw new Exception('This account is not active. Please contact administrator.');
    }
    
    // Hash the new password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Update user password
        $updateResult = $db->update(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?",
            [$passwordHash, $tokenData['user_id']]
        );
        
        if (!$updateResult) {
            throw new Exception('Failed to update password');
        }
        
        // Delete the used token (one-time use)
        $db->delete("DELETE FROM user_tokens WHERE token_id = ?", [$tokenData['token_id']]);
        
        // Delete all other password reset tokens for this user
        $db->delete(
            "DELETE FROM user_tokens WHERE user_id = ? AND type = 'password_reset'",
            [$tokenData['user_id']]
        );
        
        // Optionally, delete all remember_me tokens to force re-login on all devices
        $db->delete(
            "DELETE FROM user_tokens WHERE user_id = ? AND type = 'remember_me'",
            [$tokenData['user_id']]
        );
        
        // Log activity
        if (function_exists('logActivity')) {
            logActivity(
                $tokenData['user_id'], 
                'Password Reset Completed', 
                'Password was successfully reset for ' . $tokenData['email']
            );
        }
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Password has been reset successfully! Redirecting to login...';
        $response['data'] = [
            'redirect' => 'login.php?reset=success'
        ];
        
        // Send confirmation email (optional)
        sendPasswordChangeConfirmation($tokenData);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log error
    error_log("Reset Password Error: " . $e->getMessage());
}

// Discard any stray PHP output (warnings/notices) and return clean JSON
ob_clean();
echo json_encode($response);
exit;

/**
 * Send password change confirmation email
 * @param array $userData User data
 */
function sendPasswordChangeConfirmation($userData) {
    try {
        $email = $userData['email'];
        $userName = htmlspecialchars($userData['full_name']);
        $userRole = ucfirst($userData['role']);
        
        $subject = 'Password Changed Successfully - Bachat Gat';
        
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .warning { background: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Password Changed Successfully</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$userName}</strong>,</p>
                    
                    <p>This email confirms that the password for your <strong>{$userRole}</strong> account has been changed successfully.</p>
                    
                    <div class='warning'>
                        <strong>📌 Account Security:</strong>
                        <ul>
                            <li>If you made this change, no action is needed</li>
                            <li>If you did NOT make this change, please contact your administrator immediately</li>
                            <li>All active sessions have been logged out for security</li>
                        </ul>
                    </div>
                    
                    <p><strong>Changed on:</strong> " . date('F j, Y \a\t g:i A') . "</p>
                    
                    <p>You can now log in with your new password.</p>
                    
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
        @mail($email, $subject, $emailBody, implode("\r\n", $headers));
        
    } catch (Exception $e) {
        // Log error but don't fail the password reset
        error_log("Password change confirmation email error: " . $e->getMessage());
    }
}
