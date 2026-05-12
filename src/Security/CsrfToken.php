<?php

namespace Security;

/**
 * CsrfToken
 * CSRF (Cross-Site Request Forgery) protection
 * Generates and validates tokens for form submissions
 */
class CsrfToken
{
    const TOKEN_KEY = '_csrf_token';
    const TOKEN_NAME = '_token';
    
    /**
     * Generate CSRF token for form
     */
    public static function generate()
    {
        // Generate new token if not exists
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[self::TOKEN_KEY];
    }
    
    /**
     * Get HTML input field with token
     */
    public static function field()
    {
        $token = self::generate();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::TOKEN_NAME,
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
    
    /**
     * Validate CSRF token from POST/request
     * 
     * Usage:
     *   if (!CsrfToken::validate($_POST['_token'])) {
     *       throw new \Exception('CSRF token mismatch');
     *   }
     */
    public static function validate($token)
    {
        // Ensure session started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get stored token
        $storedToken = $_SESSION[self::TOKEN_KEY] ?? null;
        
        // Token must exist and match
        if (!$storedToken || !$token) {
            return false;
        }
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($storedToken, $token);
    }
    
    /**
     * Validate token from any source (POST, GET, custom)
     */
    public static function validateFromRequest($fieldName = self::TOKEN_NAME)
    {
        $token = $_POST[$fieldName] ?? $_GET[$fieldName] ?? null;
        
        if (!$token) {
            return false;
        }
        
        return self::validate($token);
    }
    
    /**
     * Regenerate token (after form submission for example)
     */
    public static function regenerate()
    {
        unset($_SESSION[self::TOKEN_KEY]);
        return self::generate();
    }
    
    /**
     * Get token field name
     */
    public static function getTokenName()
    {
        return self::TOKEN_NAME;
    }
}
