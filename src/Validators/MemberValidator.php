<?php

namespace Validators;

/**
 * MemberValidator
 * Validation rules for member-related operations
 */
class MemberValidator extends BaseValidator
{
    /**
     * Validate member registration
     */
    public static function validateCreate($data)
    {
        $errors = [];
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!self::validateEmail($data['email'])) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Phone validation
        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone is required';
        } elseif (!self::validatePhone($data['phone'])) {
            $errors['phone'] = 'Phone must be 10 digits';
        }
        
        // First name validation
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        } elseif (!self::validateStringLength($data['first_name'], 2, 50)) {
            $errors['first_name'] = 'First name must be between 2-50 characters';
        }
        
        // Last name validation
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        } elseif (!self::validateStringLength($data['last_name'], 2, 50)) {
            $errors['last_name'] = 'Last name must be between 2-50 characters';
        }
        
        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!self::validateStringLength($data['password'], 6, 255)) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        return $errors;
    }
    
    /**
     * Validate member update
     */
    public static function validateUpdate($data)
    {
        $errors = [];
        
        // Email validation
        if (!empty($data['email']) && !self::validateEmail($data['email'])) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Phone validation
        if (!empty($data['phone']) && !self::validatePhone($data['phone'])) {
            $errors['phone'] = 'Phone must be 10 digits';
        }
        
        // First name validation
        if (!empty($data['first_name']) && !self::validateStringLength($data['first_name'], 2, 50)) {
            $errors['first_name'] = 'First name must be between 2-50 characters';
        }
        
        // Last name validation
        if (!empty($data['last_name']) && !self::validateStringLength($data['last_name'], 2, 50)) {
            $errors['last_name'] = 'Last name must be between 2-50 characters';
        }
        
        return $errors;
    }
    
    /**
     * Validate member status
     */
    public static function validateStatus($status)
    {
        return self::validateInArray($status, ['active', 'inactive', 'suspended']);
    }
}
