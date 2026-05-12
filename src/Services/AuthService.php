<?php

namespace Services;

use Exception;
use Response;

/**
 * AuthService
 * Handle all authentication-related business logic
 * 
 * Methods:
 * - register()          : Register new user
 * - login()             : Login user
 * - logout()            : Logout user
 * - validateLogin()     : Validate login credentials
 * - resetPassword()     : Reset user password
 * - forgotPassword()    : Send password reset link
 * - changePassword()    : Change password for logged-in user
 * - getLoginAttempts()  : Get failed login attempts
 * - lockoutUser()       : Lock user after failed attempts
 * - isUserLocked()      : Check if user is locked
 */
class AuthService extends BaseService
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 900; // 15 minutes in seconds
    
    /**
     * Register new user
     */
    public function register($data)
    {
        try {
            // Validate input
            $errors = $this->validate($data, [
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'confirm_password' => 'required|match:password',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'phone' => 'required|numeric|min:10',
                'member_type' => 'required|in:member,admin'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            // Check email not already registered
            $existing = $this->db->selectOne(
                "SELECT user_id FROM users WHERE email = ?",
                [$data['email']]
            );
            
            if ($existing) {
                return Response::error('Email already registered', 400);
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Create user
            $userId = $this->db->insert('users', [
                'email' => strtolower($data['email']),
                'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'role' => $data['member_type'] === 'admin' ? 'admin' : 'member',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$userId) {
                throw new Exception('Failed to create user');
            }
            
            // If member type, create member record
            if ($data['member_type'] === 'member') {
                // Generate member code
                $memberCode = $this->generateMemberCode();
                
                $memberId = $this->db->insert('members', [
                    'user_id' => $userId,
                    'member_code' => $memberCode,
                    'status' => 'active',
                    'joined_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$memberId) {
                    throw new Exception('Failed to create member record');
                }
                
                // Create savings record
                $this->db->insert('savings', [
                    'member_id' => $memberId,
                    'total_savings' => 0,
                    'interest_earned' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->log('user_registered', "New user registered: " . $data['email']);
            $this->db->commit();
            
            return Response::created('Registration successful', ['user_id' => $userId]);
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->handleDbError($e, 'Registration failed');
        }
    }
    
    /**
     * Login user with credentials
     */
    public function login($data)
    {
        try {
            // Validate input
            $errors = $this->validate($data, [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            // Check if user is locked
            if ($this->isUserLocked($data['email'])) {
                return Response::error('Account temporarily locked due to multiple failed login attempts', 429);
            }
            
            // Get user
            $user = $this->db->selectOne(
                "SELECT user_id, password_hash, role, status FROM users WHERE email = ? AND status = 'active'",
                [strtolower($data['email'])]
            );
            
            if (!$user) {
                $this->recordFailedAttempt($data['email']);
                return Response::error('Invalid email or password', 401);
            }
            
            // Verify password
            if (!password_verify($data['password'], $user['password_hash'])) {
                $this->recordFailedAttempt($data['email']);
                return Response::error('Invalid email or password', 401);
            }
            
            // Clear failed attempts
            $this->clearFailedAttempts($data['email']);
            
            // Create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = strtolower($data['email']);
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Get user details for response
            $userDetails = $this->db->selectOne(
                "SELECT user_id, email, first_name, last_name, phone, role FROM users WHERE user_id = ?",
                [$user['user_id']]
            );
            
            $this->log('user_login', "User logged in: " . $data['email']);
            
            return Response::success('Login successful', $userDetails);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Login failed');
        }
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        try {
            $email = $_SESSION['email'] ?? 'Unknown';
            
            // Clear session
            $_SESSION = [];
            
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
            
            $this->log('user_logout', "User logged out: " . $email);
            
            return Response::success('Logout successful');
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Logout failed');
        }
    }
    
    /**
     * Change password for logged-in user
     */
    public function changePassword($data)
    {
        try {
            // Validate input
            $errors = $this->validate($data, [
                'current_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|match:new_password'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            if (!isset($_SESSION['user_id'])) {
                return Response::unauthorized('Not logged in');
            }
            
            // Get user
            $user = $this->db->selectOne(
                "SELECT password_hash FROM users WHERE user_id = ?",
                [$_SESSION['user_id']]
            );
            
            if (!$user) {
                return Response::notFound('User not found');
            }
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password_hash'])) {
                return Response::error('Current password is incorrect', 400);
            }
            
            // Update password
            $updated = $this->db->update('users',
                [
                    'password_hash' => password_hash($data['new_password'], PASSWORD_BCRYPT, ['cost' => 12]),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                ['user_id' => $_SESSION['user_id']]
            );
            
            if (!$updated) {
                return Response::error('Failed to update password', 500);
            }
            
            $this->log('password_changed', "Password changed for user ID: " . $_SESSION['user_id']);
            
            return Response::success('Password changed successfully');
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to change password');
        }
    }
    
    /**
     * Reset password (forgot password flow)
     */
    public function resetPassword($data)
    {
        try {
            $errors = $this->validate($data, [
                'email' => 'required|email',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|match:new_password'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            // Get user
            $user = $this->db->selectOne(
                "SELECT user_id FROM users WHERE email = ? AND status = 'active'",
                [strtolower($data['email'])]
            );
            
            if (!$user) {
                // Don't reveal if user exists (security)
                return Response::success('If email exists, password reset link has been sent');
            }
            
            // Update password
            $updated = $this->db->update('users',
                [
                    'password_hash' => password_hash($data['new_password'], PASSWORD_BCRYPT, ['cost' => 12]),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                ['user_id' => $user['user_id']]
            );
            
            if (!$updated) {
                return Response::error('Failed to reset password', 500);
            }
            
            $this->log('password_reset', "Password reset for email: " . $data['email']);
            
            return Response::success('Password reset successfully');
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to reset password');
        }
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt($email)
    {
        $key = 'failed_attempts_' . md5(strtolower($email));
        $attempts = (int)($_SESSION[$key] ?? 0) + 1;
        $_SESSION[$key] = $attempts;
        $_SESSION[$key . '_time'] = time();
    }
    
    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts($email)
    {
        $key = 'failed_attempts_' . md5(strtolower($email));
        unset($_SESSION[$key]);
        unset($_SESSION[$key . '_time']);
    }
    
    /**
     * Check if user account is locked
     */
    private function isUserLocked($email)
    {
        $key = 'failed_attempts_' . md5(strtolower($email));
        $attempts = (int)($_SESSION[$key] ?? 0);
        $lastAttemptTime = (int)($_SESSION[$key . '_time'] ?? 0);
        
        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $timeSinceLastAttempt = time() - $lastAttemptTime;
            if ($timeSinceLastAttempt < self::LOCKOUT_DURATION) {
                return true;
            } else {
                // Lockout period expired, clear attempts
                $this->clearFailedAttempts($email);
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Generate member code
     */
    private function generateMemberCode()
    {
        $lastCode = $this->db->selectValue(
            "SELECT MAX(CAST(SUBSTRING(member_code, 4) AS UNSIGNED)) FROM members WHERE member_code LIKE 'MEM%'"
        );
        
        $nextNumber = ($lastCode ?? 0) + 1;
        return 'MEM' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
