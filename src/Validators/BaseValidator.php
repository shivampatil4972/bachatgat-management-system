<?php

namespace Validators;

/**
 * BaseValidator
 * Base class for all validators with common validation methods
 */
class BaseValidator
{
    /**
     * Validate email format
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (10 digits for India)
     */
    public static function validatePhone($phone)
    {
        return preg_match('/^[0-9]{10}$/', $phone) === 1;
    }
    
    /**
     * Validate numeric value
     */
    public static function validateNumeric($value, $min = null, $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate string length
     */
    public static function validateStringLength($value, $min = null, $max = null)
    {
        $length = strlen($value);
        
        if ($min !== null && $length < $min) {
            return false;
        }
        
        if ($max !== null && $length > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate date format
     */
    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate required field
     */
    public static function validateRequired($value)
    {
        return !empty($value);
    }
    
    /**
     * Validate field in array
     */
    public static function validateInArray($value, $array)
    {
        return in_array($value, $array, true);
    }
    
    /**
     * Validate minimum value
     */
    public static function validateMin($value, $min)
    {
        return $value >= $min;
    }
    
    /**
     * Validate maximum value
     */
    public static function validateMax($value, $max)
    {
        return $value <= $max;
    }
}
