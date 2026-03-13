<?php
/**
 * Authentication Controller
 * Bachat Gat Smart Management System
 * 
 * Handles user authentication, registration, and password management
 * 
 * @author Your Name
 * @version 1.0
 */

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * User Login
     * 
     * @param string $email User email
     * @param string $password User password
     * @param bool $rememberMe Remember me checkbox
     * @return array Response with success status and message
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            $email = trim((string)$email);
            $password = (string)$password;

            // Validate input
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Email and password are required.'
                ];
            }
            
            // Validate email format
            if (!validateEmail($email)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format.'
                ];
            }
            
            // Check login attempts
            $attemptCheck = checkLoginAttempts($email);
            if ($attemptCheck['locked']) {
                return [
                    'success' => false,
                    'message' => $attemptCheck['message']
                ];
            }
            
            // Get user from database
            $user = $this->db->selectOne(
                "SELECT * FROM users WHERE email = ?",
                [$email]
            );
            
            // Check if user exists
            if (!$user) {
                logFailedLogin($email);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password.'
                ];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                logFailedLogin($email);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password.',
                    'remaining_attempts' => $attemptCheck['remaining'] - 1
                ];
            }
            
            // Check if user is active
            if ($user['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Your account is ' . $user['status'] . '. Please contact administrator.'
                ];
            }
            
            // Get member data if user is a member
            $member = null;
            if ($user['role'] === 'member') {
                $member = $this->db->selectOne(
                    "SELECT * FROM members WHERE user_id = ?",
                    [$user['user_id']]
                );
            }
            
            // Initialize session
            initUserSession($user, $member);
            
            // Handle remember me
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                setRememberMeCookie($user['user_id'], $token);
            }
            
            // Update last login
            $this->db->update(
                "UPDATE users SET last_login = NOW() WHERE user_id = ?",
                [$user['user_id']]
            );
            
            return [
                'success' => true,
                'message' => MSG_LOGIN_SUCCESS,
                'redirect' => getLoginRedirectUrl()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }
    
    /**
     * User Registration
     * 
     * @param array $data User registration data
     * @return array Response with success status and message
     */
    public function register($data) {
        try {
            // Validate required fields
            $required = ['full_name', 'email', 'phone', 'password', 'confirm_password'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => 'All fields are required.'
                    ];
                }
            }
            
            // Validate email
            if (!validateEmail($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format.'
                ];
            }
            
            // Validate phone
            if (!validatePhone($data['phone'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number. Must be 10 digits starting with 6-9.'
                ];
            }
            
            // Validate password strength
            $passwordValidation = validatePassword($data['password']);
            if (!$passwordValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $passwordValidation['message']
                ];
            }
            
            // Check if passwords match
            if ($data['password'] !== $data['confirm_password']) {
                return [
                    'success' => false,
                    'message' => 'Passwords do not match.'
                ];
            }
            
            // Check if email already exists
            $emailExists = $this->db->exists(
                "SELECT user_id FROM users WHERE email = ?",
                [$data['email']]
            );
            
            if ($emailExists) {
                return [
                    'success' => false,
                    'message' => 'Email already registered. Please login or use a different email.'
                ];
            }
            
            // Check if phone already exists
            $phoneExists = $this->db->exists(
                "SELECT user_id FROM users WHERE phone = ?",
                [$data['phone']]
            );
            
            if ($phoneExists) {
                return [
                    'success' => false,
                    'message' => 'Phone number already registered.'
                ];
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Insert user
            $userId = $this->db->insert(
                "INSERT INTO users (full_name, email, phone, password, role, status, created_at) 
                 VALUES (?, ?, ?, ?, 'member', 'active', NOW())",
                [
                    $data['full_name'],
                    $data['email'],
                    $data['phone'],
                    $hashedPassword
                ]
            );
            
            // Generate member code
            $memberCode = generateMemberCode();
            
            // Insert member
            $memberId = $this->db->insert(
                "INSERT INTO members (user_id, member_code, address, city, state, pincode, joining_date, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW(), 'active', NOW())",
                [
                    $userId,
                    $memberCode,
                    $data['address'] ?? '',
                    $data['city'] ?? '',
                    $data['state'] ?? '',
                    $data['pincode'] ?? ''
                ]
            );
            
            // Log activity
            logActivity($userId, 'User Registration', 'New member registered: ' . $data['full_name']);
            
            // Send welcome notification
            $welcomeTemplate = NOTIFY_TEMPLATE_WELCOME;
            sendNotification(
                $userId,
                $welcomeTemplate['title'],
                sprintf($welcomeTemplate['message'], $data['full_name'], $memberCode),
                'info'
            );
            
            // Commit transaction
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Registration successful! Your member code is: ' . $memberCode . '. Please login to continue.',
                'member_code' => $memberCode
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            
            // Log the actual error for debugging
            error_log("Registration Error: " . $e->getMessage());
            
            // In development mode, show detailed error
            $errorMessage = 'An error occurred during registration. Please try again.';
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }
            
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        clearRememberMeCookie();
        destroyUserSession();
        return [
            'success' => true,
            'message' => 'Logged out successfully.'
        ];
    }
    
    /**
     * Check if email exists
     */
    public function checkEmail($email) {
        return $this->db->exists(
            "SELECT user_id FROM users WHERE email = ?",
            [$email]
        );
    }
    
    /**
     * Check if phone exists
     */
    public function checkPhone($phone) {
        return $this->db->exists(
            "SELECT user_id FROM users WHERE phone = ?",
            [$phone]
        );
    }
    
    /**
     * Forgot Password - Send reset link
     */
    public function forgotPassword($email) {
        try {
            // Validate email
            if (!validateEmail($email)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format.'
                ];
            }
            
            // Check if user exists
            $user = $this->db->selectOne(
                "SELECT user_id, full_name FROM users WHERE email = ?",
                [$email]
            );
            
            if (!$user) {
                // Don't reveal if email exists for security
                return [
                    'success' => true,
                    'message' => 'If your email is registered, you will receive a password reset link.'
                ];
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);
            
            // Delete old tokens
            $this->db->delete(
                "DELETE FROM user_tokens WHERE user_id = ? AND type = 'password_reset'",
                [$user['user_id']]
            );
            
            // Insert new token (valid for 1 hour)
            $this->db->insert(
                "INSERT INTO user_tokens (user_id, token, type, expires_at, created_at) 
                 VALUES (?, ?, 'password_reset', DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())",
                [$user['user_id'], $hashedToken]
            );
            
            // In production, send email with reset link
            // For now, we'll return the token (in production, this should be emailed)
            $resetLink = BASE_URL . 'auth/reset-password.php?token=' . $token;
            
            // Log activity
            logActivity($user['user_id'], 'Password Reset Request', 'Password reset requested for: ' . $email);
            
            return [
                'success' => true,
                'message' => 'Password reset link has been sent to your email.',
                'reset_link' => $resetLink // Remove in production
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }
    
    /**
     * Reset Password
     */
    public function resetPassword($token, $newPassword, $confirmPassword) {
        try {
            // Validate passwords
            if (empty($newPassword) || empty($confirmPassword)) {
                return [
                    'success' => false,
                    'message' => 'Password fields are required.'
                ];
            }
            
            if ($newPassword !== $confirmPassword) {
                return [
                    'success' => false,
                    'message' => 'Passwords do not match.'
                ];
            }
            
            // Validate password strength
            $passwordValidation = validatePassword($newPassword);
            if (!$passwordValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $passwordValidation['message']
                ];
            }
            
            // Verify token
            $hashedToken = hash('sha256', $token);
            $userToken = $this->db->selectOne(
                "SELECT user_id FROM user_tokens 
                 WHERE token = ? 
                 AND type = 'password_reset' 
                 AND expires_at > NOW()",
                [$hashedToken]
            );
            
            if (!$userToken) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset link.'
                ];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $this->db->update(
                "UPDATE users SET password = ? WHERE user_id = ?",
                [$hashedPassword, $userToken['user_id']]
            );
            
            // Delete reset token
            $this->db->delete(
                "DELETE FROM user_tokens WHERE token = ?",
                [$hashedToken]
            );
            
            // Log activity
            logActivity($userToken['user_id'], 'Password Reset', 'Password was reset successfully');
            
            return [
                'success' => true,
                'message' => 'Password reset successful. Please login with your new password.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }
    
    /**
     * Change Password (for logged-in users)
     */
    public function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        try {
            // Validate inputs
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return [
                    'success' => false,
                    'message' => 'All password fields are required.'
                ];
            }
            
            // Get user
            $user = $this->db->selectOne(
                "SELECT password FROM users WHERE user_id = ?",
                [$userId]
            );
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ];
            }
            
            // Check if new password matches confirm
            if ($newPassword !== $confirmPassword) {
                return [
                    'success' => false,
                    'message' => 'New passwords do not match.'
                ];
            }
            
            // Validate new password strength
            $passwordValidation = validatePassword($newPassword);
            if (!$passwordValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $passwordValidation['message']
                ];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $this->db->update(
                "UPDATE users SET password = ? WHERE user_id = ?",
                [$hashedPassword, $userId]
            );
            
            // Log activity
            logActivity($userId, 'Password Change', 'Password changed successfully');
            
            return [
                'success' => true,
                'message' => 'Password changed successfully.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }
}

?>
