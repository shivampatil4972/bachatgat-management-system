<?php
/**
 * Session Management
 * Bachat Gat Smart Management System
 * 
 * Handles session-based authentication and security
 * 
 * @author Your Name
 * @version 1.0
 */

/**
 * Initialize user session after login
 */
function initUserSession($user, $member = null) {
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set user session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['profile_image'] = $user['profile_image'];
    $_SESSION['status'] = $user['status'];
    $_SESSION['login_time'] = time();
    $_SESSION['LAST_ACTIVITY'] = time();
    
    // Set member-specific session variables
    if ($member) {
        $_SESSION['member_id'] = $member['member_id'];
        $_SESSION['member_code'] = $member['member_code'];
    }
    
    // Log login activity
    logActivity($user['user_id'], 'User Login', 'User logged in successfully');
}

/**
 * Destroy user session (logout)
 */
function destroyUserSession() {
    // Log logout activity before destroying session
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'User Logout', 'User logged out');
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Check if user is authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('Please login to continue.', 'warning');
        redirect(BASE_URL . 'auth/login.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        setFlashMessage('Access denied. Admin privileges required.', 'error');
        redirect(BASE_URL . 'member/dashboard.php');
    }
}

/**
 * Require member role
 */
function requireMember() {
    requireLogin();
    
    if (!isMember()) {
        setFlashMessage('Access denied. Member privileges required.', 'error');
        redirect(BASE_URL . 'admin/dashboard.php');
    }
}

/**
 * Check if user is already logged in and redirect
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            redirect(BASE_URL . 'admin/dashboard.php');
        } else {
            redirect(BASE_URL . 'member/dashboard.php');
        }
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->selectOne(
        "SELECT * FROM users WHERE user_id = ?",
        [$_SESSION['user_id']]
    );
}

/**
 * Get current member data
 */
function getCurrentMember() {
    if (!isLoggedIn() || !isMember()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->selectOne(
        "SELECT * FROM members WHERE user_id = ?",
        [$_SESSION['user_id']]
    );
}

/**
 * Update session data
 */
function updateSessionData($key, $value) {
    if (isLoggedIn()) {
        $_SESSION[$key] = $value;
    }
}

/**
 * Get session data
 */
function getSessionData($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
            destroyUserSession();
            setFlashMessage('Your session has expired. Please login again.', 'warning');
            redirect(BASE_URL . 'auth/login.php?timeout=1');
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

/**
 * Prevent session fixation
 */
function preventSessionFixation() {
    if (isLoggedIn() && !isset($_SESSION['SESSION_CREATED'])) {
        session_regenerate_id(true);
        $_SESSION['SESSION_CREATED'] = time();
    } elseif (isset($_SESSION['SESSION_CREATED']) && time() - $_SESSION['SESSION_CREATED'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['SESSION_CREATED'] = time();
    }
}

/**
 * Check for multiple login attempts
 */
function checkLoginAttempts($email) {
    $db = Database::getInstance();
    
    // Check if user has exceeded login attempts
    $attempts = $db->selectValue(
        "SELECT COUNT(*) FROM activity_logs 
         WHERE action = 'Failed Login' 
         AND description LIKE ? 
         AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)",
        ['%' . $email . '%', LOGIN_LOCKOUT_DURATION]
    );
    
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        return [
            'locked' => true,
            'attempts' => $attempts,
            'message' => 'Too many failed login attempts. Please try again after ' . (LOGIN_LOCKOUT_DURATION / 60) . ' minutes.'
        ];
    }
    
    return [
        'locked' => false,
        'attempts' => $attempts,
        'remaining' => MAX_LOGIN_ATTEMPTS - $attempts
    ];
}

/**
 * Log failed login attempt
 */
function logFailedLogin($email) {
    $db = Database::getInstance();
    $db->insert(
        "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
        [null, 'Failed Login', 'Failed login attempt for: ' . $email, getIpAddress(), getUserAgent()]
    );
}

/**
 * Remember me functionality
 */
function setRememberMeCookie($userId, $token) {
    // Hash token before storing
    $hashedToken = hash('sha256', $token);
    
    // Store in database (ignore if table doesn't exist)
    try {
        $db = Database::getInstance();
        $db->insert(
            "INSERT INTO user_tokens (user_id, token, type, expires_at) VALUES (?, ?, 'remember_me', DATE_ADD(NOW(), INTERVAL 30 DAY))",
            [$userId, $hashedToken]
        );
    } catch (Exception $e) {
        // user_tokens table may not exist — ignore
    }
    
    // Set cookie (30 days)
    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
}

/**
 * Check remember me cookie
 */
function checkRememberMeCookie() {
    if (isset($_COOKIE['remember_token']) && !isLoggedIn()) {
        try {
            $token = $_COOKIE['remember_token'];
            $hashedToken = hash('sha256', $token);
            
            $db = Database::getInstance();
            $userToken = $db->selectOne(
                "SELECT user_id FROM user_tokens 
                 WHERE token = ? 
                 AND type = 'remember_me' 
                 AND expires_at > NOW()",
                [$hashedToken]
            );
            
            if ($userToken) {
                $user = $db->selectOne(
                    "SELECT u.*, m.member_id, m.member_code 
                     FROM users u 
                     LEFT JOIN members m ON u.user_id = m.user_id 
                     WHERE u.user_id = ? AND u.status = 'active'",
                    [$userToken['user_id']]
                );
                
                if ($user) {
                    $member = $user['member_id'] ? [
                        'member_id' => $user['member_id'],
                        'member_code' => $user['member_code']
                    ] : null;
                    
                    initUserSession($user, $member);
                    return true;
                }
            }
        } catch (Exception $e) {
            // user_tokens table may not exist — ignore
        }
    }
    return false;
}

/**
 * Clear remember me cookie
 */
function clearRememberMeCookie() {
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $token);
        
        // Delete from database (ignore if table doesn't exist)
        try {
            $db = Database::getInstance();
            $db->delete("DELETE FROM user_tokens WHERE token = ?", [$hashedToken]);
        } catch (Exception $e) {
            // user_tokens table may not exist — ignore
        }
        
        // Delete cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}

/**
 * Get login redirect URL based on role
 */
function getLoginRedirectUrl() {
    if (isAdmin()) {
        return BASE_URL . 'admin/dashboard.php';
    } elseif (isMember()) {
        return BASE_URL . 'member/dashboard.php';
    }
    return BASE_URL;
}

/**
 * Session security check
 */
function sessionSecurityCheck() {
    // Check session timeout
    checkSessionTimeout();
    
    // Prevent session fixation
    preventSessionFixation();
    
    // Check user status
    if (isLoggedIn()) {
        $user = getCurrentUser();
        if (!$user || $user['status'] !== 'active') {
            destroyUserSession();
            setFlashMessage('Your account has been deactivated. Please contact administrator.', 'error');
            redirect(BASE_URL . 'auth/login.php');
        }
    }
}

// Run security check on every page load
sessionSecurityCheck();

?>
