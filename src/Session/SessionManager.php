<?php

namespace Session;

/**
 * SessionManager
 * Centralized session handling with security features
 * Prevents direct $_SESSION access throughout application
 * Implements session validation, expiry, and regeneration
 */
class SessionManager
{
    const SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    const ACTIVITY_KEY = '_activity_time';
    const REGENERATE_KEY = '_regenerate_requested';
    
    /**
     * Initialize session with security settings
     */
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Validate existing session
        self::validateSession();
    }
    
    /**
     * Set session value (secure)
     */
    public static function set($key, $value)
    {
        self::ensureSession();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value with optional default
     */
    public static function get($key, $default = null)
    {
        self::ensureSession();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has($key)
    {
        self::ensureSession();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public static function remove($key)
    {
        self::ensureSession();
        unset($_SESSION[$key]);
    }
    
    /**
     * Set flash message (shown once then removed)
     */
    public static function setFlash($key, $message, $type = 'info')
    {
        self::ensureSession();
        $_SESSION['_flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }
    
    /**
     * Get and remove flash message
     */
    public static function getFlash($key, $default = null)
    {
        self::ensureSession();
        
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Check if flash message exists
     */
    public static function hasFlash($key)
    {
        self::ensureSession();
        return isset($_SESSION['_flash'][$key]);
    }
    
    /**
     * Create user session after login
     */
    public static function createUserSession($userId, $role, $email, $name)
    {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set user data
        self::set('user_id', $userId);
        self::set('role', $role);
        self::set('email', $email);
        self::set('name', $name);
        self::set(self::ACTIVITY_KEY, time());
        
        // Log session creation
        \logEvent('SESSION_CREATED', "User {$userId} logged in");
    }
    
    /**
     * Destroy user session on logout
     */
    public static function destroyUserSession()
    {
        $userId = self::get('user_id');
        
        // Log session destruction
        if ($userId) {
            \logEvent('SESSION_DESTROYED', "User {$userId} logged out");
        }
        
        // Clear all session data
        $_SESSION = [];
        
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        self::ensureSession();
        return self::has('user_id') && self::has('role');
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        self::ensureSession();
        return self::get('role') === $role;
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        return self::hasRole('admin');
    }
    
    /**
     * Check if user is member
     */
    public static function isMember()
    {
        return self::hasRole('member');
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId()
    {
        return self::get('user_id');
    }
    
    /**
     * Get current user role
     */
    public static function getUserRole()
    {
        return self::get('role');
    }
    
    /**
     * Update activity timestamp
     */
    public static function updateActivity()
    {
        self::ensureSession();
        self::set(self::ACTIVITY_KEY, time());
    }
    
    /**
     * Validate session (check expiry and regeneration)
     */
    private static function validateSession()
    {
        self::ensureSession();
        
        // Skip validation if not logged in
        if (!self::isLoggedIn()) {
            return;
        }
        
        // Check session timeout
        $lastActivity = self::get(self::ACTIVITY_KEY);
        if ($lastActivity && (time() - $lastActivity) > self::SESSION_TIMEOUT) {
            self::destroyUserSession();
            return;
        }
        
        // Update activity
        self::updateActivity();
        
        // Check if regeneration was requested
        if (self::get(self::REGENERATE_KEY)) {
            session_regenerate_id(true);
            self::remove(self::REGENERATE_KEY);
        }
    }
    
    /**
     * Request session regeneration (security feature)
     */
    public static function requestRegeneration()
    {
        self::ensureSession();
        self::set(self::REGENERATE_KEY, true);
    }
    
    /**
     * Ensure session is started
     */
    private static function ensureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Get all session data (for debugging)
     */
    public static function getAll()
    {
        self::ensureSession();
        // Don't expose internal keys
        $data = $_SESSION;
        unset($data[self::ACTIVITY_KEY]);
        unset($data[self::REGENERATE_KEY]);
        return $data;
    }
}
