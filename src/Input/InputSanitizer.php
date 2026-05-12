<?php

namespace Input;

/**
 * InputSanitizer
 * Comprehensive input validation and sanitization
 * Prevents XSS, SQL injection, and other attacks
 */
class InputSanitizer
{
    /**
     * Sanitize input by type
     * 
     * Types: 'string', 'email', 'int', 'float', 'url', 'html'
     */
    public static function sanitize($value, $type = 'string')
    {
        // Remove BOM if present
        if (is_string($value)) {
            $value = \preg_replace("/\x{FEFF}/u", '', $value);
        }
        
        switch ($type) {
            case 'string':
                return self::sanitizeString($value);
            case 'email':
                return self::sanitizeEmail($value);
            case 'int':
            case 'integer':
                return self::sanitizeInteger($value);
            case 'float':
            case 'decimal':
                return self::sanitizeFloat($value);
            case 'url':
                return self::sanitizeUrl($value);
            case 'html':
                return self::sanitizeHtml($value);
            case 'phone':
                return self::sanitizePhone($value);
            case 'aadhar':
                return self::sanitizeAadhar($value);
            case 'pan':
                return self::sanitizePan($value);
            default:
                return self::sanitizeString($value);
        }
    }
    
    /**
     * Sanitize string (XSS protection)
     * Strips tags and escapes HTML entities
     */
    private static function sanitizeString($value)
    {
        if (!is_string($value)) {
            return '';
        }
        
        // Trim whitespace
        $value = trim($value);
        
        // Strip tags
        $value = strip_tags($value);
        
        // Escape HTML
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
    
    /**
     * Sanitize email
     */
    private static function sanitizeEmail($value)
    {
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
    }
    
    /**
     * Sanitize integer
     */
    private static function sanitizeInteger($value)
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float/decimal
     */
    private static function sanitizeFloat($value)
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, 
            FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Sanitize URL
     */
    private static function sanitizeUrl($value)
    {
        $value = filter_var($value, FILTER_SANITIZE_URL);
        return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
    }
    
    /**
     * Sanitize HTML (keep safe tags)
     */
    private static function sanitizeHtml($value)
    {
        // Allow only safe HTML tags
        $allowed = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img>';
        return strip_tags($value, $allowed);
    }
    
    /**
     * Sanitize phone number (Indian format)
     */
    private static function sanitizePhone($value)
    {
        // Remove all non-digits
        $value = preg_replace('/\D/', '', $value);
        
        // Return only if exactly 10 digits
        if (strlen($value) === 10) {
            return $value;
        }
        
        return '';
    }
    
    /**
     * Sanitize Aadhar number
     */
    private static function sanitizeAadhar($value)
    {
        // Remove all non-digits and hyphens
        $value = preg_replace('/[^0-9\-]/', '', $value);
        
        // Validate format: XXXX-XXXX-XXXX
        if (preg_match('/^\d{4}-\d{4}-\d{4}$/', $value)) {
            return $value;
        }
        
        return '';
    }
    
    /**
     * Sanitize PAN number
     */
    private static function sanitizePan($value)
    {
        // Convert to uppercase
        $value = strtoupper(trim($value));
        
        // Remove spaces
        $value = str_replace(' ', '', $value);
        
        // Validate PAN format: AAAAA9999A
        if (preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $value)) {
            return $value;
        }
        
        return '';
    }
    
    /**
     * Batch sanitize array of inputs
     * 
     * Usage:
     *   $rules = [
     *       'email' => 'email',
     *       'amount' => 'float',
     *       'name' => 'string'
     *   ];
     *   $clean = InputSanitizer::sanitizeBatch($_POST, $rules);
     */
    public static function sanitizeBatch($data, $rules)
    {
        $sanitized = [];
        
        foreach ($rules as $field => $type) {
            $value = $data[$field] ?? null;
            $sanitized[$field] = self::sanitize($value, $type);
        }
        
        return $sanitized;
    }
    
    /**
     * Escape output for HTML context
     */
    public static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Escape output for HTML attributes
     */
    public static function escapeAttribute($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Escape output for JavaScript context
     */
    public static function escapeJs($value)
    {
        return json_encode($value);
    }
    
    /**
     * Check if input contains potential SQL injection
     */
    public static function detectSqlInjection($value)
    {
        $patterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bor\b.*=.*)/i',
            '/(\b(and|or)\b.*=)/i',
            '/(--|#|\/\*|\*\/)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\b(table|database)\b)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if input contains potential XSS
     */
    public static function detectXss($value)
    {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<object[^>]*>/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }
}
